<?php

namespace App\Enums;

enum BookingStatus : string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case CANCELLED = 'cancelled';
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::CONFIRMED => 'مؤكد',
            self::CANCELLED => 'ملغي',
        };
    }
}
