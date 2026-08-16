@extends('layouts.app')

@section('title', 'التقارير')

@section('content')
    @php
        $reports = [
            [
                'title' => 'استهلاك الوقود',
                'description' => 'تفاصيل عمليات التعبئة مع معدل الاستهلاك لكل مركبة.',
                'route' => 'reports.fuel-consumption.form',
                'permission' => 'fuel.view',
            ],
            [
                'title' => 'نظرة عامة على الأسطول',
                'description' => 'لقطة حاليّة لجميع المركبات وحالتها والسائق الحالي لكل منها.',
                'route' => 'reports.fleet-overview.form',
                'permission' => 'vehicles.view',
            ],
            [
                'title' => 'سجل تغيير الزيوت والفلاتر',
                'description' => 'تاريخ عمليات تغيير الزيت والفلتر لجميع المركبات.',
                'route' => 'reports.oil-filter-changes.form',
                'permission' => 'oil-changes.view',
            ],
            [
                'title' => 'حالة التأمينات',
                'description' => 'البوليصات الحالية والمنتهية حسب تاريخ الانتهاء.',
                'route' => 'reports.insurance-status.form',
                'permission' => 'insurance-policies.view',
            ],
            [
                'title' => 'سجل الحوادث',
                'description' => 'جميع الحوادث المسجلة مع حالة المطالبة التامينية.',
                'route' => 'reports.incidents-log.form',
                'permission' => 'incidents.view',
            ],
            [
                'title' => 'تقرير المصروفات',
                'description' => 'المصروفات حسب المركبة والنوع مع إجمالي المبالغ.',
                'route' => 'reports.expenses.form',
                'permission' => 'expenses.view',
            ],
            [
                'title' => 'مخالفات السائقين',
                'description' => 'المخالفات المرورية المسجلة على السائقين.',
                'route' => 'reports.driver-violations.form',
                'permission' => 'violations.view',
            ],
            [
                'title' => 'تكاليف الصيانة',
                'description' => 'تكاليف أوامر الصيانة حسب المركبة والفترة الزمنية.',
                'route' => 'reports.maintenance-cost.form',
                'permission' => 'maintenance.view',
            ],
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                التقارير
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                اختر تقريرًا لتصفية البيانات ثم طباعتها أو تصديرها بصيغة PDF.
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($reports as $report)
                @can($report['permission'])
                    <a
                        href="{{ route($report['route']) }}"
                        class="group flex flex-col rounded-xl border border-border bg-surface p-5 shadow-sm transition-colors hover:border-primary/50 hover:shadow-md"
                    >
                        <h2 class="text-lg font-semibold text-foreground transition-colors group-hover:text-primary">
                            {{ $report['title'] }}
                        </h2>
                        <p class="mt-2 flex-1 text-sm text-muted-foreground">
                            {{ $report['description'] }}
                        </p>
                        <span class="mt-4 text-sm font-medium text-primary">
                            فتح التقرير &larr;
                        </span>
                    </a>
                @endcan
            @endforeach
        </div>
    </div>
@endsection
