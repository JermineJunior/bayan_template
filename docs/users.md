# المستخدمون والأدوار (Users & Roles)

## الغرض (Purpose)

وحدة إدارة المستخدمين (Admin) تتيح إنشاء حسابات المستخدمين وتعيين دور واحد لكل
مستخدم، مع تعديل وحذف وتفعيل/تعطيل الحسابات و إعادة تعيين كلمة المرور مباشرة
(بلا بريد إلكتروني — أداة داخلية).

وحدة الأدوار تدير الأدوار والصلاحيات عبر **Spatie Laravel Permission**.
مصدر الصلاحيات الوحيد: `config/permissions.php`. الأدوار المسبقة التهيئة:

- **Admin:** كل الصلاحيات.
- **Viewer:** كل صلاحيات `*.view`.

---

## المكونات (Components)

### المستخدمون (Users)

| المكوّن | الملف |
| ------- | ----- |
| `UserController` | `app/Http/Controllers/Admin/UserController.php` |
| `UserService` | `app/Services/UserService.php` |
| `UserPolicy` | `app/Policies/UserPolicy.php` |
| `StoreUserRequest` | `app/Http/Requests/Admin/StoreUserRequest.php` |
| `UpdateUserRequest` | `app/Http/Requests/Admin/UpdateUserRequest.php` |
| `ResetUserPasswordRequest` | `app/Http/Requests/Admin/ResetUserPasswordRequest.php` |
| `User` | `app/Models/User.php` |

**نموذج `User`** — الحقول المهمة:

- `name`, `username` (فريد)، `email` (اختياري)، `password` (مُشفّر)،
  `font_size` (الافتراضي `default`)، `is_active` (boolean).
- يستخدم `HasRoles` (Spatie) و `Notifiable`.

**سلوكيات مهمة:**

- **دور واحد لكل مستخدم:** `UserService::assignRole()` يستدعي `$user->syncRoles($role)`
  — لذلك لا يمكن أن يكون للمستخدم أكثر من دور واحد أبدًا عبر النظام.
- **حذف/تعطيل ذاتي ممنوع:** لا يمكن للمستخدم حذف حسابه أو تعطيله (`UserPolicy`).
- **تعطيل الحساب عند تسجيل الدخول:** إذا الحساب `is_active = false` و كلمة المرور
  صحيحة → يُرفض برسالة "تم تعطيل هذا الحساب" (بدون كشف وجود المستخدم).
- **إعادة تعيين كلمة المرور:** تغيير مباشر بدون بريد أو توكن.
- **كلمة مرور عشوائية:** إذا لم يُدخل المستخدم كلمة مرور عند الإنشاء، يُولّد
  `Str::password(8)` ويُعرض مرة واحدة في رسالة النجاح.

**الطرق (`UserController`):**

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index()` | `users.view` | قائمة مقسّمة بـ 10، ترتيب حسب `name` |
| `create()` | `users.view` | نموذج إنشاء مع قائمة الأدوار |
| `store()` | `users.create` | إنشاء مستخدم + تعيين دور |
| `edit()` | `users.view` | نموذج تعديل |
| `update()` | `users.edit` | تحديث البيانات وإعادة تعيين الدور |
| `resetPassword()` | `users.edit` | إعادة تعيين كلمة المرور مباشرة |
| `deactivate()` | `users.edit` | تعطيل الحساب |
| `activate()` | `users.edit` | تفعيل الحساب |
| `destroy()` | `users.delete` | حذف المستخدم (ليس حساب ذاتي) |

#### الـ Routes

```php
GET users                    → users.index       (can:users.view)
GET users/create             → users.create      (can:users.view)
GET users/{user}             → users.edit        (can:users.view)
POST users                   → users.store       (can:users.create)
PUT users/{user}             → users.update      (can:users.edit)
POST users/{user}/reset-password → users.reset-password (can:users.edit)
POST users/{user}/deactivate    → users.deactivate     (can:users.edit)
POST users/{user}/activate      → users.activate       (can:users.edit)
DELETE users/{user}          → users.destroy     (can:users.delete)
```

#### الـ Views

- `admin/users/index.blade.php` — قائمة المستخدمين مع الدور والحالة.
- `admin/users/create.blade.php` + `edit.blade.php` + `_form.blade.php` — نموذج
  الإنشاء/التعديل مع تحديد الدور و "اترك كلمة المرور فارغة لتوليد تلقائي".
- `components/user-status-toggle.blade.php` — زر تعطيل/تفعيل (Alpine).

### الأدوار (Roles)

| المكوّن | الملف |
| ------- | ----- |
| `RoleController` | `app/Http/Controllers/Admin/RoleController.php` |
| `RolePolicy` | `app/Policies/RolePolicy.php` |
| `RoleRequest` | `app/Http/Requests/Admin/RoleRequest.php` |
| `PermissionSeeder` | `database/seeders/PermissionSeeder.php` |
| `RoleSeeder` | `database/seeders/RoleSeeder.php` |

**`syncRolePermissions()`** — يستدعي `Permission::firstOrCreate()` لكل صلاحية مختارة
قبل `syncPermissions()` — لذا الصلاحيات الجديدة في الكتالوج تعمل فورًا قبل إعادة
تهيئة البذور.

**`RoleRequest`** — يتحقق أن كل صلاحية مختارة موجودة في `config/permissions.php`
(وليس في قاعدة البيانات مباشرة).

**الطرق (`RoleController`):**

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index()` | `roles.view` | قائمة الأدوار مع عدد الصلاحيات |
| `create()` | `roles.view` | نموذج إنشاء مع صلاحيات مجمّعة حسب المنطقة |
| `store()` | `roles.create` | إنشاء دور + مزامنة الصلاحيات |
| `edit()` | `roles.view` | نموذج تعديل مع الصلاحيات الحالية |
| `update()` | `roles.edit` | تحديث الدور |
| `destroy()` | `roles.delete` | حذف الدور |

#### الـ Routes

```php
GET roles            → roles.index    (can:roles.view)
GET roles/create     → roles.create   (can:roles.view)
GET roles/{role}     → roles.edit     (can:roles.view)
POST roles           → roles.store    (can:roles.create)
PUT roles/{role}     → roles.update   (can:roles.edit)
DELETE roles/{role}  → roles.destroy  (can:roles.delete)
```

#### الـ Views

- `admin/roles/index.blade.php` — قائمة الأدوار مع عدد الصلاحيات.
- `admin/roles/create.blade.php` + `edit.blade.php` + `_form.blade.php` —
  حقل الاسم + شبكة صلاحيات مجمّعة حسب المنطقة مع تحديد الكل/إلغائه (Alpine).

---

## مثال بسيط (Example)

```php
use App\Models\User;
use Spatie\Permission\Models\Role;

// إنشاء مستخدم مع دور Viewer
$user = User::create([
    'name'     => 'محمد',
    'username' => 'mohammed',
    'password' => 'secret',
]);

$user->assignRole('Viewer'); // تعيين دور واحد فقط

// التحقق
$user->hasRole('Viewer');    // true
$user->can('vehicles.view'); // true (Viewer يملك كل *.view)
```

---

## تفاعلات مع وحدات أخرى

- **الإشعارات (Notifications):** التنبيهات تُرسل فقط لمستخدمين يملكون الصلاحية
  المطلوبة عبر Spatie.
- **إعدادات التطبيق (Application Settings):** `font_size` يخزن على `User` وتُمرّر للواجهة عبر
  view composer.
- **الثيم (Theme):** يخزن في كوكي وليس في قاعدة البيانات.