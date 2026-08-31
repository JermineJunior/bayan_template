<table class="min-w-full divide-y divide-border text-sm">
    <thead>
        <tr class="text-xs uppercase tracking-wide text-muted-foreground">
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                المركبة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                السائق
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ التعبئة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                اللترات
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                سعر اللتر
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                القيمة الإجمالية
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                قراءة العداد
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
                    {{ $row->driver?->full_name ?? '—' }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->filled_at?->format('Y-m-d H:i') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ number_format((float) $row->liters, 2) }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ money($row->price_per_liter, 3) }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ money($row->total_value) }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ number_format((float) $row->odometer_reading, 0) }} كم
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
