@extends('layouts.app')

@section('title', 'تغيير فلتر — '.$vehicle->internal_number)

@section('content')
    <div x-data="filterChangeForm('{{ route('filters.store') }}')" class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل تغيير فلتر
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
                action="{{ route('vehicles.filter-changes.store', $vehicle) }}"
                class="space-y-6"
            >
                @csrf

                <x-form-section title="اختيار الفلتر">
                    <div>
                        <label for="filter_id" class="mb-1 block text-sm font-medium text-foreground">
                            الفلتر
                        </label>
                        <div class="flex items-start gap-2">
                            <select
                                id="filter_id"
                                name="filter_id"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">اختر الفلتر</option>
                                @foreach (config('filter_types') as $typeValue => $typeLabel)
                                    @if ($filters->where('filter_type', $typeValue)->isNotEmpty())
                                        <optgroup label="{{ $typeLabel }}">
                                            @foreach ($filters->where('filter_type', $typeValue) as $filter)
                                                <option
                                                    value="{{ $filter->id }}"
                                                    @selected(old('filter_id') == $filter->id)
                                                >
                                                    {{ $filter->filter_name }} ({{ $filter->filter_code }})
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                @endforeach
                            </select>

                            @can('filters.create')
                                <button
                                    type="button"
                                    @click="openFilterModal()"
                                    class="shrink-0 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                                >
                                    + إضافة فلتر
                                </button>
                            @endcan
                        </div>
                        @error('filter_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </x-form-section>

                <x-form-section title="بيانات التغيير">
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
                                كيلومتر التغيير القادم = هذه القراءة + العمر الافتراضي للفلتر.
                            </p>
                            @error('odometer_when_change')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="cost" class="mb-1 block text-sm font-medium text-foreground">
                                التكلفة (المبلغ المدفوع فعليًا)
                            </label>
                            <input
                                id="cost"
                                name="cost"
                                type="text"
                                inputmode="decimal"
                                min="0"
                                value="{{ old('cost') }}"
                                required
                                placeholder="0.00"
                                class="money-input w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p class="mt-1 text-xs text-muted-foreground">
                                المبلغ الفعلي المدفوع لهذه التغييرة — ليس سعر الكتالوج الافتراضي.
                            </p>
                            @error('cost')
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

        {{-- Quick-add filter modal: creates a catalog filter via AJAX and appends it
             to the #filter_id select above, so the user never loses the form. --}}
        @can('filters.create')
            <div
                x-show="filterModal"
                x-cloak
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                role="dialog"
                aria-modal="true"
                aria-label="إضافة فلتر جديد"
            >
                <div class="absolute inset-0 bg-black/40" @click="closeFilterModal()"></div>

                <div class="relative w-full max-w-md rounded-xl border border-border bg-surface p-6 shadow-lg">
                    <h3 class="mb-4 text-lg font-semibold text-foreground">
                        إضافة فلتر جديد
                    </h3>

                    <form @submit.prevent="quickAddFilter()" class="space-y-4">
                        <div>
                            <label for="filter_name" class="mb-1 block text-sm font-medium text-foreground">
                                اسم الفلتر
                            </label>
                            <input
                                id="filter_name"
                                name="filter_name"
                                type="text"
                                x-model="filterName"
                                required
                                maxlength="150"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.filter_name" x-text="modalErrors.filter_name" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="filter_code" class="mb-1 block text-sm font-medium text-foreground">
                                كود الفلتر
                            </label>
                            <input
                                id="filter_code"
                                name="filter_code"
                                type="text"
                                x-model="filterCode"
                                required
                                maxlength="50"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.filter_code" x-text="modalErrors.filter_code" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="filter_type" class="mb-1 block text-sm font-medium text-foreground">
                                نوع الفلتر
                            </label>
                            <select
                                id="filter_type"
                                name="filter_type"
                                x-model="filterType"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                @foreach (config('filter_types') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p x-show="modalErrors.filter_type" x-text="modalErrors.filter_type" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div>
                            <label for="filter_life" class="mb-1 block text-sm font-medium text-foreground">
                                العمر الافتراضي (كم)
                            </label>
                            <input
                                id="filter_life"
                                name="filter_life"
                                type="number"
                                step="0.01"
                                min="0.01"
                                x-model="filterLife"
                                required
                                placeholder="10000"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            <p x-show="modalErrors.filter_life" x-text="modalErrors.filter_life" class="mt-1 text-sm text-red-600"></p>
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                type="submit"
                                :disabled="saving"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                            >
                                إضافة الفلتر
                            </button>

                            <button
                                type="button"
                                @click="closeFilterModal()"
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
