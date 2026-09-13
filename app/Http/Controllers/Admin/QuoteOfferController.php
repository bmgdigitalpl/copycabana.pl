<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuoteOfferRequest;
use App\Mail\B2bQuoteOfferSent;
use App\Models\QuoteRequest;
use App\Services\QuoteOfferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class QuoteOfferController extends Controller
{
    public function store(StoreQuoteOfferRequest $request, QuoteRequest $quoteRequest, QuoteOfferService $offers): RedirectResponse
    {
        $result = $offers->create($quoteRequest, $request->validated(), $request->user());
        $quoteRequest = $quoteRequest->load(['items', 'latestOffer']);

        Mail::to($quoteRequest->customer_email)->queue(
            (new B2bQuoteOfferSent($quoteRequest, $result['offer'], $result['token']))->afterCommit(),
        );

        return redirect()->route('admin.quote-requests.show', $quoteRequest)
            ->with('status', 'Oferta została wysłana do klienta.');
    }
}
