@extends('layouts.app')

@section('title' ,'تعديل قطع غيار')

@section('content')
    <div class="container mx-auto px-4 py-6" dir="rtl">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-foreground">
                    تعديل فاتورة
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    تعديل فاتورة وتفاصيل قطع الغيار الخاصة بها
                </p>
            </div>

            <a href="{{ route('maintenance.show', $invoice->maintenance_id) }}"
                class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground hover:bg-muted">
                رجوع
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-red-700">
                <ul class="list-disc space-y-1 pr-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('invoice.update', $invoice) }}" method="POST" id="invoiceForm">
            @csrf
            @method('PUT')

            <div class="rounded-lg border border-border bg-background shadow-sm">

                <div class="border-b border-border px-6 py-4">
                    <h2 class="font-semibold text-foreground">
                        بيانات الفاتورة
                    </h2>
                </div>

                <div class="grid grid-cols-1 gap-5 p-6 lg:grid-cols-3">
                    <div>
                        <label for="invoice_number" class="mb-1 block text-sm font-medium text-foreground">
                            رقم الفاتورة
                        </label>

                        <input id="invoice_number" name="invoice_number" type="text"
                            value="{{ old('invoice_number', $invoice->invoice_number) }}" required readonly
                            placeholder="مثال: INV-00001"
                            class="w-full rounded-md border border-border bg-gray-200 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <input type="hidden" name="maintenance_id" value="{{ $invoice->maintenance_id }}">
                    </div>

                    <div>
                        <label for="date" class="mb-1 block text-sm font-medium text-foreground">
                            التاريخ
                        </label>

                        <input id="date" name="date" type="date"
                            value="{{ old('date', $invoice->date->format('Y-m-d')) }}" required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>

                    <div>
                        <label for="supplier" class="mb-1 block text-sm font-medium text-foreground">
                            المورد
                        </label>

                        <input id="supplier" name="supplier" type="text"
                            value="{{ old('supplier', $invoice->supplier) }}" placeholder="اسم المورد"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-lg border border-border bg-background shadow-sm">
                <div class="flex items-center justify-between border-b border-border px-6 py-4">
                    <div>
                        <h2 class="font-semibold text-foreground">
                            تفاصيل الفاتورة
                        </h2>

                        <p class="mt-1 text-xs text-muted-foreground">
                            أضف قطع الغيار الموجودة في الفاتورة
                        </p>
                    </div>
                    <button type="button" id="addRow"
                        class="inline-flex items-center gap-2 rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary/90">
                        <span class="text-lg leading-none">+</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-muted/50 text-xs text-muted-foreground">
                            <tr>
                                <th class="px-4 py-3">
                                    #
                                </th>

                                <th class="px-4 py-3 min-w-[250px]">
                                    قطعة الغيار
                                </th>

                                <th class="px-4 py-3 min-w-[120px]">
                                    الكمية
                                </th>

                                <th class="px-4 py-3 min-w-[150px]">
                                    السعر
                                </th>

                                <th class="px-4 py-3 min-w-[160px]">
                                    الإجمالي
                                </th>

                                <th class="px-4 py-3 text-center">
                                    إجراء
                                </th>
                            </tr>
                        </thead>

                        <tbody id="invoiceDetails">
                            @foreach ($invoice->details as $item)
                                <tr class="invoice-row border-b border-border">
                                    <td class="row-number px-4 py-3">
                                        {{ $loop->iteration }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text" name="details[{{ $loop->index }}][spare]" required
                                            placeholder="اسم قطعة الغيار"
                                            value="{{ $item->spare }}"
                                            class="spare w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="number" name="details[{{ $loop->index }}][qty]" value="{{ $item->qty }}" min="1"
                                            step="1" required
                                            class="qty w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="number" name="details[{{ $loop->index }}][price]" min="0" step="0.01" value="{{ $item->price }}"
                                            required
                                            class="price w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                                    </td>

                                    <td class="px-4 py-3">
                                        <input type="text" value="0.00" readonly name="details[{{ $loop->index }}][row_sub_total]" value="{{ $item->row_sub_total }}"
                                            class="row-total w-full rounded-md border border-border bg-muted px-3 py-2 text-sm font-medium text-foreground">
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        <button type="button"
                                            class="remove-row rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">

                                            حذف

                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

                <div class="flex justify-end border-t border-border px-6 py-5">
                    <div class="w-full sm:w-80">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-muted-foreground">
                                إجمالي الفاتورة
                            </span>

                            <span id="invoiceTotal" class="text-xl font-bold text-foreground">
                                {{ $invoice->total_amount }}
                            </span>

                        </div>

                        <input type="hidden" name="total_amount" id="totalAmount" value="{{ $invoice->total_amount }}">
                    </div>
                </div>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <a href="{{ route('maintenance.show', $invoice->maintenance_id) }}"
                    class="rounded-md border border-border px-5 py-2.5 text-sm font-medium text-foreground hover:bg-muted">
                    إلغاء
                </a>

                <button type="submit"
                    class="rounded-md bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary/90">
                    حفظ الفاتورة
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const detailsContainer = document.getElementById('invoiceDetails');
            const addRowButton = document.getElementById('addRow');
            const invoiceTotal = document.getElementById('invoiceTotal');
            const totalAmount = document.getElementById('totalAmount');

            let rowIndex = 1;

            addRowButton.addEventListener('click', function() {

                const row = document.createElement('tr');

                row.className = 'invoice-row border-b border-border';

                row.innerHTML = `
            <td class="row-number px-4 py-3"></td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="details[${rowIndex}][spare]"
                    required
                    placeholder="اسم قطعة الغيار"
                    class="spare w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </td>

            <td class="px-4 py-3">
                <input
                    type="number"
                    name="details[${rowIndex}][qty]"
                    value="1"
                    min="1"
                    step="1"
                    required
                    class="qty w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </td>

            <td class="px-4 py-3">
                <input
                    type="number"
                    name="details[${rowIndex}][price]"
                    value="0"
                    min="0"
                    step="0.01"
                    required
                    class="price w-full rounded-md border border-border bg-background px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="details[${rowIndex}][row_sub_total]"
                    value="0.00"
                    readonly
                    class="row-total w-full rounded-md border border-border bg-muted px-3 py-2 text-sm font-medium text-foreground">
            </td>

            <td class="px-4 py-3 text-center">
                <button
                    type="button"
                    class="remove-row rounded-md px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                    حذف
                </button>
            </td>
        `;

                detailsContainer.appendChild(row);

                rowIndex++;

                updateRows();

                calculateTotal();
            });

            detailsContainer.addEventListener('click', function(event) {

                if (!event.target.classList.contains('remove-row')) {
                    return;
                }

                const rows = detailsContainer.querySelectorAll('.invoice-row');

                if (rows.length === 1) {
                    alert('يجب أن تحتوي الفاتورة على قطعة غيار واحدة على الأقل.');
                    return;
                }

                event.target.closest('.invoice-row').remove();

                updateRows();

                calculateTotal();
            });

            detailsContainer.addEventListener('input', function(event) {

                if (
                    event.target.classList.contains('qty') ||
                    event.target.classList.contains('price')
                ) {
                    calculateRowTotal(event.target.closest('.invoice-row'));

                    calculateTotal();
                }

            });

            function calculateRowTotal(row) {

                const qty = parseFloat(row.querySelector('.qty').value) || 0;

                const price = parseFloat(row.querySelector('.price').value) || 0;

                const rowTotal = qty * price;

                row.querySelector('.row-total').value = rowTotal.toFixed(2);
            }

            function calculateTotal() {

                let total = 0;

                const rows = detailsContainer.querySelectorAll('.invoice-row');

                rows.forEach(function(row) {

                    calculateRowTotal(row);

                    const rowTotal =
                        parseFloat(row.querySelector('.row-total').value) || 0;

                    total += rowTotal;
                });

                invoiceTotal.textContent = total.toFixed(2);

                totalAmount.value = total.toFixed(2);
            }

            function updateRows() {

                const rows = detailsContainer.querySelectorAll('.invoice-row');

                rows.forEach(function(row, index) {

                    row.querySelector('.row-number').textContent = index + 1;

                });
            }


            updateRows();

            calculateTotal();

        });
    </script>
@endsection
