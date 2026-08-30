@extends('layouts.app')

@section('title', $sparePart->name)

@section('content')
    @php
        $typeLabels = [
            'purchase' => 'شراء',
            'issue' => 'صرف',
            'stocktake' => 'جرد',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('spare-parts.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى قطع الغيار
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $sparePart->name }}
                    </h1>
                    <dl class="mt-2 space-y-0.5 text-sm text-muted-foreground">
                        <div>
                            <dt class="inline">رقم القطعة: </dt>
                            <dd class="inline font-medium text-foreground">{{ $sparePart->part_number }}</dd>
                        </div>
                        @if ($sparePart->category)
                            <div>
                                <dt class="inline">التصنيف: </dt>
                                <dd class="inline">{{ $sparePart->category }}</dd>
                            </div>
                        @endif
                        @if ($sparePart->defaultSupplier)
                            <div>
                                <dt class="inline">المورد الافتراضي: </dt>
                                <dd class="inline">{{ $sparePart->defaultSupplier->name }}</dd>
                            </div>
                        @endif
                        @if ($sparePart->purchase_price !== null)
                            <div>
                                <dt class="inline">سعر الشراء: </dt>
                                <dd class="inline">{{ number_format((float) $sparePart->purchase_price, 2) }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="flex items-center gap-2">
                    @can('spare-parts.edit')
                        <a
                            href="{{ route('spare-parts.edit', $sparePart) }}"
                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        >
                            تعديل
                        </a>
                    @endcan

                    @can('spare-parts.delete')
                        <form
                            method="POST"
                            action="{{ route('spare-parts.destroy', $sparePart) }}"
                            onsubmit="return confirmForm(this, 'هل تريد حذف هذه القطعة؟', 'نعم، احذف')"
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
                <p class="text-xs text-muted-foreground">المتوفر بالمخزون</p>
                <p class="mt-1 text-2xl font-semibold text-foreground">
                    {{ number_format($sparePart->quantity_on_hand, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">الحد الأدنى</p>
                <p class="mt-1 text-2xl font-semibold text-foreground">
                    {{ number_format($sparePart->minimum_quantity, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">الحالة</p>
                @if ($sparePart->is_low_stock && $sparePart->quantity_on_hand <= 0)
                    <span class="mt-1 inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                        نفد المخزون
                    </span>
                @elseif ($sparePart->is_low_stock)
                    <span class="mt-1 inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                        منخفض
                    </span>
                @else
                    <span class="mt-1 inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                        متوفر
                    </span>
                @endif
            </div>
        </div>

        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-foreground">
                سجل الحركات
            </h2>

            @can('spare-parts.create')
                <div class="flex flex-wrap items-center gap-2">
                    <a
                        href="{{ route('spare-parts.purchase.create', $sparePart) }}"
                        class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-green-700"
                    >
                        + شراء
                    </a>
                    <a
                        href="{{ route('spare-parts.issue.create', $sparePart) }}"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700"
                    >
                        + صرف
                    </a>
                    <a
                        href="{{ route('spare-parts.stocktake.create', $sparePart) }}"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        + جرد
                    </a>
                </div>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">النوع</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">الكمية</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">المرجع</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">ملاحظات</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">بواسطة</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="px-4 py-3">
                                @if ($transaction->type === 'purchase')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        {{ $typeLabels['purchase'] }}
                                    </span>
                                @elseif ($transaction->type === 'issue')
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        {{ $typeLabels['issue'] }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                        {{ $typeLabels['stocktake'] }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium {{ $transaction->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->quantity > 0 ? '+' : '' }}{{ number_format((float) $transaction->quantity, 2) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                @if ($transaction->type === 'issue' && $transaction->maintenanceOrder)
                                    أمر صيانة
                                    <span class="font-medium text-foreground">
                                        {{ $transaction->maintenanceOrder->maintenance_number }}
                                    </span>
                                    @if ($transaction->maintenanceOrder->vehicle)
                                        ({{ $transaction->maintenanceOrder->vehicle->internal_number }})
                                    @endif
                                @elseif ($transaction->type === 'purchase' && $transaction->supplier)
                                    مورد:
                                    <span class="font-medium text-foreground">
                                        {{ $transaction->supplier->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $transaction->notes ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $transaction->recordedBy?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $transaction->created_at?->format('Y-m-d H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد حركات لهذه القطعة بعد.
                                @can('spare-parts.create')
                                    <div class="mt-2">
                                        سجّل أول حركة باستخدام أحد الأزرار أعلاه.
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
