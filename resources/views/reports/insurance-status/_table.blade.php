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
                رقم البوليصا
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                شركة التأمين
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ البداية
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                تاريخ الانتهاء
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                القيمة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                الحالة
            </th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                الأيام المتبقية
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
                    {{ $row->policy_number }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->insurance_company }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->start_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->end_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ money($row->value) }}
                </td>
                <td class="px-4 py-3">
                    <span class="badge">{{ $row->is_expired ? 'منتهية' : ($row->is_current ? 'سارية' : 'غير سارية') }}</span>
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->is_expired ? '—' : $row->days_until_expiry.' يوم' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                    لا توجد بيانات مطابقة.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
