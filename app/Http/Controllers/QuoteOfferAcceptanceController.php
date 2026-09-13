<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\QuoteOfferStatus;
use App\Enums\QuoteRequestStatus;
use App\Mail\OrderReceivedMail;
use App\Models\QuoteOffer;
use App\Services\InvoiceService;
use App\Services\PayuService;
use App\Services\QuoteOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class QuoteOfferAcceptanceController extends Controller
{
    public function show(string $token): View
    {
        $offer = QuoteOffer::query()
            ->with(['quoteRequest.items', 'order.payments'])
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        return view('quote-offers.show', compact('offer'));
    }

    public function accept(
        string $token,
        QuoteOfferService $offers,
        InvoiceService $invoiceService,
        PayuService $payu,
    ): RedirectResponse {
        $offer = QuoteOffer::query()->where('token_hash', hash('sha256', $token))->firstOrFail();
        $payment = null;

        if ($offer->status->value === 'sent' && $offer->valid_until->isBefore(today())) {
            $offer->forceFill(['status' => QuoteOfferStatus::Expired])->save();
            $offer->quoteRequest()->update(['status' => QuoteRequestStatus::Expired]);

            return back()->withErrors(['offer' => 'Termin ważności tej oferty minął.']);
        }

        try {
            $result = $offers->accept($offer, $invoiceService);
            $order = $result['order'];
            $payment = $result['payment'];

            if (! $result['created'] && ! $result['retry']) {
                $paymentUrl = is_array($payment->payload) ? ($payment->payload['redirect_uri'] ?? null) : null;
                if (! is_string($paymentUrl) || $paymentUrl === '') {
                    return back()->withErrors(['offer' => 'Płatność dla tej oferty nie jest już dostępna.']);
                }

                return redirect()->away($paymentUrl);
            }

            $paymentUrl = $payu->createPayment(
                $order->load('items'),
                $payment,
                route('checkout.success', ['token' => $result['success_token']]),
                route('api.payments.payu.notify'),
                request()->ip(),
            );
            $order->transitionTo(OrderStatus::PaymentAwaited);
            if ($result['created']) {
                Mail::to($order->customer_email)->queue((new OrderReceivedMail($order->refresh()))->afterCommit());
            }

            return redirect()->away($paymentUrl);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        } catch (Throwable $exception) {
            if ($payment?->exists) {
                $payment->forceFill(['status' => 'failed'])->save();
            }
            report($exception);

            return back()->withErrors(['offer' => 'Nie udało się rozpocząć płatności. Spróbuj ponownie.']);
        }
    }
}
