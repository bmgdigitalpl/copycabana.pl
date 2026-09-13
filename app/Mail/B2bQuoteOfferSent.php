<?php

namespace App\Mail;

use App\Models\QuoteOffer;
use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class B2bQuoteOfferSent extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
        public QuoteOffer $offer,
        public string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CopyCabana: oferta dla '.$this->quoteRequest->company_name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.b2b-quote-offer-sent');
    }
}
