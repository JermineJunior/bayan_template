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
                الزيت / الفلتر
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ التغيير
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                عداد التغيير
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                العداد التالي
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
                    <span class="badge">{{ $row->type_label }}</span>
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->item_name }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->last_change?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ number_format((float) $row->odometer_when_change, 0) }} كم
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->next_change_odometer !== null ? number_format((float) $row->next_change_odometer, 0).' كم' : '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->cost !== null ? money($row->cost) : '—' }}
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
