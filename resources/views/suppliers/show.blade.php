@extends('layouts.app')

@section('title', $supplier->name)

@section('content')
    @php
        $hasBalance = $supplier->balance > 0;
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('suppliers.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الموردين
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $supplier->name }}
                    </h1>
                    <dl class="mt-2 space-y-0.5 text-sm text-muted-foreground">
                        @if ($supplier->phone)
                            <div>
                                <dt class="inline">الهاتف: </dt>
                                <dd class="inline">{{ $supplier->phone }}</dd>
                            </div>
                        @endif
                        @if ($supplier->address)
                            <div>
                                <dt class="inline">العنوان: </dt>
                                <dd class="inline">{{ $supplier->address }}</dd>
                            </div>
                        @endif
                        @unless ($supplier->phone || $supplier->address)
                            <div>لا توجد بيانات اتصال مسجلة.</div>
                        @endunless
                    </dl>
                </div>

                <div class="flex items-center gap-2">
                    @can('suppliers.edit')
                        <a
                            href="{{ route('suppliers.edit', $supplier) }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تعديل
                        </a>
                    @endcan

                    @can('suppliers.delete')
                        <form
                            method="POST"
                            action="{{ route('suppliers.destroy', $supplier) }}"
                            onsubmit="return confirmForm(this, 'هل تريد حذف هذا المورد؟', 'نعم، احذف')"
                        >
                            @csrf
                            @method('DELETE')
                            <button
                                type="submit"
                                class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                            >
                                حذف
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">إجمالي الفواتير</p>
                <p class="mt-1 text-2xl font-semibold text-foreground">
                    {{ number_format($supplier->total_invoiced, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">إجمالي المدفوع</p>
                <p class="mt-1 text-2xl font-semibold text-foreground">
                    {{ number_format($supplier->total_paid, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">الرصيد</p>
                <p class="mt-1 text-2xl font-semibold {{ $hasBalance ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($supplier->balance, 2) }}
                </p>
            </div>
        </div>

        <div class="mb-6 flex items-center justify-between gap-4">
            <h2 class="text-lg font-semibold text-foreground">
                فواتير المورد
            </h2>

            @can('suppliers.create')
                <a
                    href="{{ route('suppliers.invoices.create', $supplier) }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إضافة فاتورة
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            رقم الفاتورة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            التاريخ
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المبلغ
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المدفوع
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الرصيد
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('supplier-invoices.show', $invoice) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $invoice->invoice_date?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format((float) $invoice->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format($invoice->total_paid, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($invoice->balance > 0)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        {{ number_format($invoice->balance, 2) }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        {{ number_format($invoice->balance, 2) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <a
                                        href="{{ route('supplier-invoices.show', $invoice) }}"
                                        class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                    >
                                        عرض
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد فواتير لهذا المورد بعد.
                                @can('suppliers.create')
                                    <a href="{{ route('suppliers.invoices.create', $supplier) }}" class="text-primary hover:underline">
                                        أضف أول فاتورة
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
