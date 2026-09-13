<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\ThesisOrderRequest;
use App\Mail\OrderReceivedMail;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminNotificationService;
use App\Services\ClientResolver;
use App\Services\IdempotencyService;
use App\Services\InvoiceService;
use App\Services\PayuService;
use App\Services\PdfUploadService;
use App\Services\ProductPricingService;
use App\Services\ThesisPricingService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $pricing,
        private readonly ClientResolver $clients,
        private readonly IdempotencyService $idempotency,
    ) {}

    public function store(CheckoutRequest $request, InvoiceService $invoiceService, PayuService $payu, AdminNotificationService $notifications): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $idempotencyKey = $this->idempotency->key($request);
        $fingerprint = $this->idempotency->fingerprint('checkout', $data);

        if ($existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first()) {
            return $this->replayOrder($request, $existingOrder, $fingerprint);
        }

        $shippingTotal = match ($data['shipping_method']) {
            'parcel' => (float) config('business.shipping.parcel'),
            'courier' => (float) config('business.shipping.courier'),
            default => (float) config('business.shipping.pickup'),
        };
        $successToken = Str::random(64);

        try {
            [$order, $payment] = DB::transaction(function () use ($data, $shippingTotal, $invoiceService, $successToken, $idempotencyKey, $fingerprint): array {
                $customer = $data['customer'];
                $client = $this->clients->resolve($customer, (bool) ($data['marketing_consent'] ?? false));

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
                    'idempotency_fingerprint' => $fingerprint,
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

                $payment = $order->payments()->create([
                    'provider' => config('payment.provider'),
                    'status' => 'pending',
                    'amount' => $order->total,
                    'currency' => $order->currency,
                ]);

                return [$order, $payment];
            }, attempts: 3);
        } catch (QueryException|ValidationException $exception) {
            $existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first();

            if ($existingOrder) {
                return $this->replayOrder($request, $existingOrder, $fingerprint);
            }

            throw $exception;
        }

        $notifications->orderCreated($order);

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
        AdminNotificationService $notifications,
    ): JsonResponse|RedirectResponse {
        $data = $request->validated();
        $idempotencyKey = $this->idempotency->key($request);
        $fingerprint = $this->idempotency->fingerprint('thesis', $data);

        if ($existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first()) {
            return $this->replayOrder($request, $existingOrder, $fingerprint);
        }

        $successToken = Str::random(64);

        try {
            [$order, $payment] = DB::transaction(function () use ($data, $invoiceService, $successToken, $idempotencyKey, $fingerprint, $uploads, $thesisPricing): array {
                $customer = $data['customer'];
                $file = $uploads->findTemporaryForUpdate($data['upload_token']);
                $pricing = $thesisPricing->calculate($file, $data);
                $client = $this->clients->resolve($customer, (bool) ($data['marketing_consent'] ?? false));

                $product = Product::query()->active()->where('slug', 'praca-dyplomowa')->firstOrFail();
                $order = Order::create([
                    'number' => $this->orderNumber(),
                    'success_token_hash' => hash('sha256', $successToken),
                    'idempotency_key' => $idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
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

                $payment = $order->payments()->create([
                    'provider' => config('payment.provider'),
                    'status' => 'pending',
                    'amount' => $order->total,
                    'currency' => $order->currency,
                ]);

                return [$order, $payment];
            }, attempts: 3);
        } catch (QueryException|ValidationException $exception) {
            $existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first();

            if ($existingOrder) {
                return $this->replayOrder($request, $existingOrder, $fingerprint);
            }

            throw $exception;
        }

        $notifications->orderCreated($order);

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

    private function replayOrder(Request $request, Order $order, string $fingerprint): JsonResponse|RedirectResponse
    {
        if (! $this->idempotency->matches($order->idempotency_fingerprint, $fingerprint)) {
            return response()->json(['message' => 'Ten Idempotency-Key został już użyty dla innego żądania.'], 409);
        }

        $payment = $order->payments()->latest('id')->first();
        $paymentUrl = is_array($payment?->payload) ? ($payment->payload['redirect_uri'] ?? null) : null;

        return $this->paymentResponse($request, $order, is_string($paymentUrl) ? $paymentUrl : null, false);
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
