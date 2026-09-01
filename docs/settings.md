# الإعدادات والتفضيلات (Settings & Preferences)

## الغرض (Purpose)

قسمان منفصلان:

1. **إعدادات التطبيق** (Admin): اسم التطبيق وشعاره — يُطبقان على كل المستخدمين.
2. **التفضيلات الشخصية** (Account): حجم الخط — لكل حساب على حدة.

> ملاحظة مهمة: **الثيم** (فاتح/داكن) ليس إعدادًا في قاعدة البيانات ولا تفضيلًا
> شخصيًا — بل يُخزن في **كوكي** نصية باسم `theme` (مُستثناة من التشفير في
> `bootstrap/app.php`).

---

## المكونات (Components)

### إعدادات التطبيق (Settings)

| المكوّن | الملف |
| ------- | ----- |
| `SettingsController` | `app/Http/Controllers/Admin/SettingsController.php` |
| `UpdateSettingsRequest` | `app/Http/Requests/Admin/UpdateSettingsRequest.php` |
| `SettingsService` | `app/Services/SettingsService.php` |
| `Setting` | `app/Models/Setting.php` |

- **`Setting`:** تخزين مفتاح-قيمة؛ المفتاح الأولي هو المفتاح النصي (`key`)
  وليس `id` تلقائيًا.
- **`SettingsService`:** CRUD مع تخزين مؤقت (Cache) لكل مفتاح بادئة `settings.`
  (`get`, `set`, `has`, `forget`).
- **`SettingsController`:** `edit()` يعرض `admin.settings.edit`؛ و `update()` يخزّن
  `app_name` ويرفع الشعار (إن وُجد) إلى `logos` ثم يخزن مساره في `logo_path`.

قواعد التحقق (`app_name`: مطلوب حتى 255؛ `logo`: صورة حتى 2048KB).

> اسم التطبيق والشعار لا يمرّان عبر الـ Controller مباشرة — بل عبر **مكوّن
> العرض الشامل (global view composer)** في `AppServiceProvider` الذي يشارك
> `appName` و `logoUrl` و `userFontSize` و `theme` مع كل صفحة.

#### الـ Routes (صلاحية واحدة `settings.edit`)

```php
GET settings → settings.edit
PUT settings → settings.update
```

#### الـ View

`admin/settings/edit.blade.php` — نموذج اسم التطبيق + الشعار مع معاينة الشعار
الحالي (enctype="multipart/form-data").

### التفضيلات الشخصية (Preferences / Account)

| المكوّن | الملف |
| ------- | ----- |
| `PreferencesController` | `app/Http/Controllers/Account/PreferencesController.php` |
| `UpdatePreferencesRequest` | `app/Http/Requests/Account/UpdatePreferencesRequest.php` |

- حاليًا تفضيل واحد: `font_size` بقيم `small | default | large`، يُخزن على سجل
  `User` نفسه.
- `edit()` و `update()` يعرضان ويحدّثان `auth()->user()->font_size`.

قواعد التحقق (`font_size`: مطلوب داخل القيم الثلاث).

#### الـ Routes (محميّة بـ `auth` فقط، دون صلاحية)

```php
GET account/preferences           → account.preferences.edit
PUT account/preferences           → account.preferences.update
```

#### الـ View

`account/preferences.blade.php` — أزرار راديو "حجم الخط" (صغير/افتراضي/كبير).

---

## الـ Views

- `admin/settings/edit.blade.php` — إعدادات التطبيق.
- `account/preferences.blade.php` — التفضيلات الشخصية.

---

## مثال بسيط (Example)

```php
use App\Services\SettingsService;

// قراءة اسم التطبيق (مع افتراضي)
$appName = app(SettingsService::class)->get('app_name', config('app.name'));

// تحديث اسم التطبيق
app(SettingsService::class)->set('app_name', 'نظام إدارة الأسطول');
```

---

## تفاعلات مع وحدات أخرى

- **التخطيطات (Layouts):** `appName`/`logoUrl`/`theme`/`userFontSize` تُشارك مع
  كل الصفحات عبر view composer.
- **المستخدمون (Users):** `font_size` عمود في جدول `users` (حقل ضمن `User.fillable`).