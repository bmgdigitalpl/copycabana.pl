<?php

namespace App\Enums;

enum Carrier: string
{
    case InPost = 'inpost';
    case DHL = 'dhl';
    case PocztaPolska = 'poczta_polska';
    case Pickup = 'pickup';
    case Other = 'other';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::InPost->value => 'InPost',
            self::DHL->value => 'DHL',
            self::PocztaPolska->value => 'Poczta Polska',
            self::Pickup->value => 'Odbiór osobisty',
            self::Other->value => 'Inny',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function trackingUrl(?string $trackingNumber): ?string
    {
        if (! $trackingNumber || $this === self::Pickup) {
            return null;
        }

        return match ($this) {
            self::InPost => 'https://inpost.pl/sledzenie-przesylek?number='.urlencode($trackingNumber),
            self::DHL => 'https://www.dhl.com/pl-pl/home/tracking.html?tracking-id='.urlencode($trackingNumber),
            self::PocztaPolska => 'https://emonitoring.poczta-polska.pl/?numer='.urlencode($trackingNumber),
            self::Other => null,
        };
    }
}
