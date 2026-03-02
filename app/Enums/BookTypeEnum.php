<?php

namespace App\Enums;

enum BookTypeEnum : string
{
    case GO_WAY = 'go_way';
    case RETURN_WAY = 'return_way';
    case ROUND_TRIP = 'round_trip';

    public function label(): string
    {
        return match ($this) {
            self::GO_WAY => 'ذهاب',
            self::RETURN_WAY => 'عودة',
            self::ROUND_TRIP => 'ذهاب وعودة',
        };
    }
}
