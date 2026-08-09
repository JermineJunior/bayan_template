@extends('layouts.app')

@section('title', 'الإدارات')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الإدارات
            </h1>

            @can('basic-data.create')
                <a
                    href="{{ route('managements.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء إدارة
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
                            الأقسام
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المركبات
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($managements as $management)
                        <tr>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $management->number }}
                            </td>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $management->name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $management->departments_count }} قسم
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $management->vehicles_count }} مركبة
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('basic-data.edit')
                                        <a
                                            href="{{ route('managements.edit', $management) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل
                                        </a>
                                    @endcan

                                    @can('basic-data.delete')
                                        <form
                                            method="POST"
                                            action="{{ route('managements.destroy', $management) }}"
                                            onsubmit="return confirm('هل تريد حذف هذه الإدارة؟')"
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
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد إدارات بعد.
                                @can('basic-data.create')
                                    <a href="{{ route('managements.create') }}" class="text-primary hover:underline">
                                        أضف أول إدارة
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($managements->hasPages())
            <div class="mt-6">
                {{ $managements->links() }}
            </div>
        @endif
    </div>
@endsection
