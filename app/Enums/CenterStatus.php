<?php

namespace App\Enums;

enum CenterStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case REJECTED = 'rejected';
    case INACTIVE = 'inactive';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
