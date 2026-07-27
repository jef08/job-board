<?php

namespace App\Enum;

enum ListingStatus: string
{
    case Open = 'open';
    case Closed = 'closed';
    case Filled = 'filled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Closed => 'Closed',
            self::Filled => 'Filled',
        };
    }
}