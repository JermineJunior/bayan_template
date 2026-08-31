<div x-show="tab === 'fuel'" x-cloak role="tabpanel">
    <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
            <h2 class="text-sm font-semibold text-foreground">
                عمليات التعبئة
            </h2>

            @can('fuel.create')
                <a
                    href="{{ route('vehicles.fuel-logs.create', $vehicle) }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إضافة تعبئة
                </a>
            @endcan
        </div>

        <div class="mb-4 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-border bg-surface p-5 shadow-sm">
                <h3 class="text-xs font-medium text-muted-foreground">
                    تكلفة الكيلومتر
                </h3>
                <p class="mt-1 text-3xl font-bold tracking-tight text-foreground">
                    {{ $fuelCostPerKm !== null ? money($fuelCostPerKm) : '—' }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    إجمالي قيمة التعبئة ÷ إجمالي الكيلومترات المقطوعة
                </p>
            </div>

            <div class="rounded-xl border border-border bg-surface p-5 shadow-sm">
                <h3 class="text-xs font-medium text-muted-foreground">
                    متوسط الاستهلاك الشهري
                </h3>
                <p class="mt-1 text-3xl font-bold tracking-tight text-foreground">
                    {{ $avgMonthlyFuelLiters !== null ? number_format($avgMonthlyFuelLiters, 2).' لتر' : '—' }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    إجمالي اللترات ÷ عدد الأشهر بين أول وآخر تعبئة
                </p>
            </div>
        </div>

        @if ($vehicle->fuelLogs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-border text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wide text-muted-foreground">
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
                                السائق
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach ($vehicle->fuelLogs as $log)
                            <tr>
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
                                    {{ $log->driver?->full_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ money($log->price_per_liter, 3) }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ $log->discount !== null ? money($log->discount) : '—' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-foreground">
                                    {{ money($log->total_value) }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ number_format((float) $log->odometer_reading, 0) }} كم
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ $log->consumption_rate !== null ? number_format($log->consumption_rate, 2).' كم/لتر' : 'تعبئة اولية' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-sm text-muted-foreground">
                لا توجد عمليات تعبئة لهذه المركبة.
            </p>
        @endif
    </div>
</div>
