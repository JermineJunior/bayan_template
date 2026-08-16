<table class="min-w-full divide-y divide-border text-sm">
    <thead>
        <tr class="text-xs uppercase tracking-wide text-muted-foreground">
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                رقم المخالفة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                السائق
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                المركبة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ المخالفة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                الوصف
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                المبلغ
            </th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->id }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->driver?->full_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->vehicle?->internal_number ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->violation_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->description ?? '—' }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ number_format((float) $row->amount, 2) }}
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
