# قطع الغيار والمخزون (Spare Parts)

## الغرض (Purpose)

هذه الوحدة هي "سجل قطع الغيار" في النظام. تسمح بـ:

1. إدارة كتالوج القطع (إضافة / تعديل / حذف / بحث).
2. تتبع **المخزون** لكل قطعة عبر سجل حركات (**شراء** يزيد، **جرد** يصحّح،
   **صرف** ينقص).

النقطة الأهم: **الكمية المتوفرة (quantity on hand) ليست عمودًا في قاعدة
البيانات** — بل تُحسب دائمًا كمجموع كل الحركات المسجلة على القطعة. هذا يضمن
أن تصحيح المخزون (الجرد) يعمل بشكل طبيعي دون كسر الأرقام.

### صرف القطع

صرف القطع (تخفيض المخزون لاستخدامها في صيانة) لا يتم من هنا مباشرة — بل من
خلال **فواتير الصرف** داخل أمر الصيانة (انظر [invoices.md](invoices.md)).

### شراء القطع

شراء القطع (زيادة المخزون) لا يتم من هنا مباشرة أيضًا — بل من خلال **بند في
فاتورة مورد** (انظر [suppliers.md](suppliers.md)). عند حفظ فاتورة مورد تحتوي
على بنود قطع غيار، يضيف `SupplierInvoiceDetailObserver` تلقائيًا حركة شراء لكل
بند. 

---

## المكونات (Components)

### الموديلات (Models)

| الموديل | الملف | الدور |
| ------- | ----- | ----- |
| `SparePart` | `app/Models/SparePart.php` | بيانات القطعة نفسها |
| `SparePartTransaction` | `app/Models/SparePartTransaction.php` | سجل الحركات: شراء / جرد / صرف |

#### `SparePart`

الحقول الأساسية:

- `part_number` — رقم تلقائي فريد بصيغة `SP-00001` (يُولَّد تلقائيًا عند الإنشاء).
- `name` — اسم القطعة.
- `category` — التصنيف (اختياري، مثلا "إطارات").
- `default_supplier_id` — المورد الافتراضي (اختياري).
- `purchase_price` — سعر الشراء الافتراضي.
- `minimum_quantity` — حد أدنى للتنبيه (تحت هذا الرقم تُعتبر القطعة "منخفضة المخزون").

الميزات (Accessors) المحسوبة تلقائيًا — ليست أعمدة في DB:

- `quantity_on_hand` → مجموع كل الحركات (`transactions()->sum('quantity')`).
- `is_low_stock` → `quantity_on_hand <= minimum_quantity`.

#### `SparePartTransaction`

حقول مهمة:

- `type` — `purchase` | `stocktake` | `issue`.
- `quantity` — موجب للشراء، **سالب** للصرف، وأي فرق (موجب أو سالب) للجرد.
- `spare_part_id`, `supplier_id`, `maintenance_order_id`, `unit_price`,
  `notes`, `recorded_by`.

**مهم:** لا تنشئ الحركات مباشرة بـ `SparePartTransaction::create()` — استخدم
الطرق الثابتة الجاهزة دائمًا:

- `recordPurchase($part, $qty, $supplier, $user, $unitPrice?, $notes?)`
  — تزيد المخزون وتتطلب موردًا.
- `recordIssue($part, $qty, $maintenance, $user, $unitPrice?, $notes?)`
  — تنقص المخزون (تُخزّن كمية سالبة) وترفض إذا تجاوزت الكمية المتوفرة
  (ترمي `RuntimeException`).
- `recordStocktake($part, $countedQty, $user, $notes?)`
  — تأخذ الرقم الفعلي المُحصى من المستخدم وتحسب وتخزّن **الفرق** تلقائيًا.

### الـ Controllers

| الـ Controller | الملف | الدور |
| -------------- | ----- | ----- |
| `SparePartController` | `app/Http/Controllers/SparePartController.php` | كتالوج CRUD + بحث/فلترة + عرض سجل الحركات |
| `SparePartStocktakeController` | `app/Http/Controllers/SparePartStocktakeController.php` | تسجيل جرد |

> ملاحظة: لا يوجد Controller منفصل للشراء — الشراء يتم من خلال بنود فواتير
> الموردين (راجع [suppliers.md](suppliers.md)).

ملاحظات سلوكية:

- **الحذف ممنوع** إذا كانت القطعة عليها أي حركات مخزون (للحفاظ على التاريخ).
- بحث الـ index يبحث في `part_number` و `name`، ويمكن فلترة بالتصنيف أو
  بفعل "منخفض المخزون" فقط.
- الجرد محميّ بصلاحية الإنشاء `spare-parts.create`.

### الـ Views

في `resources/views/spare-parts/`:

- `index.blade.php` — قائمة القطع مع بحث وفلترة.
- `create.blade.php` + `edit.blade.php` + `_form.blade.php` — نموذج إنشاء/تعديل.
- `show.blade.php` — تفاصيل القطعة + سجل الحركات.
- `stocktake.blade.php` — نموذج تسجيل جرد.

### الـ Routes

في `routes/web.php`:

```php
Route::resource('spare-parts', SparePartController::class);

Route::middleware('can:spare-parts.create')->group(function () {
    Route::get('spare-parts/{sparePart}/stocktake', [SparePartStocktakeController::class, 'create']);
    Route::post('spare-parts/{sparePart}/stocktake',[SparePartStocktakeController::class, 'store']);
});
```

---

## مثال بسيط (Example)

**سيناريو:** استلمنا 50 إطارًا من مورد عبر فاتورة مورد، ثم جردنا المخزن
فوجدنا 48 فقط.

```php
use App\Models\SparePart;
use App\Models\Supplier;
use App\Models\SupplierInvoiceDetail;

$part = SparePart::create(['name' => 'إطار 215/60R16']); // ينتج SP-00001 تلقائيًا

// 1) شراء 50 إطار — عبر بند في فاتورة المورد (يُظهر المتحرك recordPurchase تلقائيًا)
use App\Models\SupplierInvoice;

$invoice = Supplier::find(1)->invoices()->create([
    'invoice_number' => SupplierInvoice::generateInvoiceNumber(), // PINV-2026-00001
    'amount'         => 5000,
    'invoice_date'   => now(),
    'recorded_by'    => auth()->id(),
]);

SupplierInvoiceDetail::create([
    'supplier_invoice_id' => $invoice->id,
    'spare_part_id'       => $part->id,
    'qty'                 => 50,
    'price'               => 100,
]); // ← المخزون يصبح 50 تلقائيًا (عبر الـ Observer)

// 2) جرد — المخزون يصبح 48
use App\Models\SparePartTransaction;
SparePartTransaction::recordStocktake($part, 48, $user);

echo $part->quantity_on_hand; // 48 (محسوب تلقائيًا، ما عُدّل يدويًا)
```

---

## تفاعلات مع وحدات أخرى

- **فواتير الصرف (Invoices):** تضاف حركة `issue` للقطعة عند إنشاء فاتورة داخل
  أمر صيانة — لا تُنشأ هذه الحركة يدويًا من هذه الوحدة.
- **فواتير الموردين (Suppliers):** تشتري القطع عبر بنود فاتورة المورد،
  والمتحرك `SupplierInvoiceDetailObserver` يضيف حركة `purchase` للقطعة تلقائيًا.
  القطعة قد ترتبط بموردها الافتراضي.
