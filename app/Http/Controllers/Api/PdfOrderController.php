<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\PdfOrderRequest;
use App\Http\Requests\PdfQuoteRequest;
use App\Mail\OrderReceivedMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Services\AdminNotificationService;
use App\Services\InvoiceService;
use App\Services\PayuService;
use App\Services\PdfPricingService;
use App\Services\PdfUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PdfOrderController extends Controller
{
    public function quote(PdfQuoteRequest $request, PdfUploadService $uploads, PdfPricingService $pricing): JsonResponse
    {
        $data = $request->validated();

        return response()->json(['quote' => $pricing->calculate($uploads->findTemporary($data['upload_token']), $data)]);
    }

    public function store(
        PdfOrderRequest $request,
        PdfUploadService $uploads,
        PdfPricingService $pricing,
        InvoiceService $invoiceService,
        PayuService $payu,
        AdminNotificationService $notifications,
    ): JsonResponse {
        $data = $request->validated();
        $idempotencyKey = $request->header('Idempotency-Key');

        if (is_string($idempotencyKey) && strlen($idempotencyKey) > 100) {
            return response()->json(['message' => 'Idempotency-Key jest zbyt długi.'], 422);
        }

        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $existingOrder = Order::query()->with('items')->where('idempotency_key', $idempotencyKey)->first();
            if ($existingOrder) {
                $payment = $existingOrder->payments()->latest('id')->first();
                $paymentUrl = is_array($payment?->payload) ? ($payment->payload['redirect_uri'] ?? null) : null;

                if (is_string($paymentUrl) && $paymentUrl !== '') {
                    return response()->json(['message' => 'Zwrócono istniejącą płatność.', 'payment_url' => $paymentUrl, 'order' => $existingOrder], 200);
                }

                return response()->json(['message' => 'Płatność dla tego zamówienia nie jest obecnie dostępna.'], 409);
            }
        }

        $file = $uploads->findTemporaryForUpdate($data['upload_token']);
        $quote = $pricing->calculate($file, $data);
        $successToken = Str::random(64);
        $product = Product::query()->active()->where('slug', 'druk')->firstOrFail();

        $order = DB::transaction(function () use ($data, $file, $quote, $invoiceService, $successToken, $idempotencyKey, $product): Order {
            $customer = $data['customer'];
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
                'subtotal' => $quote['subtotal'],
                'shipping_total' => $quote['shipping_total'],
                'tax_rate' => config('business.tax_rate'),
                'invoice_required' => (bool) ($data['invoice_required'] ?? false),
                'invoice_nip' => $customer['nip'] ?? null,
                'total' => $quote['total'],
                'notes' => $customer['notes'] ?? null,
            ]);
            $item = $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_slug' => $product->slug,
                'configuration' => ['file' => ['name' => $file->original_name, 'pages' => $file->pages, 'sha256' => $file->sha256], 'pricing' => $quote['configuration']],
                'unit_price' => $quote['subtotal'],
                'quantity' => 1,
                'total' => $quote['subtotal'],
            ]);
            $file->forceFill(['order_id' => $order->id, 'order_item_id' => $item->id, 'status' => 'attached', 'expires_at' => null])->save();
            $order->statusHistories()->create(['to_status' => OrderStatus::Pending, 'changed_by' => null]);

            if ($order->invoice_required) {
                $invoiceService->createForOrder($order);
            }

            return $order;
        });

        $notifications->orderCreated($order);

        $payment = $order->payments()->create([
            'provider' => config('payment.provider'),
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => $order->currency,
        ]);

        try {
            $paymentUrl = $payu->createPayment($order->load('items'), $payment, route('checkout.success', ['token' => $successToken]), route('api.payments.payu.notify'), $request->ip());
            $order->transitionTo(OrderStatus::PaymentAwaited);
            Mail::to($order->customer_email)->queue(new OrderReceivedMail($order->refresh()));

            return response()->json(['message' => 'Zamówienie zostało przyjęte.', 'payment_url' => $paymentUrl, 'order' => $order->load('items')], 201);
        } catch (Throwable $exception) {
            $payment->forceFill(['status' => 'failed'])->save();
            report($exception);

            return response()->json(['message' => 'Nie udało się rozpocząć płatności. Spróbuj ponownie.'], 503);
        }
    }

    private function orderNumber(): string
    {
        do {
            $number = 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
