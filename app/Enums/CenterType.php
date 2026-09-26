<?php

namespace App\Enums;

enum CenterType: string
{
    case INDIVIDUAL = 'individual';
    case INSTITUTION = 'institution';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
