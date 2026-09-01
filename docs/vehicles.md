# المركبات (Vehicles)

## الغرض (Purpose)

الوحدة الأكثر شمولًا في النظام. إدارة بيانات المركبة (الرقم الداخلي، اللوحة،
الفئة، المواصفات الفنية، الإدارة)، حالتها، العداد، الوقود، الزيوت، الفلاتر،
التأمين، الحوادث، المصروفات، وإسناد السائقين. تدعم الحذف الناعم (`SoftDeletes`).

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `Vehicle` | `app/Models/Vehicle.php` |

الحقول الأساسية:

- `internal_number` (فريد)، `plate_number` (فريد).
- `type`, `category`, `model`, `manufacture_year`, `color`.
- `chassis_number`, `engine_number`, `engine_capacity`.
- `fuel_type` — `gasoline` | `diesel`.
- `management_id` — الإدارة (اختياري، `cascadeOnDelete`).
- `status` — `active` | `maintenance` | `stopped` | `sold` | `out_of_service`.
- `stopped_at`, `current_odometer`, `operating_hours`, `image_path`.

العلاقات:

- `management()` — `belongsTo(Management)`.
- `currentAssignment()` — `hasOne(VehicleDriver)->where('is_current', true)`.
- `driverAssignments()` — تاريخي (hasMany).
- `odometerLogs()` — hasMany (أحدث أولًا).
- `fuelLogs()`, `oilChanges()`, `filterChanges()`.
- `insurancePolicies()` — hasMany + `currentInsurancePolicy()` (hasOne).
- `incidents()`, `expenses()`.

الحاسوبات (محسوبة وليست أعمدة):

- `image_url` — رابط الصورة على القرص العام.
- `currentDriver(): ?Driver` — السائق الحالي.
- `currentOilStatus()` — أحدث حالة لكل نوع زيت.
- `currentFilterStatus()` — أحدث حالة لكل نوع فلتر.
- `fuelCostPerKilometer()` — تكلفة الكيلومتر = مجموع `total_value` ÷ المسافة بين
  أول وآخر تعبئة (نرجّع `null` إذا أقل من سجلّين).
- `averageMonthlyFuelConsumption()` — متوسط لترات الشهر (÷ 30.44 يوم/شهر).

### مزامنة الحالة مع `stopped_at` (مهم)

في `booted()` عبر `static::saving`:

- عند الدخول إلى حالة `stopped` → يُضبط `stopped_at = now()`.
- عند الخروج من `stopped` → يُصفَّر `stopped_at = null`.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `VehicleController` | `app/Http/Controllers/BasicData/VehicleController.php` |

الطرق:

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index(Request)` | `vehicles.view` | بحث في عدة حقول + فلترة بالإدارة والحالة. |
| `create()` | `vehicles.view` | نموذج مع الإدارات. |
| `store()` | `vehicles.create` | حفظ + رفع صورة + تسجيل العداد الابتدائي عبر `OdometerService`. |
| `show(Vehicle)` | `vehicles.view` | صفحة عرض بتبويبات (Alpine). |
| `edit()` | `vehicles.view` | نموذج تعديل. |
| `update()` | `vehicles.edit` | تحديث + حذف الصورة القديمة إذا رُفعت صورة جديدة. |
| `destroy()` | `vehicles.delete` | حذف (ممنوع إذا وُجدت إسنادات) + حذف الصورة. |

قواعد التحقق المشتركة (`rules`): الرقم الداخلي/اللوحة مطلوبان فريدان (مع ignore
عند التعديل)، `fuel_type` ضمن `[gasoline, diesel]`، `status` ضمن القيم الخمس،
`manufacture_year` رقمي، الصورة صورة حتى 2048KB.

![تبويبات صفحة المركبة]
تحتوي صفحة `show` على تبويبات: معلومات، إسناد، عداد، وقود، زيوت، فلاتر،
تأمين، حوادث، مصروفات.

### الإسناد (VehicleDriverController)

| المكوّن | الملف |
| ------- | ----- |
| `VehicleDriverController` | `app/Http/Controllers/BasicData/VehicleDriverController.php` |
| `VehicleDriver` | `app/Models/VehicleDriver.php` |

نموذج `VehicleDriver` (pivot):

- الحقول: `vehicle_id`, `driver_id`, `assignment_date`, `assigned_by`, `is_current`,
  `ended_at`.
- `assign(Vehicle, Driver, user)` — داخل معاملة DB: يُنهي الإسناد الحالي
  للمركبة والسائق (مع `lockForUpdate`) ثم يُنشئ سجلًا جديدًا `is_current = true`.
  يدعم "نقل السائق".
- `endAssignment()` — يضبط `is_current=false, ended_at=now()`.

جدول `vehicle_driver` يعرّف أعمدة مولّدة فريدة (`current_vehicle_id`,
`current_driver_id`) لفرض إسناد حالي واحد لكل مركبة وكل سائق.

الطرق:

- `store(Request, Vehicle)` — `vehicles.assign` (تعيين سائق).
- `destroy(VehicleDriver)` — `vehicles.end-assignment` (إنهاء؛ يرفض إذا كان منتهيًا).

### الـ Odometer

راجع [odometer.md](odometer.md) للتفاصيل الكاملة — هنا نلخّص أن تعديل
`current_odometer` يتم فقط عبر `OdometerService::record()` (قراءة جديدة أو تصحيح
بالسبب).

### الـ Routes

```php
GET vehicles                  → vehicles.index   (can:vehicles.view)
GET vehicles/create           → vehicles.create  (can:vehicles.view)
GET vehicles/{vehicle}        → vehicles.show    (can:vehicles.view)
GET vehicles/{vehicle}/edit   → vehicles.edit    (can:vehicles.view)
POST vehicles                 → vehicles.store   (can:vehicles.create)
PUT vehicles/{vehicle}        → vehicles.update  (can:vehicles.edit)
DELETE vehicles/{vehicle}     → vehicles.destroy (can:vehicles.delete)
POST vehicles/{vehicle}/assign-driver      → vehicles.assign-driver (can:vehicles.assign)
DELETE assignments/{assignment}            → assignments.destroy    (can:vehicles.end-assignment)
POST vehicles/{vehicle}/odometer-readings  → vehicles.odometer.store (can:vehicles.edit;
                                                                       التصحيح يتطلب odometer.correct)
```

### الـ Views

- `basic-data/vehicles/index.blade.php` — بحث وفلترة + جدول متعدد الأعمدة.
- `basic-data/vehicles/create.blade.php` + `edit.blade.php` + `_form.blade.php` —
  أقسام: البيانات الأساسية، المواصفات الفنية، بيانات التشغيل، صورة المركبة.
- `basic-data/vehicles/show.blade.php` + تبويبات `tabs/{info,assignment,odometer,fuel,oil,filters,...}.blade.php`.

---

## مثال بسيط (Example)

```php
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\VehicleDriver;
use App\Models\User;

$vehicle = Vehicle::create([
    'internal_number' => 'V-001',
    'plate_number'    => '12345',
    'fuel_type'       => 'diesel',
    'status'          => 'active',
    'management_id'   => 1,
]);

$driver = Driver::find(1);

// تعيين السائق (نقل الدعم — ينهي الإسناد الحالي تلقائيًا)
VehicleDriver::assign($vehicle, $driver, auth()->id());

echo $vehicle->currentDriver()?->full_name; // السائق الحالي
```

---

## تفاعلات مع وحدات أخرى

- **الإدارات (Managements):** المركبة ترتبط بالإدارة.
- **السائقون (Drivers):** عبر الإسناد (`VehicleDriver`).
- **العداد (Odometer):** يقرأ/يُعدَّل عبر `OdometerService`.
- **الوقود/الزيوت/الفلاتر/التأمين/الحوادث/المصروفات:** كلها تتعلق بمركبة.
- **لوحة المعلومات (Dashboard):** العدادات والحالات من المركبات.