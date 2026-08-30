@extends('layouts.app')

@section('title', 'صرف قطعة غيار — '.$sparePart->name)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('spare-parts.show', $sparePart) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى القطعة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل صرف قطعة (أمر صيانة)
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            <x-form-section title="بيانات القطعة">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">القطعة</p>
                        <p class="text-lg font-semibold text-foreground">
                            {{ $sparePart->name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            رقم القطعة: {{ $sparePart->part_number }}
                        </p>
                    </div>
                    <div class="text-end">
                        <p class="text-sm text-muted-foreground">المتوفر بالمخزون</p>
                        <p class="text-lg font-semibold text-foreground">
                            {{ number_format($sparePart->quantity_on_hand, 2) }}
                        </p>
                    </div>
                </div>
            </x-form-section>

            <form
                method="POST"
                action="{{ route('spare-parts.issue.store', $sparePart) }}"
                class="space-y-6"
            >
                @csrf

                <x-form-section title="بيانات الصرف">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="mb-1 block text-sm font-medium text-foreground">
                                الكمية المطلوبة
                            </label>
                            <input
                                id="quantity"
                                name="quantity"
                                type="number"
                                step="0.01"
                                min="0.01"
                                value="{{ old('quantity') }}"
                                required
                                placeholder="0.00"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('quantity')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="maintenance_order_id" class="mb-1 block text-sm font-medium text-foreground">
                                أمر الصيانة
                            </label>
                            <select
                                id="maintenance_order_id"
                                name="maintenance_order_id"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">اختر أمر الصيانة</option>
                                @foreach ($maintenanceOrders as $maintenance)
                                    <option
                                        value="{{ $maintenance->id }}"
                                        @selected(old('maintenance_order_id') == $maintenance->id)
                                    >
                                        {{ $maintenance->maintenance_number }}
                                        @if ($maintenance->vehicle)
                                            ({{ $maintenance->vehicle->internal_number }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('maintenance_order_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="notes" class="mb-1 block text-sm font-medium text-foreground">
                                ملاحظات
                            </label>
                            <input
                                id="notes"
                                name="notes"
                                type="text"
                                value="{{ old('notes') }}"
                                maxlength="255"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('notes')
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
                        تسجيل الصرف
                    </button>

                    <a
                        href="{{ route('spare-parts.show', $sparePart) }}"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
