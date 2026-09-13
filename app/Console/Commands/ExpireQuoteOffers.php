<?php

namespace App\Console\Commands;

use App\Enums\QuoteOfferStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\QuoteOffer;
use App\Models\QuoteRequest;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('quote-offers:expire')]
#[Description('Expire sent B2B quote offers past their validity date')]
class ExpireQuoteOffers extends Command
{
    public function handle(): int
    {
        $expired = 0;

        QuoteOffer::query()
            ->where('status', QuoteOfferStatus::Sent)
            ->whereDate('valid_until', '<', today())
            ->chunkById(100, function ($offers) use (&$expired): void {
                foreach ($offers as $offer) {
                    DB::transaction(function () use ($offer, &$expired): void {
                        $lockedOffer = QuoteOffer::query()->whereKey($offer->id)->lockForUpdate()->first();
                        if (! $lockedOffer || $lockedOffer->status !== QuoteOfferStatus::Sent || ! $lockedOffer->valid_until->isBefore(today())) {
                            return;
                        }

                        $lockedOffer->forceFill(['status' => QuoteOfferStatus::Expired])->save();
                        $request = QuoteRequest::query()->whereKey($lockedOffer->quote_request_id)->lockForUpdate()->first();
                        if ($request?->status === QuoteRequestStatus::Quoted && ! $request->offers()->where('status', QuoteOfferStatus::Sent)->whereDate('valid_until', '>=', today())->exists()) {
                            $request->forceFill(['status' => QuoteRequestStatus::Expired])->save();
                        }
                        $expired++;
                    });
                }
            });

        $this->info("Expired {$expired} quote offer(s).");

        return self::SUCCESS;
    }
}
