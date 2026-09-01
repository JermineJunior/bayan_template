# بوليصات التأمين (Insurance Policies)

## الغرض (Purpose)

تسجيل بوليصات التأمين لكل مركبة. لكل مركبة عدة بوليصات تاريخية، وواحدة فقط
تُعلَّم **حالية** (`is_current = true`) في أي وقت. التجديد يتم عبر طريقة `renew()`
التي تُنهي البوليصة الحالية وتُنشئ الجديدة تلقائيًا في نفس المعاملة.

> عند إنشاء بوليصة جديدة، يظهر تنبيه في لوحة المعلومات وعلى لوحة التأمين إذا
> كانت تنتهي خلال 30 يومًا (`insurance_expiring`).

---

## المكونات (Components)

### النموذج (Model)

| النموذج | الملف |
| ------- | ----- |
| `InsurancePolicy` | `app/Models/InsurancePolicy.php` |

الحقول:

- `vehicle_id`.
- `policy_number` — رقم البوليصة.
- `insurance_company` — شركة التأمين.
- `start_date`, `end_date`.
- `value` — قيمة البوليصة (اختياري).
- `is_current` — boolean (الافتراضي `true`).
- `recorded_by`.

العلاقات: `vehicle()`, `recordedBy()`.

الحاسوبات:

- `getIsExpiredAttribute()` — `true` إذا `end_date` في الماضي.
- `getDaysUntilExpiryAttribute()` — الأيام حتى الانتهاء (سالب إذا منتهية).

المتحرك الثابت:

- `renew(Vehicle, policyNumber, company, startDate, endDate, value, user)`
  — داخل `DB::transaction`: يضبط كل البوليصات `is_current = true` الحالية لهذه
  المركبة إلى `false`، ثم يُنشئ سجلًا جديدًا `is_current = true`.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `InsurancePolicyController` | `app/Http/Controllers/InsurancePolicyController.php` |

الطرق:

- `create(Vehicle)` — نموذج من سياق المركبة + البوليصة الحالية إن وُجدت.
- `store(Request, Vehicle)` — تحقق (policy_number مطلوب، بداية/نهاية تاريخ مع
  `end_date after start_date`، `value` رقمي ≥ 0) ثم `renew()`.

### الـ Routes

```php
GET vehicles/{vehicle}/insurance-policies/create → vehicles.insurance-policies.create (can:insurance-policies.view)
POST vehicles/{vehicle}/insurance-policies       → vehicles.insurance-policies.store  (can:insurance-policies.create)
```

### الـ Views

`insurance-policies/create.blade.php` — بطاقة البوليصة الحالية (إن وُجدت) +
نموذج: policy_number, insurance_company, start_date, end_date, value (money-input).

---

## مثال بسيط (Example)

```php
use App\Models\InsurancePolicy;

$policy = InsurancePolicy::renew(
    $vehicle,
    'INS-2026-001',
    'شركة التأمين العربية',
    '2026-01-01',
    '2027-01-01',
    50000,
    auth()->user(),
);

echo $policy->is_current;           // true (الأخرى أصبحت false)
echo $policy->days_until_expiry;    // عدد الأيام المتبقية
echo $policy->is_expired;           // false
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** `currentInsurancePolicy()` على المركبة تُرجع الحالية.
- **الحوادث (Incidents):** الحادث يمكن ربطه بالبوليصة السارية (خيار).
- **لوحة المعلومات/التنبيهات (Dashboard/Notifications):** تستخدمان `is_current`
  و `days_until_expiry` و `is_expired`.