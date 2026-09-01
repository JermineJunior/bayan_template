# السائقون (Drivers)

## الغرض (Purpose)

إدارة بيانات السائقين الأساسية (اسم كامل، رقم وطني، رخصة قيادة، قسم) مع تتبّع
علاقتهم بالمركبات عبر الإسناد. تشمل صفحة عرض مفصّلة (show) بتبويبات للمخالفات
والحوادث وال_current vehicle_.

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `Driver` | `app/Models/Driver.php` |

الحقول الأساسية:

- `full_name` — اسم السائق الكامل.
- `national_id` — رقم وطني فريد.
- `phone_number` — رقم الهاتف (اختياري).
- `department_id` — القسم التابع له (اختياري، `cascadeOnDelete`).
- `hire_date` — تاريخ التعيين.
- `license_type` — `general` | `private` | `other`.
- `license_expiry_date` — تاريخ انتهاء الرخصة.
- `status` — `active` | `inactive`.

العلاقات:

- `department()` → `belongsTo(Department)`.
- `currentAssignment()` → `hasOne(VehicleDriver)->where('is_current', true)`.
- `violations()` → `hasMany(DriverViolation)`.
- `incidents()` → `hasMany(Incident)`.
- `vehicleAssignments()` → `hasMany(VehicleDriver)` (التاريخي).

الطرق المحسوبة:

- `currentVehicle(): ?Vehicle` — يُرجع المركبة الحالية (قد يكون `null`).

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `DriverController` | `app/Http/Controllers/BasicData/DriverController.php` |

الطرق:

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index(Request)` | `drivers.view` | بحث في `full_name`/`national_id`/`phone_number`، فلترة بالحالة
  وحالة الرخصة (`expired` = منتهية، `expiring` = تنتهي خلال 30 يومًا). |
| `create()` | `drivers.view` | نموذج مع قائمة الأقسام. |
| `store()` | `drivers.create` | حفظ سائق جديد. |
| `show(Driver)` | `drivers.view` | صفحة عرض بتبويبات: معلومات + المركبة الحالية + المخالفات + الحوادث. |
| `edit()` | `drivers.view` | نموذج تعديل. |
| `update()` | `drivers.edit` | تحديث البيانات. |
| `destroy()` | `drivers.delete` | حذف (ممنوع إذا وُجدت إسنادات مركبات مرتبطة). |

**حماية الحذف:** لا يمكن حذف سائق إذا كانت لديه إسنادات مركبات.

### الـ Views

- `drivers/index.blade.php` — قائمة مع بحث وفلترة (الحالة، رخصة منتهية/تنتهي خلال
  30 يومًا).
- `drivers/show.blade.php` — صفحة عرض بتبويبات (Alpine.js):
  - **معلومات السائق:** الاسم، الرقم الوطني، الهاتف، القسم، تاريخ التعيين، نوع
    الرخصة، تاريخ الانتهاء (مع شارة "منتهية" إن انتهت).
  - **الإسناد:** المركبة الحالية + سجل الإسناد مع زر "إنهاء الإسناد".
  - **المخالفات:** جدول المخالفات المرتبطة.
  - **الحوادث:** جدول الحوادث المرتبطة.
- `drivers/create.blade.php` + `edit.blade.php` + `_form.blade.php` — الحقول:
  الاسم الكامل، الرقم الوطني، رقم الهاتف، القسم، تاريخ التعيين، نوع الرخصة،
  تاريخ الانتهاء، الحالة.

### الـ Routes

```php
GET drivers              → drivers.index   (can:drivers.view)
GET drivers/create       → drivers.create  (can:drivers.view)
GET drivers/{driver}     → drivers.show    (can:drivers.view)
GET drivers/{driver}/edit → drivers.edit   (can:drivers.view)
POST drivers             → drivers.store   (can:drivers.create)
PUT drivers/{driver}     → drivers.update  (can:drivers.edit)
DELETE drivers/{driver}  → drivers.destroy (can:drivers.delete)
```

---

## مثال بسيط (Example)

```php
use App\Models\Driver;

$driver = Driver::create([
    'full_name'          => 'أحمد محمد',
    'national_id'        => '1234567890',
    'department_id'      => 1,
    'license_type'       => 'general',
    'license_expiry_date' => '2027-01-01',
    'status'             => 'active',
]);

echo $driver->currentVehicle()?->plate_number; // null إذا لم يكن عليه مركبة
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** السائق يرتبط بالمركبة عبر `VehicleDriver` (إسناد).
- **المخالفات (Violations):** كل مخالفة مرتبطة بسائق اختياريًا.
- **الحوادث (Incidents):** كل حادث مرتبطة بسائق اختياريًا.
- **الإسناد (Vehicle Assignment):** `currentAssignment()` تُرجع الإسناد الحالي فقط.