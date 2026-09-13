<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Mail\OrderReceivedMail;
use App\Services\InvoiceService;
use App\Services\PayuService;
use App\Services\QuoteOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $quotes = $request->user()->client->quoteRequests()
            ->with('latestOffer')
            ->latest()
            ->paginate(10);

        return view('customer.quotes.index', compact('quotes'));
    }

    public function show(Request $request, string $reference): View
    {
        $quoteRequest = $request->user()->client->quoteRequests()
            ->where('reference', $reference)
            ->with(['items', 'files', 'offers.order'])
            ->firstOrFail();
        Gate::authorize('view', $quoteRequest);

        return view('customer.quotes.show', compact('quoteRequest'));
    }

    public function accept(
        Request $request,
        string $reference,
        int $offer,
        QuoteOfferService $offers,
        InvoiceService $invoiceService,
        PayuService $payu,
    ): RedirectResponse {
        $quoteRequest = $request->user()->client->quoteRequests()->where('reference', $reference)->firstOrFail();
        Gate::authorize('view', $quoteRequest);
        $quoteOffer = $quoteRequest->offers()->whereKey($offer)->firstOrFail();
        $payment = null;

        try {
            $result = $offers->accept($quoteOffer, $invoiceService);
            $payment = $result['payment'];

            if (! $result['created'] && ! $result['retry']) {
                $paymentUrl = is_array($payment->payload) ? ($payment->payload['redirect_uri'] ?? null) : null;

                if (! is_string($paymentUrl) || $paymentUrl === '') {
                    return back()->withErrors(['offer' => 'Płatność dla tej oferty nie jest już dostępna.']);
                }

                return redirect()->away($paymentUrl);
            }

            $paymentUrl = $payu->createPayment(
                $result['order']->load('items'),
                $payment,
                route('checkout.success', ['token' => $result['success_token']]),
                route('api.payments.payu.notify'),
                $request->ip(),
            );
            $result['order']->transitionTo(OrderStatus::PaymentAwaited);

            if ($result['created']) {
                Mail::to($result['order']->customer_email)->queue(
                    (new OrderReceivedMail($result['order']->refresh()))->afterCommit(),
                );
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
