@php
    $statusLabels = [
        'active' => 'نشط',
        'maintenance' => 'صيانة',
        'stopped' => 'متوقفة',
        'sold' => 'مباعة',
        'out_of_service' => 'خارج الخدمة',
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
                الإدارة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                النوع
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                الحالة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                السائق الحالي
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                العداد الحالي
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->internal_number }}
                    <p class="text-xs text-muted-foreground">
                        {{ $row->plate_number }}
                    </p>
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->management?->name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->type ?? '—' }}
                </td>
                <td class="px-4 py-3">
                    <span class="badge">{{ $statusLabels[$row->status] ?? $row->status }}</span>
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->currentAssignment?->driver?->full_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ number_format((float) $row->current_odometer, 0) }} كم
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
