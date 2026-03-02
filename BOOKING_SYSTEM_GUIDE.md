# نظام الحجز واختيار المقاعد

## نظرة عامة
نظام حجز متكامل يسمح للمستخدمين باختيار المقاعد المتاحة على الحافلات مع منع تداخل الحجوزات بين المستخدمين.

## المكونات الرئيسية

### 1. قاعدة البيانات

#### Migration: add_bus_and_seats_to_booking_items_table
**الموقع:** `database/migrations/2026_02_28_231620_add_bus_and_seats_to_booking_items_table.php`

**الأعمدة المضافة:**
- `bus_id`: Foreign key للسيارة
- `seat_numbers`: JSON array لتخزين أرقام المقاعد المحجوزة

#### جدول booking_items (بعد التحديث)
```sql
- id
- booking_id
- schedule_trip_id
- bus_id (جديد)
- type (go_way/return_way)
- number_of_seats
- seat_numbers (JSON - جديد)
- price
- from_city
- to_city
- timestamps
```

### 2. Backend (Laravel)

#### BookingItem Model
**الموقع:** `app/Models/BookingItem.php`

**التحديثات:**
- إضافة `bus_id` و `seat_numbers` إلى `$fillable`
- إضافة cast لـ `seat_numbers` كـ array
- إضافة علاقة `bus()` مع موديل Bus

#### BookingService
**الموقع:** `app/Services/BookingService.php`

**الوظائف الرئيسية:**

1. **getReservedSeats($scheduleTripId, $busId)**
   - جلب المقاعد المحجوزة لسيارة في رحلة محددة
   - يأخذ فقط الحجوزات بحالة `pending` أو `confirmed`

2. **checkSeatsAvailability($scheduleTripId, $busId, $seatNumbers)**
   - التحقق من توفر المقاعد المطلوبة
   - التحقق من عدم تداخل الحجوزات
   - التحقق من صحة أرقام المقاعد

3. **getBusSeatsInfo($scheduleTripId, $busId)**
   - جلب معلومات كاملة عن مقاعد السيارة
   - إرجاع حالة كل مقعد (متاح/محجوز)

4. **createBooking($data)**
   - إنشاء حجز جديد مع Transaction
   - التحقق من توفر المقاعد قبل الحجز
   - إنشاء الحجز وعناصره

5. **applyDiscountCode($bookingId, $discountCode)**
   - تطبيق كود خصم على حجز موجود

6. **confirmBooking($bookingId)**
   - تأكيد الحجز

7. **cancelBooking($bookingId)**
   - إلغاء الحجز

#### BookingController
**الموقع:** `app/Http/Controllers/BookingController.php`

**Endpoints:**

1. **GET /seat-selection**
   - عرض صفحة اختيار المقاعد
   - Parameters: schedule_trip_id, bus_id, trip_data

2. **POST /bookings/bus-seats**
   - جلب معلومات المقاعد
   - Request: `{schedule_trip_id, bus_id}`
   - Response: معلومات السيارة + جميع المقاعد مع حالتها

3. **POST /bookings/check-seats**
   - التحقق من توفر مقاعد محددة
   - Request: `{schedule_trip_id, bus_id, seat_numbers[]}`
   - Response: `{available: bool, message, unavailable_seats?}`

4. **POST /bookings/create**
   - إنشاء حجز جديد
   - Request: بيانات الحجز كاملة
   - Response: الحجز المنشأ

5. **POST /bookings/apply-discount**
   - تطبيق كود خصم
   - Request: `{booking_id, discount_code}`

6. **POST /bookings/{id}/confirm**
   - تأكيد الحجز

7. **POST /bookings/{id}/cancel**
   - إلغاء الحجز

### 3. Frontend (React + TypeScript)

#### SeatSelection Component
**الموقع:** `resources/js/pages/bookings/SeatSelection.tsx`

**الوظائف:**

1. **عرض المقاعد:**
   - Grid layout يعرض جميع المقاعد
   - ألوان مختلفة: رمادي (متاح)، أخضر (مختار)، أحمر (محجوز)

2. **اختيار المقاعد:**
   - النقر على المقعد لاختياره/إلغاء اختياره
   - منع اختيار أكثر من عدد المسافرين
   - منع اختيار المقاعد المحجوزة

3. **إنشاء الحجز:**
   - إدخال بيانات العميل (الاسم، الهاتف)
   - التحقق من توفر المقاعد قبل الحجز
   - إنشاء الحجز وإرسال البيانات

4. **Real-time Validation:**
   - التحقق الفوري من المدخلات
   - رسائل خطأ واضحة

#### TripResults Updates
**التحديثات:**
- تحديث `handleBooking()` للتوجيه إلى صفحة اختيار المقاعد
- تمرير جميع البيانات المطلوبة (schedule_trip_id, bus_id, trip_data)

## آلية منع التداخل

### 1. Database Level
```php
// استخدام transactions
DB::transaction(function () {
    // التحقق من توفر المقاعد
    // إنشاء الحجز
});
```

### 2. Application Level
```php
// التحقق المزدوج من توفر المقاعد
1. عند عرض المقاعد
2. قبل إنشاء الحجز (داخل transaction)
```

### 3. Frontend Level
```typescript
// تحديث المقاعد عند فشل الحجز
await fetchSeats(); // إعادة تحميل المقاعد
setSelectedSeats([]); // مسح الاختيار
```

## سير العمل (Workflow)

```
1. المستخدم يبحث عن رحلة (TripForm)
   ↓
2. عرض النتائج مع السيارات المتاحة (TripResults)
   ↓
3. المستخدم يختار سيارة → زر "احجز الآن"
   ↓
4. التوجيه إلى صفحة اختيار المقاعد (SeatSelection)
   ↓
5. تحميل معلومات المقاعد من الخادم
   ↓
6. المستخدم يختار المقاعد + إدخال البيانات
   ↓
7. عند الضغط على "تأكيد الحجز":
   a. التحقق من توفر المقاعد
   b. إذا متاحة: إنشاء الحجز
   c. إذا محجوزة: رسالة خطأ + إعادة تحميل المقاعد
   ↓
8. عند نجاح الحجز: رسالة نجاح + توجيه إلى Dashboard
```

## هيكل البيانات

### طلب الحجز (Create Booking Request)
```json
{
  "customer_name": "أحمد محمد",
  "phone_number": "01234567890",
  "subtotal_price": 200,
  "items": [
    {
      "schedule_trip_id": 5,
      "bus_id": 1,
      "type": "go_way",
      "seat_numbers": [12, 13],
      "price": 200,
      "from_city": "القاهرة",
      "to_city": "الإسكندرية"
    }
  ]
}
```

### استجابة معلومات المقاعد (Bus Seats Response)
```json
{
  "success": true,
  "bus": {
    "id": 1,
    "name": "حافلة 1",
    "plate_number": "ABC-123",
    "total_seats": 40
  },
  "seats": [
    {
      "number": 1,
      "is_reserved": false
    },
    {
      "number": 2,
      "is_reserved": true
    }
  ],
  "reserved_count": 10,
  "available_count": 30
}
```

### استجابة التحقق من المقاعد (Check Seats Response)
```json
{
  "available": true,
  "message": "جميع المقاعد متاحة"
}

// أو في حالة عدم التوفر:
{
  "available": false,
  "message": "بعض المقاعد محجوزة بالفعل",
  "unavailable_seats": [12, 15]
}
```

## الحماية من التداخل

### Scenario 1: مستخدمان يحجزان نفس المقعد
```
User A: يختار مقعد 12
User B: يختار مقعد 12

User A: يضغط "تأكيد الحجز" أولاً
→ Transaction يبدأ
→ التحقق: المقعد 12 متاح ✓
→ إنشاء الحجز ✓
→ Commit

User B: يضغط "تأكيد الحجز"
→ Transaction يبدأ
→ التحقق: المقعد 12 محجوز ✗
→ Rollback
→ رسالة خطأ + إعادة تحميل المقاعد
```

### Scenario 2: مستخدم يحاول حجز مقعد محجوز
```
1. تحميل الصفحة: المقعد 15 متاح
2. مستخدم آخر يحجز المقعد 15
3. المستخدم الأول يختار المقعد 15
4. عند الضغط على "تأكيد":
   → checkSeatsAvailability() يكتشف أن المقعد محجوز
   → رفض الحجز
   → إعادة تحميل المقاعد (المقعد 15 سيظهر محجوز)
```

## Validation Rules

### 1. على مستوى الـ Controller
```php
[
    'customer_name' => 'required|string|max:255',
    'phone_number' => 'required|string|max:20',
    'items.*.seat_numbers' => 'required|array|min:1',
    'items.*.seat_numbers.*' => 'required|integer|min:1',
]
```

### 2. على مستوى الـ Service
- التحقق من عدم تداخل المقاعد
- التحقق من صحة أرقام المقاعد (1 إلى عدد المقاعد الكلي)
- التحقق من وجود السيارة والرحلة

### 3. على مستوى الـ Frontend
- عدد المقاعد = عدد المسافرين
- لا يمكن اختيار مقعد محجوز
- يجب إدخال الاسم ورقم الهاتف

## التعامل مع الأخطاء

### 1. Backend
```php
try {
    DB::transaction(function () {
        // منطق الحجز
    });
} catch (\Exception $e) {
    return [
        'success' => false,
        'message' => $e->getMessage()
    ];
}
```

### 2. Frontend
```typescript
try {
    const response = await axios.post('/bookings/create', bookingData);
    if (response.data.success) {
        // نجح الحجز
    }
} catch (error) {
    // رسالة خطأ
    toast({
        variant: 'destructive',
        title: 'خطأ',
        description: 'حدث خطأ أثناء إنشاء الحجز'
    });
}
```

## الميزات الأمنية

1. **Database Transactions:** ضمان عدم تداخل الحجوزات
2. **Double Check:** التحقق من المقاعد مرتين (عند العرض وعند الحجز)
3. **Authorization:** جميع endpoints محمية بـ `auth` middleware
4. **Validation:** Validation متعدد المستويات
5. **Unique Constraints:** منع حجز نفس المقعد مرتين

## اختبارات مقترحة

1. **Unit Tests:**
   - `BookingService::checkSeatsAvailability()`
   - `BookingService::createBooking()`
   - `BookingService::getReservedSeats()`

2. **Integration Tests:**
   - حجز ناجح
   - محاولة حجز مقعد محجوز
   - حجز متزامن من مستخدمين

3. **Frontend Tests:**
   - اختيار المقاعد
   - التحقق من البيانات
   - رسائل الخطأ

## تحسينات مستقبلية

1. **WebSocket/Pusher:**
   - تحديث المقاعد في الوقت الفعلي
   - إشعارات عند حجز مقعد من قبل مستخدم آخر

2. **Timer:**
   - حجز مؤقت للمقاعد (15 دقيقة)
   - تحرير المقاعد تلقائياً إذا لم يتم الدفع

3. **Payment Gateway:**
   - ربط مع بوابة دفع
   - تأكيد الحجز بعد الدفع

4. **Seat Layout:**
   - عرض تخطيط السيارة الفعلي
   - صفوف وأعمدة مخصصة

5. **Reservation History:**
   - سجل الحجوزات للمستخدم
   - إمكانية إلغاء/تعديل الحجوزات

## ملاحظات مهمة

- ⚠️ جميع الحجوزات تبدأ بحالة `pending`
- ⚠️ يتم احتساب المقاعد المحجوزة فقط للحجوزات بحالة `pending` أو `confirmed`
- ⚠️ الحجوزات الملغاة (`cancelled`) لا تؤثر على توفر المقاعد
- ⚠️ يجب اختيار عدد مقاعد يساوي عدد المسافرين بالضبط
- ⚠️ يتم استخدام Database Transactions لضمان عدم التداخل
