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
                            onsubmit="return confirm('هل تريد حذف هذه المركبة؟')"
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
            </div>

            <div x-show="tab === 'info'" x-cloak role="tabpanel">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6">
                        @if ($vehicle->image_url)
                            <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
                                <img
                                    src="{{ $vehicle->image_url }}"
                                    alt="{{ $vehicle->internal_number }}"
                                    class="h-56 w-full object-cover"
                                >
                            </div>
                        @endif

                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                ملخص
                            </h2>

                            <dl class="space-y-3 text-sm [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">رقم اللوحة</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->plate_number }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">الإدارة</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->management?->name ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">السائق الحالي</dt>
                                    <dd class="font-medium text-foreground">{{ $currentDriver?->full_name ?? 'غير مخصصة' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">العداد الحالي</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->current_mileage ?? '—' }} كم</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-muted-foreground">ساعات التشغيل</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->operating_hours ?? '—' }} ساعة</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-6 lg:col-span-2">
                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                البيانات العامة
                            </h2>

                            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الرقم الداخلي</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->internal_number }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">رقم اللوحة</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->plate_number }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">النوع</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->type ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الفئة</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->category ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الموديل</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->model ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">سنة الصنع</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->manufacture_year ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">اللون</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->color ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">الحالة</dt>
                                    <dd class="font-medium text-foreground">{{ $status[0] }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                            <h2 class="mb-4 text-sm font-semibold text-foreground">
                                التفاصيل الفنية
                            </h2>

                            <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">رقم الهيكل</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->chassis_number ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">رقم المحرك</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->engine_number ?: '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">نوع الوقود</dt>
                                    <dd class="font-medium text-foreground">{{ $fuelLabels[$vehicle->fuel_type] ?? '—' }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                                    <dt class="text-muted-foreground">سعة المحرك</dt>
                                    <dd class="font-medium text-foreground">{{ $vehicle->engine_capacity ?: '—' }}لتر</dd>
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
                                السائق الحالي
                            </h2>

                            @if ($currentDriver)
                                <div class="flex flex-wrap items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-foreground">
                                            <a href="{{ route('drivers.show', $currentDriver) }}" class="text-primary hover:underline">
                                                {{ $currentDriver->full_name }}
                                            </a>
                                        </p>
                                        <p class="mt-0.5 text-sm text-muted-foreground">
                                            {{ $currentDriver->national_id }}
                                        </p>
                                    </div>

                                    @can('vehicles.end-assignment')
                                        <form
                                            method="POST"
                                            action="{{ route('assignments.destroy', $vehicle->currentAssignment) }}"
                                            onsubmit="return confirm('هل تريد إنهاء إسناد هذا السائق؟')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                            >
                                                إنهاء الإسناد
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            @else
                                <p class="mb-4 text-sm text-muted-foreground">
                                    لا يوجد سائق مسند حاليًا.
                                </p>
                            @endif

                            @can('vehicles.assign')
                                <div class="{{ $currentDriver ? 'mt-6 border-t border-border py-5' : '' }}">
                                    <form
                                        method="POST"
                                        action="{{ route('vehicles.assign-driver', $vehicle) }}"
                                        class="flex flex-wrap items-end gap-3"
                                    >
                                        @csrf
                                        <div class="min-w-48 flex-1">
                                            <label for="driver_id" class="mb-1 block text-sm font-medium text-foreground">
                                                اختر سائقًا
                                            </label>
                                            <select
                                                id="driver_id"
                                                name="driver_id"
                                                required
                                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                            >
                                                @foreach ($drivers as $driver)
                                                    <option value="{{ $driver->id }}">
                                                        {{ $driver->full_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button
                                            type="submit"
                                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                        >
                                            {{ $currentDriver ? 'تغيير السائق' : 'إسناد سائق' }}
                                        </button>
                                    </form>
                                    <p class="mt-3 text-xs text-muted-foreground">
                                        إذا اخترت سائقًا مسندًا لمركبة أخرى، سيُنقل إلى هذه المركبة تلقائيًا.
                                    </p>
                                </div>
                            @endcan
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
                                                    السائق
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
                                                            href="{{ route('drivers.show', $assignment->driver) }}"
                                                            class="font-medium text-foreground hover:text-primary"
                                                        >
                                                            {{ $assignment->driver->full_name }}
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
                                    لا يوجد سجل إسناد لهذه المركبة.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
