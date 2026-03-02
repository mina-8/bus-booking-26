<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'discount_id',
        'customer_name',
        'phone_number',
        'subtotal_price',
        'discount_amount',
        'total_price',
        'status',
    ];

    protected $casts = [
        'subtotal_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // ==================== العلاقات ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    // ==================== Helper Methods ====================

    /**
     * معرفة إذا كان حجز ذهاب وعودة
     */
    public function isRoundTrip(): bool
    {
        return $this->items()->count() === 2;
    }

    /**
     * معرفة إذا كان حجز رحلة واحدة فقط
     */
    public function isSingleTrip(): bool
    {
        return $this->items()->count() === 1;
    }

    /**
     * الحصول على رحلة الذهاب
     */
    public function getGoWayItem(): ?BookingItem
    {
        return $this->items()->where('type', BookTypeEnum::GO_WAY)->first();
    }

    /**
     * الحصول على رحلة العودة
     */
    public function getReturnWayItem(): ?BookingItem
    {
        return $this->items()->where('type', BookTypeEnum::RETURN_WAY)->first();
    }

    /**
     * الحصول على إجمالي عدد المقاعد المحجوزة
     */
    public function getTotalSeats(): int
    {
        return $this->items()->sum('number_of_seats');
    }

    /**
     * الحصول على وصف الحجز كاملاً
     */
    public function getBookingDescription(): string
    {
        if ($this->isRoundTrip()) {
            $goWay = $this->getGoWayItem();
            return "حجز ذهاب وعودة: {$goWay->from_city} ⟷ {$goWay->to_city}";
        } elseif ($this->isSingleTrip()) {
            $item = $this->items()->first();
            return $item ? $item->getTripDescription() : "غير محدد";
        }

        return "غير محدد";
    }

    /**
     * معرفة إذا كان الحجز مؤكد
     */
    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED->value;
    }

    /**
     * معرفة إذا كان الحجز قيد الانتظار
     */
    public function isPending(): bool
    {
        return $this->status === BookingStatus::PENDING->value;
    }

    /**
     * معرفة إذا كان الحجز ملغي
     */
    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::CANCELLED->value;
    }

    /**
     * تطبيق كود الخصم على الحجز
     */
    public function applyDiscount(Discount $discount): bool
    {
        if (!$discount->isValid()) {
            return false;
        }

        $discountAmount = $discount->calculateDiscount($this->subtotal_price);

        if ($discountAmount > 0) {
            $this->discount_id = $discount->id;
            $this->discount_amount = $discountAmount;
            $this->total_price = $this->subtotal_price - $discountAmount;
            $this->save();

            $discount->incrementUsage();

            return true;
        }

        return false;
    }

    /**
     * إزالة الخصم من الحجز
     */
    public function removeDiscount(): void
    {
        $this->discount_id = null;
        $this->discount_amount = 0;
        $this->total_price = $this->subtotal_price;
        $this->save();
    }

    /**
     * التحقق من وجود خصم مطبق
     */
    public function hasDiscount(): bool
    {
        return $this->discount_id !== null && $this->discount_amount > 0;
    }
}

