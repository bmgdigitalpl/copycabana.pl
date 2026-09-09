<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case PaymentAwaited = 'payment_awaited';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            self::Pending->value => 'Nowe',
            self::PaymentAwaited->value => 'Oczekuje na płatność',
            self::Processing->value => 'W realizacji',
            self::Shipped->value => 'Wysłane',
            self::Delivered->value => 'Dostarczone',
            self::Cancelled->value => 'Anulowane',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, match ($this) {
            self::Pending => [self::PaymentAwaited, self::Processing, self::Cancelled],
            self::PaymentAwaited => [self::Processing, self::Cancelled],
            self::Processing => [self::Shipped, self::Cancelled],
            self::Shipped => [self::Delivered],
            self::Delivered, self::Cancelled => [],
        }, true);
    }
}
