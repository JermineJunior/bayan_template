@extends('layouts.app')

@section('title', 'تقرير الموردين')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                تقرير الموردين
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                حدد المورد أو الفترة الزمنية لعرض الفواتير والأرصدة.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('reports.suppliers.results') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="mb-4">
                @include('reports._quick_dates')
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="supplier_id" class="mb-1 block text-sm font-medium text-foreground">
                        المورد
                    </label>
                    <select
                        id="supplier_id"
                        name="supplier_id"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل الموردين</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="from_date" class="mb-1 block text-sm font-medium text-foreground">
                        من تاريخ
                    </label>
                    <input
                        id="from_date"
                        name="from_date"
                        type="date"
                        value="{{ request('from_date') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div>
                    <label for="to_date" class="mb-1 block text-sm font-medium text-foreground">
                        إلى تاريخ
                    </label>
                    <input
                        id="to_date"
                        name="to_date"
                        type="date"
                        value="{{ request('to_date') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
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
