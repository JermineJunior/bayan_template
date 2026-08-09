@extends('layouts.app')

@section('title', 'الأقسام')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الأقسام
            </h1>

            @can('basic-data.create')
                <a
                    href="{{ route('departments.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء قسم
                </a>
            @endcan
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الرقم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الاسم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الإدارة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            السائقون
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($departments as $department)
                        <tr>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $department->number }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $department->name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $department->management?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-end text-muted-foreground">
                                {{ $department->drivers_count }} سائق
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('basic-data.edit')
                                        <a
                                            href="{{ route('departments.edit', $department) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل
                                        </a>
                                    @endcan

                                    @can('basic-data.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('departments.destroy', $department) }}"
                                            onsubmit="return confirm('هل تريد حذف هذا القسم؟')"
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
                            <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد أقسام بعد.
                                @can('basic-data.create')
                                    <a href="{{ route('departments.create') }}" class="text-primary hover:underline">
                                        أضف أول قسم
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($departments->hasPages())
            <div class="mt-6">
                {{ $departments->links() }}
            </div>
        @endif
    </div>
@endsection
