@extends('layouts.app')

@section('title', 'صرف قطع غيار — '.$maintenance->maintenance_number)

@section('content')
    @php
        // Re-bind submitted (old) line items after a validation redirect, or
        // fall back to a single empty row.
        $initialItems = old('items') ? array_values(old('items')) : [['spare_part_id' => '', 'qty' => '', 'price' => '']];

        // Map any server-side per-line validation error back to its row, so the
        // insufficient-stock message from recordIssue() shows inline on the exact
        // line that ran short after a redirect-back.
        $lineErrors = [];
        foreach ($initialItems as $i => $item) {
            foreach (['spare_part_id', 'qty', 'price'] as $field) {
                if ($msg = $errors->first("items.{$i}.{$field}")) {
                    $lineErrors[$i] = $msg;
                    break;
                }
            }
        }

        // Part lookup map for the Alpine price/stock auto-fill — no round-trip
        // needed when a part is selected.
        $partsData = $spareParts->mapWithKeys(function ($part) {
            return [$part->id => [
                'name' => $part->name,
                'part_number' => $part->part_number,
                'purchase_price' => $part->purchase_price,
                'quantity_on_hand' => $part->quantity_on_hand,
            ]];
        })->all();

        // Group the part options by category so the dropdown stays navigable
        // instead of one long flat list.
        $groupedParts = $spareParts->groupBy(function ($part) {
            return $part->category ?: 'بدون تصنيف';
        });
    @endphp

    <div
        x-data="invoiceItemForm()"
        class="mx-auto max-w-7xl px-4 py-8"
        x-init="initItems({{ json_encode($initialItems) }}, {{ json_encode($lineErrors) }})"
    >
        <div class="mb-6">
            <a
                href="{{ route('maintenance.show', $maintenance) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى أمر الصيانة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                صرف قطع غيار
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

        <x-form-section title="بيانات أمر الصيانة">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-muted-foreground">أمر الصيانة</p>
                    <p class="text-lg font-semibold text-foreground">{{ $maintenance->maintenance_number }}</p>
                    @if ($maintenance->vehicle)
                        <p class="text-sm text-muted-foreground">
                            المركبة: {{ $maintenance->vehicle->internal_number }} — {{ $maintenance->vehicle->plate_number }}
                        </p>
                    @endif
                </div>
                <div class="text-end">
                    <p class="text-sm text-muted-foreground mb-2">التاريخ</p>
                    <input
                        type="date"
                        name="date"
                        form="invoiceItemsForm"
                        value="{{ old('date', now()->format('Y-m-d')) }}"
                        required
                        class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-form-section>

        <form id="invoiceItemsForm" method="POST" action="{{ route('maintenances.invoices.store', $maintenance) }}" class="mt-6">
            @csrf

            <x-form-section title="بنود الصرف">
                <template x-for="(line, index) in lines" :key="index">
                    <div class="group relative rounded-xl border border-border bg-surface p-4 transition-colors hover:border-primary/40">
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
                                        required
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
                                    <p class="mt-1 truncate text-xs text-muted-foreground" x-show="line.spare_part_id && !line.stockError">
                                        <span x-text="'الكود: ' + (partsById[line.spare_part_id]?.part_number ?? '—')"></span>
                                        <span class="mx-1">·</span>
                                        <span x-text="'المتوفر: ' + (partsById[line.spare_part_id]?.quantity_on_hand ?? '—')"></span>
                                    </p>
                                    <p class="mt-1 text-xs font-medium text-red-600" x-show="line.stockError" x-text="line.stockError"></p>
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
                                    required
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
                                    required
                                    class="money-input w-full rounded-md border border-border bg-background px-2 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                            </div>

                            <div class="col-span-4 sm:col-span-1 text-start sm:text-center">
                                <label class="mb-1 block text-xs font-medium text-muted-foreground sm:hidden">الإجمالي</label>
                                <div
                                    class="rounded-md bg-muted/60 px-2 py-2 text-sm font-semibold text-foreground"
                                    x-text="window.formatMoney(lineTotal(line))"
                                ></div>
                            </div>

                            <div class="col-span-12 sm:col-span-1 flex items-center justify-end sm:justify-center">
                                <template x-if="lines.length > 1">
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
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </x-form-section>

            <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
                <button
                    type="button"
                    @click="addLine()"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    + إضافة بند
                </button>

                <div class="flex items-center gap-4">
                    <div class="text-end">
                        <p class="text-sm text-muted-foreground">الإجمالي</p>
                        <p class="text-xl font-bold text-foreground" x-text="window.formatMoney(grandTotal())"></p>
                    </div>

                    <button
                        type="submit"
                        class="rounded-md bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        صرف القطع
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
<script>
    function invoiceItemForm() {
        const parts = {!! json_encode($partsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};

        return {
            lines: [],
            parts,
            get partsById() {
                return this.parts;
            },
            initItems(items, lineErrors = {}) {
                this.lines = items.map((item, index) => ({
                    spare_part_id: item.spare_part_id || '',
                    qty: item.qty || '',
                    price: item.price === '' || item.price === null || item.price === undefined ? '' : window.formatMoney(item.price),
                    stockError: lineErrors[index] || '',
                }));
                if (this.lines.length === 0) {
                    this.lines = [this.emptyLine()];
                }
            },
            emptyLine() {
                return { spare_part_id: '', qty: '', price: '', stockError: '' };
            },
            addLine() {
                this.lines.push(this.emptyLine());
            },
            removeLine(index) {
                if (this.lines.length > 1) {
                    this.lines.splice(index, 1);
                }
            },
            selectPart(line) {
                const part = this.parts[line.spare_part_id];
                if (part && (line.price === '' || line.price === null)) {
                    line.price = part.purchase_price === null || part.purchase_price === '' ? '' : window.formatMoney(part.purchase_price);
                }
                line.stockError = '';
            },
            lineTotal(line) {
                const qty = parseFloat(line.qty) || 0;
                const price = window.parseMoney(line.price);
                return qty * price;
            },
            grandTotal() {
                return this.lines.reduce((sum, line) => sum + this.lineTotal(line), 0);
            },
        };
    }
</script>
@endpush
