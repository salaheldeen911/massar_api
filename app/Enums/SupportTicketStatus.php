<?php

namespace App\Enums;

enum SupportTicketStatus: string
{
    case OPEN = 'open';
    case REPLIED = 'replied';
    case CLOSED = 'closed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
