@extends('layouts.app')

@section('title', 'تعبئة وقود — '.$vehicle->internal_number)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل عملية تعبئة
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">المركبة</p>
                        <p class="text-lg font-semibold text-foreground">
                            {{ $vehicle->internal_number }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            رقم اللوحة: {{ $vehicle->plate_number }}
                        </p>
                    </div>
                    @if ($lastFuelLog)
                        <div class="text-end">
                            <p class="text-sm text-muted-foreground">آخر تعبئة مسجلة</p>
                            <p class="text-sm font-medium text-foreground">
                                {{ number_format((float) $lastFuelLog->odometer_reading, 0) }} كم
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ $lastFuelLog->filled_at?->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    @endif
                    <div class="flex items-center justify-end gap-2">
                        <div class="text-end">
                            <p class="text-sm text-muted-foreground">العداد الحالي</p>
                            <p class="text-sm font-medium text-foreground">
                                {{ $vehicle->current_odometer !== null ? number_format((float) $vehicle->current_odometer, 0).' كم' : '—' }}
                            </p>
                        </div>
                        @if ($vehicle->current_odometer !== null)
                            <x-copy-odometer :value="$vehicle->current_odometer" />
                        @endif
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('vehicles.fuel-logs.store', $vehicle) }}"
                class="space-y-6"
                x-data="{
                    liters: {{ (float) old('liters', 0) }},
                    price: '{{ old('price_per_liter') }}',
                    discount: '{{ old('discount') }}',
                    formatMoney(value) {
                        if (value === null || value === undefined || isNaN(value)) return '0.00';
                        return Number(value).toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2,
                        });
                    },
                    get total() {
                        const l = parseFloat(this.liters) || 0;
                        const p = parseFloat(String(this.price).replace(/[^0-9.-]+/g, '')) || 0;
                        const d = parseFloat(String(this.discount).replace(/[^0-9.-]+/g, '')) || 0;
                        const t = l * p - d;
                        return t > 0 ? t : 0;
                    },
                    get formattedTotal() {
                        return this.formatMoney(this.total);
                    }
                }"
            >
                @csrf

                <x-form-section title="بيانات التعبئة">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="filled_at" class="mb-1 block text-sm font-medium text-foreground">
                                تاريخ التعبئة
                            </label>
                            <input
                                id="filled_at"
                                name="filled_at"
                                type="datetime-local"
                                value="{{ old('filled_at') }}"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('filled_at')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="fuel_type" class="mb-1 block text-sm font-medium text-foreground">
                                نوع الوقود
                            </label>
                            <select
                                id="fuel_type"
                                name="fuel_type"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">اختر النوع</option>
                                <option value="gasoline" @selected(old('fuel_type') === 'gasoline')>بنزين</option>
                                <option value="diesel" @selected(old('fuel_type') === 'diesel')>جازولين</option>
                            </select>
                            @error('fuel_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="liters" class="mb-1 block text-sm font-medium text-foreground">
                                كمية الوقود (لتر)
                            </label>
                            <input
                                id="liters"
                                name="liters"
                                type="number"
                                step="0.01"
                                min="0.01"
                                value="{{ old('liters') }}"
                                x-model.number="liters"
                                required
                                placeholder="0.00"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('liters')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="price_per_liter" class="mb-1 block text-sm font-medium text-foreground">
                                سعر اللتر
                            </label>
                            <input
                                id="price_per_liter"
                                name="price_per_liter"
                                type="text"
                                inputmode="decimal"
                                value="{{ old('price_per_liter') }}"
                                x-model="price"
                                x-mask:function="$money"
                                placeholder="0.000"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('price_per_liter')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="discount" class="mb-1 block text-sm font-medium text-foreground">
                                الخصم
                            </label>
                            <input
                                id="discount"
                                name="discount"
                                type="text"
                                inputmode="decimal"
                                value="{{ old('discount') }}"
                                x-model="discount"
                                x-mask:function="$money"
                                placeholder="0.00"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('discount')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="total_value" class="mb-1 block text-sm font-medium text-foreground">
                                القيمة الإجمالية
                            </label>
                            <input
                                id="total_value"
                                type="text"
                                inputmode="decimal"
                                :value="formattedTotal"
                                x-mask:function="$money"
                                readonly
                                placeholder="0.00"
                                class="w-full cursor-not-allowed rounded-md border border-border bg-muted px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground"
                            >
                            <p class="mt-1 text-xs text-muted-foreground">
                                تُحسب تلقائيًا: اللترات × سعر اللتر − الخصم
                            </p>
                        </div>
                    </div>
                </x-form-section>

                <x-form-section title="بيانات العداد والسائق">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="driver_id" class="mb-1 block text-sm font-medium text-foreground">
                                السائق
                            </label>
                            <select
                                id="driver_id"
                                name="driver_id"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">بدون سائق</option>
                                @foreach ($drivers as $driver)
                                    <option
                                        value="{{ $driver->id }}"
                                        @selected(old('driver_id') == $driver->id)
                                    >
                                        {{ $driver->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="odometer_reading" class="mb-1 block text-sm font-medium text-foreground">
                                قراءة العداد
                            </label>
                            <input
                                id="odometer_reading"
                                name="odometer_reading"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('odometer_reading') }}"
                                required
                                placeholder="0.00"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('odometer_reading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="station" class="mb-1 block text-sm font-medium text-foreground">
                                المحطة
                            </label>
                            <input
                                id="station"
                                name="station"
                                type="text"
                                value="{{ old('station') }}"
                                maxlength="255"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('station')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="invoice_number" class="mb-1 block text-sm font-medium text-foreground">
                                رقم الفاتورة
                            </label>
                            <input
                                id="invoice_number"
                                name="invoice_number"
                                type="text"
                                value="{{ old('invoice_number') }}"
                                maxlength="255"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('invoice_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </x-form-section>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل التعبئة
                    </button>

                    <a
                        href="{{ route('vehicles.show', $vehicle) }}"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
