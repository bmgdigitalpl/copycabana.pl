<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\QuoteOfferStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Payment;
use App\Models\QuoteOffer;
use App\Models\QuoteRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QuoteOfferService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{offer: QuoteOffer, token: string}
     */
    public function create(QuoteRequest $quoteRequest, array $data, User $user): array
    {
        return DB::transaction(function () use ($quoteRequest, $data, $user): array {
            $request = QuoteRequest::query()->whereKey($quoteRequest->id)->lockForUpdate()->firstOrFail();
            if (in_array($request->status, [QuoteRequestStatus::Closed, QuoteRequestStatus::Rejected, QuoteRequestStatus::Accepted], true)) {
                throw ValidationException::withMessages(['status' => 'Nie można przygotować oferty dla zamkniętego zapytania.']);
            }

            $token = Str::random(64);
            $version = ((int) $request->offers()->max('version')) + 1;
            $request->offers()->where('status', QuoteOfferStatus::Sent)->update(['status' => QuoteOfferStatus::Superseded]);
            $offer = $request->offers()->create([
                'version' => $version,
                'status' => QuoteOfferStatus::Sent,
                'token_hash' => hash('sha256', $token),
                'subtotal' => $data['subtotal'],
                'shipping_total' => $data['shipping_total'],
                'total' => $data['total'],
                'currency' => strtoupper($data['currency']),
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'],
                'sent_at' => now(),
                'created_by' => $user->id,
            ]);
            $request->forceFill(['status' => QuoteRequestStatus::Quoted])->save();

            return ['offer' => $offer, 'token' => $token];
        });
    }

    /**
     * @return array{offer: QuoteOffer, order: Order, payment: Payment, success_token: string|null, created: bool, retry: bool}
     */
    public function accept(QuoteOffer $quoteOffer, InvoiceService $invoiceService): array
    {
        return DB::transaction(function () use ($quoteOffer, $invoiceService): array {
            $offer = QuoteOffer::query()
                ->with(['quoteRequest.items', 'quoteRequest.files'])
                ->whereKey($quoteOffer->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($offer->order_id) {
                $order = Order::query()->with('items')->findOrFail($offer->order_id);
                $payment = $order->payments()->latest('id')->firstOrFail();

                if ($payment->status === 'failed') {
                    $successToken = Str::random(64);
                    $order->prepareForPaymentRetry();
                    $order->forceFill([
                        'success_token_hash' => hash('sha256', $successToken),
                    ])->save();
                    $payment = $order->payments()->create([
                        'provider' => config('payment.provider'),
                        'status' => 'pending',
                        'amount' => $order->total,
                        'currency' => $order->currency,
                    ]);

                    return [
                        'offer' => $offer,
                        'order' => $order->load('items'),
                        'payment' => $payment,
                        'success_token' => $successToken,
                        'created' => false,
                        'retry' => true,
                    ];
                }

                return [
                    'offer' => $offer,
                    'order' => $order,
                    'payment' => $payment,
                    'success_token' => null,
                    'created' => false,
                    'retry' => false,
                ];
            }

            if ($offer->status !== QuoteOfferStatus::Sent) {
                throw ValidationException::withMessages(['offer' => 'Ta oferta nie jest już dostępna.']);
            }

            if ($offer->valid_until->isBefore(today())) {
                throw ValidationException::withMessages(['offer' => 'Termin ważności tej oferty minął.']);
            }

            $quoteRequest = $offer->quoteRequest;
            $successToken = Str::random(64);
            $order = Order::create([
                'number' => $this->orderNumber(),
                'success_token_hash' => hash('sha256', $successToken),
                'client_id' => $quoteRequest->client_id,
                'status' => OrderStatus::Pending,
                'payment_status' => 'pending',
                'currency' => $offer->currency,
                'customer_name' => $quoteRequest->customer_name,
                'customer_email' => $quoteRequest->customer_email,
                'customer_phone' => $quoteRequest->customer_phone,
                'customer_company' => $quoteRequest->company_name,
                'shipping_method' => $quoteRequest->shipping_method,
                'shipping_address' => $quoteRequest->shipping_address,
                'requested_by_date' => $quoteRequest->requested_by_date,
                'subtotal' => $offer->subtotal,
                'shipping_total' => $offer->shipping_total,
                'tax_rate' => config('business.tax_rate'),
                'invoice_required' => $quoteRequest->invoice_required,
                'invoice_nip' => $quoteRequest->nip,
                'total' => $offer->total,
                'notes' => $quoteRequest->notes,
            ]);
            $item = $order->items()->create([
                'product_id' => null,
                'product_name' => 'Indywidualna wycena B2B '.$quoteRequest->reference,
                'product_slug' => 'wycena-b2b',
                'configuration' => [
                    'quote_request_reference' => $quoteRequest->reference,
                    'items' => $quoteRequest->items->map(fn ($requestItem): array => [
                        'product' => $requestItem->product_name,
                        'quantity' => $requestItem->quantity,
                        'configuration' => $requestItem->configuration,
                        'help_wanted' => $requestItem->help_wanted,
                    ])->all(),
                ],
                'unit_price' => $offer->subtotal,
                'quantity' => 1,
                'total' => $offer->subtotal,
            ]);

            foreach ($quoteRequest->files as $file) {
                OrderFile::create([
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'token_hash' => hash('sha256', Str::random(64)),
                    'disk' => $file->disk,
                    'path' => $file->path,
                    'original_name' => $file->original_name,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'sha256' => $file->sha256,
                    'pages' => $file->pages ?? 0,
                    'status' => 'attached',
                ]);
            }

            $order->statusHistories()->create(['to_status' => OrderStatus::Pending, 'changed_by' => null]);
            if ($order->invoice_required) {
                $invoiceService->createForOrder($order);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'provider' => config('payment.provider'),
                'status' => 'pending',
                'amount' => $order->total,
                'currency' => $order->currency,
            ]);
            $offer->forceFill([
                'status' => QuoteOfferStatus::Accepted,
                'order_id' => $order->id,
                'accepted_at' => now(),
            ])->save();
            $quoteRequest->forceFill(['status' => QuoteRequestStatus::Accepted])->save();

            return [
                'offer' => $offer,
                'order' => $order->load('items'),
                'payment' => $payment,
                'success_token' => $successToken,
                'created' => true,
                'retry' => false,
            ];
        });
    }

    private function orderNumber(): string
    {
        do {
            $number = 'CC-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
