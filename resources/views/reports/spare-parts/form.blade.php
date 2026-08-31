@extends('layouts.app')

@section('title', 'تقرير قطع الغيار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                تقرير قطع الغيار
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                حدد التصنيف أو المورد أو اعرض القطع منخفضة المخزون فقط.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('reports.spare-parts.results') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="category" class="mb-1 block text-sm font-medium text-foreground">
                        التصنيف
                    </label>
                    <select
                        id="category"
                        name="category"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">كل التصنيفات</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category }}" @selected(request('category') === $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

                <div class="flex items-end">
                    <label class="inline-flex cursor-pointer items-center gap-2 pb-2 text-sm font-medium text-foreground">
                        <input
                            type="checkbox"
                            name="low_stock"
                            value="1"
                            @checked(request()->boolean('low_stock'))
                            class="h-4 w-4 rounded border-border text-primary focus:ring-primary"
                        >
                        القطع منخفضة المخزون فقط
                    </label>
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
