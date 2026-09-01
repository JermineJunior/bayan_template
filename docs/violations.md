# مخالفات السائقين (Driver Violations)

## الغرض (Purpose)

تسجيل المخالفات المرورية المرتبطة بالسائقين. المخالفة تُنشأ دائمًا من سياق
السائق، ويمكن ربطها بمركبة اختياريًا مع مبلغ غرامة. عند إنشاء مخالفة تحمل مركبة
**و** مبلغًا، يُولّد تلقائيًا **مصروف نوع `violations`** عبر الـ Observer.

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `DriverViolation` | `app/Models/DriverViolation.php` |

الحقول:

- `driver_id` (مطلوب).
- `vehicle_id` (اختياري).
- `violation_date` — تاريخ المخالفة.
- `description` — الوصف (مطلوب).
- `amount` — مبلغ الغرامة (اختياري).
- `recorded_by`.

العلاقات: `driver()`, `vehicle()`, `recordedBy()`.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `DriverViolationController` | `app/Http/Controllers/DriverViolationController.php` |

الطرق:

- `create(Driver)` — نموذج من سياق السائق + قائمة المركبات.
- `store(Request, Driver)` — حفظ.
- `edit(Driver, Violation)` — نموذج تعديل (يرجّع 404 إذا المخالفة ليست لهذا
  السائق).
- `update(...)` — تحديث (يمنع 404 عند عدم تطابق الملكية).
- `destroy(Violation)` — حذف.

قواعد التحقق: `violation_date` تاريخ `before_or_equal:today`، `description`
مطلوب حتى 255، `amount` اختياري رقمي ≥ 0، `vehicle_id` اختياري موجود.

### الـ Observer

| الملف | السلوك |
| ----- | ------ |
| `app/Observers/DriverViolationObserver.php` | عند **إنشاء** مخالفة: إذا `vehicle_id` و `amount` غير null → ينشئ `Expense` من نوع `violations` قيمته `amount` ووصفه "مخالفة — {description}". |

### الـ Routes

```php
GET drivers/{driver}/violations/create        → drivers.violations.create (can:violations.view)
POST drivers/{driver}/violations              → drivers.violations.store  (can:violations.create)
GET drivers/{driver}/violations/{violation}/edit → drivers.violations.edit  (can:violations.edit)
PUT drivers/{driver}/violations/{violation}   → drivers.violations.update (can:violations.edit)
DELETE violations/{violation}                 → violations.destroy        (can:violations.delete)
```

### الـ Views

- `violations/create.blade.php` — بطاقة السائق + نموذج: مركبة (اختياري)، تاريخ،
  وصف، مبلغ (money-input).
- `violations/edit.blade.php` — تعديل بنفس الحقول.

---

## مثال بسيط (Example)

```php
use App\Models\DriverViolation;

$violation = DriverViolation::create([
    'driver_id'      => $driver->id,
    'vehicle_id'     => $vehicle->id,
    'violation_date' => '2026-08-20',
    'description'    => 'تجاوز السرعة',
    'amount'         => 150,
    'recorded_by'    => auth()->id(),
]);
// ← الـ Observer ينشئ مصروف نوع violations بقيمة 150
```

---

## تفاعلات مع وحدات أخرى

- **السائقون (Drivers):** المخالفات تظهر في تبويب المخالفات لصفحة السائق.
- **المركبات (Vehicles):** ربط اختياري بالمركبة.
- **المصروفات (Expenses):** توليد مصروف تلقائي نوع `violations` (عند وجود مركبة
  ومبلغ).