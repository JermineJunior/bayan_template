@extends('layouts.app')

@section('title', $driver->full_name)

@section('content')
    @php
        $licenseTypeLabels = ['general' => 'عامة', 'private' => 'خاصة', 'other' => 'أخرى'];
        $currentVehicle = $driver->currentVehicle();
        $assignments = $driver->vehicleAssignments()->with('vehicle')->latest('assignment_date')->get();
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('drivers.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى السائقين
            </a>

            <div class="mt-2 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">
                            {{ $driver->full_name }}
                        </h1>
                        <span class="text-sm text-muted-foreground">{{ $driver->national_id }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @can('drivers.edit')
                        <a
                            href="{{ route('drivers.edit', $driver) }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تعديل
                        </a>
                    @endcan

                    @can('drivers.delete')
                        <form
                            method="POST"
                            action="{{ route('drivers.destroy', $driver) }}"
                            onsubmit="return confirm('هل تريد حذف هذا السائق؟')"
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

        <div x-data="{ tab: 'info' }">
            <div
                class="mb-6 flex gap-1 overflow-x-auto border-b border-border"
                role="tablist"
                aria-label="تبويبات السائق"
            >
                <button
                    type="button"
                    role="tab"
                    @click="tab = 'info'"
                    :aria-selected="tab === 'info'"
                    :class="tab === 'info' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
                    class="-mb-px shrink-0 border-b-2 px-4 py-2.5 text-sm font-medium transition-colors"
                >
                    معلومات السائق
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
                                        @if ($driver->status === 'active')
                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">نشط</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">غير نشط</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">القسم</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->department?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">المركبة الحالية</dt>
                                    <dd class="font-medium text-foreground">{{ $currentVehicle?->internal_number ?? 'غير مخصصة' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">تاريخ التعيين</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->hire_date?->format('Y-m-d') ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                البيانات الشخصية
                            </h2>

                            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الاسم</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->full_name }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الرقم الوطني</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->national_id }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">رقم الهاتف</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->phone_number ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">القسم</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->department?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تاريخ التعيين</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->hire_date?->format('Y-m-d') ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الحالة</dt>
                                    <dd class="font-medium text-foreground">{{ $driver->status === 'active' ? 'نشط' : 'غير نشط' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                رخصة القيادة
                            </h2>

                            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">النوع</dt>
                                    <dd class="font-medium text-foreground">{{ $licenseTypeLabels[$driver->license_type] ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">تاريخ الانتهاء</dt>
                                    <dd class="flex items-center gap-2 font-medium text-foreground">
                                        {{ $driver->license_expiry_date?->format('Y-m-d') ?? '—' }}
                                        @if ($driver->license_expiry_date?->isPast())
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">منتهية</span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div x-show="tab === 'assign'" x-cloak role="tabpanel">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div>
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                المركبة الحالية
                            </h2>

                            @if ($currentVehicle)
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground">
                                        <a href="{{ route('vehicles.show', $currentVehicle) }}" class="text-primary hover:underline">
                                            {{ $currentVehicle->internal_number }}
                                        </a>
                                    </p>
                                    <p class="mt-0.5 text-sm text-muted-foreground">
                                        {{ $currentVehicle->plate_number }}
                                    </p>
                                </div>
                            @else
                                <p class="text-sm text-muted-foreground">
                                    لا توجد مركبة مسندة حاليًا.
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                سجل الإسناد
                            </h2>

                            @if ($assignments->isNotEmpty())
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-border text-sm">
                                        <thead>
                                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    المركبة
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    تاريخ الإسناد
                                                </th>
                                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                                    تاريخ الإنهاء
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-border">
                                            @foreach ($assignments as $assignment)
                                                <tr>
                                                    <td class="px-4 py-3">
                                                        <a
                                                            href="{{ route('vehicles.show', $assignment->vehicle) }}"
                                                            class="font-medium text-foreground hover:text-primary"
                                                        >
                                                            {{ $assignment->vehicle->internal_number }}
                                                        </a>
                                                    </td>
                                                    <td class="px-4 py-3 text-muted-foreground">
                                                        {{ $assignment->assignment_date?->format('Y-m-d') ?? '—' }}
                                                    </td>
                                                    <td class="px-4 py-3">
                                                        @if ($assignment->is_current)
                                                            <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                                حالي
                                                            </span>
                                                        @else
                                                            <span class="text-muted-foreground">
                                                                {{ $assignment->ended_at?->format('Y-m-d') ?? '—' }}
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
                                    لا يوجد سجل إسناد لهذا السائق.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
