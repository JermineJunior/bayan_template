@extends('layouts.app')

@section('title', $maintenance->maintenance_number)

@section('content')
    @php
        $typeLabels = [
            'periodic' => ['دورية', 'bg-blue-100 text-blue-700'],
            'preventive' => ['وقائية', 'bg-green-100 text-green-700'],
            'emergency' => ['طارئة', 'bg-red-100 text-red-700'],
        ];

        $statusLabels = [
            'draft' => ['مسودة', 'bg-gray-100 text-gray-700'],
            'pending' => ['معلقة', 'bg-amber-100 text-amber-700'],
            'in_progress' => ['قيد التنفيذ', 'bg-blue-100 text-blue-700'],
            'completed' => ['مكتملة', 'bg-green-100 text-green-700'],
            'cancelled' => ['ملغاة', 'bg-red-100 text-red-700'],
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a href="{{ route('maintenance.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                &larr; العودة إلى اوامرالصيانة
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">
                            {{ $maintenance->maintenance_number }}
                        </h1>
                        <span class="text-sm text-muted-foreground">{{ $typeLabels[$maintenance->type][0] }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @can('maintenance.edit')
                        <a href="{{ route('maintenance.edit', $maintenance) }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                            تعديل
                        </a>
                    @endcan

                    @can('maintenance.delete')
                        <form method="POST" action="{{ route('maintenance.destroy', $maintenance) }}"
                            onsubmit="return confirmForm(this, 'هل تريد حذف هذا السائق؟', 'نعم، احذف')">
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
            <div class="mb-6 flex gap-1 overflow-x-auto border-b border-border" role="tablist"
                aria-label="تبويبات امر الصيانة">
                <button type="button" role="tab" @click="tab = 'info'" :aria-selected="tab === 'info'"
                    :class="tab === 'info' ? 'border-primary text-primary' :
                        'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">
                    معلومات امر الصيانة
                </button>
                <button type="button" role="tab" @click="tab = 'spare'" :aria-selected="tab === 'spare'"
                    :class="tab === 'spare' ? 'border-primary text-primary' :
                        'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">
                    قطع الغيار
                </button>
            </div>

            <div x-show="tab === 'info'" x-cloak role="tabpanel">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                ملخص
                            </h2>

                            <dl class="space-y-3 text-sm [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">الحالة</dt>
                                    <dd>
                                        <span
                                            class="inline-flex rounded-full {{ $statusLabels[$maintenance->status][1] }} px-2.5 py-0.5 text-xs font-medium text-green-700">{{ $statusLabels[$maintenance->status][0] }}</span>

                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">المركبة</dt>
                                    <dd class="font-medium text-foreground">
                                        {{ $maintenance->vehicle?->plate_number ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">التاريخ </dt>
                                    <dd class="font-medium text-foreground">
                                        {{ $maintenance->date?->format('Y-m-d') ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                تفاصيل الصيانة
                            </h2>

                            <dl
                                class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الورشة</dt>
                                    <dd class="font-medium text-foreground">{{ $maintenance->workshop }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">االفني</dt>
                                    <dd class="font-medium text-foreground">{{ $maintenance->technical }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground"> سبب الصيانة</dt>
                                    <dd class="font-medium text-foreground">{{ $maintenance->reason ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground"> نوع الصيانة</dt>
                                    <dd class="font-medium text-foreground">{{ $typeLabels[$maintenance->type][0] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                التكلفة والموعد
                            </h2>

                            <dl
                                class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تاريخ البداية</dt>
                                    <dd class="font-medium text-foreground">
                                        {{ $maintenance->start_date->format('Y-m-d') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تاريخ الانتهاء</dt>
                                    <dd class="flex items-center gap-2 font-medium text-foreground">
                                        {{ $maintenance->end_date?->format('Y-m-d') ?? '—' }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تكلفة الفني</dt>
                                    <dd class="flex items-center gap-2 font-medium text-foreground">
                                        {{ number_format($maintenance->labor_cost) }}
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تكلفة الاسبير</dt>
                                    <dd class="flex items-center gap-2 font-medium text-foreground">
                                        {{ number_format($maintenance->spare_cost) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'spare'" x-cloak role="tabpanel">
                <div class="grid gap-6 lg:grid-cols-1">
                    <div>
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <div class="flex items-center justify-between p-2">
                                <h2 class="mb-4 text-sm font-semibold text-foreground">
                                    المركبة الحالية
                                </h2>

                                <a href="{{ route('invoice.create', $maintenance) }}"
                                    class="inline-flex items-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50">
                                    إنشاء قطع غيار
                                </a>
                            </div>

                            @if ($maintenance->invoices->isNotEmpty())
                                <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm ">
                                    <table class="min-w-full divide-y divide-border text-sm">
                                        <thead>
                                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    رقم الامر
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    المركبة
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    النوع
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    التاريخ
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    التكلفة
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                                                    إجراءات
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            @foreach ($maintenance->invoices as $invoice)
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $invoice->invoice_number }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $invoice->maintenance->vehicle->plate_number }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $typeLabels[$invoice->maintenance->type][0] }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ $invoice->date->format('Y-m-d') }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    {{ number_format($invoice->total_amount) }}
                                                </td>
                                                <td class="px-4 py-3 font-medium text-foreground">
                                                    <div class="flex items-center justify-end gap-2">

                                                        <a href="{{ route('invoice.show', $invoice) }}"
                                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted">
                                                            عرض
                                                        </a>
                                                        <a href="{{ route('invoice.edit', $invoice) }}"
                                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted">
                                                            تعديل
                                                        </a>

                                                        <form method="POST"
                                                            action="{{ route('invoice.destroy', $invoice) }}"
                                                            onsubmit="return confirmForm(this, 'هل تريد حذف هذه فاتورة الغيار', 'نعم، احذف')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                                                حذف
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-muted-foreground">
                                    لا يوجد قطع غيار لهذا امر الصيانة.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
