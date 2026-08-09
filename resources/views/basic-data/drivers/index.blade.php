@extends('layouts.app')

@section('title', 'السائقون')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                السائقون
            </h1>

            @can('drivers.create')
                <a
                    href="{{ route('drivers.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء سائق
                </a>
            @endcan
        </div>

        <form
            method="GET"
            action="{{ route('drivers.index') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-foreground">
                        بحث
                    </label>
                    <input
                        id="search"
                        name="search"
                        type="text"
                        value="{{ request('search') }}"
                        placeholder="اسم السائق، الرقم الوطني..."
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-foreground">
                        الحالة
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الحالات</option>
                        <option value="active" @selected(request('status') === 'active')>نشط</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>غير نشط</option>
                    </select>
                </div>

                <div>
                    <label for="license" class="mb-1 block text-sm font-medium text-foreground">
                        الرخصة
                    </label>
                    <select
                        id="license"
                        name="license"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الرخص</option>
                        <option value="expired" @selected(request('license') === 'expired')>منتهية</option>
                        <option value="expiring" @selected(request('license') === 'expiring')>تنتهي خلال 30 يوم</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        بحث
                    </button>

                    @if (request()->hasAny(['search', 'status', 'license']))
                        <a
                            href="{{ route('drivers.index') }}"
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
                            الاسم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الرقم الوطني
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            القسم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الرخصة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الحالة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($drivers as $driver)
                        <tr>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $driver->full_name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $driver->national_id }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $driver->department?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($driver->license_type)
                                    <div class="flex items-center gap-2 text-muted-foreground">
                                        <span>
                                            {{ $driver->license_type === 'general' ? 'عامة' : ($driver->license_type === 'private' ? 'خاصة' : 'أخرى') }}
                                            @if ($driver->license_expiry_date)
                                                — {{ $driver->license_expiry_date->format('Y-m-d') }}
                                            @endif
                                        </span>
                                        @if ($driver->license_expiry_date?->isPast())
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">
                                                منتهية
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($driver->status === 'active')
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        نشط
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        غير نشط
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('drivers.view')
                                        <a
                                            href="{{ route('drivers.show', $driver) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            عرض
                                        </a>
                                    @endcan

                                    @can('drivers.edit')
                                        <a
                                            href="{{ route('drivers.edit', $driver) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
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
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                لا يوجد سائقون بعد.
                                @can('drivers.create')
                                    <a href="{{ route('drivers.create') }}" class="text-primary hover:underline">
                                        أضف أول سائق
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($drivers->hasPages())
            <div class="mt-6">
                {{ $drivers->links() }}
            </div>
        @endif
    </div>
@endsection
