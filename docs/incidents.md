# الحوادث (Incidents)

## الغرض (Purpose)

تسجيل حوادث المركبات بصور وحالة مطالبة تأمينية، مع ربط اختياري لبوليصة التأمين
السارية. تعرض تفاصيل كاملة مع معرض صور.

- حالة المطالبة تمر بأربع حالات: `pending` (قيد المراجعة) → `approved`/`rejected`
  → `paid`.
- رقم التقرير `report_number` فريد.

---

## المكونات (Components)

### النماذج (Models)

| النموذج | الملف |
| ------- | ----- |
| `Incident` | `app/Models/Incident.php` |
| `IncidentPhoto` | `app/Models/IncidentPhoto.php` |

**`Incident`:**

- الحقول: `report_number` (فريد)، `vehicle_id`, `driver_id` (اختياري),
  `incident_date`, `location`, `description`, `repair_cost`, `insurance_policy_id`
  (اختياري), `claim_status`, `recorded_by`.
- العلاقات: `vehicle()`, `driver()`, `insurancePolicy()`, `recordedBy()`,
  `photos()` (hasMany).
- الحاسوبات: `getHasClaimAttribute()` — `true` إذا `claim_status` موجود.

**`IncidentPhoto`:**

- الحقل: `incident_id` (cascadeOnDelete), `file_path`.
- العلاقة: `incident()`.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `IncidentController` | `app/Http/Controllers/IncidentController.php` |

الطرق:

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index(Request)` | `incidents.view` | قائمة + فلترة (مركبة، حالة مطالبة). |
| `create(Vehicle)` | `incidents.create` | نموذج من سياق المركبة + السائقون + البوليصة الحالية. |
| `store(Request, Vehicle)` | `incidents.create` | حفظ الحادث + رفع الصور. |
| `show(Incident)` | `incidents.view` | صفحة تفاصيل + معرض صور. |
| `updateClaimStatus(Request, Incident)` | `incidents.edit` | تحديث `claim_status` فقط. |
| `destroy(Incident)` | `incidents.delete` | حذف الصور والمراجعات والحادث. |

قواعد التحقق في `store`:

- `report_number` مطلوب فريد.
- `driver_id` اختياري موجود؛ `incident_date` تاريخ `before_or_equal:today`.
- `location` حتى 255، `description` نص.
- `repair_cost` اختياري رقمي ≥ 0.
- `insurance_policy_id` اختياري موجود.
- `claim_status` ضمن `[pending, approved, rejected, paid]`.
- `photos` مصفوفة حتى 10، كل صورة صورة حتى 5120KB.

**ربط البوليصة:** خانة "ربط بوليصة التأمين" — عند تحديدها يستخدم
`$vehicle->currentInsurancePolicy?->id` بدل الحقل اليدوي.

### الـ Routes

```php
GET incidents                             → incidents.index   (can:incidents.view)
GET vehicles/{vehicle}/incidents/create   → vehicles.incidents.create (can:incidents.create)
POST vehicles/{vehicle}/incidents         → vehicles.incidents.store  (can:incidents.create)
GET incidents/{incident}                  → incidents.show    (can:incidents.view)
PATCH incidents/{incident}/claim-status   → incidents.update-claim-status (can:incidents.edit)
DELETE incidents/{incident}               → incidents.destroy (can:incidents.delete)
```

### الـ Views

- `incidents/index.blade.php` — قائمة + فلترة + أعمدة (رقم التقرير، مركبة، سائق،
  تاريخ، حالة مطالبة كشارة ملونة، تكلفة الإصلاح `money()`).
- `incidents/create.blade.php` — بطاقة المركبة + نموذج + رفع صور متعددة (حتى 10).
- `incidents/show.blade.php` — بطاقة التفاصيل + بطاقة حالة المطالبة (نموذج inline
  PATCH محمي بـ `incidents.edit`) + معرض صور (Alpine).

تسميات الحالة: `pending` = "قيد المراجعة", `approved` = "موافق عليه", `rejected`
= "مرفوض", `paid` = "مدفوع".

---

## مثال بسيط (Example)

```php
use App\Models\Incident;

$incident = Incident::create([
    'report_number'  => 'ACC-2026-0001',
    'vehicle_id'     => $vehicle->id,
    'driver_id'      => $driver->id,
    'incident_date'  => '2026-08-25',
    'location'       => 'الطريق السريع',
    'description'    => 'تصادم خلفي',
    'repair_cost'    => 2000,
    'claim_status'   => 'pending',
    'recorded_by'    => auth()->id(),
]);

$incident->update(['claim_status' => 'approved']);
echo $incident->has_claim; // true
```

---

## تفاعلات مع وحدات أخرى

- **المركبات/السائقون (Vehicles/Drivers):** الحادث يعود لمركبة وسائق اختياريًا.
- **التأمينات (Insurance Policies):** ربط اختياري بالبوليصة.
- **الصور (IncidentPhoto):** العلاقة `photos()` لتخزين وعرض صور الحادث.