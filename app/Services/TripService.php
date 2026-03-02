<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\City;
use App\Models\ScheduleWork;
use App\Models\Trip;
use App\Models\ScheduleTrip;
use App\Models\BookingItem;
use Carbon\Carbon;

class TripService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function cities()
    {
        return City::all();
    }

    public function gettrips(string $id)
    {
        $tripscitytwo = Trip::select('id' , 'city_one_id' , 'city_two_id' , 'price')
        ->where(function ($q) use ($id){
            $q->where('city_one_id' , $id)
                ->orWhere('city_two_id' , $id)
                ;
        })
        ->where('is_active' , true)
        ->get()
        ->map(function ($trip) use ($id) {
            return [
                // 'id' => $trip->id,
                'id' =>  $trip->city_one_id === (int)$id ? $trip->city_two_id : $trip->city_one_id,
                'name' =>  $trip->city_one_id === (int)$id ? $trip->toCity->name : $trip->fromCity->name,

            ];
        });

        return $tripscitytwo;
    }

    /**
     * التحقق من أن التاريخ ضمن فترة العمل المسموحة
     */
    public function checkWorkScheduale($date)
    {
        $scheduleWork = ScheduleWork::where('date_from' , '<=', $date)
            ->where('date_to' , '>=', $date)
            ->exists();
        return $scheduleWork;
    }

    /**
     * البحث عن الرحلات المتاحة بناءً على البيانات المرسلة من الفورم
     */
    public function searchAvailableTrips($data)
    {
        $fromCityId = $data['from_city'];
        $toCityId = $data['to_city'];
        $tripDate = $data['trip_date'];
        $passengerCount = $data['passenger_count'];
        $tripType = $data['trip_type']; // 'go', 'return', 'goreturn'
        $returnDate = $data['return_date'] ?? null;

        $results = [];

        // البحث عن الرحلة بين المدينتين
        $trip = Trip::where(function ($q) use ($fromCityId, $toCityId) {
            $q->where('city_one_id', $fromCityId)->where('city_two_id', $toCityId)
              ->orWhere(function ($q) use ($fromCityId, $toCityId) {
                  $q->where('city_one_id', $toCityId)->where('city_two_id', $fromCityId);
              });
        })
        ->where('is_active', true)
        ->first();

        if (!$trip) {
            return [
                'success' => false,
                'message' => 'لا توجد رحلة بين المدينتين المحددتين',
            ];
        }

        // تحديد المدن بناءً على اتجاه الرحلة
        $fromCity = City::find($fromCityId);
        $toCity = City::find($toCityId);

        // معالجة رحلة الذهاب
        if ($tripType === 'go' || $tripType === 'goreturn') {
            // التحقق من فترة العمل
            if (!$this->checkWorkScheduale($tripDate)) {
                return [
                    'success' => false,
                    'message' => 'التاريخ المحدد ليس ضمن فترة العمل المسموحة',
                ];
            }

            $goTripResult = $this->getAvailableBusesForDate(
                $trip,
                $tripDate,
                $passengerCount,
                $fromCity->name,
                $toCity->name,
                'go_way'
            );

            $results['go_trip'] = $goTripResult;
        }

        // معالجة رحلة العودة
        if ($tripType === 'return' || ($tripType === 'goreturn' && $returnDate)) {
            $returnDateToCheck = $tripType === 'return' ? $tripDate : $returnDate;

            // التحقق من فترة العمل
            if (!$this->checkWorkScheduale($returnDateToCheck)) {
                return [
                    'success' => false,
                    'message' => 'تاريخ العودة ليس ضمن فترة العمل المسموحة',
                ];
            }

            $returnTripResult = $this->getAvailableBusesForDate(
                $trip,
                $returnDateToCheck,
                $passengerCount,
                $toCity->name,
                $fromCity->name,
                'return_way'
            );

            $results['return_trip'] = $returnTripResult;
        }

        // حساب السعر الإجمالي
        $totalPrice = 0;
        if (isset($results['go_trip']) && $results['go_trip']['available']) {
            $totalPrice += $trip->price * $passengerCount;
        }
        if (isset($results['return_trip']) && $results['return_trip']['available']) {
            $totalPrice += $trip->price * $passengerCount;
        }

        // إذا كانت رحلة ذهاب وعودة، استخدم سعر الرحلة الكاملة
        if ($tripType === 'goreturn' && isset($results['go_trip']) && isset($results['return_trip'])
            && $results['go_trip']['available'] && $results['return_trip']['available']) {
            $totalPrice = $trip->round_trip_price ? ($trip->round_trip_price * $passengerCount) : $totalPrice;
        }

        return [
            'success' => true,
            'trip' => [
                'id' => $trip->id,
                'from_city' => $fromCity->name,
                'to_city' => $toCity->name,
                'price' => $trip->price,
                'round_trip_price' => $trip->round_trip_price,
            ],
            'passenger_count' => $passengerCount,
            'trip_type' => $tripType,
            'total_price' => $totalPrice,
            'results' => $results,
        ];
    }

    /**
     * الحصول على السيارات المتاحة لتاريخ محدد
     */
    private function getAvailableBusesForDate($trip, $date, $passengerCount, $fromCityName, $toCityName, $direction = 'go_way')
    {
        // البحث عن جدول الرحلة المسجلة
        $scheduleTrip = ScheduleTrip::where('trip_id', $trip->id)
            ->where('date', $date)
            ->first();

        // إذا لم يكن موجودًا، إنشاء جدول رحلة جديد
        if (!$scheduleTrip) {
            $scheduleTrip = ScheduleTrip::create([
                'trip_id' => $trip->id,
                'date' => $date,
            ]);

            // جلب جميع السيارات المرتبطة بالرحلة
            $buses = $trip->buses()->where('is_active', true)->get();

            if ($buses->isEmpty()) {
                return [
                    'available' => false,
                    'message' => 'لا توجد سيارات مرتبطة بهذه الرحلة',
                    'schedule_trip_id' => $scheduleTrip->id,
                    'date' => $date,
                    'buses' => [],
                ];
            }

            // جميع السيارات متاحة بالكامل
            $availableBuses = $buses->map(function ($bus) use ($passengerCount) {
                return [
                    'bus_id' => $bus->id,
                    'bus_name' => $bus->name,
                    'plate_number' => $bus->plate_number,
                    'total_seats' => $bus->seats,
                    'available_seats' => $bus->seats,
                    'booked_seats' => 0,
                    'can_book' => $bus->seats >= $passengerCount,
                ];
            })->filter(function ($bus) use ($passengerCount) {
                return $bus['can_book'];
            })->values();

            return [
                'available' => $availableBuses->isNotEmpty(),
                'message' => $availableBuses->isNotEmpty()
                    ? 'توجد سيارات متاحة'
                    : 'لا توجد سيارات متاحة لهذا العدد من المسافرين',
                'schedule_trip_id' => $scheduleTrip->id,
                'date' => $date,
                'from_city' => $fromCityName,
                'to_city' => $toCityName,
                'buses' => $availableBuses->toArray(),
            ];
        }

        // إذا كان موجودًا، حساب الكراسي المتاحة
        $buses = $trip->buses()->where('is_active', true)->get();

        if ($buses->isEmpty()) {
            return [
                'available' => false,
                'message' => 'لا توجد سيارات مرتبطة بهذه الرحلة',
                'schedule_trip_id' => $scheduleTrip->id,
                'date' => $date,
                'buses' => [],
            ];
        }

        // حساب عدد المقاعد المحجوزة لكل سيارة (حسب الاتجاه)
        $bookedSeats = BookingItem::where('schedule_trip_id', $scheduleTrip->id)
            ->where('from_city', $fromCityName)
            ->where('to_city', $toCityName)
            ->whereHas('booking', function ($q) {
                $q->whereIn('status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value]);
            })
            // ->selectRaw('SUM(number_of_seats) as total')
            // ->value('total') ?? 0;
            ->selectRaw('bus_id, SUM(number_of_seats) as total')
            ->groupBy('bus_id')
            ->pluck('total', 'bus_id')
            ->toArray();
        $availableBuses = $buses->map(function ($bus) use ($passengerCount, $bookedSeats, $buses) {
            // توزيع الحجوزات بشكل متساوٍ على السيارات (تقريبًا)
            // $bookedPerBus = floor($bookedSeats / $buses->count());
            $bookedPerBus = $bookedSeats[$bus->id] ?? 0;
            $availableSeats = $bus->seats - $bookedPerBus;

            return [
                'bus_id' => $bus->id,
                'bus_name' => $bus->name,
                'plate_number' => $bus->plate_number,
                'total_seats' => $bus->seats,
                'available_seats' => max(0, $availableSeats),
                'booked_seats' => $bookedPerBus,
                'can_book' => $availableSeats >= $passengerCount,
            ];
        })->filter(function ($bus) use ($passengerCount) {
            return $bus['can_book'];
        })->values();

        return [
            'available' => $availableBuses->isNotEmpty(),
            'message' => $availableBuses->isNotEmpty()
                ? 'توجد سيارات متاحة'
                : 'لا توجد كراسي متاحة لهذا العدد من المسافرين',
            'schedule_trip_id' => $scheduleTrip->id,
            'date' => $date,
            'from_city' => $fromCityName,
            'to_city' => $toCityName,
            'buses' => $availableBuses->toArray(),
        ];
    }
}
