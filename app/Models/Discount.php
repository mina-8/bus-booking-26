<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    protected $fillable = [
        'code',
        'description',
        'type', // 'percentage' or 'fixed'
        'value',
        'min_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ==================== العلاقات ====================

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    // ==================== Helper Methods ====================

    /**
     * التحقق من صلاحية الكوبون
     */
    public function isValid(): bool
    {
        // التحقق من تفعيل الكوبون
        if (!$this->is_active) {
            return false;
        }

        // التحقق من تاريخ البداية
        if ($this->starts_at && now()->isBefore($this->starts_at)) {
            return false;
        }

        // التحقق من تاريخ الانتهاء
        if ($this->expires_at && now()->isAfter($this->expires_at)) {
            return false;
        }

        // التحقق من عدد مرات الاستخدام
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * حساب قيمة الخصم
     */
    public function calculateDiscount(float $amount): float
    {
        // التحقق من الحد الأدنى للمبلغ
        if ($this->min_amount && $amount < $this->min_amount) {
            return 0;
        }

        $discount = 0;

        if ($this->type === 'percentage') {
            $discount = ($amount * $this->value) / 100;

            // تطبيق الحد الأقصى للخصم إذا وجد
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = $this->max_discount;
            }
        } elseif ($this->type === 'fixed') {
            $discount = min($this->value, $amount);
        }

        return round($discount, 2);
    }

    /**
     * زيادة عداد الاستخدام
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }

    /**
     * التحقق من توفر الكوبون
     */
    public function isAvailable(): bool
    {
        return $this->isValid() &&
               (!$this->usage_limit || $this->used_count < $this->usage_limit);
    }
}
