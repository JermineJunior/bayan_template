@extends('layouts.app')

@section('title', 'فاتورة ' . $invoice->invoice_number)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('suppliers.show', $invoice->supplier) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى {{ $invoice->supplier->name }}
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        فاتورة {{ $invoice->invoice_number }}
                    </h1>
                    @if ($invoice->is_paid_in_full)
                        <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                            مدفوعة بالكامل
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                            غير مسددة
                        </span>
                    @endif
                </div>
            </div>

            <dl class="mt-4 grid gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                    <dt class="text-xs text-muted-foreground">المورد</dt>
                    <dd class="mt-1 font-medium text-foreground">{{ $invoice->supplier->name }}</dd>
                </div>
                <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                    <dt class="text-xs text-muted-foreground">المبلغ</dt>
                    <dd class="mt-1 text-xl font-semibold text-foreground">{{ money($invoice->amount) }}</dd>
                    @if ($invoice->amount_differs_from_line_items)
                        <p class="mt-2 text-xs text-muted-foreground">
                            قيمة الفاتورة تختلف عن مجموع البنود ({{ money($invoice->line_items_total) }})
                        </p>
                    @endif
                </div>
                <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                    <dt class="text-xs text-muted-foreground">المدفوع</dt>
                    <dd class="mt-1 text-xl font-semibold text-foreground">{{ money($invoice->total_paid) }}</dd>
                </div>
                <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                    <dt class="text-xs text-muted-foreground">الرصيد المتبقي</dt>
                    <dd class="mt-1 text-xl font-semibold {{ $invoice->balance > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ money($invoice->balance) }}
                    </dd>
                </div>
            </dl>

            <p class="mt-2 text-sm text-muted-foreground">
                تاريخ الفاتورة: {{ $invoice->invoice_date?->format('Y-m-d') }}
                — سُجّلت بواسطة: {{ $invoice->recordedBy?->name ?? '—' }}
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    سجل الدفعات
                </h2>

                <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    المبلغ
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    تاريخ الدفع
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                                    تم التسجيل بواسطة
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($invoice->payments as $payment)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ money($payment->amount) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $payment->paid_at?->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3 text-end text-muted-foreground">
                                        {{ $payment->recordedBy?->name ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                        لا توجد دفعات مسجلة بعد.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    إضافة دفعة
                </h2>

                @can('suppliers.view')
                    <form
                        method="POST"
                        action="{{ route('supplier-invoices.payments.store', $invoice) }}"
                        x-data="{
                            balance: {{ (float) $invoice->balance }},
                            amount: '',
                            submit() {
                                const entered = parseFloat(String(this.amount).replace(/,/g, ''));
                                if (!Number.isFinite(entered) || entered <= this.balance) {
                                    this.$el.submit();
                                    return;
                                }
                                Swal.fire({
                                    title: 'تأكيد العملية',
                                    text: 'المبلغ المدخل أكبر من الرصيد المتبقي ({{ money($invoice->balance) }}) — هل تريد المتابعة؟',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'نعم، متابعة',
                                    cancelButtonText: 'إلغاء',
                                    reverseButtons: true,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        this.$el.submit();
                                    }
                                });
                            }
                        }"
                        @submit.prevent="submit()"
                        class="space-y-5 rounded-xl border border-border bg-surface p-6 shadow-sm"
                    >
                        @csrf

                        <div>
                            <label for="amount" class="mb-1 block text-sm font-medium text-foreground">
                                المبلغ
                            </label>
                            <input
                                id="amount"
                                name="amount"
                                x-model="amount"
                                inputmode="decimal"
                                placeholder="0.00"
                                required
                                class="money-input w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('amount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="paid_at" class="mb-1 block text-sm font-medium text-foreground">
                                تاريخ الدفع
                            </label>
                            <input
                                id="paid_at"
                                name="paid_at"
                                type="date"
                                value="{{ old('paid_at', now()->toDateString()) }}"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('paid_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تسجيل الدفعة
                        </button>
                    </form>
                @else
                    <div class="rounded-xl border border-border bg-surface p-6 text-sm text-muted-foreground shadow-sm">
                        ليست لديك صلاحية لإضافة دفعات.
                    </div>
                @endcan
            </div>
        </div>

        @if ($invoice->details->isNotEmpty())
            <div class="mt-6">
                <h2 class="mb-4 text-lg font-semibold text-foreground">
                    بنود الفاتورة (قطع الغيار)
                </h2>

                <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    قطعة الغيار
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الكمية
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    السعر
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                                    الإجمالي
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($invoice->details as $detail)
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="font-medium text-foreground">{{ $detail->sparePart->name }}</span>
                                        <span class="text-xs text-muted-foreground">({{ $detail->sparePart->part_number }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $detail->qty) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ money($detail->price) }}
                                    </td>
                                    <td class="px-4 py-3 text-end font-medium text-foreground">
                                        {{ money($detail->row_sub_total) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
