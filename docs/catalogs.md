# كتالوج الزيوت والفلاتر (Oils & Filters)

## الغرض (Purpose)

كتالوجان مرجعيان يُستخدمان عند تسجيل تغيير الزيت/الفلتر لكل مركبة. كل صنف يحمل
**العمر الافتراضي بالكيلومترات** (`oil_life` / `filter_life`) الذي تُحسب منه
قراءة التغيير التالية.

- **الزيوت (Oils):** أنواع الزيت (محرك، ناقل حركة، هيدروليك، فرامل، دفرنس).
- **الفلاتر (Filters):** أنواع الفلاتر (زيت، هواء، وقود، مكيّف).

> ملاحظة: لا يوجد سعر في الكتالوج — التكلفة تُسجَّل لكل عملية تغيير، وليس على
> الصنف.

---

## المكونات (Components)

### النماذج (Models)

| النموذج | الملف |
| ------- | ----- |
| `Oil` | `app/Models/Oil.php` |
| `Filter` | `app/Models/Filter.php` |

**`Oil`:**

- الحقول: `oil_name`, `oil_code` (فريد), `oil_type`
  (`engine|transmission|hydraulic|brake|differential`), `oil_life`.
- العلاقة: `changes()` → `hasMany(VehicleOilChange)`.

**`Filter`:**

- الحقول: `filter_name`, `filter_code` (فريد), `filter_type`
  (`oil|air|fuel|ac`), `filter_life`.
- العلاقة: `changes()` → `hasMany(VehicleFilterChange)`.

التسميات العربية للأنواع في `config/oil_types.php` و `config/filter_types.php`.

### كتالوج الحب (CatalogController)

| الـ Controller | الملف |
| -------------- | ----- |
| `CatalogController` | `app/Http/Controllers/CatalogController.php` |

- `index(Request)` — يفتح الصفحة إن كان للمستخدم `oils.view` أو `filters.view`
  (أي صلاحية)؛ كل جدول يظهر فقط إن كانت صلاحيته محققة.
- يعرض `oil` و `filter` مع `withCount('changes')`، مقسّمة لكلٍّ بـ 10.

```php
GET catalogs → catalog.index
```

### الـ Controllers

| الـ Controller | الملف |
| -------------- | ----- |
| `OilController` | `app/Http/Controllers/OilController.php` |
| `FilterController` | `app/Http/Controllers/FilterController.php` |

الطرق (متطابقة البنية):

- `index()` — يوجّه إلى `catalog.index` (الكتالوج المقترن).
- `create()` — نموذج إنشاء.
- `store()` — حفظ؛ يدعم **إضافة سريعة عبر JSON** (`expectsJson()`) تُستخدم في
  نوافذ "＋ إضافة زيت/فلتر" داخل نماذج التغيير.
- `edit()` / `update()` — تعديل مع `Rule::unique()->ignore()`.
- `destroy()` — حذف **ممنوع** إذا وُجدت عمليات تغيير مرتبطة (`changes()->exists()`).

### الـ Routes

```php
# الزيوت
GET oils            → oils.index    (can:oils.view)
GET oils/create     → oils.create   (can:oils.view)
GET oils/{oil}/edit → oils.edit     (can:oils.view)
POST oils           → oils.store    (can:oils.create)
PUT oils/{oil}      → oils.update   (can:oils.edit)
DELETE oils/{oil}   → oils.destroy  (can:oils.delete)

# الفلاتر
GET filters            → filters.index    (can:filters.view)
GET filters/create     → filters.create   (can:filters.view)
GET filters/{filter}/edit → filters.edit  (can:filters.view)
POST filters           → filters.store    (can:filters.create)
PUT filters/{filter}   → filters.update   (can:filters.edit)
DELETE filters/{filter} → filters.destroy (can:filters.delete)
```

### الـ Views

- `catalog/index.blade.php` — صفحة "الزيوت والفلاتر" بجدولين (اسم، كود، نوع
  كشارة، العمر بالكم، عدد التغييرات) مع صلاحية عرض/إنشاء لكل جدول.
- `oils/{create,edit,_form}` و `filters/{create,edit,_form}`.

---

## مثال بسيط (Example)

```php
use App\Models\Filter;
use App\Models\Oil;

$oil = Oil::create([
    'oil_name' => 'زيت محرك 10W-40',
    'oil_code' => 'OIL-001',
    'oil_type' => 'engine',
    'oil_life' => 5000,
]);

$filter = Filter::create([
    'filter_name' => 'فلتر زيت',
    'filter_code' => 'FILT-001',
    'filter_type' => 'oil',
    'filter_life' => 5000,
]);
```

---

## تفاعلات مع وحدات أخرى

- **تغيير الزيت (Oil Changes):** عند تسجيل تغيير، يُحسب `next_change_odometer =
  odometer_when_change + oil->oil_life`.
- **تغيير الفلتر (Filter Changes):** نفس المنطق بـ `filter_life`.
- **الحذف محمي:** لا يمكن حذف صنف عليه عمليات تغيير (حفاظًا على التاريخ).