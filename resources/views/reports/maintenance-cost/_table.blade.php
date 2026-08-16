@php
    $typeLabels = [
        'periodic' => 'دورية',
        'preventive' => 'وقائية',
        'emergency' => 'طارئة',
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
                المركبة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                نوع الصيانة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ البداية
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ الانتهاء
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                السبب
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                التكلفة
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->vehicle?->internal_number ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="badge">{{ $typeLabels[$row->type] ?? $row->type }}</span>
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->start_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->end_date?->format('Y-m-d') ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->reason ?? '—' }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ number_format((float) $row->total_cost, 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                    لا توجد بيانات مطابقة.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
