<?php

namespace App\Models;

use App\Enums\BookTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingItem extends Model
{
    protected $fillable = [
        'booking_id',
        'schedule_trip_id',
        'bus_id',
        'type',
        'number_of_seats',
        'seat_numbers',
        'price',
        'from_city',
        'to_city',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'number_of_seats' => 'integer',
        'type' => BookTypeEnum::class,
        'seat_numbers' => 'array',
    ];

    // ==================== العلاقات ====================

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scheduleTrip(): BelongsTo
    {
        return $this->belongsTo(ScheduleTrip::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    // ==================== Helper Methods ====================

    /**
     * معرفة إذا كانت الرحلة ذهاب
     */
    public function isGoWay(): bool
    {
        return $this->type === BookTypeEnum::GO_WAY;
    }

    /**
     * معرفة إذا كانت الرحلة عودة
     */
    public function isReturnWay(): bool
    {
        return $this->type === BookTypeEnum::RETURN_WAY;
    }

    /**
     * الحصول على وصف الرحلة
     */
    public function getTripDescription(): string
    {
        if ($this->isGoWay()) {
            return "ذهاب من {$this->from_city} إلى {$this->to_city}";
        } elseif ($this->isReturnWay()) {
            return "عودة من {$this->from_city} إلى {$this->to_city}";
        }

        return "غير محدد";
    }
}

