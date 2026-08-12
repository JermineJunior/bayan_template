@extends('layouts.app')

@section('title', 'الزيوت والفلاتر')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الزيوت والفلاتر
            </h1>
        </div>

        @can('oils.view')
            <div class="mb-6 overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-foreground">
                        الزيوت
                    </h2>

                    @can('oils.create')
                        <a
                            href="{{ route('oils.create') }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            إضافة زيت
                        </a>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    اسم الزيت
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الكود
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    النوع
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    العمر الافتراضي
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    التغييرات
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                                    إجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($oils as $oil)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ $oil->oil_name }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $oil->oil_code }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-foreground">
                                            {{ config('oil_types.'.$oil->oil_type, $oil->oil_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $oil->oil_life, 0) }} كم
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $oil->changes_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @can('oils.edit')
                                                <a
                                                    href="{{ route('oils.edit', $oil) }}"
                                                    class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                                >
                                                    تعديل
                                                </a>
                                            @endcan

                                            @can('oils.delete')
                                                <form
                                                    method="POST"
                                                    action="{{ route('oils.destroy', $oil) }}"
                                                    onsubmit="return confirmForm(this, 'هل تريد حذف هذا الزيت؟', 'نعم، احذف')"
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
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                        لا توجد زيوت بعد.
                                        @can('oils.create')
                                            <a href="{{ route('oils.create') }}" class="text-primary hover:underline">
                                                أضف أول زيت
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($oils->hasPages())
                    <div class="border-t border-border p-4">
                        {{ $oils->links() }}
                    </div>
                @endif
            </div>
        @endcan

        @can('filters.view')
            <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-border px-4 py-3">
                    <h2 class="text-sm font-semibold text-foreground">
                        الفلاتر
                    </h2>

                    @can('filters.create')
                        <a
                            href="{{ route('filters.create') }}"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            إضافة فلتر
                        </a>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border text-sm">
                        <thead>
                            <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    اسم الفلتر
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    الكود
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    النوع
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    العمر الافتراضي
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                    التغييرات
                                </th>
                                <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                                    إجراءات
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($filters as $filter)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-foreground">
                                        {{ $filter->filter_name }}
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $filter->filter_code }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-foreground">
                                            {{ config('filter_types.'.$filter->filter_type, $filter->filter_type) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ number_format((float) $filter->filter_life, 0) }} كم
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">
                                        {{ $filter->changes_count }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            @can('filters.edit')
                                                <a
                                                    href="{{ route('filters.edit', $filter) }}"
                                                    class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                                >
                                                    تعديل
                                                </a>
                                            @endcan

                                            @can('filters.delete')
                                                <form
                                                    method="POST"
                                                    action="{{ route('filters.destroy', $filter) }}"
                                                    onsubmit="return confirmForm(this, 'هل تريد حذف هذا الفلتر؟', 'نعم، احذف')"
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
                                    <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">
                                        لا توجد فلاتر بعد.
                                        @can('filters.create')
                                            <a href="{{ route('filters.create') }}" class="text-primary hover:underline">
                                                أضف أول فلتر
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($filters->hasPages())
                    <div class="border-t border-border p-4">
                        {{ $filters->links() }}
                    </div>
                @endif
            </div>
        @endcan
    </div>
@endsection
