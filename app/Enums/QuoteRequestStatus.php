<?php

namespace App\Enums;

enum QuoteRequestStatus: string
{
    case Submitted = 'submitted';
    case InProgress = 'in_progress';
    case Quoted = 'quoted';
    case Accepted = 'accepted';
    case Expired = 'expired';
    case Closed = 'closed';
    case Rejected = 'rejected';

    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            self::Submitted->value => 'Nowe',
            self::InProgress->value => 'W trakcie analizy',
            self::Quoted->value => 'Wycena wysłana',
            self::Accepted->value => 'Zaakceptowane',
            self::Expired->value => 'Wygasłe',
            self::Closed->value => 'Zamknięte',
            self::Rejected->value => 'Odrzucone',
        ];
    }
}
