# التقارير (Reports)

## الغرض (Purpose)

مركز تقارير للقراءة فقط: يتيح للمستخدمين المصرّح لهم تصفية السجلات (مركبة/
مورد/تاريخ/حالة)، وعرض النتيجة على الشاشة، ثم **طباعتها أو تصديرها PDF**.

- التصميم موحّد: **نموذج فلترة → نتائج على الشاشة → صفحة طباعة** . كل تقرير
  يعتمد نفس نمط الأربعة ملفات (`form`/`results`/`print`/`_table`).
- فلاتر التاريخ تُمرَّر كبارامترات GET، وتُعيد صفحة الطباعة استخدام نفس الاستعلام
  الحالي (`request()->query()`) لإعادة توليد نفس النتيجة.

---

## المكونات (Components)

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `ReportController` | `app/Http/Controllers/ReportController.php` |

- `index()` — حارس الصفحة: يفتحها إذا كان للمستخدم أي واحدة من صلاحيات العرض
  العشر (`Gate::any`).
- لكل تقرير ثلاث طرق: `XForm()` (نموذج)، `XResults()` (نتائج)، `XPrint()`
  (طباعة)، وطريقة خاصة واحدة `getXData(Request)` تحمل منطق الاستعلام.

### المركز (الـ Hub)

`reports/index.blade.php` — شبكة بطاقات؛ كل بطاقة تظهر فقط مع صلاحية تقريرها
(`@can`).

### القوالب (Views)

في `resources/views/reports/<report>/`:

- `form.blade.php` — نموذج فلترة (GET) مع تضمين `reports._quick_dates` عند وجود
  تاريخ، ثم شبكة الحقول وحق "إنشاء التقرير".
- `results.blade.php` — عنوان + رابط "طباعة / تصدير PDF"
  (`route('reports.<x>.print', request()->query())`) + بطاقات إجماليات (إن وُجدت)
  + جدول عبر `@include('reports.<x>._table')`.
- `print.blade.php` — يمتد `layouts.print` ويتضمن `_table` + سطر إجمالي.
- `_table.blade.php` — جدول النتائج مع `@forelse` وشارات ملونة (`badge`).

### فلاتر التواريخ السريعة

`reports/_quick_dates.blade.php` — أزرار "اليوم / أمس / هذا الشهر / هذه السنة"
تعبّئ حقلي `from_date`/`to_date` في النموذج عبر `app.js` (لا تُرسل النموذج
تلقائيًا).

### تخطيط الطباعة

`layouts/print.blade.php` — `@page A4`، ترويسة بالشعار والاسم والتاريخ، شريط أدوات
("رجوع" + "طباعة / حفظ PDF" يستدعي `window.print()`)، وجدول بحدود عند الطباعة.
يُطلق `window.print()` تلقائيًا بعد 400ms ويعود للنتائج بعد الطباعة.

### دالة التنسيق

`app/Support/helpers.php` → `money($value, $precision = 2)` — تُنسّق المبالغ
بالفاصلة الآلاف وإزالة `.00` عند كون الرقم صحيحًا.

---

## قائمة التقارير (10)

| التقرير | صلاحية | فلترة | المصدر | الإجماليات |
| ------- | ------- | ----- | ------ | ---------- |
| استهلاك الوقود | `fuel.view` | مركبة، من/إلى تاريخ | `FuelLog` | لترات + قيمة |
| نظرة عامة على الأسطول | `vehicles.view` | حالة، إدارة | `Vehicle` | — |
| سجل تغيير الزيوت والفلاتر | `oil-changes.view` | مركبة، من/إلى تاريخ | `VehicleOilChange` + `VehicleFilterChange` | — |
| حالة التأمينات | `insurance-policies.view` | سارية فقط، تنتهي خلال أيام | `InsurancePolicy` | — |
| سجل الحوادث | `incidents.view` | مركبة، سائق، حالة مطالبة، من/إلى تاريخ | `Incident` | — |
| تقرير المصروفات | `expenses.view` | مركبة، نوع، من/إلى تاريخ | `Expense` | إجمالي المبالغ |
| مخالفات السائقين | `violations.view` | سائق، مركبة، من/إلى تاريخ | `DriverViolation` | — |
| تكاليف الصيانة | `maintenance.view` | مركبة، من/إلى تاريخ | `Maintenance` | إجمالي التكاليف |
| الموردون | `suppliers.view` | مورد، من/إلى تاريخ | `SupplierInvoice` | فواتير/مدفوع/رصيد |
| قطع الغيار | `spare-parts.view` | تصنيف، مورد، منخفض المخزون فقط | `SparePart` | عدد القطع + منخفضة |

الـ Routes لكل تقرير (داخل مجموعة `reports`، محمية بصلاحية التقرير):

```php
GET reports/<slug>              → reports.<slug>.form
GET reports/<slug>/results      → reports.<slug>.results
GET reports/<slug>/print        → reports.<slug>.print
```

---

## مثال بسيط (Example)

```php
// داخل ReportController::getExpensesData()
return Expense::query()
    ->with('vehicle')
    ->when($request->filled('vehicle_id'), fn ($q) => $q->where('vehicle_id', $request->integer('vehicle_id')))
    ->when($request->filled('from_date'),  fn ($q) => $q->whereDate('expense_date', '>=', $request->query('from_date')))
    ->when($request->filled('to_date'),    fn ($q) => $q->whereDate('expense_date', '<=', $request->query('to_date')))
    ->latest('expense_date')
    ->get();
```

---

## تفاعلات مع وحدات أخرى

- **كل وحدات النظام:** مصدر كل تقرير هو نموذج من الوحدات (وقود، مركبات، صيانة،
  موردين، قطع غيار…).
- **الإعدادات:** `layouts.print` يستخدم اسم التطبيق وشعاره.
- **الصلاحيات:** كل تقرير محمي بصلاحية `*.view` من `config/permissions.php`.