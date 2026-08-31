<style>
    .badge {
        display: inline-block;
        font-size: 7.5pt;
        padding: 1px 6px;
        border: 0.3pt solid #9ca3af;
        border-radius: 3px;
    }
    .badge-green {
        border-color: #16a34a;
        color: #16a34a;
    }
    .badge-red {
        border-color: #dc2626;
        color: #dc2626;
    }
</style>

<table class="min-w-full divide-y divide-border text-sm">
    <thead>
        <tr class="text-xs uppercase tracking-wide text-muted-foreground">
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الرمز</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الاسم</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">التصنيف</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">المورد</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">سعر الشراء</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الحد الأدنى</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">المتوفر</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الحالة</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->part_number }}
                </td>
                <td class="px-4 py-3 text-foreground">
                    {{ $row->name }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->category }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->supplier_name }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ money($row->purchase_price) }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ number_format((float) $row->minimum_quantity) }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ number_format((float) $row->quantity_on_hand) }}
                </td>
                <td class="px-4 py-3">
                    @if ($row->is_low_stock)
                        <span class="badge badge-red">منخفض</span>
                    @else
                        <span class="badge badge-green">متوفر</span>
                    @endif
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
