# دليل نظام البحث عن الرحلات

## نظرة عامة
تم تطوير نظام متكامل للبحث عن الرحلات المتاحة مع التحقق من فترات العمل وتوفر المقاعد على الحافلات.

## المكونات الرئيسية

### 1. Backend (Laravel)

#### TripService.php
**الموقع:** `app/Services/TripService.php`

**الوظائف الرئيسية:**

- `checkWorkScheduale($date)`: التحقق من أن التاريخ ضمن فترة العمل المسموحة
- `searchAvailableTrips($data)`: البحث عن الرحلات المتاحة بناءً على:
  - المدينة من/إلى
  - تاريخ السفر
  - نوع الرحلة (ذهاب/عودة/ذهاب وعودة)
  - عدد المسافرين
  
- `getAvailableBusesForDate()`: الحصول على السيارات المتاحة لتاريخ محدد

**المنطق:**
1. التحقق من وجود الرحلة بين المدينتين
2. التحقق من فترة العمل للتاريخ/التواريخ
3. البحث عن جدول الرحلة (ScheduleTrip)
4. إذا لم يكن موجودًا: إنشاء جدول رحلة جديد وجلب جميع السيارات المرتبطة
5. إذا كان موجودًا: حساب المقاعد المحجوزة والمتاحة لكل سيارة
6. إرجاع السيارات التي لديها مقاعد كافية

#### TripController.php
**الموقع:** `app/Http/Controllers/TripController.php`

**Endpoints:**

1. **POST /search-trips**
   - البحث عن الرحلات المتاحة
   - Validation للبيانات المدخلة
   - إرجاع JSON مع النتائج

2. **GET /trip-results**
   - عرض صفحة نتائج البحث
   - استقبال البيانات من query parameters

### 2. Frontend (React + TypeScript)

#### TripForm.tsx
**الموقع:** `resources/js/pages/trips/TripForm.tsx`

**الوظائف:**
- نموذج البحث عن الرحلات
- اختيار المدينة من/إلى
- اختيار تاريخ السفر/العودة
- تحديد نوع الرحلة
- تحديد عدد المسافرين (1-8)
- إرسال البيانات إلى `/search-trips`
- التوجيه إلى صفحة النتائج

#### TripResults.tsx
**الموقع:** `resources/js/pages/trips/TripResults.tsx`

**الوظائف:**
- عرض ملخص البحث
- عرض نتائج رحلة الذهاب
- عرض نتائج رحلة العودة (إذا وجدت)
- عرض تفاصيل كل سيارة متاحة:
  - اسم السيارة
  - رقم اللوحة
  - إجمالي المقاعد
  - المقاعد المحجوزة
  - المقاعد المتاحة
  - زر الحجز

### 3. Components المساعدة

#### Toast Components
**الملفات:**
- `resources/js/hooks/use-toast.ts`
- `resources/js/components/ui/toast.tsx`
- `resources/js/components/ui/toaster.tsx`

**الاستخدام:** عرض رسائل النجاح والخطأ للمستخدم

## سير العمل (Workflow)

```
1. المستخدم يدخل بيانات البحث في TripForm
   ↓
2. إرسال البيانات إلى POST /search-trips
   ↓
3. TripService يتحقق من:
   - فترة العمل
   - وجود الرحلة
   - جدول الرحلة (أو إنشاء جديد)
   - المقاعد المتاحة
   ↓
4. إرجاع النتائج
   ↓
5. التوجيه إلى صفحة TripResults
   ↓
6. عرض السيارات المتاحة مع تفاصيلها
```

## هيكل البيانات

### طلب البحث (Search Request)
```json
{
  "from_city": "1",
  "to_city": "2",
  "trip_date": "2026-03-15",
  "trip_type": "goreturn",
  "passenger_count": 2,
  "return_date": "2026-03-20"
}
```

### استجابة البحث (Search Response)
```json
{
  "success": true,
  "trip": {
    "id": 1,
    "from_city": "القاهرة",
    "to_city": "الإسكندرية",
    "price": 100,
    "round_trip_price": 180
  },
  "passenger_count": 2,
  "trip_type": "goreturn",
  "total_price": 360,
  "results": {
    "go_trip": {
      "available": true,
      "message": "توجد سيارات متاحة",
      "schedule_trip_id": 5,
      "date": "2026-03-15",
      "from_city": "القاهرة",
      "to_city": "الإسكندرية",
      "buses": [
        {
          "bus_id": 1,
          "bus_name": "حافلة 1",
          "plate_number": "ABC-123",
          "total_seats": 40,
          "available_seats": 38,
          "booked_seats": 2,
          "can_book": true
        }
      ]
    },
    "return_trip": {
      // نفس الهيكل
    }
  }
}
```

## Models المستخدمة

### Trip
- العلاقة بين مدينتين
- السعر
- سعر الرحلة الكاملة (ذهاب وعودة)
- الحالة (نشط/غير نشط)

### ScheduleTrip
- جدول رحلة محددة
- التاريخ
- العلاقة مع Trip

### Bus
- معلومات السيارة
- عدد المقاعد
- الحالة

### BookingItem
- تفاصيل حجز لرحلة محددة
- عدد المقاعد المحجوزة
- النوع (ذهاب/عودة)

### ScheduleWork
- فترات العمل المسموحة
- تاريخ البداية والنهاية

## Validation Rules

```php
'from_city' => 'required|exists:cities,id',
'to_city' => 'required|exists:cities,id|different:from_city',
'trip_date' => 'required|date|after_or_equal:today',
'trip_type' => 'required|in:go,return,goreturn',
'passenger_count' => 'required|integer|min:1|max:8',
'return_date' => 'required_if:trip_type,goreturn|nullable|date|after_or_equal:trip_date'
```

## الخطوات التالية (للتطوير المستقبلي)

1. تطبيق وظيفة الحجز الفعلية عند الضغط على زر "احجز الآن"
2. إضافة تفاصيل أكثر عن الرحلات (وقت المغادرة، وقت الوصول)
3. إضافة فلترة وترتيب للنتائج
4. إضافة صفحة تأكيد الحجز
5. إضافة نظام دفع
6. إضافة إشعارات للمستخدمين
7. تحسين حساب توزيع المقاعد المحجوزة على السيارات

## ملاحظات مهمة

- جميع التواريخ يجب أن تكون بصيغة `YYYY-MM-DD`
- الحد الأدنى للمسافرين: 1
- الحد الأقصى للمسافرين: 8
- يتم إنشاء `ScheduleTrip` تلقائيًا إذا لم يكن موجودًا
- يتم حساب المقاعد المتاحة بناءً على الحجوزات الحالية (pending & confirmed)
- يتم التحقق من فترة العمل قبل عرض أي نتائج
