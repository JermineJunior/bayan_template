# تغيير الزيت والفلتر (Oil & Filter Changes)

## الغرض (Purpose)

تسجيل عمليات تغيير الزيت والفلتر لكل مركبة، مع حساب وتخزين **قراءة التغيير
التالي** عند التسجيل، وحساب **المتبقي/المتأخر** ديناميكيًا عند العرض.

> **النقطة الجوهرية:** `next_change_odometer` يُخزَّن (مجمَّد) عند التسجيل =
> `odometer_when_change + عمر الصنف`، بينما `remaining_change` و `is_overdue`
> يُحسبان حيًا مقابل `current_odometer`. وهذا يحافظ على الدقة التاريخية حتى لو
> عُدّل عمر الصنف لاحقًا.

---

## المكونات (Components)

### النماذج (Models)

| النموذج | الملف |
| ------- | ----- |
| `VehicleOilChange` | `app/Models/VehicleOilChange.php` |
| `VehicleFilterChange` | `app/Models/VehicleFilterChange.php` |

**الحقول (لكلٍّ):**

- `vehicle_id`, `oil_id`/`filter_id`.
- `last_change` — تاريخ التغيير (date).
- `odometer_when_change` — قراءة العداد عند التغيير.
- `cost` — المبلغ المدفوع فعليًا لهذه التغييرة.
- `next_change_odometer` — محسوب عند التسجيل.
- `recorded_by`.

**الطرق المحسوبة:**

- `getRemainingChangeAttribute()` = `next_change_odometer − vehicle->current_odometer`.
- `getIsOverdueAttribute()` = `remaining_change < 0`.

**المتحرك الثابت `record(...)`:**

- هو **المكان الوحيد** الذي يُحسب فيه `next_change_odometer =
  odometer_when_change + (float) oil->oil_life` (أو `filter_life`).
- تنبيه في الكود: لا تستخدم `Model::create()` مباشرة أبدًا لإدخال تغيير.

### الـ Controllers

| الـ Controller | الملف |
| -------------- | ----- |
| `VehicleOilChangeController` | `app/Http/Controllers/VehicleOilChangeController.php` |
| `VehicleFilterChangeController` | `app/Http/Controllers/VehicleFilterChangeController.php` |

الطرق:

- `create(Vehicle)` — نموذج من سياق المركبة مع قائمة الأصناف.
- `store(Request, Vehicle)` — حفظ (مع تجريد الفاصلة من `cost`)؛ تحقق:
  - `oil_id`/`filter_id` مطلوب وموجود.
  - `last_change` تاريخ `before_or_equal:today`.
  - `odometer_when_change` رقمي ≥ 0.
  - `cost` رقمي ≥ 0.

### الـ Routes

```php
# تغيير الزيت
GET vehicles/{vehicle}/oil-changes/create   → vehicles.oil-changes.create (can:oil-changes.view)
POST vehicles/{vehicle}/oil-changes         → vehicles.oil-changes.store  (can:oil-changes.create)

# تغيير الفلتر
GET vehicles/{vehicle}/filter-changes/create   → vehicles.filter-changes.create (can:filter-changes.view)
POST vehicles/{vehicle}/filter-changes         → vehicles.filter-changes.store  (can:filter-changes.create)
```

### الـ Views

- `oil-changes/create.blade.php` — ترويسة المركبة (العداد الحالي + زر نسخ)،
  قائمة الزيوت مجمّعة حسب `config('oil_types')` مع نافذة "＋ إضافة زيت" (AJAX عبر
  `oils.store` محمية بـ `oils.create`)، الحقول: `last_change`, `odometer_when_change`,
  `cost` (money مع تلميح "المبلغ الفعلي المدفوع — ليس سعر الكتالوج").
- `filter-changes/create.blade.php` — نفس البنية للفلاتر مع "＋ إضافة فلتر".
- تبويبات العرض: `basic-data/vehicles/tabs/oil.blade.php` (بطاقات حالة الزيوت لكل
  نوع + سجل التغييرات) و `tabs/filters.blade.php`.

---

## مثال بسيط (Example)

```php
use App\Models\VehicleOilChange;

$change = VehicleOilChange::record(
    $vehicle,
    $oil,              // صنف الزيت (oil_life = 5000)
    '2026-09-01',
    50000,             // عداد التغيير
    auth()->user(),
    120,               // التكلفة الفعلية
);

echo $change->next_change_odometer; // 55000 (50000 + 5000) — مجمَّد
echo $change->remaining_change;     // 55000 − current_odometer (حي)
echo $change->is_overdue;           // true/false (حي)
```

---

## تفاعلات مع وحدات أخرى

- **الكتالوجات (Oils/Filters):** `oil_life`/`filter_life` تحدد التغيير التالي.
- **المركبات (Vehicles):** التغييرات تظهر في تبويبَي الزيوت والفلاتر.
- **العداد (Odometer):** يقرأ `current_odometer` لحساب المتبقي.
- **المصروفات (Expenses):** توليد مصروف تلقائي (`oil`/`filter`) بقيمة `cost`
  (عبر الـ Observers).
- **التنبيهات (Notifications):** `oil_due` و `filter_due` تعتمدان على
  `remaining_change`.