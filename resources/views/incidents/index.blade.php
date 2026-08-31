@extends('layouts.app')

@section('title', 'الحوادث')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الحوادث
            </h1>
        </div>

        <form
            method="GET"
            action="{{ route('incidents.index') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
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
                    <label for="claim_status" class="mb-1 block text-sm font-medium text-foreground">
                        حالة المطالبة
                    </label>
                    <select
                        id="claim_status"
                        name="claim_status"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الحالات</option>
                        <option value="pending" @selected(request('claim_status') === 'pending')>قيد المراجعة</option>
                        <option value="approved" @selected(request('claim_status') === 'approved')>موافق عليه</option>
                        <option value="rejected" @selected(request('claim_status') === 'rejected')>مرفوض</option>
                        <option value="paid" @selected(request('claim_status') === 'paid')>مدفوع</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        بحث
                    </button>

                    @if (request()->hasAny(['vehicle_id', 'claim_status']))
                        <a
                            href="{{ route('incidents.index') }}"
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
                            رقم التقرير
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المركبة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            السائق
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            تاريخ الحادث
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            حالة المطالبة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            تكلفة الإصلاح
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($incidents as $incident)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('incidents.show', $incident) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $incident->report_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                <a
                                    href="{{ route('vehicles.show', $incident->vehicle) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $incident->vehicle->internal_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $incident->driver?->full_name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $incident->incident_date?->format('Y-m-d') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($incident->claim_status)
                                    @php
                                        $claimLabels = [
                                            'pending' => 'قيد المراجعة',
                                            'approved' => 'موافق عليه',
                                            'rejected' => 'مرفوض',
                                            'paid' => 'مدفوع',
                                        ];
                                        $claimColors = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'approved' => 'bg-green-100 text-green-700',
                                            'rejected' => 'bg-red-100 text-red-700',
                                            'paid' => 'bg-blue-100 text-blue-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $claimColors[$incident->claim_status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $claimLabels[$incident->claim_status] ?? $incident->claim_status }}
                                    </span>
                                @else
                                    <span class="text-muted-foreground">لا توجد مطالبة</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $incident->repair_cost !== null ? money($incident->repair_cost) : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end">
                                    @can('incidents.view')
                                        <a
                                            href="{{ route('incidents.show', $incident) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            عرض
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد حوادث مسجلة.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($incidents->hasPages())
            <div class="mt-6">
                {{ $incidents->links() }}
            </div>
        @endif
    </div>
@endsection
