# قراءات العداد (Odometer)

## الغرض (Purpose)

تسجيل قراءات العدادات لكل مركبة والحفاظ على **المصدر الوحيد للقراءة الحالية**
(`vehicles.current_odometer`). يدعم القراءات العادية والتصحيحات (مع سبب مطلوب).

> **النقطة الجوهرية:** `current_odometer` هو عمود مخزَّن (denormalized) على
> المركبة، وليس مشتقًا من الجدول بقراءة "أحدث سجل". المكان الوحيد المسموح به
> لتعديله هو `OdometerService::record()` — الذي يكتب السجل ويحدّث العمود داخل
> معاملة DB.

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `OdometerLog` | `app/Models/OdometerLog.php` |

الحقول: `vehicle_id`, `reading`, `recorded_at`, `recorded_by`, `is_correction`, `note`.

العلاقات: `vehicle()` ، `recordedBy()`.

### الخدمة (Service)

| الخدمة | الملف |
| ------ | ----- |
| `OdometerService` | `app/Services/OdometerService.php` |

`record(Vehicle, reading, User, isCorrection = false, note = null)`:

- إذا ليست تصحيحًا والقراءة أقل من الحالية → يرمي `InvalidOdometerReadingException`
  ("القراءة أقل من آخر قراءة مسجّلة…").
- إذا كانت تصحيحًا و لا يوجد `note` → يرمي (السبب مطلوب).
- إذا كانت تصحيحًا والمستخدم لا يملك `odometer.correct` → يرمي (لا صلاحية).
- داخل `DB::transaction`: ينشئ `OdometerLog` ثم يحدّث `vehicles.current_odometer`.

### الـ Exception

| الاستثناء | الملف |
| --------- | ----- |
| `InvalidOdometerReadingException` | `app/Exceptions/InvalidOdometerReadingException.php` |

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `OdometerController` | `app/Http/Controllers/OdometerController.php` |

الطريقة `store(Request, Vehicle)`:

- تحقق: `reading` مطلوب رقمي؛ `is_correction` اختياري؛ `note` مطلوب عند
  `is_correction = true`.
- يستدعي `OdometerService::record(...)` ويلتقط الاستثناء ويعيده كخطأ على حقل
  `reading`.

### الـ Routes

```php
POST vehicles/{vehicle}/odometer-readings → vehicles.odometer.store (can:vehicles.edit)
```

> لا يوجد وسم `odometer.update` على المسار — يُعاد استخدام `vehicles.edit`.
> صلاحية `odometer.correct` تُفحص داخل الخدمة وليس كوسيط.

### الـ Views

`basic-data/vehicles/tabs/odometer.blade.php` — القراءة الحالية + نموذج تسجيل قراءة
(مع خيار "تصحيح" وخانة السبب، محمية بـ `@can('odometer.correct')`) + سجل القراءات.

---

## مثال بسيط (Example)

```php
use App\Services\OdometerService;

// قراءة عادية
$log = OdometerService::record($vehicle, 50000, auth()->user());

// تصحيح (يتطلب سببًا وصلاحية odometer.correct)
$log = OdometerService::record($vehicle, 49800, auth()->user(), true, 'تصحيح خطأ إدخال');

echo $vehicle->current_odometer; // 49800
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** عند إنشاء مركبة يُسجَّل العداد الابتدائي عبر
  `OdometerService` (وليس كتابة مباشرة على العمود).
- **الزيوت/الفلاتر (Oil/Filter Changes):** تقرأ `current_odometer` لحساب
  `remaining_change` و `is_overdue`.
- **الوقود (Fuel Logs):** القراءة في وحدة الوقود تُحفظ في `fuel_logs.odometer_reading`
  (سلسلة مستقلة) — لا تُحدّث `current_odometer`.