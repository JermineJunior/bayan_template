@extends('layouts.app')

@section('title', $vehicle->internal_number)

@section('content')
    @php
        $statusLabels = [
            'active' => ['نشط', 'bg-green-100 text-green-700'],
            'maintenance' => ['صيانة', 'bg-amber-100 text-amber-700'],
            'stopped' => ['متوقفة', 'bg-gray-100 text-gray-700'],
            'sold' => ['مباعة', 'bg-gray-100 text-gray-700'],
            'out_of_service' => ['خارج الخدمة', 'bg-red-100 text-red-700'],
        ];
        $fuelLabels = ['gasoline' => 'بنزين', 'diesel' => 'ديزل'];
        $status = $statusLabels[$vehicle->status] ?? ['—', 'bg-gray-100 text-gray-700'];
        $currentDriver = $vehicle->currentDriver();
        $assignments = $vehicle->driverAssignments()->with('driver')->latest('assignment_date')->get();
        $fuelCostPerKm = $vehicle->fuelCostPerKilometer();
        $avgMonthlyFuelLiters = $vehicle->averageMonthlyFuelConsumption();
        $oilStatus = $vehicle->currentOilStatus();
        $filterStatus = $vehicle->currentFilterStatus();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('vehicles.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبات
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        {{ $vehicle->internal_number }}
                    </h1>
                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $status[1] }}">
                        {{ $status[0] }}
                    </span>
                </div>

                <div class="flex items-center gap-2">
                    @can('vehicles.edit')
                        <a
                            href="{{ route('vehicles.edit', $vehicle) }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تعديل
                        </a>
                    @endcan

                    @can('vehicles.delete')
                        <form
                            method="POST"
                            action="{{ route('vehicles.destroy', $vehicle) }}"
                            onsubmit="return confirmForm(this, 'هل تريد حذف هذه المركبة؟', 'نعم، احذف')"
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

        <div x-data="{
            tab: localStorage.getItem('vehicle-tabs-{{ $vehicle->id }}') || 'info',
            init() {
                const valid = ['info', 'assign', 'odometer', 'fuel', 'oil', 'filters'];
                if (!valid.includes(this.tab)) this.tab = 'info';
                this.$watch('tab', value => localStorage.setItem('vehicle-tabs-{{ $vehicle->id }}', value));
            }
        }">
            <div
                class="mb-6 flex gap-1 overflow-x-auto border-b border-border"
                role="tablist"
                aria-label="تبويبات المركبة"
            >
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'info'"
                    :aria-selected="tab === 'info'"
                    :class="tab === 'info' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    معلومات المركبة
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
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'fuel'"
                    :aria-selected="tab === 'fuel'"
                    :class="tab === 'fuel' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    الوقود
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'oil'"
                    :aria-selected="tab === 'oil'"
                    :class="tab === 'oil' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    الزيوت
                </button>
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'filters'"
                    :aria-selected="tab === 'filters'"
                    :class="tab === 'filters' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    الفلاتر
                </button>
            </div>

            @include('basic-data.vehicles.tabs.info', [
                'vehicle' => $vehicle,
                'status' => $status,
                'currentDriver' => $currentDriver,
                'fuelLabels' => $fuelLabels,
            ])

            @include('basic-data.vehicles.tabs.assignment', [
                'vehicle' => $vehicle,
                'currentDriver' => $currentDriver,
                'assignments' => $assignments,
                'drivers' => $drivers,
            ])

            @include('basic-data.vehicles.tabs.odometer', [
                'vehicle' => $vehicle,
            ])

            @include('basic-data.vehicles.tabs.fuel', [
                'vehicle' => $vehicle,
                'fuelLabels' => $fuelLabels,
                'fuelCostPerKm' => $fuelCostPerKm,
                'avgMonthlyFuelLiters' => $avgMonthlyFuelLiters,
            ])

            @include('basic-data.vehicles.tabs.oil', [
                'vehicle' => $vehicle,
                'oilStatus' => $oilStatus,
            ])

            @include('basic-data.vehicles.tabs.filters', [
                'vehicle' => $vehicle,
                'filterStatus' => $filterStatus,
            ])
        </div>
    </div>
@endsection
