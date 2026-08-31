@extends('layouts.app')

@section('title', $invoice->invoice_number)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('invoices.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الفواتير
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $invoice->invoice_number }}
                    </h1>
                    <dl class="mt-2 space-y-0.5 text-sm text-muted-foreground">
                        <div>
                            <dt class="inline">التاريخ: </dt>
                            <dd class="inline">{{ $invoice->date?->format('Y-m-d') }}</dd>
                        </div>
                        @if ($invoice->maintenance)
                            <div>
                                <dt class="inline">أمر الصيانة: </dt>
                                <dd class="inline">
                                    <a
                                        href="{{ route('maintenance.show', $invoice->maintenance) }}"
                                        class="font-medium text-foreground hover:text-primary"
                                    >
                                        {{ $invoice->maintenance->maintenance_number }}
                                    </a>
                                    @if ($invoice->maintenance->vehicle)
                                        ({{ $invoice->maintenance->vehicle->internal_number }})
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <h2 class="mb-4 text-lg font-semibold text-foreground">
            بنود الفاتورة
        </h2>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 font-medium">#</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">قطعة الغيار</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">رقم القطعة</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">الكمية</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">السعر</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($invoice->details as $detail)
                        <tr>
                            <td class="px-4  py-3 gont-medium texr-foreground">{{ $loop->iteration }}</td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $detail->sparePart?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $detail->sparePart?->part_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format((float) $detail->qty) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ money($detail->price) }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ money($detail->row_sub_total) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد بنود في هذه الفاتورة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6 flex justify-end">
            <div class="w-full sm:w-80 rounded-xl border border-border bg-surface p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <span class="text-sm font-medium text-muted-foreground">الإجمالي</span>
                    <span class="text-xl font-bold text-foreground">
                        {{ money($invoice->total_amount) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection
