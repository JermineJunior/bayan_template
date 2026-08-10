@extends('layouts.app')

@section('title', 'المركبات')

@section('content')
    @php
        $statusLabels = [
            'active' => ['نشط', 'bg-green-100 text-green-700'],
            'maintenance' => ['صيانة', 'bg-amber-100 text-amber-700'],
            'stopped' => ['متوقفة', 'bg-gray-100 text-gray-700'],
            'sold' => ['مباعة', 'bg-gray-100 text-gray-700'],
            'out_of_service' => ['خارج الخدمة', 'bg-red-100 text-red-700'],
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                المركبات
            </h1>

            @can('vehicles.create')
                <a
                    href="{{ route('vehicles.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء مركبة
                </a>
            @endcan
        </div>

        <form
            method="GET"
            action="{{ route('vehicles.index') }}"
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
                        placeholder="رقم داخلي، لوحة، نوع، موديل..."
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="management_id" class="mb-1 block text-sm font-medium text-foreground">
                        الإدارة
                    </label>
                    <select
                        id="management_id"
                        name="management_id"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الإدارات</option>
                        @foreach ($managements as $management)
                            <option
                                value="{{ $management->id }}"
                                @selected(request('management_id') == $management->id)
                            >
                                {{ $management->name }}
                            </option>
                        @endforeach
                    </select>
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
                        @foreach ($statusLabels as $value => $statusLabel)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $statusLabel[0] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        بحث
                    </button>

                    @if (request()->hasAny(['search', 'management_id', 'status']))
                        <a
                            href="{{ route('vehicles.index') }}"
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
                            الرقم الداخلي
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            رقم اللوحة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            النوع
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الإدارة
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
                    @forelse ($vehicles as $vehicle)
                        <tr>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $vehicle->internal_number }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $vehicle->plate_number }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $vehicle->type ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $vehicle->management?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $status = $statusLabels[$vehicle->status] ?? ['—', 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $status[1] }}">
                                    {{ $status[0] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('vehicles.view')
                                        <a
                                            href="{{ route('vehicles.show', $vehicle) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            عرض
                                        </a>
                                    @endcan

                                    @can('vehicles.edit')
                                        <a
                                            href="{{ route('vehicles.edit', $vehicle) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
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
                                لا توجد مركبات بعد.
                                @can('vehicles.create')
                                    <a href="{{ route('vehicles.create') }}" class="text-primary hover:underline">
                                        أضف أول مركبة
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($vehicles->hasPages())
            <div class="mt-6">
                {{ $vehicles->links() }}
            </div>
        @endif
    </div>
@endsection
