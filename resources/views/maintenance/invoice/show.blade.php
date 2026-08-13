@extends('layouts.app')

@section('title', $invoice->invoice_number)

@section('content')

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('maintenance.show', $invoice->maintenance_id) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                &larr; العودة إلى قطع الغيار
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $invoice->invoice_number }}
                    </h1>
                </div>

                <div class="flex items-center gap-2">
                    @can('invoices.edit')
                        <a href="{{ route('invoices.edit', $invoice) }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            تعديل
                        </a>
                    @endcan

                    @can('invoices.delete')
                        <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                            onsubmit="return confirmForm(this, 'هل تريد حذف هذه قطع الفيار', 'نعم، احذف')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50">
                                حذف
                            </button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>

        <div x-data="{ tab: 'info' }">
            {{-- <div
                class="mb-6 flex gap-1 overflow-x-auto border-b border-border"
                role="tablist"
                aria-label="تبويبات عامة عن قطع الفيار"
            >
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'info'"
                    :aria-selected="tab === 'info'"
                    :class="tab === 'info' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    معلومات قطع الفيار
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'assign'"
                    :aria-selected="tab === 'assign'"
                    :class="tab === 'assign' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    الإسناد
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'odometer'"
                    :aria-selected="tab === 'odometer'"
                    :class="tab === 'odometer' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    العداد
                </button>
            </div> --}}

            <div x-show="tab === 'info'" x-cloak role="tabpanel">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6">

                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                ملخص
                            </h2>

                            <dl class="space-y-3 text-sm [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">رقم اللوحة</dt>
                                    <dd class="font-medium text-foreground">{{ $invoice->maintenance->vehicle->plate_number ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">التاريخ</dt>
                                    <dd class="font-medium text-foreground">{{ $invoice->date->format('Y-m-d') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">المورد</dt>
                                    <dd class="font-medium text-foreground">{{ $invoice?->supplier }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                تفاصيل قطغ الغيار
                            </h2>

                            @if ($invoice->details->isNotEmpty())
                                <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm ">
                                    <table class="min-w-full divide-y divide-border text-sm">
                                        <thead>
                                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    قطعة الغيار </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    الكمية
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    السعر
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    الاجمالي
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            @foreach ($invoice->details as $item)
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $item->spare }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $item->qty}}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ number_format($item->price) }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ number_format($item->row_sub_total) }}
                                                </td>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-muted-foreground">
                                    لا يوجد قطع تفاصيل غيار لهذا امر الصيانة.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
