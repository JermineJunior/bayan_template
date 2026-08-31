@extends('layouts.app')

@section('title', 'الموردون')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الموردون
            </h1>

            @can('suppliers.create')
                <a
                    href="{{ route('suppliers.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إضافة مورد
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الاسم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الهاتف
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            العنوان
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            إجمالي الفواتير
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المدفوع
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الرصيد
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('suppliers.show', $supplier) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $supplier->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $supplier->phone ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $supplier->address ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ money($supplier->total_invoiced) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ money($supplier->total_paid) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($supplier->balance > 0)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        {{ money($supplier->balance) }}
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        {{ money($supplier->balance) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('suppliers.show', $supplier) }}"
                                        class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                    >
                                        عرض
                                    </a>

                                    @can('suppliers.edit')
                                        <a
                                            href="{{ route('suppliers.edit', $supplier) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل
                                        </a>
                                    @endcan

                                    @can('suppliers.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('suppliers.destroy', $supplier) }}"
                                            onsubmit="return confirmForm(this, 'هل تريد حذف هذا المورد؟', 'نعم، احذف')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                            >
                                                حذف
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">
                                لا يوجد موردون بعد.
                                @can('suppliers.create')
                                    <a href="{{ route('suppliers.create') }}" class="text-primary hover:underline">
                                        أضف أول مورد
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($suppliers->hasPages())
            <div class="mt-6">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>
@endsection
