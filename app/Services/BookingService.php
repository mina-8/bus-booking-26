<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\ScheduleTrip;
use App\Models\Bus;
use App\Enums\BookTypeEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Models\User;

class BookingService
{
    /**
     * الحصول على المقاعد المحجوزة لسيارة في رحلة محددة
     */
    public function getReservedSeats(int $scheduleTripId, int $busId, ?string $fromCity = null, ?string $toCity = null): array
    {
        $query = BookingItem::where('schedule_trip_id', $scheduleTripId)
            ->where('bus_id', $busId)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value]);
            });

        // فلترة حسب الاتجاه إذا تم تحديده
        if ($fromCity && $toCity) {
            $query->where('from_city', $fromCity)
                  ->where('to_city', $toCity);
        }

        $reservedSeats = $query->get()
            ->pluck('seat_numbers')
            ->flatten()
            ->unique()
            ->values()
            ->toArray();

        return $reservedSeats;
    }

    /**
     * التحقق من توفر المقاعد المطلوبة
     */
    public function checkSeatsAvailability(int $scheduleTripId, int $busId, array $seatNumbers, ?string $fromCity = null, ?string $toCity = null): array
    {
        $reservedSeats = $this->getReservedSeats($scheduleTripId, $busId, $fromCity, $toCity);

        // التحقق من أن المقاعد المطلوبة غير محجوزة
        $unavailableSeats = array_intersect($seatNumbers, $reservedSeats);

        if (!empty($unavailableSeats)) {
            return [
                'available' => false,
                'message' => 'بعض المقاعد محجوزة بالفعل',
                'unavailable_seats' => array_values($unavailableSeats),
            ];
        }

        // التحقق من أن المقاعد ضمن نطاق السيارة
        $bus = Bus::find($busId);
        $invalidSeats = array_filter($seatNumbers, function ($seat) use ($bus) {
            return $seat < 1 || $seat > $bus->seats;
        });

        if (!empty($invalidSeats)) {
            return [
                'available' => false,
                'message' => 'بعض أرقام المقاعد غير صحيحة',
                'invalid_seats' => array_values($invalidSeats),
            ];
        }

        return [
            'available' => true,
            'message' => 'جميع المقاعد متاحة',
        ];
    }

    /**
     * إنشاء حجز جديد
     */
    public function createBooking(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // إنشاء الحجز الرئيسي
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'customer_name' => $data['customer_name'],
                    'phone_number' => $data['phone_number'],
                    'subtotal_price' => $data['subtotal_price'],
                    'discount_amount' => 0,
                    'total_price' => $data['subtotal_price'],
                    'status' => BookingStatus::CONFIRMED->value,
                ]);

                // إضافة عناصر الحجز
                foreach ($data['items'] as $item) {
                    // التحقق من توفر المقاعد مرة أخرى (للأمان)
                    $availability = $this->checkSeatsAvailability(
                        $item['schedule_trip_id'],
                        $item['bus_id'],
                        $item['seat_numbers'],
                        $item['from_city'] ?? null,
                        $item['to_city'] ?? null
                    );

                    if (!$availability['available']) {
                        throw new \Exception($availability['message']);
                    }

                    // إنشاء عنصر الحجز
                    BookingItem::create([
                        'booking_id' => $booking->id,
                        'schedule_trip_id' => $item['schedule_trip_id'],
                        'bus_id' => $item['bus_id'],
                        'type' => $item['type'],
                        'number_of_seats' => count($item['seat_numbers']),
                        'seat_numbers' => $item['seat_numbers'],
                        'price' => $item['price'],
                        'from_city' => $item['from_city'],
                        'to_city' => $item['to_city'],
                    ]);
                }

                // إرسال إشعار للمسؤولين (المستخدمين الذين لديهم دور "admin")
                try {
                    $admins = User::role('super_admin')->get();
                    foreach ($admins as $admin) {
                        Notification::make()
                            ->title('طلب جديد')
                            ->body("{$booking->id} : يوجد طلب تذكرة جديد")
                            ->success()
                            ->sendToDatabase($admin)
                            ->send();
                    }
                } catch (\Throwable $e) {
                    // لا نرمي استثناء هنا لأن الإشعار اختياري — يمكن تسجيله إذا رغبت
                }

                return [
                    'success' => true,
                    'message' => 'تم إنشاء الحجز بنجاح',
                    'booking' => $booking->load('items'),
                ];
            });
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        
    }

    /**
     * الحصول على معلومات المقاعد لسيارة في رحلة محددة
     */
    public function getBusSeatsInfo(int $scheduleTripId, int $busId, ?string $fromCity = null, ?string $toCity = null): array
    {
        $bus = Bus::find($busId);

        if (!$bus) {
            return [
                'success' => false,
                'message' => 'السيارة غير موجودة',
            ];
        }

        $reservedSeats = $this->getReservedSeats($scheduleTripId, $busId, $fromCity, $toCity);

        // إنشاء مصفوفة بكل المقاعد
        $seats = [];
        for ($i = 1; $i <= $bus->seats; $i++) {
            $seats[] = [
                'number' => $i,
                'is_reserved' => in_array($i, $reservedSeats),
            ];
        }

        return [
            'success' => true,
            'bus' => [
                'id' => $bus->id,
                'name' => $bus->name,
                'plate_number' => $bus->plate_number,
                'total_seats' => $bus->seats,
            ],
            'seats' => $seats,
            'reserved_count' => count($reservedSeats),
            'available_count' => $bus->seats - count($reservedSeats),
        ];
    }

    /**
     * تطبيق كود خصم على حجز
     */
    public function applyDiscountCode(int $bookingId, string $discountCode)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'الحجز غير موجود',
            ];
        }

        $discount = \App\Models\Discount::where('code', $discountCode)->first();

        if (!$discount) {
            return [
                'success' => false,
                'message' => 'كود الخصم غير صحيح',
            ];
        }

        $applied = $booking->applyDiscount($discount);

        if ($applied) {
            return [
                'success' => true,
                'message' => 'تم تطبيق الخصم بنجاح',
                'booking' => $booking->fresh(),
            ];
        }

        return [
            'success' => false,
            'message' => 'فشل تطبيق الخصم',
        ];
    }

    /**
     * تأكيد الحجز
     */
    public function confirmBooking(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'الحجز غير موجود',
            ];
        }

        $booking->status = 'confirmed';
        $booking->save();

        return [
            'success' => true,
            'message' => 'تم تأكيد الحجز بنجاح',
            'booking' => $booking->fresh(),
        ];
    }

    /**
     * إلغاء الحجز
     */
    public function cancelBooking(int $bookingId)
    {
        $booking = Booking::find($bookingId);

        if (!$booking) {
            return [
                'success' => false,
                'message' => 'الحجز غير موجود',
            ];
        }

        $booking->status = 'cancelled';
        $booking->save();

        return [
            'success' => true,
            'message' => 'تم إلغاء الحجز بنجاح',
            'booking' => $booking->fresh(),
        ];
    }
}
