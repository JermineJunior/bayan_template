@extends('layouts.app')

@section('title', 'اوامر الصيانة')

@section('content')
    @php
        $typeLabels = [
            'periodic' => ['دورية', 'bg-blue-100 text-blue-700'],
            'preventive' => ['وقائية', 'bg-green-100 text-green-700'],
            'emergency' => ['طارئة', 'bg-red-100 text-red-700'],
        ];

        $statusLabels = [
            'draft' => ['مسودة', 'bg-gray-100 text-gray-700'],
            'pending' => ['معلقة', 'bg-amber-100 text-amber-700'],
            'in_progress' => ['قيد التنفيذ', 'bg-blue-100 text-blue-700'],
            'completed' => ['مكتملة', 'bg-green-100 text-green-700'],
            'cancelled' => ['ملغاة', 'bg-red-100 text-red-700'],
        ];
    @endphp
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                إدارة اوامر الصيانة
            </h1>

            @can('maintenance.create')
                <a href="{{ route('maintenance.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    إنشاء امر صيانة
                </a>
            @endcan
        </div>

        <form method="GET" action="{{ route('maintenance.index') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="mb-1 block text-sm font-medium text-foreground">
                        بحث
                    </label>
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        placeholder="رقم امر الصيانة ..."
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                </div>

                <div>
                    <label for="type" class="mb-1 block text-sm font-medium text-foreground">
                        نوع الصيانة
                    </label>
                    <select id="type" name="type"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">كل انواع االصيانات</option>
                        <option value="periodic" @selected(request('type') == 'periodic')>صيانة دورية</option>
                        <option value="preventive" @selected(request('type') == 'preventive')>صيانة وقائية</option>
                        <option value="emergency" @selected(request('type') == 'emergency')>صيانة طارئة</option>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-foreground">
                        الحالة
                    </label>
                    <select id="status" name="status"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                        <option value="">كل الحالات</option>
                        <option value="pending" @selected(request('status') == 'pending')>قيد الانتظار</option>
                        <option value="in_progress" @selected(request('status') == 'in_progress')>في الصيانة</option>
                        <option value="completed" @selected(request('status') == 'completed')>اكتملت</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')> ملغية</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                        بحث
                    </button>

                    @if (request()->hasAny(['search', 'status', 'type']))
                        <a href="{{ route('maintenance.index') }}"
                            class="rounded-md border border-border px-4 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
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
                            رقم الامر
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المركبة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            النوع
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            التاريخ
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الورشة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            التكلفة
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
                    @forelse ($maintenances as $maintenance)
                        <tr>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $maintenance->maintenance_number }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $maintenance->vehicle->plate_number }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $type = $typeLabels[$maintenance->type] ?? ['—', 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $type[1] }}">
                                    {{ $type[0] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-4 py-3 text-muted-foreground">
                                    {{ $maintenance->date->format('Y-m-d') }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-4 py-3 text-muted-foreground">
                                    {{ $maintenance->workshop }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-muted px-2 py-1 text-xs text-foreground">
                                    {{ number_format($maintenance->total_cost) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $status = $statusLabels[$maintenance->status] ?? ['—', 'bg-gray-100 text-gray-700'];
                                @endphp
                                <span
                                    class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $status[1] }}">
                                    {{ $status[0] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('maintenance.view')
                                        <a href="{{ route('maintenance.show', $maintenance) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted">
                                            عرض
                                        </a>
                                    @endcan

                                    @can('maintenance.edit', $maintenance)
                                        <a href="{{ route('maintenance.edit', $maintenance) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted">
                                            تعديل
                                        </a>
                                    @endcan

                                    @can('maintenance.delete', $maintenance)
                                        <form method="POST" action="{{ route('maintenance.destroy', $maintenance) }}"
                                            onsubmit="return confirmForm(this, 'هل تريد حذف هذا السجل', 'نعم، احذف')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                                حذف
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                لا يوجد اوامر صيانة بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($maintenances->hasPages())
            <div class="mt-6">
                {{ $maintenances->links() }}
            </div>
        @endif
    </div>
@endsection
