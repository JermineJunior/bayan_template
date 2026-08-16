@extends('layouts.app')

@section('title', 'نظرة عامة على الأسطول')

@section('content')
    @php
        $statusLabels = [
            'active' => 'نشط',
            'maintenance' => 'صيانة',
            'stopped' => 'متوقفة',
            'sold' => 'مباعة',
            'out_of_service' => 'خارج الخدمة',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                نظرة عامة على الأسطول
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                لقطة حاليّة لجميع المركبات حسب الحالة أو الإدارة.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('reports.fleet-overview.results') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="status" class="mb-1 block text-sm font-medium text-foreground">
                        الحالة
                    </label>
                    <select
                        id="status"
                        name="status"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الحالات</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="management_id" class="mb-1 block text-sm font-medium text-foreground">
                        الإدارة
                    </label>
                    <select
                        id="management_id"
                        name="management_id"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الإدارات</option>
                        @foreach ($managements as $management)
                            <option value="{{ $management->id }}" @selected(request('management_id') == $management->id)>
                                {{ $management->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        إنشاء التقرير
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
