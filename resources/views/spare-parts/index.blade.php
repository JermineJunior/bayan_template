@extends('layouts.app')

@section('title', 'قطع الغيار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                قطع الغيار
            </h1>

            @can('spare-parts.create')
                <a
                    href="{{ route('spare-parts.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إضافة قطعة غيار
                </a>
            @endcan
        </div>

        <form method="GET" action="{{ route('spare-parts.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
            <div>
                <label for="search" class="mb-1 block text-sm font-medium text-foreground">
                    البحث (الرقم / الاسم)
                </label>
                <input
                    id="search"
                    name="search"
                    type="text"
                    value="{{ request('search') }}"
                    placeholder="رقم القطعة أو الاسم"
                    class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:w-64"
                >
            </div>

            <div>
                <label for="category" class="mb-1 block text-sm font-medium text-foreground">
                    التصنيف
                </label>
                <select
                    id="category"
                    name="category"
                    class="w-full rounded-md border border-border bg-surface px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary sm:w-48"
                >
                    <option value="">كل التصنيفات</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category }}" @selected(request('category') == $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <label class="flex items-center gap-2 py-2 text-sm text-foreground">
                <input
                    type="checkbox"
                    name="low_stock"
                    value="1"
                    @checked(request()->boolean('low_stock'))
                    class="size-4 rounded border-border text-primary focus:ring-primary"
                >
                المنخفض فقط
            </label>

            <button
                type="submit"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90"
            >
                تصفية
            </button>

            @if (request()->hasAny(['search', 'category', 'low_stock']))
                <a
                    href="{{ route('spare-parts.index') }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    مسح
                </a>
            @endif
        </form>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            رقم القطعة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الاسم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            التصنيف
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المورد الافتراضي
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المتوفر
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الحد الأدنى
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الحالة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($spareParts as $sparePart)
                        <tr>
                            <td class="px-4 py-3">
                                <a
                                    href="{{ route('spare-parts.show', $sparePart) }}"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ $sparePart->part_number }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $sparePart->name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $sparePart->category ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $sparePart->defaultSupplier?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ number_format($sparePart->quantity_on_hand, 2) }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ number_format($sparePart->minimum_quantity, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($sparePart->is_low_stock && $sparePart->quantity_on_hand <= 0)
                                    <span class="inline-flex rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">
                                        نفد المخزون
                                    </span>
                                @elseif ($sparePart->is_low_stock)
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                                        منخفض
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                        متوفر
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a
                                        href="{{ route('spare-parts.show', $sparePart) }}"
                                        class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                    >
                                        عرض
                                    </a>

                                    @can('spare-parts.edit')
                                        <a
                                            href="{{ route('spare-parts.edit', $sparePart) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل
                                        </a>
                                    @endcan

                                    @can('spare-parts.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('spare-parts.destroy', $sparePart) }}"
                                            onsubmit="return confirmForm(this, 'هل تريد حذف هذه القطعة؟', 'نعم، احذف')"
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
                            <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد قطع غيار بعد.
                                @can('spare-parts.create')
                                    <a href="{{ route('spare-parts.create') }}" class="text-primary hover:underline">
                                        أضف أول قطعة غيار
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($spareParts->hasPages())
            <div class="mt-6">
                {{ $spareParts->links() }}
            </div>
        @endif
    </div>
@endsection
