@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('content')
    @php
        // Re-bind submitted (old) line items after a validation redirect, or
        // fall back to no line items (they're optional).
        $initialItems = old('items') ? array_values(old('items')) : [];

        // Map any server-side per-line validation error back to its row.
        $lineErrors = [];
        foreach ($initialItems as $i => $item) {
            foreach (['spare_part_id', 'qty', 'price'] as $field) {
                if ($msg = $errors->first("items.{$i}.{$field}")) {
                    $lineErrors[$i] = $msg;
                    break;
                }
            }
        }

        // Part lookup map for the Alpine price pre-fill — no round-trip needed
        // when a part is selected.
        $partsData = $spareParts->mapWithKeys(function ($part) {
            return [$part->id => [
                'name' => $part->name,
                'purchase_price' => $part->purchase_price,
            ]];
        })->all();

        // Group the part options by category so the dropdown stays navigable.
        $groupedParts = $spareParts->groupBy(function ($part) {
            return $part->category ?: 'بدون تصنيف';
        });
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('suppliers.show', $supplier) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى {{ $supplier->name }}
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة فاتورة — {{ $supplier->name }}
            </h1>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc space-y-1 ps-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('suppliers.invoices.store', $supplier) }}"
            x-data="supplierInvoiceItemForm()"
            x-init="initItems({{ json_encode($initialItems) }}, {{ json_encode($lineErrors) }}, {{ json_encode((string) old('amount', '')) }})"
            class="max-w-5xl space-y-6"
        >
            @csrf

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    بيانات الفاتورة
                </h2>

                <div class="space-y-6">
                    <div>
                        <label for="invoice_number" class="mb-1 block text-sm font-medium text-foreground">
                            رقم الفاتورة
                        </label>
                        <input
                            id="invoice_number"
                            name="invoice_number"
                            type="text"
                            value="{{ old('invoice_number') }}"
                            disabled
                            placeholder="يُولَّد تلقائيًا"
                            maxlength="50"
                            class="w-full rounded-md border border-border bg-muted/50 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-60"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">
                            يُولَّد رقم الفاتورة تلقائيًا عند الحفظ (PINV-2026-00001 …).
                        </p>
                        @error('invoice_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="amount" class="mb-1 block text-sm font-medium text-foreground">
                                المبلغ
                            </label>
                            <input
                                id="amount"
                                name="amount"
                                type="text"
                                inputmode="decimal"
                                x-model="amount"
                                @input="markAmountTouched()"
                                required
                                placeholder="0.00"
                                class="money-input w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p class="mt-1 text-xs text-muted-foreground">
                                يُملأ تلقائيًا من مجموع البنود، ويبقى قابلاً للتعديل يدويًا (مصاريف شحن/ضرائب/خصومات).
                            </p>
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="invoice_date" class="mb-1 block text-sm font-medium text-foreground">
                                تاريخ الفاتورة
                            </label>
                            <input
                                id="invoice_date"
                                name="invoice_date"
                                type="date"
                                value="{{ old('invoice_date', now()->toDateString()) }}"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('invoice_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <div class="mb-1 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-semibold text-foreground">
                            بنود الفاتورة (قطع الغيار)
                        </h2>
                        <p class="text-xs text-muted-foreground">
                            اختياري — تُخصم من المخزون عند الحفظ. الفواتير غير المتعلقة بالقطع (مثل خدمات) لا تحتاج بنودًا.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="addLine()"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        + إضافة بند
                    </button>
                </div>

                <template x-if="lines.length === 0">
                    <div class="mt-4 rounded-lg border border-dashed border-border bg-background/40 px-4 py-6 text-center text-sm text-muted-foreground">
                        لا توجد بنود بعد — اضغط "+ إضافة بند" لتسجيل قطع مشتراة من المورد.
                    </div>
                </template>

                <div class="mt-4 space-y-3">
                    <template x-for="(line, index) in lines" :key="index">
                        <div class="rounded-xl border border-border bg-background/50 p-4">
                            <div class="grid grid-cols-12 items-center gap-3">
                                <div class="col-span-12 flex items-center gap-3 sm:col-span-7">
                                    <span
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-xs font-bold text-primary"
                                        x-text="index + 1"
                                    ></span>
                                    <div class="min-w-0 flex-1">
                                        <select
                                            :name="`items[${index}][spare_part_id]`"
                                            x-model="line.spare_part_id"
                                            @change="selectPart(line)"
                                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                        >
                                            <option value="">اختر قطعة الغيار…</option>
                                            @foreach ($groupedParts as $category => $parts)
                                                <optgroup label="{{ $category }}">
                                                    @foreach ($parts as $part)
                                                        <option value="{{ $part->id }}">
                                                            {{ $part->name }} — {{ $part->part_number }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                        <p class="mt-1 text-xs font-medium text-red-600" x-show="line.rowError" x-text="line.rowError"></p>
                                    </div>
                                </div>

                                <div class="col-span-4 sm:col-span-1">
                                    <label class="mb-1 block text-xs font-medium text-muted-foreground">الكمية</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0.01"
                                        :name="`items[${index}][qty]`"
                                        x-model="line.qty"
                                        @input="updateAmountFromLines()"
                                        class="w-full rounded-md border border-border bg-background px-2 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    >
                                </div>

                                <div class="col-span-4 sm:col-span-2">
                                    <label class="mb-1 block text-xs font-medium text-muted-foreground">السعر</label>
                                    <input
                                        type="text"
                                        inputmode="decimal"
                                        min="0"
                                        :name="`items[${index}][price]`"
                                        x-model="line.price"
                                        @input="updateAmountFromLines()"
                                        class="money-input w-full rounded-md border border-border bg-background px-2 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                    >
                                </div>

                                <div class="col-span-4 text-start sm:col-span-1 sm:text-center">
                                    <label class="mb-1 block text-xs font-medium text-muted-foreground sm:hidden">الإجمالي</label>
                                    <div
                                        class="rounded-md bg-muted/60 px-2 py-2 text-sm font-semibold text-foreground"
                                        x-text="window.formatMoney(lineTotal(line))"
                                    ></div>
                                </div>

                                <div class="col-span-12 sm:col-span-1 flex items-center justify-end sm:justify-center">
                                    <button
                                        type="button"
                                        @click="removeLine(index)"
                                        title="حذف البند"
                                        class="flex h-8 w-8 items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-red-50 hover:text-red-600"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    حفظ الفاتورة
                </button>

                <a
                    href="{{ route('suppliers.show', $supplier) }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function supplierInvoiceItemForm() {
        const parts = {!! json_encode($partsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

        return {
            lines: [],
            parts,
            amount: '',
            amountTouched: false,
            initItems(items, lineErrors = {}, initialAmount = '') {
                this.lines = items.map((item, index) => ({
                    spare_part_id: item.spare_part_id || '',
                    qty: item.qty || '',
                    price: item.price === '' || item.price === null || item.price === undefined ? '' : window.formatMoney(item.price),
                    rowError: lineErrors[index] || '',
                }));
                this.amount = initialAmount === '' ? '' : String(initialAmount);
                if (this.amount !== '' && this.lineItemsSum() > 0 && Math.abs(window.parseMoney(this.amount) - this.lineItemsSum()) > 0.005) {
                    this.amountTouched = true;
                }
                this.updateAmountFromLines();
            },
            emptyLine() {
                return { spare_part_id: '', qty: '', price: '', rowError: '' };
            },
            addLine() {
                this.lines.push(this.emptyLine());
                this.updateAmountFromLines();
            },
            removeLine(index) {
                this.lines.splice(index, 1);
                this.updateAmountFromLines();
            },
            selectPart(line) {
                const part = this.parts[line.spare_part_id];
                if (part && (line.price === '' || line.price === null)) {
                    line.price = part.purchase_price === null || part.purchase_price === '' ? '' : window.formatMoney(part.purchase_price);
                }
                line.rowError = '';
                this.updateAmountFromLines();
            },
            markAmountTouched() {
                this.amountTouched = true;
            },
            lineTotal(line) {
                const qty = parseFloat(line.qty) || 0;
                const price = window.parseMoney(line.price);
                return qty * price;
            },
            lineItemsSum() {
                return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
            },
            updateAmountFromLines() {
                if (this.amountTouched) {
                    return;
                }
                const sum = this.lineItemsSum();
                this.amount = sum > 0 ? window.formatMoney(sum) : '';
            },
        };
    }
</script>
@endpush