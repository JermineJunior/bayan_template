# الإشعارات والتنبيهات (Notifications)

## الغرض (Purpose)

نظام إشعارات داخل التطبيق (database) يظهر في جرس لجميع المستخدمين، مع
تنبيهات تلقائية مجدولة تُخطر المستخدمين أصحاب الصلاحية المناسبة عن البنود
قريبة الانتهاء أو المتأخرة أو منخفضة المخزون.

- الإشعارات معلّمة بـ `alert_type` + رسالة عربية جاهزة + السجل المرتبط
  (`related_type` / `related_id`).
- التنبيهات تُفحص يوميًا بأمر مجدول `fleet:check-alerts`.

---

## المكونات (Components)

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `NotificationController` | `app/Http/Controllers/NotificationController.php` |

الطرق (محميّة بـ `auth` فقط، دون صلاحية `can:`):

- `index()` — قائمة الإشعارات (15/صفحة) → `notifications.index`.
- `markAsRead(id)` — تمييز واحد كمقروء (يرجع JSON عند طلب AJAX للجرس).
- `markAllAsRead()` — تمييز الكل كمقروء.

### الـ Routes

```php
GET    notifications                  → notifications.index
PATCH  notifications/read-all         → notifications.mark-all-read
PATCH  notifications/{id}/read        → notifications.mark-read
```

### فئة الإشعار

| الفئة | الملف |
| ----- | ----- |
| `FleetAlertNotification` | `app/Notifications/FleetAlertNotification.php` |

- `via()` → `['database']`.
- `__construct(alertType, message, related Model)`.
- البيانات المحفوظة: `alert_type`, `message`, `related_type`, `related_id`.

### أمر التنبيهات المجدول

| الأمر | الملف |
| ----- | ----- |
| `CheckFleetAlerts` | `app/Console/Commands/CheckFleetAlerts.php` |

يُجدول يوميًا في `routes/console.php` (`dailyAt('06:00')`). يستدعي الشيكات
التالية (كلٌّ يعتمد عتبة وصلاحية من `config/fleet_alerts.php`):

| المفتاح (`alert_type`) | الشرط | الصلاحية |
| ---------------------- | ----- | --------- |
| `oil_due` | `remaining_change <= threshold_km` (500 كم) | `oil.view` |
| `filter_due` | `remaining_change <= threshold_km` | `filters.view` |
| `insurance_expiring` | بوليصات `is_current` تنتهي خلال `threshold_days` (30 يوم) | `insurance-policies.view` |
| `driver_license_expiring` | رخص تنتهي خلال `threshold_days` | `drivers.view` |
| `vehicle_stopped` | مركبة `stopped` لـ `threshold_days` (30) أو أكثر | `vehicles.view` |
| `maintenance_overdue` | أمر صيانة مفتوح تجاوز `end_date` | `maintenance.view` |
| `spare_parts_low_stock` | `is_low_stock` (المتوفر ≤ الحد الأدنى) | `spare-parts.view` |

### منطق `notifyPermittedUsers()`

- يستعلم المستخدمين بالصلاحية (`User::permission($permission)`).
- يتخطى إرسال التنبيه إذا كان للمستخدم **إشعار غير مقروء** يحمل نفس
  `related_type` + `related_id` — لمنع تكرار إرسال نفس التنبيه كل يوم.

### إعدادات التنبيهات

| الملف | الدور |
| ----- | ----- |
| `config/fleet_alerts.php` | مصدر كل نوع تنبيه: العتبة والصلاحية والتسمية. |

---

## الـ Views

- `notifications/index.blade.php` — صفحة الإشعارات مع تمييز غير المقروء وزر
  "تحديد الكل كمقروء".
- داخل `layouts/app.blade.php` — الجرس (Alpine component `notificationBell`)
  مع العدّاد وزر "عرض الكل".

---

## مثال بسيط (Example)

```php
use App\Models\SparePart;
use App\Models\User;
use App\Notifications\FleetAlertNotification;

$part = SparePart::where('name', 'فلتر زيت')->first();

User::permission('spare-parts.view')->get()->each(function ($user) use ($part) {
    $user->notify(new FleetAlertNotification(
        'spare_parts_low_stock',
        "قطع الغيار منخفضة المخزون: {$part->name}",
        $part,
    ));
});
```

---

## تفاعلات مع وحدات أخرى

- **المركبات/الزيوت/الفلاتر/التأمينات/السائقين/الصيانة/قطع الغيار:** التنبيهات
  تعتمد على ما توفّره هذه الوحدات من حالات وعتبات ومحسوبات.
- **المستخدمون (Users):** التنبيه يتجه فقط لمن يملك الصلاحية المطلوبة.