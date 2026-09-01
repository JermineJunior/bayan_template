# عمليات التعبئة والوقود (Fuel Logs)

## الغرض (Purpose)

تسجيل عمليات تزويد المركبات بالوقود، مع **سلسلة عداد خاصة بها** داخل
`fuel_logs.odometer_reading` (لا تُلمس `vehicles.current_odometer` ولا
`odometer_logs`)، وحساب الإجمالي ومعدل الاستهلاك.

- كل تعبئة تُنشأ من سياق مركبة.
- تُحسب `total_value = liters × price_per_liter − discount` تلقائيًا (لا تُقبل
  كمدخل).
- عند إنشاء التعبئة يُولّد تلقائيًا **مصروف نوع `fuel`** عبر الـ Observer
  (انظر [expenses.md](expenses.md)).

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `FuelLog` | `app/Models/FuelLog.php` |

الحقول:

- `vehicle_id`, `driver_id`.
- `filled_at` — datetime.
- `fuel_type` — `gasoline` | `diesel` (اختياري).
- `liters`, `price_per_liter`, `discount`, `total_value`.
- `odometer_reading` — قراءة التعبئة (سلسلة مستقلة).
- `station`, `invoice_number`, `recorded_by`.

الطرق الثابتة:

- `record(Vehicle, filledAt, liters, pricePerLiter, odometerReading, user, ...)`
  — منشئ ثابت يحسب `total_value`.
- `previousLog()` — آخر تعبئة لنفس المركبة (أساس حساب الاستهلاك).
- `getDistanceSinceLastFillAttribute()` — `odometer_reading − previous`.
- `getConsumptionRateAttribute()` — `round(distance / liters, 2)` كم/لتر (null إذا
  أول تعبئة أو قيم ≤ 0).

حسابات المركبة المرتبطة (`Vehicle`):

- `fuelCostPerKilometer()` — مجموع `total_value` ÷ المسافة بين أول وآخر تعبئة.
- `averageMonthlyFuelConsumption()` — لترات الشهر ÷ (الأيام ÷ 30.44).

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `FuelLogController` | `app/Http/Controllers/FuelLogController.php` |

الطرق:

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index(Request)` | `fuel.view` | قائمة مع فلترة (مركبة، من/إلى تاريخ). |
| `create(Vehicle)` | `fuel.view` | نموذج من سياق المركبة + آخر تعبئة + السائقون النشطون. |
| `store(Request, Vehicle)` | `fuel.create` | حفظ (مع تجريد الفاصلة من السعر/الخصم). |
| `destroy(FuelLog)` | `fuel.delete` | حذف. |

**تحقق العداد المتزايد في `store`:** يستعلم آخر `odometer_reading` لآخر تعبئة
وإذا كانت الجديدة ≤ السابقة → خطأ "قراءة العداد يجب أن تكون أكبر من قراءة آخر
تعبئة مسجلة". هذه السلسلة مستقلة عن `current_odometer`.

### الـ Routes

```php
GET fuel-logs                    → fuel-logs.index        (can:fuel.view)
GET vehicles/{vehicle}/fuel-logs/create → vehicles.fuel-logs.create (can:fuel.view)
POST vehicles/{vehicle}/fuel-logs       → vehicles.fuel-logs.store  (can:fuel.create)
DELETE fuel-logs/{fuelLog}        → fuel-logs.destroy     (can:fuel.delete)
```

### الـ Views

- `fuel-logs/index.blade.php` — فلترة + جدول (مركبة، سائق، تاريخ، نوع، لترات،
  سعر/لتر، خصم، القيمة، العداد، معدل الاستهلاك "كم/لتر").
- `fuel-logs/create.blade.php` — ترويسة المركبة (آخر تعبئة + العداد الحالي + زر
  نسخ العداد) + نموذج (تعبئة `datetime-local`, نوع الوقود, لترات, سعر `money`,
  خصم `money`, `total_value` للقراءة فقط يُحسب في المتصفح عبر Alpine, سائق,
  عداد التعبئة, محطة, رقم الفاتورة).

---

## مثال بسيط (Example)

```php
use App\Models\FuelLog;

$fuel = FuelLog::record(
    $vehicle,
    '2026-09-01 08:00',
    40,          // لتر
    2.5,         // سعر/لتر
    52300,       // عداد التعبئة
    auth()->user(),
);

echo $fuel->total_value;          // 40 × 2.5 = 100
echo $fuel->consumption_rate;     // كم/لتر حسب الفرق عن التعبئة السابقة
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** التعبئة مرتبطة بمركبة وتظهر في تبويب الوقود.
- **المصروفات (Expenses):** توليد مصروف تلقائي نوع `fuel` بقيمة `total_value`
  (عبر `FuelLogObserver`).
- **العداد (Odometer):** سلسلة مستقلة — لا تستخدم `current_odometer`.