<?php

namespace App\Enums;

enum QuoteOfferStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Rejected = 'rejected';
    case Superseded = 'superseded';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Draft->value => 'Wersja robocza',
            self::Sent->value => 'Wysłana',
            self::Accepted->value => 'Zaakceptowana',
            self::Expired->value => 'Wygasła',
            self::Rejected->value => 'Odrzucona',
            self::Superseded->value => 'Zastąpiona nowszą wersją',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }
}
