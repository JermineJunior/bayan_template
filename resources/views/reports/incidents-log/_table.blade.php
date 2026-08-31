@php
    $claimStatusLabels = [
        'pending' => 'قيد المراجعة',
        'approved' => 'موافق عليه',
        'rejected' => 'مرفوض',
        'paid' => 'مدفوع',
    ];
@endphp

<style>
    .badge {
        display: inline-block;
        font-size: 7.5pt;
        padding: 1px 6px;
        border: 0.3pt solid #9ca3af;
        border-radius: 3px;
    }
</style>

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
                الموقع
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تكلفة الإصلاح
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                حالة المطالبة
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->report_number }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->vehicle?->internal_number ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->driver?->full_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->incident_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->location ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->repair_cost !== null ? money($row->repair_cost) : '—' }}
                </td>
                <td class="px-4 py-3">
                    @if ($row->claim_status)
                        <span class="badge">{{ $claimStatusLabels[$row->claim_status] ?? $row->claim_status }}</span>
                    @else
                        <span class="text-muted-foreground">—</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                    لا توجد بيانات مطابقة.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
