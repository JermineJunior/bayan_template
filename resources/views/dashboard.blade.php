@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                لوحة التحكم
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                مرحبًا {{ auth()->user()->name }}.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($totalVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">إجمالي المركبات</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($activeVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">مركبات نشطة</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($maintenanceVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تحت الصيانة</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($stoppedVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">مركبات متوقفة</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($licensesExpiringSoon) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">رخص سائقين قريبة الانتهاء</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($insurancesExpiringSoon) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تأمينات قريبة الانتهاء</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($dueOilChanges) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تغيير زيوت مستحق</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format((float) $fleetFuelCost, 2) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تكلفة الوقود الشهرية</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format((float) $monthlyMaintenanceCost, 2) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تكلفة الصيانة الشهرية</div>
            </div>
        </div>

        @if ($expiringPolicies->isNotEmpty())
            <div class="mt-6 rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    تأمينات قريبة الانتهاء / منتهية
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    المركبة
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    رقم البوليصة
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    شركة التأمين
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    تاريخ الانتهاء
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الأيام المتبقية
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($expiringPolicies as $policy)
                                <tr>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('vehicles.show', $policy->vehicle) }}" class="font-medium text-foreground hover:text-primary">
                                            {{ $policy->vehicle->internal_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $policy->policy_number }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $policy->insurance_company }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $policy->end_date?->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @php
                                            $days = $policy->days_until_expiry;
                                        @endphp
                                        <span class="font-medium {{ $days < 0 ? 'text-red-600' : 'text-amber-600' }}">
                                            {{ $days < 0 ? 'منتهية' : $days.' يوم' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="mt-6 grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    توزيع المركبات حسب الحالة
                </h2>
                <div class="relative h-72">
                    <canvas id="vehicleStatusChart"></canvas>
                </div>
            </div>
            <div dir="rtl" class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    استهلاك الوقود الشهري
                </h2>
                <div class="relative h-72">
                    <canvas id="fuelConsumptionChart"></canvas>
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    أعلى المركبات استهلاكًا للوقود هذا الشهر
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    المركبة
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الاستهلاك (لتر)
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($topFuelVehicles as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('vehicles.show', $item->vehicle) }}" class="font-medium text-foreground hover:text-primary">
                                            {{ $item->vehicle->internal_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $item->total_liters, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-3 text-center text-muted-foreground">
                                        لا توجد بيانات لهذا الشهر
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    أعلى المركبات تكلفةً في الصيانة
                </h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    المركبة
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    إجمالي التكلفة
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($topMaintenanceVehicles as $item)
                                <tr>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('vehicles.show', $item->vehicle) }}" class="font-medium text-foreground hover:text-primary">
                                            {{ $item->vehicle->internal_number }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $item->total_cost, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-4 py-3 text-center text-muted-foreground">
                                        لا توجد بيانات صيانة
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const statusColors = {
                    active: '#22c55e',
                    maintenance: '#f59e0b',
                    stopped: '#ef4444',
                    sold: '#6b7280',
                    out_of_service: '#3b82f6',
                };

                const statusLabels = {
                    active: 'نشط',
                    maintenance: 'تحت الصيانة',
                    stopped: 'متوقفة',
                    sold: 'مباعة',
                    out_of_service: 'خارج الخدمة',
                };

                const vehicleStatusCtx = document.getElementById('vehicleStatusChart');
                if (vehicleStatusCtx) {
                    const labels = @json(array_keys($vehicleStatusData));
                    const data = @json(array_values($vehicleStatusData));
                    const backgroundColors = labels.map(label => statusColors[label] || '#9ca3af');

                    new window.Chart(vehicleStatusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: labels.map(label => statusLabels[label] || label),
                            datasets: [{
                                data: data,
                                backgroundColor: backgroundColors,
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { family: 'Cairo', size: 12 },
                                        padding: 16,
                                        rtl: true,
                                        usePointStyle: true,
                                    },
                                },
                            },
                        },
                    });
                }

                const fuelCtx = document.getElementById('fuelConsumptionChart');
                if (fuelCtx) {
                    new window.Chart(fuelCtx, {
                        type: 'bar',
                        data: {
                            labels: @json(array_keys($monthlyFuelConsumption)),
                            datasets: [{
                                label: 'استهلاك الوقود (لتر)',
                                data: @json(array_values($monthlyFuelConsumption)),
                                backgroundColor: '#3b82f6',
                                borderRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        font: { family: 'Cairo', size: 11 },
                                    },
                                    grid: {
                                        color: '#e5e7eb',
                                    },
                                },
                                x: {
                                    ticks: {
                                        font: { family: 'Cairo', size: 11 },
                                    },
                                    grid: {
                                        display: false,
                                    },
                                },
                            },
                            plugins: {
                                legend: {
                                    display: false,
                                },
                            },
                        },
                    });
                }
            });
        </script>
    @endpush
@endsection