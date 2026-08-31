@extends('layouts.app')

@section('title', 'المصروفات')

@section('content')
    @php
        $expenseTypeLabels = [
            'fuel' => 'وقود',
            'oil' => 'زيوت',
            'filter' => 'فلاتر',
            'maintenance' => 'صيانة',
            'spare_parts' => 'قطع غيار',
            'violations' => 'مخالفات',
            'other' => 'أخرى',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                المصروفات
            </h1>

            @can('expenses.create')
                <a
                    href="{{ route('expenses.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إضافة مصروف
                </a>
            @endcan
        </div>

        <form
            method="GET"
            action="{{ route('expenses.index') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="vehicle_id" class="mb-1 block text-sm font-medium text-foreground">
                        المركبة
                    </label>
                    <select
                        id="vehicle_id"
                        name="vehicle_id"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل المركبات</option>
                        @foreach ($vehicles as $vehicle)
                            <option
                                value="{{ $vehicle->id }}"
                                @selected(request('vehicle_id') == $vehicle->id)
                            >
                                {{ $vehicle->internal_number }} — {{ $vehicle->plate_number }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="expense_type" class="mb-1 block text-sm font-medium text-foreground">
                        نوع المصروف
                    </label>
                    <select
                        id="expense_type"
                        name="expense_type"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الأنواع</option>
                        @foreach ($expenseTypeLabels as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(request('expense_type') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="date_from" class="mb-1 block text-sm font-medium text-foreground">
                        من تاريخ
                    </label>
                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ request('date_from') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="date_to" class="mb-1 block text-sm font-medium text-foreground">
                        إلى تاريخ
                    </label>
                    <input
                        id="date_to"
                        name="date_to"
                        type="date"
                        value="{{ request('date_to') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>
            </div>

            <div class="mt-4 flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    بحث
                </button>

                @if (request()->hasAny(['vehicle_id', 'expense_type', 'date_from', 'date_to']))
                    <a
                        href="{{ route('expenses.index') }}"
                        class="rounded-md border border-border px-4 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        مسح
                    </a>
                @endif
            </div>
        </form>

        <div class="mb-6 flex flex-wrap items-center gap-4 rounded-xl border border-border bg-surface p-4 shadow-sm">
            <div>
                <p class="text-xs text-muted-foreground">إجمالي المصروفات (حسب الفلترة)</p>
                <p class="mt-1 text-2xl font-semibold text-primary">
                    {{ money($totalAmount) }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المركبة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            النوع
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المبلغ
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            التاريخ
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الوصف
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المصدر
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($expenses as $expense)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('vehicles.show', $expense->vehicle) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $expense->vehicle->internal_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $expenseTypeLabels[$expense->expense_type] ?? $expense->expense_type }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ money($expense->amount) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $expense->expense_date?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $expense->description ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($expense->is_auto_generated)
                                    <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">
                                        تلقائي
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">
                                        يدوي
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if (! $expense->is_auto_generated)
                                        @can('expenses.delete', $expense)
                                            <form
                                                method="POST"
                                                action="{{ route('expenses.destroy', $expense) }}"
                                                onsubmit="return confirmForm(this, 'هل تريد حذف هذا المصروف؟', 'نعم، احذف')"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                                >
                                                    حذف
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <span class="text-xs text-muted-foreground">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد مصروفات مطابقة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($expenses->hasPages())
            <div class="mt-6">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>
@endsection
