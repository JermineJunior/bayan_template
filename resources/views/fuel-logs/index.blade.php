@extends('layouts.app')

@section('title', 'عمليات التعبئة')

@section('content')
    @php
        $fuelLabels = ['gasoline' => 'بنزين', 'diesel' => 'ديزل'];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                عمليات التعبئة
            </h1>
        </div>

        <form
            method="GET"
            action="{{ route('fuel-logs.index') }}"
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
                    <label for="date_from" class="mb-1 block text-sm font-medium text-foreground">
                        من تاريخ
                    </label>
                    <input
                        id="date_from"
                        name="date_from"
                        type="date"
                        value="{{ request('date_from') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
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
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        بحث
                    </button>

                    @if (request()->hasAny(['vehicle_id', 'date_from', 'date_to']))
                        <a
                            href="{{ route('fuel-logs.index') }}"
                            class="rounded-md border border-border px-4 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        >
                            مسح
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المركبة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            السائق
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            تاريخ التعبئة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            نوع الوقود
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            اللترات
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            سعر اللتر
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الخصم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            القيمة الإجمالية
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            قراءة العداد
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الاستهلاك
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($fuelLogs as $log)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('vehicles.show', $log->vehicle) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $log->vehicle->internal_number }}
                                </a>
                                <p class="text-xs text-muted-foreground">
                                    {{ $log->vehicle->plate_number }}
                                </p>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $log->driver?->full_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $log->filled_at?->format('Y-m-d H:i') }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $fuelLabels[$log->fuel_type] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format((float) $log->liters, 2) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format((float) $log->price_per_liter, 3) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $log->discount !== null ? number_format((float) $log->discount, 2) : '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ number_format((float) $log->total_value, 2) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format((float) $log->odometer_reading, 0) }} كم
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $log->consumption_rate !== null ? number_format($log->consumption_rate, 2).' كم/لتر' : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('fuel.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('fuel-logs.destroy', $log) }}"
                                            onsubmit="return confirmForm(this, 'هل تريد حذف عملية التعبئة هذه؟', 'نعم، احذف')"
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
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد عمليات تعبئة .
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($fuelLogs->hasPages())
            <div class="mt-6">
                {{ $fuelLogs->links() }}
            </div>
        @endif
    </div>
@endsection
