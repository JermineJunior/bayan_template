# فواتير الصرف (Invoices / Warehouse Issues)

## الغرض (Purpose)

فواتير الصرف هي **الطريقة الوحيدة لصرف قطع الغيار من المخزن** لصالح أمر
صيانة معيّن. ببساطة:

- كل فاتورة تخص **أمر صيانة واحد** (المركبة التي نُجري الصيانة لها).
- الفاتورة تحتوي على **سطر أو أكثر** (بنود)، كل بند هو قطعة غيار + كمية + سعر.
- عند حفظ الفاتورة تُسجَّل حركة **صرف (issue)** تلقائيًا لكل بند، و**تنقص**
  الكمية من مخزون كل قطعة.

بهذا نضمن أن كل قطعة خرجت من المخزن تُربط بأمر صيانة، فتبقى السجلات واضحة
وقابلة للتتبع.

> ملاحظة مهمة: هذا يختلف عن **فواتير الموردين** (انظر [suppliers.md](suppliers.md))
> — فواتير الموردين هي "كم علينا أن ندفع للمورد"، أما فواتير الصرف فهي
> "قطع خرجت من المخزن لصيانة".

---

## المكونات (Components)

### الموديلات (Models)

| الموديل | الملف | الدور |
| ------- | ----- | ----- |
| `Invoice` | `app/Models/Invoice.php` | رأس الفاتورة (الذي يخص أمر الصيانة) |
| `InvoiceDetail` | `app/Models/InvoiceDetail.php` | بنود الفاتورة (قطعة + كمية + سعر) |

العلاقات:

- `Invoice` → `maintenance()` : الفاتورة تخص أمر صيانة.
- `Invoice` → `details()` : بنود الفاتورة.
- `InvoiceDetail` → `invoice()` و `sparePart()`.

الحقول:

- `Invoice`: `invoice_number`، `maintenance_id`، `date`.
  - `invoice_number` يُولَّد تلقائيًا بـ `generateInvoiceNumber()` بصيغة `INV-2026-00001`.
- `InvoiceDetail`: `invoice_id`، `spare_part_id`، `qty`، `price`.

محسوب تلقائيًا:

- `InvoiceDetail::row_sub_total` → `qty * price` (لم يُحفظ).
- `Invoice::total_amount` → مجموع `row_sub_total` لجميع البنود (لم يُحفظ).

### الـ Observer (المهم!)

| الملف | الدور |
| ----- | ----- |
| `app/Observers/InvoiceDetailObserver.php` | عند **إنشاء** أي بند فاتورة، يستدعي تلقائيًا `SparePartTransaction::recordIssue(...)` |

نقطة جوهرية: **لا يوجد كود في الـ Controller يخصم المخزون يدويًا.** المقصود هو:

```
أنشئ بند فاتورة (InvoiceDetail)
   ↓ (يُطلق observer تلقائيًا)
SparePartTransaction::recordIssue() → يضيف حركة صرف سالبة
   ↓
تقل الكمية المتوفرة تلقائيًا
```

الـ Observer مسجّل في `AppServiceProvider::boot()`.

### الـ Controller

| الـ Controller | الملف |
| -------------- | ----- |
| `InvoiceController` | `app/Http/Controllers/InvoiceController.php` |

الطرق: `index` (قائمة كل الفواتير)، `create` (نموذج لصرف قطع لأمر صيانة)،
`store` (حفظ الفاتورة)، `show` (عرض فاتورة).

#### `store` — كيف يعمل الحفظ بأمان؟

كل الحفظ يتم داخل `DB::transaction()`:

1. إنشاء رأس الفاتورة مع رقم تلقائي.
2. إنشاء بنود الفاتورة واحدًا تلو الآخر (الـ observer يخصم المخزون لكل بند).
3. إذا أي بند تجاوز المخزون المتوفر، يرمي `recordIssue` استثناءً
   (`RuntimeException`)، فيُلغى **كل شيء** (التراجع الكامل): لا فاتورة، لا
   بنود، ولا خصم جزئي للمخزون.
4. يُعرض خطأ "الكمية أكبر من المتوفر" على السطر المحدد تحديدًا.

```php
try {
    DB::transaction(function () use (...) {
        $invoice = Invoice::create([...]);
        foreach ($items as $index => $item) {
            InvoiceDetail::create([...]); // observer يخصم المخزون
        }
    });
} catch (\RuntimeException $e) {
    return back()->withInput()->withErrors(["items.{$e->getCode()}.qty" => $e->getMessage()]);
}
```

بعد نجاح الحفظ، تُستدعى `createMaintenanceExpense()` لتسجيل **مصروف الصيانة**
(انظر [expenses.md](expenses.md)): قيمته = `labor_cost` الخاص بأمر الصيانة +
إجمالي هذه الفاتورة (`qty × price`)، ويُربط بالمصدر `sourceable` (Invoice) —
أي أن مصروف الصيانة يُسجَّل **بمجرد** ربط قطع الغيار بأمر الصيانة عبر الفاتورة.

### الـ Views

في `resources/views/invoices/`:

- `create.blade.php` — نموذج صرف القطع (بنود متعددة، يختار المستخدم القطعة
  والكمية والسعر، يُعرض الإجمالي مباشرة). يستخدم Alpine.js ويوجد تكامل مع
  الـ `quantity_on_hand` لتنبيه السعر/المخزون تلقائيًا.
- `show.blade.php` — عرض فاتورة مع بنودها.
- `index.blade.php` — قائمة الفواتير (مع فلترة حسب أمر الصيانة).

### الـ Routes

في `routes/web.php`:

```php
Route::get('maintenances/{maintenance}/invoices/create', [InvoiceController::class, 'create']);
Route::post('maintenances/{maintenance}/invoices',      [InvoiceController::class, 'store']);

Route::get('invoices',       [InvoiceController::class, 'index']);
Route::get('invoices/{invoice}', [InvoiceController::class, 'show']);
```

لاحظ أن النموذج والحفظ (`create`/`store`) يُنشآن **من سياق أمر الصيانة**
(المسار يشمل `maintenances/{maintenance}/...`)، لأن كل فاتورة تخص أمر صيانة.

---

## مثال بسيط (Example)

**سيناريو:** أمر صيانة رقم `MO-2026-00001` لمركبة. نريد صرف (2) فلتر زيت و
(4) فلاتر هواء، بنفس السعر الذي أدخله المستخدم.

```php
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Maintenance;

$maintenance = Maintenance::find(1);
$invoice = Invoice::create([
    'invoice_number' => Invoice::generateInvoiceNumber('2026-08-31'), // INV-2026-00001
    'maintenance_id' => $maintenance->id,
    'date'           => '2026-08-31',
]);

InvoiceDetail::create([
    'invoice_id'     => $invoice->id,
    'spare_part_id'  => 2,   // فلتر زيت
    'qty'            => 2,
    'price'          => 25.00,
]);
// ← هنا فقط: observer يخصم 2 من مخزون فلتر الزيت تلقائيًا

InvoiceDetail::create([
    'invoice_id'     => $invoice->id,
    'spare_part_id'  => 3,   // فلتر هواء
    'qty'            => 4,
    'price'          => 15.00,
]);
// ← يخصم 4 من مخزون فلتر الهواء تلقائيًا

echo $invoice->total_amount; // (2×25) + (4×15) = 110
```

---

## تفاعلات مع وحدات أخرى

- **قطع الغيار (Spare Parts):** الفاتورة تخصم المخزون عبر `recordIssue`.
  إذا المخزون غير كافٍ، تُرفض الفاتورة كاملة.
- **أوامر الصيانة (Maintenance):** كل فاتورة تخص أمر صيانة، والنموذج يُفتح من
  داخل صفحة أمر الصيانة.
