@php
    $expenseTypeLabels = [
        'fuel' => 'وقود',
        'oil' => 'زيوت',
        'filter' => 'فلاتر',
        'maintenance' => 'صيانة',
        'spare_parts' => 'قطع غيار',
        'violations' => 'مخالفات',
        'other' => 'أخرى',
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
                النوع
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                المبلغ
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                التاريخ
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                الوصف
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                المصدر
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->vehicle?->internal_number ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $expenseTypeLabels[$row->expense_type] ?? $row->expense_type }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ money($row->amount) }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->expense_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->description ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="badge">{{ $row->is_auto_generated ? 'تلقائي' : 'يدوي' }}</span>
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
