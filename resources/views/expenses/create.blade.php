@extends('layouts.app')

@section('title', 'إضافة مصروف')

@section('content')
    @php
        $expenseTypeLabels = [
            'spare_parts' => 'قطع غيار',
            'other' => 'أخرى',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('expenses.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المصروفات
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة مصروف
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('expenses.store') }}"
            class="max-w-3xl space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
        >
            @csrf

            <div>
                <label for="vehicle_id" class="mb-1 block text-sm font-medium text-foreground">
                    المركبة
                </label>
                <select
                    id="vehicle_id"
                    name="vehicle_id"
                    required
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                    <option value="">اختر المركبة</option>
                    @foreach ($vehicles as $vehicle)
                        <option
                            value="{{ $vehicle->id }}"
                            @selected(old('vehicle_id', $selectedVehicle?->id) == $vehicle->id)
                        >
                            {{ $vehicle->internal_number }} — {{ $vehicle->plate_number }}
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="expense_type" class="mb-1 block text-sm font-medium text-foreground">
                        نوع المصروف
                    </label>
                    <select
                        id="expense_type"
                        name="expense_type"
                        required
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                        <option value="">اختر النوع</option>
                        @foreach ($expenseTypes as $type)
                            <option
                                value="{{ $type }}"
                                @selected(old('expense_type') === $type)
                            >
                                {{ $expenseTypeLabels[$type] ?? $type }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-muted-foreground">
                        يتم تسجيل الوقود والزيوت والفلاتر والصيانة تلقائيًا من سجلاتها.
                    </p>
                    @error('expense_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="expense_date" class="mb-1 block text-sm font-medium text-foreground">
                        تاريخ المصروف
                    </label>
                    <input
                        id="expense_date"
                        name="expense_date"
                        type="date"
                        value="{{ old('expense_date', now()->toDateString()) }}"
                        required
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('expense_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="amount" class="mb-1 block text-sm font-medium text-foreground">
                    المبلغ
                </label>
                <input
                    id="amount"
                    name="amount"
                    type="text"
                    inputmode="decimal"
                    value="{{ old('amount') }}"
                    required
                    placeholder="0.00"
                    class="money-input w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                @error('amount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-foreground">
                    الوصف
                </label>
                <input
                    id="description"
                    name="description"
                    type="text"
                    value="{{ old('description') }}"
                    maxlength="255"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    حفظ المصروف
                </button>

                <a
                    href="{{ route('expenses.index') }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection
