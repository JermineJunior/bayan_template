@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                لوحة التحكم
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                مرحبًا {{ auth()->user()->name }}.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($totalVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">إجمالي المركبات</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($activeVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">مركبات نشطة</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($maintenanceVehicles) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">تحت الصيانة</div>
            </div>
            <div class="flex min-h-24 flex-col items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-4 text-center">
                <div class="text-3xl font-semibold text-primary">{{ number_format($licensesExpiringSoon) }}</div>
                <div class="mt-2 text-sm text-muted-foreground">رخص سائقين قريبة الانتهاء</div>
            </div>
        </div>

        <div class="mt-3 grid gap-4 sm:grid-cols-1 lg:grid-cols-2">
            <div dir="rtl" class="min-h-56 flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-6 text-center text-2xl text-muted-foreground">
                قائمة — آخر عمليات التعبئة
            </div>
            <div dir="rtl" class="min-h-56 flex items-center justify-center rounded-lg border-2 border-dashed border-muted-foreground/30 bg-surface p-6 text-center text-2xl text-muted-foreground">
                رسم بياني — استهلاك الوقود الشهري
            </div>
        </div>

    </div>
@endsection
