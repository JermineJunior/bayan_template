# لوحة المعلومات (Dashboard)

## الغرض (Purpose)

صفحة الهبوط بعد تسجيل الدخول (`/`). تقدم نظرة حيّة على الأرقام الرئيسية للأسطول
والتنبيهات القريبة، وتُظهر رسومين (Chart.js) وجدولين للترتيب.

> المسار `/` محمي بالوسيط `auth` فقط — لا يتطلب صلاحية `can:`، وأي مستخدم
> مسجّل الدخول يرى اللوحة.

---

## المكونات (Components)

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `DashboardController` | `app/Http/Controllers/DashboardController.php` |

الطريقة `index()` تحسب القيم التالية وتمرّرها إلى العرض `dashboard`:

| المتغير | المعنى |
| ------- | ------ |
| `totalVehicles` | عدد المركبات. |
| `activeVehicles` / `maintenanceVehicles` / `stoppedVehicles` | عدد المركبات حسب الحالة. |
| `licensesExpiringSoon` | سائقو رخصهم تنتهي خلال 30 يومًا. |
| `insurancesExpiringSoon` | البوليصات السارية (`is_current`) التي `end_date <= now()+30`. |
| `dueOilChanges` | مركبات موعد تغيير الزيت التالي لها (`next_change_odometer`) أقل من العداد الحالي. |
| `fleetFuelCost` | تكلفة وقود الشهر الحالي `Σ(liters×price_per_liter − discount)`. |
| `monthlyMaintenanceCost` | تكلفة صيانة الشهر الحالي `Σ(total_cost)` (غير الملغاة). |
| `topFuelVehicles` | أفضل 5 مركبات لترًا هذا الشهر. |
| `topMaintenanceVehicles` | أفضل 5 مركبات تكلفةً الصيانة (غير الملغاة). |
| `vehicleStatusData` | عدد المركبات لكل حالة (لرسمة الدونات). |
| `monthlyFuelConsumption` | لترات الوقود لكل شهر لآخر 6 أشهر (بأسماء الأشهر العربية). |
| `expiringPolicies` | البوليصات السارية التي تنتهي خلال 30 يومًا (للجدول). |

### الـ View

| الملف | المحتوى |
| ----- | ------- |
| `resources/views/dashboard.blade.php` | 9 بطاقات إحصائية + جدول "تأمينات قريبة الانتهاء" + رسمة الدونات "توزيع المركبات حسب الحالة" + رسمة الأعمدة "استهلاك الوقود الشهري" + جدولا الترتيب. |

تستخدم الدالة `money()` للمبالغ و `number_format()` للأعداد.

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** العدادات والحالات والتوزيع مصدرها `Vehicle`.
- **التأمينات (Insurance Policies):** `days_until_expiry` و `is_expired` تُستخدم
  في جدول "قريبة الانتهاء".
- **الوقود (Fuel Logs):** التكلفة الشهرية وأفضل مستهلكي الوقود.
- **الصيانة (Maintenance):** التكلفة الشهرية وأفضل المركبات تكلفة.
- **الزيوت (Oil Changes):** `dueOilChanges` من `next_change_odometer`.