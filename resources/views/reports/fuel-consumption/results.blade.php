@extends('layouts.app')

@section('title', 'استهلاك الوقود')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    استهلاك الوقود
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    نتائج التقرير حسب الفلترة.
                </p>
            </div>

            <a
                href="{{ route('reports.fuel-consumption.print', request()->query()) }}"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                طباعة / تصدير PDF
            </a>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">
                    إجمالي اللترات
                </p>
                <p class="mt-1 text-2xl font-semibold text-primary">
                    {{ number_format((float) $totalLiters, 2) }}
                </p>
            </div>
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <p class="text-xs text-muted-foreground">
                    إجمالي القيمة
                </p>
                <p class="mt-1 text-2xl font-semibold text-primary">
                    {{ number_format((float) $totalValue, 2) }}
                </p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            @include('reports.fuel-consumption._table')
        </div>
    </div>
@endsection
