@php
    $isEdit = filled($maintenance);
@endphp
<!-- one form for both create and edit -->
<div id="maintenance-form">
    <form method="POST" action="{{ $isEdit ? route('maintenance.update', $maintenance) : route('maintenance.store') }}"
        class="max-w-3xl space-y-6">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div>
            <label for="maintenance_number" class="mb-1 block text-sm font-medium text-foreground">
                امر الصيانة
            </label>
            <input id="maintenance_number" name="maintenance_number" type="text"
                value="{{ $isEdit ? $maintenance->maintenance_number : \App\Models\Maintenance::generateMaintenanceNumber() }}"
             readonly
                class="w-full rounded-md border border-border bg-gray-200 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            @error('maintenance_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="vehicle_id" class="mb-1 block text-sm font-medium text-foreground">
                    المركبة
                </label>

                <select id="vehicle_id" name="vehicle_id"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="">بدون مركبة</option>

                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}" @selected(old('vehicle_id', $maintenance?->vehicle_id) == $vehicle->id)>
                            {{ $vehicle->plate_number }}
                        </option>
                    @endforeach
                </select>

                @error('vehicle_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="odometer_reading" class="mb-1 block text-sm font-medium text-foreground">
                    عداد القراءة
                </label>
                <input id="odometer_reading" name="odometer_reading" type="text"
                    value="{{ old('odometer_reading', $maintenance?->odometer_reading) }}" readonly
                    class="w-full rounded-md border border-border bg-gray-200 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('odometer_reading')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="start_date" class="mb-1 block text-sm font-medium text-foreground">
                    تاريخ البداية
                </label>
                <input id="start_date" name="start_date" type="date"
                    value="{{ old('start_date', $maintenance?->start_date->format('Y-m-d')) }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="end_date" class="mb-1 block text-sm font-medium text-foreground">
                    تاريخ النهاية
                </label>
                <input id="end_date" name="end_date" type="date"
                    value="{{ old('end_date', $maintenance?->end_date->format('Y-m-d')) }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('end_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="reason" class="mb-1 block text-sm font-medium text-foreground">
                سبب الصيانة
            </label>
            <textarea id="reason" name="reason" cols="5" rows="4"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
            {{ old('reason', $maintenance?->reason) }}
        
        </textarea>
            @error('reason')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="workshop" class="mb-1 block text-sm font-medium text-foreground">
                    الورشة
                </label>
                <input id="workshop" name="workshop" type="text"
                    value="{{ old('workshop', $maintenance?->workshop) }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('workshop')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="technical" class="mb-1 block text-sm font-medium text-foreground">
                    الفني
                </label>
                <input id="technical" name="technical" type="text"
                    value="{{ old('technical', $maintenance?->technical) }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('technical')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-3">
            <div>
                <label for="labor_cost" class="mb-1 block text-sm font-medium text-foreground">
                    تكلفة الفني
                </label>
                <input id="labor_cost" name="labor_cost" type="text" value="{{ old('labor_cost', $maintenance?->labor_cost) }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('labor_cost')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="spare_cost" class="mb-1 block text-sm font-medium text-foreground">
                    تكلفة الاسبير
                </label>
                <input id="spare_cost" name="spare_cost" type="text"
                    value="{{ old('spare_cost', $maintenance?->spare_cost) }}" readonly
                    class="w-full rounded-md border border-border bg-gray-200 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('spare_cost')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="total_cost" class="mb-1 block text-sm font-medium text-foreground">
                    التكلفة الكلية
                </label>
                <input id="total_cost" name="total_cost" type="text"
                    value="{{ old('total_cost', $maintenance?->total_cost) }}" readonly
                    class="w-full rounded-md border border-border bg-gray-200 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                @error('total_cost')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="labor" class="mb-1 block text-sm font-medium text-foreground">
                    نوع الصيانة
                </label>

                <select id="type" name="type"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="periodic" @selected(old('type', $maintenance?->type == 'periodic'))>صيانة دورية</option>
                    <option value="preventive" @selected(old('type', $maintenance?->type == 'preventive'))>صيانة وقائية</option>
                    <option value="emergency" @selected(old('type', $maintenance?->type == 'emergency'))>صيانة طارئة</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="labor" class="mb-1 block text-sm font-medium text-foreground">
                    الحالة
                </label>

                <select id="status" name="status"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                    <option value="pending" @selected(old('status', $maintenance?->status == 'pending'))>قيد الانتظار</option>
                    <option value="in_progress" @selected(old('status', $maintenance?->status == 'in_progress'))>في الصيانة</option>
                    <option value="completed" @selected(old('status', $maintenance?->status == 'completed'))>اكتملت</option>
                    <option value="cancelled" @selected(old('status', $maintenance?->status == 'cancelled'))> ملغية</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="note" class="mb-1 block text-sm font-medium text-foreground">
                ملاحظات
            </label>
            <textarea id="note" name="note" cols="5" rows="4"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary">
                {{ old('note', $maintenance?->note) }}
            </textarea>
            @error('note')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                {{ $isEdit ? 'حفظ امر الصيانة' : 'إنشاء امر الصيانة' }}
            </button>

            <a href="{{ route('drivers.index') }}"
                class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                إلغاء
            </a>
        </div>
    </form>
</div>
