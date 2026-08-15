@php
    $formatRemaining = function (\App\Models\VehicleFilterChange $change): string {
        $remaining = (float) $change->remaining_change;
        $formatted = number_format(abs($remaining), 0);

        if ($remaining > 0) {
            return "+{$formatted} كم";
        }

        if ($remaining < 0) {
            return "-{$formatted} كم";
        }

        return '0 كم';
    };
@endphp

<div x-show="tab === 'filters'" x-cloak role="tabpanel">
    <div class="space-y-6">
        {{-- Current filter status per filter type. --}}
        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-sm font-semibold text-foreground">
                    حالة الفلاتر
                </h2>

                @can('filter-changes.create')
                    <a
                        href="{{ route('vehicles.filter-changes.create', $vehicle) }}"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل تغيير فلتر
                    </a>
                @endcan
            </div>

            @if ($filterStatus->isNotEmpty())
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($filterStatus as $change)
                        <div class="rounded-xl border p-5 shadow-sm {{ $change->is_overdue ? 'border-red-200 bg-red-50/50' : 'border-border bg-surface' }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-foreground">
                                        {{ $change->filter->filter_name }}
                                    </h3>
                                    <p class="mt-0.5 text-xs text-muted-foreground">
                                        {{ config('filter_types.'.$change->filter->filter_type, $change->filter->filter_type) }}
                                    </p>
                                </div>

                                @if ($change->is_overdue)
                                    <span class="inline-flex shrink-0 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        متأخرة
                                    </span>
                                @endif
                            </div>

                            <dl class="mt-4 space-y-2 text-sm [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="shrink-0 text-muted-foreground">آخر تغيير</dt>
                                    <dd class="font-medium text-foreground">{{ $change->last_change?->format('Y-m-d') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="shrink-0 text-muted-foreground">التغيير القادم</dt>
                                    <dd class="font-medium text-foreground">{{ number_format((float) $change->next_change_odometer, 0) }} كم</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="shrink-0 text-muted-foreground">المتبقي</dt>
                                    <dd class="font-bold {{ $change->is_overdue ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $formatRemaining($change) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-muted-foreground">
                    لا توجد فلاتر مسجلة لهذه المركبة بعد.
                </p>
            @endif
        </div>

        {{-- Filter change history. --}}
        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
            <h2 class="mb-4 text-sm font-semibold text-foreground">
                سجل تغيير الفلاتر
            </h2>

            @if ($vehicle->filterChanges->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الفلتر
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    النوع
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    تاريخ آخر تغيير
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    العداد عند التغيير
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    التكلفة
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    التغيير القادم
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    المتبقي
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($vehicle->filterChanges as $change)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ $change->filter?->filter_name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-foreground">
                                            {{ config('filter_types.'.$change->filter?->filter_type, $change->filter?->filter_type ?? '—') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $change->last_change?->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $change->odometer_when_change, 0) }} كم
                                    </td>
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ number_format((float) $change->cost, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $change->next_change_odometer, 0) }} كم
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-medium {{ $change->is_overdue ? 'text-red-600' : 'text-green-600' }}">
                                            {{ $formatRemaining($change) }}
                                        </span>
                                        @if ($change->is_overdue)
                                            <span class="ms-1 inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                                متأخرة
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-muted-foreground">
                    لا يوجد سجل تغيير فلاتر لهذه المركبة.
                </p>
            @endif
        </div>
    </div>
</div>
