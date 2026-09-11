<?php

namespace App\Enums;

enum CenterSubscriptionStatus: string
{
    case TRIALING = 'trialing';
    case ACTIVE = 'active';
    case PAST_DUE = 'past_due';
    case CANCELED = 'canceled';
    case EXPIRED = 'expired';
    case PENDING = 'pending';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
