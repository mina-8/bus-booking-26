# 📘 أمثلة الاستخدام - نظام حجز الباصات

## 🎯 كيفية معرفة اتجاه الرحلة (ذهاب أم عودة)

### المثال 1: معرفة إذا كانت الرحلة للقاهرة أم من القاهرة

```php
// الحصول على الحجز
$booking = Booking::with(['scheduleTrip.trip.fromCity', 'scheduleTrip.trip.toCity'])
    ->find($bookingId);

// طريقة 1: استخدام Helper Methods
if ($booking->isGoingToCity('القاهرة')) {
    echo "الرحلة متجهة للقاهرة ✈️";
}

if ($booking->isComingFromCity('القاهرة')) {
    echo "الرحلة منطلقة من القاهرة 🏠";
}

// طريقة 2: الحصول على وصف كامل للرحلة
echo $booking->getTripDescription();
// النتيجة: "ذهاب من أسيوط إلى القاهرة"

// طريقة 3: معرفة نوع الرحلة
if ($booking->isGoWay()) {
    echo "رحلة ذهاب فقط";
} elseif ($booking->isReturnWay()) {
    echo "رحلة عودة فقط";
} elseif ($booking->isRoundTrip()) {
    echo "رحلة ذهاب وعودة";
}
```

---

### المثال 2: إنشاء حجز جديد

```php
// البيانات الأساسية
$scheduleTrip = ScheduleTrip::find(1); // رحلة مجدولة من أسيوط للقاهرة
$trip = $scheduleTrip->trip;

// حجز ذهاب (من أسيوط للقاهرة)
$bookingGo = new Booking();
$bookingGo->schedule_trip_id = $scheduleTrip->id;
$bookingGo->user_id = auth()->id();
$bookingGo->customer_name = 'أحمد محمد';
$bookingGo->phone_number = '01012345678';
$bookingGo->number_of_seats = 2;
$bookingGo->total_price = $trip->price * 2; // سعر المقعد × عدد المقاعد
$bookingGo->status = 'pending';
$bookingGo->type = 'go_way'; // ذهاب
$bookingGo->from_city = $trip->fromCity->name; // أسيوط
$bookingGo->to_city = $trip->toCity->name;     // القاهرة
$bookingGo->save();

// حجز عودة (من القاهرة لأسيوط)
$bookingReturn = new Booking();
$bookingReturn->schedule_trip_id = $scheduleTrip->id;
$bookingReturn->user_id = auth()->id();
$bookingReturn->customer_name = 'أحمد محمد';
$bookingReturn->phone_number = '01012345678';
$bookingReturn->number_of_seats = 2;
$bookingReturn->total_price = $trip->price * 2;
$bookingReturn->status = 'pending';
$bookingReturn->type = 'return_way'; // عودة
$bookingReturn->from_city = $trip->toCity->name;   // القاهرة
$bookingReturn->to_city = $trip->fromCity->name;   // أسيوط
$bookingReturn->save();

// حجز ذهاب وعودة
$bookingRound = new Booking();
$bookingRound->schedule_trip_id = $scheduleTrip->id;
$bookingRound->user_id = auth()->id();
$bookingRound->customer_name = 'أحمد محمد';
$bookingRound->phone_number = '01012345678';
$bookingRound->number_of_seats = 2;
$bookingRound->total_price = $trip->round_trip_price * 2;
$bookingRound->status = 'pending';
$bookingRound->type = 'round_trip'; // ذهاب وعودة
$bookingRound->from_city = $trip->fromCity->name;
$bookingRound->to_city = $trip->toCity->name;
$bookingRound->save();
```

---

### المثال 3: عرض معلومات الحجز

```php
$booking = Booking::with(['scheduleTrip.trip.fromCity', 'scheduleTrip.trip.toCity'])->find(1);

// عرض معلومات الرحلة
echo "=== معلومات الحجز ===\n";
echo "اسم العميل: {$booking->customer_name}\n";
echo "رقم الهاتف: {$booking->phone_number}\n";
echo "عدد المقاعد: {$booking->number_of_seats}\n";
echo "السعر الإجمالي: {$booking->total_price} جنيه\n";
echo "نوع الرحلة: {$booking->getTripDescription()}\n";
echo "التاريخ: {$booking->scheduleTrip->date->format('Y-m-d')}\n";

if ($booking->isGoWay()) {
    echo "الاتجاه: ذهاب من {$booking->from_city} إلى {$booking->to_city}\n";
} elseif ($booking->isReturnWay()) {
    echo "الاتجاه: عودة من {$booking->from_city} إلى {$booking->to_city}\n";
}
```

---

### المثال 4: استعلامات متقدمة

```php
// جميع الحجوزات المتجهة للقاهرة
$bookingsToCairo = Booking::with(['scheduleTrip.trip'])
    ->where('to_city', 'القاهرة')
    ->where('type', 'go_way')
    ->get();

// جميع حجوزات العودة من القاهرة
$bookingsFromCairo = Booking::with(['scheduleTrip.trip'])
    ->where('from_city', 'القاهرة')
    ->where('type', 'return_way')
    ->get();

// جميع الحجوزات ذهاب وعودة
$roundTripBookings = Booking::where('type', 'round_trip')->get();

// الحجوزات حسب المستخدم
$userBookings = Booking::where('user_id', auth()->id())
    ->with(['scheduleTrip.trip.fromCity', 'scheduleTrip.trip.toCity'])
    ->get();

foreach ($userBookings as $booking) {
    echo $booking->getTripDescription() . "\n";
}
```

---

### المثال 5: عرض الرحلات المتاحة

```php
// الرحلات من أسيوط للقاهرة
$trip = Trip::with(['fromCity', 'toCity'])
    ->whereHas('fromCity', function($q) {
        $q->where('name', 'أسيوط');
    })
    ->whereHas('toCity', function($q) {
        $q->where('name', 'القاهرة');
    })
    ->first();

if ($trip) {
    echo "الرحلة: {$trip->fromCity->name} → {$trip->toCity->name}\n";
    echo "سعر الذهاب: {$trip->price} جنيه\n";
    echo "سعر الذهاب والعودة: {$trip->round_trip_price} جنيه\n";

    // الرحلات المجدولة لهذا المسار
    $scheduleTrips = $trip->scheduleTrips()
        ->where('date', '>=', now())
        ->get();

    foreach ($scheduleTrips as $scheduleTrip) {
        echo "تاريخ: {$scheduleTrip->date->format('Y-m-d')}\n";
    }
}
```

---

### المثال 6: فلترة حسب التاريخ والمدينة

```php
// الحجوزات للقاهرة في تاريخ معين
$date = '2026-03-01';
$bookings = Booking::with(['scheduleTrip'])
    ->whereHas('scheduleTrip', function($q) use ($date) {
        $q->where('date', $date);
    })
    ->where('to_city', 'القاهرة')
    ->where('type', 'go_way')
    ->get();

echo "عدد الحجوزات للقاهرة في {$date}: " . $bookings->count() . "\n";

// حساب إجمالي المقاعد المحجوزة
$totalSeats = $bookings->sum('number_of_seats');
echo "إجمالي المقاعد المحجوزة: {$totalSeats}\n";

// حساب إجمالي الإيرادات
$totalRevenue = $bookings->sum('total_price');
echo "إجمالي الإيرادات: {$totalRevenue} جنيه\n";
```

---

## 📊 ملخص أنواع الرحلات

| النوع | الثابت | الاتجاه | السعر |
|------|--------|---------|-------|
| ذهاب | `go_way` | city_one → city_two | `price` |
| عودة | `return_way` | city_two → city_one | `price` |
| ذهاب وعودة | `round_trip` | city_one ⟷ city_two | `round_trip_price` |

---

## ✅ Helper Methods المتاحة في Booking Model

- `isGoWay()` - معرفة إذا كانت رحلة ذهاب
- `isReturnWay()` - معرفة إذا كانت رحلة عودة
- `isRoundTrip()` - معرفة إذا كانت رحلة ذهاب وعودة
- `isGoingToCity($cityName)` - معرفة إذا كانت متجهة لمدينة معينة
- `isComingFromCity($cityName)` - معرفة إذا كانت منطلقة من مدينة معينة
- `getTripDescription()` - الحصول على وصف كامل للرحلة
