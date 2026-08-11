<div x-show="tab === 'info'" x-cloak role="tabpanel">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            @if ($vehicle->image_url)
                <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
                    <img
                        src="{{ $vehicle->image_url }}"
                        alt="{{ $vehicle->internal_number }}"
                        class="h-56 w-full object-cover"
                    >
                </div>
            @endif

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    ملخص
                </h2>

                <dl class="space-y-3 text-sm [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted-foreground">رقم اللوحة</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->plate_number }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted-foreground">الإدارة</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->management?->name ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted-foreground">السائق الحالي</dt>
                        <dd class="font-medium text-foreground">{{ $currentDriver?->full_name ?? 'غير مخصصة' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted-foreground">العداد الحالي</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->current_odometer ?? '—' }} كم</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <dt class="text-muted-foreground">ساعات التشغيل</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->operating_hours ?? '—' }} ساعة</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    البيانات العامة
                </h2>

                <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">الرقم الداخلي</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->internal_number }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">رقم اللوحة</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->plate_number }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">النوع</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->type ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">الفئة</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->category ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">الموديل</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->model ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">سنة الصنع</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->manufacture_year ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">اللون</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->color ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">الحالة</dt>
                        <dd class="font-medium text-foreground">{{ $status[0] }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    التفاصيل الفنية
                </h2>

                <dl class="grid gap-x-8 gap-y-4 text-sm sm:grid-cols-2 [&_dt]:shrink-0 [&_dd]:min-w-0 [&_dd]:break-words [&_dd]:text-end">
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">رقم الهيكل</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->chassis_number ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">رقم المحرك</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->engine_number ?: '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">نوع الوقود</dt>
                        <dd class="font-medium text-foreground">{{ $fuelLabels[$vehicle->fuel_type] ?? '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-4 border-b border-border pb-2">
                        <dt class="text-muted-foreground">سعة المحرك</dt>
                        <dd class="font-medium text-foreground">{{ $vehicle->engine_capacity ?: '—' }}لتر</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
