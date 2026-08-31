@extends('layouts.app')

@section('title', 'تقرير قطع الغيار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    تقرير قطع الغيار
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    نتائج التقرير حسب الفلترة.
                </p>
            </div>

            <a
                href="{{ route('reports.spare-parts.print', request()->query()) }}"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                طباعة / تصدير PDF
            </a>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">عدد القطع</p>
                <p class="mt-1 text-2xl font-semibold text-primary">
                    {{ number_format($totalParts) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">قطع منخفضة المخزون</p>
                <p class="mt-1 text-2xl font-semibold text-primary">
                    {{ number_format($lowStockCount) }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            @include('reports.spare-parts._table')
        </div>
    </div>
@endsection
