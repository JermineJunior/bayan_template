@extends('layouts.app')

@section('title', 'تغيير زيت — '.$vehicle->internal_number)

@section('content')
    <div x-data="oilChangeForm('{{ route('oils.store') }}')" class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل تغيير زيت
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
                    <div class="text-end">
                        <p class="text-sm text-muted-foreground">العداد الحالي</p>
                        <p class="text-sm font-medium text-foreground">
                            {{ $vehicle->current_odometer !== null ? number_format((float) $vehicle->current_odometer, 0).' كم' : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('vehicles.oil-changes.store', $vehicle) }}"
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                @csrf

                <div>
                    <label for="oil_id" class="mb-1 block text-sm font-medium text-foreground">
                        الزيت
                    </label>
                    <div class="flex items-start gap-2">
                        <select
                            id="oil_id"
                            name="oil_id"
                            required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="">اختر الزيت</option>
                            @foreach (config('oil_types') as $typeValue => $typeLabel)
                                @if ($oils->where('oil_type', $typeValue)->isNotEmpty())
                                    <optgroup label="{{ $typeLabel }}">
                                        @foreach ($oils->where('oil_type', $typeValue) as $oil)
                                            <option
                                                value="{{ $oil->id }}"
                                                @selected(old('oil_id') == $oil->id)
                                            >
                                                {{ $oil->oil_name }} ({{ $oil->oil_code }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>

                        @can('oils.create')
                            <button
                                type="button"
                                @click="openOilModal()"
                                class="shrink-0 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                            >
                                + إضافة زيت
                            </button>
                        @endcan
                    </div>
                    @error('oil_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="last_change" class="mb-1 block text-sm font-medium text-foreground">
                            تاريخ آخر تغيير
                        </label>
                        <input
                            id="last_change"
                            name="last_change"
                            type="date"
                            value="{{ old('last_change') }}"
                            required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('last_change')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="odometer_when_change" class="mb-1 block text-sm font-medium text-foreground">
                            قراءة العداد عند التغيير
                        </label>
                        <input
                            id="odometer_when_change"
                            name="odometer_when_change"
                            type="number"
                            step="0.01"
                            min="0"
                            value="{{ old('odometer_when_change') }}"
                            required
                            placeholder="0.00"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">
                            كيلومتر التغيير القادم = هذه القراءة + العمر الافتراضي للزيت.
                        </p>
                        @error('odometer_when_change')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل التغيير
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

        {{-- Quick-add oil modal: creates a catalog oil via AJAX and appends it
             to the #oil_id select above, so the user never loses the form. --}}
        @can('oils.create')
            <div
                x-show="oilModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-label="إضافة زيت جديد"
            >
                <div class="absolute inset-0 bg-black/40" @click="closeOilModal()"></div>

                <div class="relative w-full max-w-md rounded-xl border border-border bg-surface p-6 shadow-lg">
                    <h3 class="mb-4 text-lg font-semibold text-foreground">
                        إضافة زيت جديد
                    </h3>

                    <form @submit.prevent="quickAddOil()" class="space-y-4">
                        <div>
                            <label for="oil_name" class="mb-1 block text-sm font-medium text-foreground">
                                اسم الزيت
                            </label>
                            <input
                                id="oil_name"
                                name="oil_name"
                                type="text"
                                x-model="oilName"
                                required
                                maxlength="150"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.oil_name" x-text="modalErrors.oil_name" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="oil_code" class="mb-1 block text-sm font-medium text-foreground">
                                كود الزيت
                            </label>
                            <input
                                id="oil_code"
                                name="oil_code"
                                type="text"
                                x-model="oilCode"
                                required
                                maxlength="50"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.oil_code" x-text="modalErrors.oil_code" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="oil_type" class="mb-1 block text-sm font-medium text-foreground">
                                نوع الزيت
                            </label>
                            <select
                                id="oil_type"
                                name="oil_type"
                                x-model="oilType"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                @foreach (config('oil_types') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p x-show="modalErrors.oil_type" x-text="modalErrors.oil_type" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="oil_life" class="mb-1 block text-sm font-medium text-foreground">
                                العمر الافتراضي (كم)
                            </label>
                            <input
                                id="oil_life"
                                name="oil_life"
                                type="number"
                                step="0.01"
                                min="0.01"
                                x-model="oilLife"
                                required
                                placeholder="10000"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.oil_life" x-text="modalErrors.oil_life" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                :disabled="saving"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                إضافة الزيت
                            </button>

                            <button
                                type="button"
                                @click="closeOilModal()"
                                class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                            >
                                إلغاء
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcan
    </div>
@endsection
