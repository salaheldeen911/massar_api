<?php

namespace App\Enums;

enum AdvertisementStatus: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case DISABLED = 'disabled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
