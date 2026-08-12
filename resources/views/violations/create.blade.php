@extends('layouts.app')

@section('title', 'تسجيل مخالفة — '.$driver->full_name)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('drivers.show', $driver) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى السائق
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل مخالفة
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">السائق</p>
                        <p class="text-lg font-semibold text-foreground">
                            {{ $driver->full_name }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            الرقم الوطني: {{ $driver->national_id }}
                        </p>
                    </div>
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('drivers.violations.store', $driver) }}"
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                @csrf

                <div>
                    <label for="violation_date" class="mb-1 block text-sm font-medium text-foreground">
                        تاريخ المخالفة
                    </label>
                    <input
                        id="violation_date"
                        name="violation_date"
                        type="date"
                        value="{{ old('violation_date') }}"
                        required
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('violation_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ticket_number" class="mb-1 block text-sm font-medium text-foreground">
                        رقم التذكرة
                    </label>
                    <input
                        id="ticket_number"
                        name="ticket_number"
                        type="text"
                        value="{{ old('ticket_number') }}"
                        maxlength="255"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('ticket_number')
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
                        required
                        maxlength="255"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="amount" class="mb-1 block text-sm font-medium text-foreground">
                        المبلغ
                    </label>
                    <input
                        id="amount"
                        name="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('amount') }}"
                        placeholder="0.00"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل المخالفة
                    </button>

                    <a
                        href="{{ route('drivers.show', $driver) }}"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
