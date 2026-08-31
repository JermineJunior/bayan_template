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
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">المورد</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">رقم الفاتورة</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">التاريخ</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">قيمة الفاتورة</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">المدفوع</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الرصيد</th>
            <th class="bg-muted/50 px-4 py-3 text-start font-medium">الحالة</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($rows as $row)
            <tr>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ $row->supplier_name }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->invoice_number }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ $row->invoice_date?->format('Y-m-d') }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ money($row->amount) }}
                </td>
                <td class="px-4 py-3 text-muted-foreground">
                    {{ money($row->total_paid) }}
                </td>
                <td class="px-4 py-3 font-medium text-foreground">
                    {{ money($row->balance) }}
                </td>
                <td class="px-4 py-3">
                    @if ($row->balance <= 0)
                        <span class="badge badge-green">مدفوع بالكامل</span>
                    @else
                        <span class="badge badge-red">لديه رصيد</span>
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
