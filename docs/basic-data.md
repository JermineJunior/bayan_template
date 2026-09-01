# الإدارات والأقسام (Managements & Departments)

## الغرض (Purpose)

الإدارات والأقسام تمثلان الهيكل التنظيمي للأسطول:

- **الإدارة (Management):** الجهة المشرفة على المركبات (أدنى مستوى هرمي).
- **القسم (Department):** فرعي للإدارة، يرتبط به السائقون.

> ملاحظة: المركبة ترتبط بالإدارة (`management_id`) وليس بالقسم مباشرة.

---

## المكونات (Components)

### الإدارات (Managements)

| المكوّن | الملف |
| ------- | ----- |
| `ManagementController` | `app/Http/Controllers/BasicData/ManagementController.php` |
| `Management` | `app/Models/Management.php` |

**نموذج `Management`:**

- الحقول: `name` (فريد)، `status` (`active`/`inactive`).
- العلاقات: `vehicles()` → `hasMany(Vehicle)`, `departments()` → `hasMany(Department)`.

**الطرق:**

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index()` | `managements.view` | قائمة مع `withCount('departments','vehicles')` |
| `create()` | `managements.view` | نموذج إنشاء |
| `store()` | `managements.create` | حفظ إدارة جديدة |
| `edit()` | `managements.view` | نموذج تعديل |
| `update()` | `managements.edit` | تحديث |
| `destroy()` | `managements.delete` | حذف (ممنوع إذا وُجدت أقسام/مركبات مرتبطة) |

**حماية الحذف:** لا يمكن حذف إدارة إذا كانت تتضمن أقسامًا أو مركبات.

### الأقسام (Departments)

| المكوّن | الملف |
| ------- | ----- |
| `DepartmentController` | `app/Http/Controllers/BasicData/DepartmentController.php` |
| `Department` | `app/Models/Department.php` |

**نموذج `Department`:**

- الحقول: `name` (فريد)، `status` (`active`/`inactive`)، `management_id`
  (اختياري، `cascadeOnDelete`).
- العلاقات: `management()` → `belongsTo(Management)`,
  `drivers()` → `hasMany(Driver)`.

**الطرق:**

| الطريقة | الصلاحية | الوصف |
| ------- | -------- | ----- |
| `index()` | `departments.view` | قائمة مع `withCount('drivers')` |
| `create()` | `departments.view` | نموذج مع قائمة الإدارات |
| `store()` | `departments.create` | حفظ |
| `edit()` | `departments.view` | نموذج تعديل |
| `update()` | `departments.edit` | تحديث |
| `destroy()` | `departments.delete` | حذف (ممنوع إذا وُجد سائقون مرتبطة) |

**حماية الحذف:** لا يمكن حذف قسم إذا كان عليه سائقون.

### الـ Routes

```php
# الإدارات
GET managements                 → managements.index   (can:managements.view)
GET managements/create          → managements.create  (can:managements.view)
GET managements/{management}    → managements.edit    (can:managements.view)
POST managements                → managements.store   (can:managements.create)
PUT managements/{management}    → managements.update  (can:managements.edit)
DELETE managements/{management} → managements.destroy (can:managements.delete)

# الأقسام
GET departments                 → departments.index   (can:departments.view)
GET departments/create          → departments.create  (can:departments.view)
GET departments/{department}    → departments.edit    (can:departments.view)
POST departments                → departments.store   (can:departments.create)
PUT departments/{department}    → departments.update  (can:departments.edit)
DELETE departments/{department} → departments.destroy (can:departments.delete)
```

### الـ Views

- `basic-data/managements/index.blade.php` — قائمة الإدارات مع الحالة وعدد الأقسام
  والمركبات.
- `basic-data/managements/create.blade.php` + `edit.blade.php` + `_form.blade.php`.
- `basic-data/departments/index.blade.php` — قائمة الأقسام مع الإدارة والحالة
  وعدد السائقين.
- `basic-data/departments/create.blade.php` + `edit.blade.php` + `_form.blade.php`.

---

## مثال بسيط (Example)

```php
use App\Models\Department;
use App\Models\Management;

$management = Management::create([
    'name'   => 'إدارة الأسطول',
    'status' => 'active',
]);

$department = Department::create([
    'name'          => 'قسم الصيانة',
    'status'        => 'active',
    'management_id' => $management->id,
]);
```

---

## تفاعلات مع وحدات أخرى

- **المركبات (Vehicles):** المركبة ترتبط بالإدارة عبر `management_id`.
- **السائقون (Drivers):** السائق يرتبط بالقسم عبر `department_id`.
- **الهيكل التنظيمي:** الإدارة → الأقسام → السائقون → المركبات.