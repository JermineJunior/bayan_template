# أوامر الصيانة (Maintenance)

## الغرض (Purpose)

وحدة أوامر الصيانة تسجّل أعمال الصيانة التي تُجرى على المركبات. لكل أمر صيانة:

- المركبة المطلوب صيانتها.
- السبب، الورشة، التاريخ، قراءة العداد.
- التكاليف (أجرة العمالة `labor_cost`، تكلفة القطع `spare_cost`، الإجمالي `total_cost`).
- النوع (`periodic` دورية / `preventive` وقائية / `emergency` طارئة).
- الحالة (`draft` / `pending` / `in_progress` / `completed` / `cancelled`).

**رابط مهم:** من داخل أمر الصيانة تُنشأ **فواتير الصرف** (القطع التي خرجت من
المخزن لهذه الصيانة) — انظر [invoices.md](invoices.md).

> قيمة محسوبة: `spare_cost` هنا هو حقل يدوي في النظام الحالي، بينما فواتير
> الصرف هي الطريقة الموثقة لصرف القطع. راجع [invoices.md](invoices.md) للتفاصيل.

---

## المكونات (Components)

### الموديل (Model)

| الموديل | الملف |
| ------- | ----- |
| `Maintenance` | `app/Models/Maintenance.php` |

الحقول الأساسية:

- `maintenance_number` — رقم تلقائي فريد بصيغة `MO-2026-00001`
  (يُولَّد يدويًا من الـ Controller عبر `generateMaintenanceNumber()`).
- `vehicle_id` — المركبة.
- `date`, `start_date`, `end_date` — التواريخ.
- `odometer_reading` — قراءة العداد.
- `reason`, `workshop`, `technical`, `note`.
- `labor_cost`, `spare_cost`, `total_cost` — التكاليف.
- `type`, `status`.
- `created_by` — المستخدم الذي أنشأ الأمر.

العلاقات:

- `vehicle()` — المركبة.
- `invoices()` — فواتير الصرف المرتبطة بهذا الأمر.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `MaintenanceController` | `app/Http/Controllers/MaintenanceController.php` |

الطرق: `index` (قائمة + فلترة)، `create`/`store`، `show`، `edit`/`update`،
`destroy`، `updateStatus` (تحديث الحالة فقط)، `getOdometer` (قراءة العداد
للمركبة لملء النموذج).

#### سلوك مهم: ربط حالة المركبة بالصيانة

عند الإنشاء أو التعديل أو تغيير الحالة، تستدعي الطريقة
`syncVehicleStatus()`:

- إذا كانت حالة الصيانة `completed` أو `cancelled` → حالة المركبة تصبح
  `active` (متاحة).
- في أي حالة أخرى → حالة المركبة تصبح `maintenance` (قيد الصيانة).

بهذا تُبتعد المركبة تلقائيًا عن الاستخدام أثناء وجود صيانة نشطة عليها.

---

### الـ Views

في `resources/views/maintenance/`:

- `index.blade.php` — قائمة أوامر الصيانة مع فلترة (رقم، نوع، حالة).
- `create.blade.php` + `edit.blade.php` + `_form.blade.php` — نموذج إنشاء/تعديل.
- `show.blade.php` — تفاصيل الأمر + فواتير الصرف المرتبطة.

### الـ Routes

في `routes/web.php`:

```php
Route::get('maintenance',            [MaintenanceController::class, 'index']);
Route::get('maintenance/create',     [MaintenanceController::class, 'create']);
Route::get('maintenance/{maintenance}',    [MaintenanceController::class, 'show']);
Route::get('maintenance/{maintenance}/edit',[MaintenanceController::class, 'edit']);

Route::post('maintenance',                       [MaintenanceController::class, 'store']);
Route::put('maintenance/{maintenance}',          [MaintenanceController::class, 'update']);
Route::patch('maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus']);
Route::delete('maintenance/{maintenance}',       [MaintenanceController::class, 'destroy']);

Route::get('/maintenance/vehicle/{vehicle}/odometer', [MaintenanceController::class, 'getOdometer']);
```

صلاحيات: القراءة/النماذج تتطلب `maintenance.view`، والتعديلات تتطلب
`maintenance.create` / `maintenance.edit` / `maintenance.delete`.

---

## مثال بسيط (Example)

**سيناريو:** ننشئ أمر صيانة وقائية (دورية) لمركبة، وتنتهي الصيانة فيُعلَّم
أمر الصيانة كمكتمل.

```php
use App\Models\Maintenance;
use App\Models\Vehicle;

$vehicle = Vehicle::find(1);

$maintenance = Maintenance::create([
    'maintenance_number' => Maintenance::generateMaintenanceNumber('2026-08-31'), // MO-2026-00001
    'vehicle_id'         => $vehicle->id,
    'date'               => now(),
    'start_date'         => now(),
    'odometer_reading'   => $vehicle->current_odometer,
    'reason'             => 'صيانة دورية',
    'type'               => 'periodic',
    'status'             => 'in_progress',
    'labor_cost'         => 200,
    'created_by'         => auth()->id(),
]);

// عند الانتهاء → تحديث الحالة يحرّر المركبة تلقائيًا
$maintenance->update(['status' => 'completed']); // المركبة تصبح active
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** حالة المركبة تتأثر بحالة أمر الصيانة (انظر
  `syncVehicleStatus`).
- **فواتير الصرف (Invoices):** تُنشأ من داخل أمر الصيانة لصرف القطع. راجع
  [invoices.md](invoices.md).
