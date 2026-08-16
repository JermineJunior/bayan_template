@extends('layouts.app')

@section('title', 'سجل الحوادث')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    سجل الحوادث
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    نتائج التقرير حسب الفلترة.
                </p>
            </div>

            <a
                href="{{ route('reports.incidents-log.print', request()->query()) }}"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                طباعة / تصدير PDF
            </a>
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            @include('reports.incidents-log._table')
        </div>
    </div>
@endsection
