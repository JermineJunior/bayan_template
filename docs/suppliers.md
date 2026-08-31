# الموردين (Suppliers / Accounts Payable)

## الغرض (Purpose)

وحدة الموردين تتبع تعاملات الشركة مع الموردين (مثل موردي قطع الغيار):

- **المورد** (`Supplier`) — بيانات المورد (الاسم، الهاتف، العنوان).
- **فاتورة مورد** (`SupplierInvoice`) — "كم علينا أن ندفع لهذا المورد"
  (فاتورة شراء تسجَّل على المورد).
- **دفعة مورد** (`SupplierPayment`) — "كم دفعنا منه حتى الآن".

من مجموع الفواتير والمدفوعات يُحسب **الرصيد المتبقي** (`balance`) تلقائيًا:

```
الرصيد = إجمالي الفواتير − إجمالي المدفوعات
```

> ⚠️ **لا تخلط بين الأنواع:** فواتير الموردين (هذه الوحدة) هي التزامات مالية
> تجاه المورد. أما **فواتير الصرف** فهي صرف قطع من المخزن لصالح أمر صيانة —
> انظر [invoices.md](invoices.md).

---

## المكونات (Components)

### الموديلات (Models)

| الموديل | الملف | الدور |
| ------- | ----- | ----- |
| `Supplier` | `app/Models/Supplier.php` | بيانات المورد |
| `SupplierInvoice` | `app/Models/SupplierInvoice.php` | فاتورة على المورد |
| `SupplierPayment` | `app/Models/SupplierPayment.php` | دفعة على فاتورة |

#### العلاقات

- `Supplier` → `invoices()` (فواتير المورد).
- `Supplier` → `payments()` عبر `hasManyThrough` (كل مدفوعات فواتير المورد).
- `SupplierInvoice` → `payments()` (المدفوعات المسددة لهذه الفاتورة).

#### المحسوب تلقائيًا (لا يُحفظ في القاعدة)

على **المورد**:

- `total_invoiced` — مجموع كل فواتيره.
- `total_paid` — مجموع كل مدفوعاته.
- `balance` — الفرق بينهما.

على **فاتورة المورد**:

- `total_paid` — ما دُفع على هذه الفاتورة.
- `balance` — الباقي على هذه الفاتورة.
- `is_paid_in_full` — هل سُدّدت بالكامل (`balance <= 0`).

### الـ Controllers

| الـ Controller | الملف | الدور |
| -------------- | ----- | ----- |
| `SupplierController` | `app/Http/Controllers/SupplierController.php` | CRUD للمورد |
| `SupplierInvoiceController` | `app/Http/Controllers/SupplierInvoiceController.php` | تسجيل فواتير المورد |
| `SupplierPaymentController` | `app/Http/Controllers/SupplierPaymentController.php` | تسجيل الدفعات |

ملاحظات سلوكية:

- **منع حذف المورد** إذا كان لديه فواتير (للحفاظ على السجل المالي).
- فواتير المورد تُنشأ **من سياق المورد** (`suppliers/{supplier}/invoices/...`).
- الدفعات تُنشأ **من سياق الفاتورة** (`supplier-invoices/{invoice}/payments`).
- **الدفع الزائد مسموح به عمدًا** (`overpayment`): لا يوجد رفض برمجي — فقط
  رسالة تأكيد من واجهة المستخدم.

### بنود فواتير المورد (قطع الغيار)

فواتير المورد **قد** تتضمن بنودًا لقطع الغيار (اختياريًا — فاتورة الخدمات مثلًا
لا تحتاج بنودًا):

- كل بند هو `SupplierInvoiceDetail` (قطعة + كمية + سعر) مرتبط بالفاتورة.
- عند حفظ بند، يضيف `SupplierInvoiceDetailObserver` تلقائيًا حركة **شراء** تزيد
  المخزون (يراجع [spare-parts.md](spare-parts.md)).
- الحفظ كله داخل `DB::transaction` — لو فشل أي بند، تُلغى الفاتورة والبنود
  وأي تغيير في المخزون.
- `amount` يبقى **حقلًا يدويًا حقيقيًا**: واجهة النموذج تقترح مجموع البنود في
  حقل المبلغ، لكن إن عدّله المستخدم يدويًا لا يُعاد تغييره. قد يختلف المبلغ عن
  مجموع البنود عمدًا (شحن/ضرائب/خصومات غير مسجلة لكل بند) — وفي صفحة العرض
  تظهر ملاحظة معلوماتية فقط: "قيمة الفاتورة تختلف عن مجموع البنود (X)".

### الـ Views

في `resources/views/suppliers/`:

- `index.blade.php` — قائمة الموردين.
- `show.blade.php` — ملف المورد (الفواتير + الرصيد).
- `create.blade.php` / `edit.blade.php` / `_form.blade.php` — النماذج.

في `resources/views/supplier-invoices/`:

- `create.blade.php` — نموذج تسجيل فاتورة على مورد (مع بنود قطع غيار اختيارية
  + اقتراح المبلغ تلقائيًا من مجموع البنود).
- `show.blade.php` — تفاصيل الفاتورة + سجل مدفوعاتها + جدول البنود (إن وُجدت).

### الـ Routes

في `routes/web.php`:

```php
Route::resource('suppliers', SupplierController::class);

Route::get('suppliers/{supplier}/invoices/create', [SupplierInvoiceController::class, 'create']);
Route::post('suppliers/{supplier}/invoices',       [SupplierInvoiceController::class, 'store']);
Route::get('supplier-invoices/{invoice}',          [SupplierInvoiceController::class, 'show']);

Route::post('supplier-invoices/{invoice}/payments', [SupplierPaymentController::class, 'store']);
```

الصلاحيات: `suppliers.view` للقراءة، `suppliers.create` لإنشاء فواتير.

---

## مثال بسيط (Example)

**سيناريو:** لدينا مورد "ورشة النور". نستلم فاتورة منه بقيمة 1000، ثم ندفع
600 على هذا الحساب.

```php
use App\Models\Supplier;

$supplier = Supplier::create(['name' => 'مورد القطع']);
echo $supplier->total_invoiced; // 0
echo $supplier->balance;        // 0

// فاتورة على المورد = علينا 1000 (الرقم يُولَّد تلقائيًا إن تُرك فارغًا)
$invoice = $supplier->invoices()->create([
    'invoice_number' => SupplierInvoice::generateInvoiceNumber(), // PINV-2026-00001
    'amount'         => 1000,
    'invoice_date'   => now(),
    'recorded_by'    => auth()->id(),
]);
echo $invoice->balance;         // 1000
echo $supplier->balance;        // 1000

// دفعة = دفعنا 600، فيبقى 400
$invoice->payments()->create([
    'amount'      => 600,
    'paid_at'     => now(),
    'recorded_by' => auth()->id(),
]);
echo $invoice->balance;         // 400
echo $invoice->is_paid_in_full; // false
echo $supplier->balance;        // 400
```

---

## تفاعلات مع وحدات أخرى

- **قطع الغيار (Spare Parts):** عملية شراء قطعة تتطلب اختيار مورد، وقد يكون
  للقطعة مورد افتراضي. انظر [spare-parts.md](spare-parts.md).
- هذه الوحدة مستقلة ماليًا عن فواتير الصرف (القطع الخارج من المخزن).