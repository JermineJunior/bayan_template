# تسجيل الدخول والخروج (Authentication)

## الغرض (Purpose)

يمنح المستخدمين الدخول إلى النظام باسم المستخدم وكلمة المرور، ويحمي كل الوحدات
المحمية خلف الوسيط `auth`، ويفرض تسجيل الخروج التلقائي للحسابات التي تم
تعطيلها.

- تسجيل الدخول يتم **باسم المستخدم وكلمة المرور** (وليس البريد الإلكتروني).
- كل المسارات في `routes/web.php` محميّة بمجموعة `auth`.

---

## المكونات (Components)

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `AuthenticatedSessionController` | `app/Http/Controllers/Auth/AuthenticatedSessionController.php` |
| `LoginRequest` | `app/Http/Requests/Auth/LoginRequest.php` |
| `EnsureUserIsActive` (Middleware) | `app/Http/Middleware/EnsureUserIsActive.php` |

الطرق:

- `create()` — يعرض نموذج `auth.login`.
- `store(LoginRequest)` — يستدعي `$request->authenticate()`، يجدد الجلسة، ثم يوجّه
  إلى `intended('/')`.
- `destroy(Request)` — يسجل خروج المستخدم ويجدد التوكن ويهبط مع الجلسة، ثم يوجّه
  إلى `/`.

### الـ Routes

في `routes/auth.php` (بدلاً من أسفل `web.php`):

```php
Route::get('login',  [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store']);
Route::post('logout',[AuthenticatedSessionController::class, 'destroy'])->name('logout');
```

### الحماية من التخمين (Rate Limiting)

تتم الحماية داخل `LoginRequest::authenticate()` عبر `RateLimiter` (وليست وسيطًا
على مستوى المسار):

- المفتاح = `username|ip`.
- يسمح بـ **5 محاولات**؛ بعدها تُشعل رسالة عربية "محاولات تسجيل دخول كثيرة
  جدًا… بعد :seconds ثانية".
- المحاولة الفاشلة تستدعي `RateLimiter::hit()`، والنجاح يستدعي `RateLimiter::clear()`.

### تعطيل الحسابات (Deactivation)

عند تعطيل حساب (`is_active = false`):

- **عند الدخول:** يُرفض الطلب برسالة "تم تعطيل هذا الحساب" — ولكن فقط بعد
  التحقق من أن كلمة المرور صحيحة، حتى لا يكشف النظام وجود اسم المستخدم.
- **في جلسة نشطة:** الوسيط `EnsureUserIsActive` يسجل الخروج ويهبط مع الجلسة
  ويوجّه إلى صفحة الدخول عند أول طلب تالٍ.

### الـ Views

- `auth/login.blade.php` — نموذج: اسم المستخدم، كلمة المرور، خيار "تذكرني".
- `layouts/guest.blade.php` — تخطيط بطاقة الوسطية لصفحة الدخول مع شعار التطبيق
  واسمه ومحدّد الثيم.

---

## مثال بسيط (Example)

```php
// يُنجز داخل LoginRequest::authenticate()
use Illuminate\Support\Facades\Auth;

$validated = Auth::validate([
    'username' => $this->username,
    'password' => $this->password,
]);
// إذا الحساب غير نشط → يرفض "تم تعطيل هذا الحساب"
// إذا صحت البيانات → Auth::login($user)
```

---

## تفاعلات مع وحدات أخرى

- **المستخدمون/الأدوار (Users/Roles):** فكرة "الحساب النشط" تأتي من `User.is_active`
  وتحدد صلاحية الدخول.
- **الإشعارات (Notifications):** جميع المستخدمين المحميين بـ `auth` يستقبلون
  الإشعارات في الجرس.