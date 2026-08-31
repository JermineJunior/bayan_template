@extends('layouts.app')

@section('title', 'فواتير صرف قطع الغيار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                فواتير صرف قطع الغيار
            </h1>
        </div>

        <form method="GET" action="{{ route('invoices.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
            <div>
                <label for="maintenance_id" class="mb-1 block text-sm font-medium text-foreground">
                    أمر الصيانة
                </label>
                <select
                    id="maintenance_id"
                    name="maintenance_id"
                    class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:w-72"
                >
                    <option value="">كل الأوامر</option>
                    @foreach ($maintenances as $maintenance)
                        <option value="{{ $maintenance->id }}" @selected(request('maintenance_id') == $maintenance->id)>
                            {{ $maintenance->maintenance_number }}
                            {{ $maintenance->vehicle ? '('.$maintenance->vehicle->internal_number.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button
                type="submit"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90"
            >
                تصفية
            </button>

            @if (request()->has('maintenance_id'))
                <a
                    href="{{ route('invoices.index') }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    مسح
                </a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">رقم الفاتورة</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">أمر الصيانة</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">التاريخ</th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">الإجمالي</th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('invoices.show', $invoice) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $invoice->invoice_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3">
                                @if ($invoice->maintenance)
                                    <a
                                        href="{{ route('maintenance.show', $invoice->maintenance) }}"
                                        class="text-foreground hover:text-primary"
                                    >
                                        {{ $invoice->maintenance->maintenance_number }}
                                    </a>
                                @else
                                    <span class="text-muted-foreground">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $invoice->date?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ money($invoice->total_amount) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    <a
                                        href="{{ route('invoices.show', $invoice) }}"
                                        class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                    >
                                        عرض
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد فواتير بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($invoices->hasPages())
            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        @endif
    </div>
@endsection
