<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ThesisOrderRequest;
use App\Mail\OrderReceivedMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\InvoiceService;
use App\Services\PayuService;
use App\Services\PdfUploadService;
use App\Services\ProductPricingService;
use App\Services\ThesisPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(private readonly ProductPricingService $pricing) {}

    public function store(CheckoutRequest $request, InvoiceService $invoiceService, PayuService $payu): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $customer = $data['customer'];
        $idempotencyKey = $request->header('Idempotency-Key');

        if (is_string($idempotencyKey) && strlen($idempotencyKey) > 100) {
            return response()->json(['message' => 'Idempotency-Key jest zbyt długi.'], 422);
        }

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first();

            if ($existingOrder) {
                $existingPayment = $existingOrder->payments()->latest('id')->first();
                $paymentUrl = is_array($existingPayment?->payload)
                    ? ($existingPayment->payload['redirect_uri'] ?? null)
                    : null;

                return $this->paymentResponse($request, $existingOrder, is_string($paymentUrl) ? $paymentUrl : null, false);
            }
        }

        $shippingTotal = match ($data['shipping_method']) {
            'parcel' => (float) config('business.shipping.parcel'),
            'courier' => (float) config('business.shipping.courier'),
            default => (float) config('business.shipping.pickup'),
        };
        $successToken = Str::random(64);

        $order = DB::transaction(function () use ($data, $customer, $shippingTotal, $invoiceService, $successToken, $idempotencyKey): Order {
            $client = Client::query()->firstOrNew(['email' => $customer['email']]);
            $client->fill([
                'name' => $customer['name'],
                'phone' => $customer['phone'] ?? null,
                'company' => $customer['company'] ?? null,
                'nip' => $customer['nip'] ?? null,
                'privacy_policy_version' => config('privacy.policy_version'),
                'privacy_policy_accepted_at' => now(),
                'retention_until' => now()->addDays((int) config('privacy.client_retention_days')),
            ]);
            if (! $client->exists || ($data['marketing_consent'] ?? false)) {
                $client->marketing_consent = (bool) ($data['marketing_consent'] ?? false);
                $client->marketing_consent_at = $client->marketing_consent ? now() : null;
            }
            $client->save();

            $items = [];
            $subtotal = 0.00;

            foreach ($data['items'] as $item) {
                $product = Product::query()->active()->where('slug', $item['product_slug'])->firstOrFail();
                $pricing = $this->pricing->calculate($product, $item['option_value_ids'] ?? []);
                $lineTotal = round($pricing['unit_price'] * $item['quantity'], 2);
                $subtotal += $lineTotal;
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'configuration' => [
                        'selected_options' => $pricing['configuration'],
                        'customer_configuration' => $item['configuration'] ?? [],
                    ],
                    'unit_price' => $pricing['unit_price'],
                    'quantity' => $item['quantity'],
                    'total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'number' => $this->orderNumber(),
                'success_token_hash' => hash('sha256', $successToken),
                'idempotency_key' => $idempotencyKey,
                'client_id' => $client->id,
                'status' => OrderStatus::Pending,
                'payment_status' => 'pending',
                'currency' => config('business.currency'),
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'] ?? null,
                'customer_company' => $customer['company'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'shipping_method' => $data['shipping_method'],
                'shipping_address' => $data['shipping_address'] ?? null,
                'requested_by_date' => $data['requested_by_date'] ?? null,
                'subtotal' => $subtotal,
                'shipping_total' => $shippingTotal,
                'tax_rate' => config('business.tax_rate'),
                'invoice_required' => (bool) ($data['invoice_required'] ?? false),
                'invoice_nip' => $customer['nip'] ?? null,
                'total' => $subtotal + $shippingTotal,
                'notes' => $customer['notes'] ?? null,
            ]);
            $order->items()->createMany($items);
            $order->statusHistories()->create(['to_status' => OrderStatus::Pending, 'changed_by' => null]);

            if ($order->invoice_required) {
                $invoiceService->createForOrder($order);
            }

            return $order;
        });

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => config('payment.provider'),
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        try {
            $paymentUrl = $payu->createPayment(
                $order->load('items'),
                $payment,
                route('checkout.success', ['token' => $successToken]),
                route('api.payments.payu.notify'),
                $request->ip(),
            );
            $order->transitionTo(OrderStatus::PaymentAwaited);
            Mail::to($order->customer_email)->queue(new OrderReceivedMail($order->refresh()));
        } catch (Throwable $exception) {
            $payment->forceFill(['status' => 'failed'])->save();
            report($exception);

            return response()->json(['message' => 'Nie udało się rozpocząć płatności. Spróbuj ponownie.'], 503);
        }

        return $this->paymentResponse($request, $order->load('items'), $paymentUrl, true);
    }

    public function thesis(
        ThesisOrderRequest $request,
        InvoiceService $invoiceService,
        PayuService $payu,
        PdfUploadService $uploads,
        ThesisPricingService $thesisPricing,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $idempotencyKey = $request->header('Idempotency-Key');

        if (is_string($idempotencyKey) && strlen($idempotencyKey) > 100) {
            return response()->json(['message' => 'Idempotency-Key jest zbyt długi.'], 422);
        }

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first();

            if ($existingOrder) {
                $existingPayment = $existingOrder->payments()->latest('id')->first();
                $paymentUrl = is_array($existingPayment?->payload)
                    ? ($existingPayment->payload['redirect_uri'] ?? null)
                    : null;

                return $this->paymentResponse($request, $existingOrder, is_string($paymentUrl) ? $paymentUrl : null, false);
            }
        }

        $customer = $data['customer'];
        $successToken = Str::random(64);

        $order = DB::transaction(function () use ($data, $customer, $invoiceService, $successToken, $idempotencyKey, $uploads, $thesisPricing): Order {
            $file = $uploads->findTemporaryForUpdate($data['upload_token']);
            $pricing = $thesisPricing->calculate($file, $data);
            $client = Client::query()->firstOrNew(['email' => $customer['email']]);
            $client->fill([
                'name' => $customer['name'],
                'phone' => $customer['phone'] ?? null,
                'company' => $customer['company'] ?? null,
                'nip' => $customer['nip'] ?? null,
                'privacy_policy_version' => config('privacy.policy_version'),
                'privacy_policy_accepted_at' => now(),
                'retention_until' => now()->addDays((int) config('privacy.client_retention_days')),
            ]);
            if (! $client->exists || ($data['marketing_consent'] ?? false)) {
                $client->marketing_consent = (bool) ($data['marketing_consent'] ?? false);
                $client->marketing_consent_at = $client->marketing_consent ? now() : null;
            }
            $client->save();

            $product = Product::query()->active()->where('slug', 'praca-dyplomowa')->firstOrFail();
            $order = Order::create([
                'number' => $this->orderNumber(),
                'success_token_hash' => hash('sha256', $successToken),
                'idempotency_key' => $idempotencyKey,
                'client_id' => $client->id,
                'status' => OrderStatus::Pending,
                'payment_status' => 'pending',
                'currency' => config('business.currency'),
                'customer_name' => $customer['name'],
                'customer_email' => $customer['email'],
                'customer_phone' => $customer['phone'] ?? null,
                'customer_company' => $customer['company'] ?? null,
                'shipping_method' => $data['shipping_method'],
                'shipping_address' => $data['shipping_address'] ?? null,
                'requested_by_date' => $data['requested_by_date'] ?? null,
                'subtotal' => $pricing['subtotal'],
                'shipping_total' => $pricing['shipping_total'],
                'tax_rate' => config('business.tax_rate'),
                'invoice_required' => (bool) ($data['invoice_required'] ?? false),
                'invoice_nip' => $customer['nip'] ?? null,
                'total' => $pricing['total'],
                'notes' => $customer['notes'] ?? null,
            ]);
            $item = $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'configuration' => [
                    'file' => [
                        'name' => $file->original_name,
                        'pages' => $file->pages,
                        'sha256' => $file->sha256,
                    ],
                    'customer_configuration' => $pricing['configuration'],
                ],
                'unit_price' => $pricing['unit_price'],
                'quantity' => $data['copies'],
                'total' => $pricing['subtotal'],
            ]);
            $uploads->attach($file, $order, $item);
            $order->statusHistories()->create(['to_status' => OrderStatus::Pending, 'changed_by' => null]);

            if ($order->invoice_required) {
                $invoiceService->createForOrder($order);
            }

            return $order;
        });

        $payment = Payment::create([
            'order_id' => $order->id,
            'provider' => config('payment.provider'),
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        try {
            $paymentUrl = $payu->createPayment(
                $order->load('items'),
                $payment,
                route('checkout.success', ['token' => $successToken]),
                route('api.payments.payu.notify'),
                $request->ip(),
            );
            $order->transitionTo(OrderStatus::PaymentAwaited);
            Mail::to($order->customer_email)->queue(new OrderReceivedMail($order->refresh()));
        } catch (Throwable $exception) {
            $payment->forceFill(['status' => 'failed'])->save();
            report($exception);

            return response()->json(['message' => 'Nie udało się rozpocząć płatności. Spróbuj ponownie.'], 503);
        }

        return $this->paymentResponse($request, $order->load('items'), $paymentUrl, true);
    }

    public function success(string $token): View
    {
        $order = Order::query()->where('success_token_hash', hash('sha256', $token))->firstOrFail();

        return view('checkout.success', compact('order'));
    }

    private function paymentResponse(Request $request, Order $order, ?string $paymentUrl, bool $created): JsonResponse|RedirectResponse
    {
        if (! $paymentUrl) {
            return response()->json(['message' => 'Płatność dla tego zamówienia nie jest dostępna.'], 409);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $created ? 'Zamówienie zostało przyjęte.' : 'Zwrócono istniejącą płatność.',
                'payment_url' => $paymentUrl,
                'order' => $order,
            ], $created ? 201 : 200);
        }

        return redirect()->away($paymentUrl);
    }

    private function orderNumber(): string
    {
        do {
            $number = 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
