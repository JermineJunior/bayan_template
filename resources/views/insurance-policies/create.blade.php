@extends('layouts.app')

@section('title', 'تسجيل تأمين — '.$vehicle->internal_number)

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
                تسجيل تأمين
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            @if ($currentPolicy)
                <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-muted-foreground">البوليصة الحالية</p>
                            <p class="text-lg font-semibold text-foreground">
                                {{ $currentPolicy->policy_number }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                شركة التأمين: {{ $currentPolicy->insurance_company }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                تاريخ الانتهاء: {{ $currentPolicy->end_date?->format('Y-m-d') ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('vehicles.insurance-policies.store', $vehicle) }}"
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                @csrf

                <div>
                    <label for="policy_number" class="mb-1 block text-sm font-medium text-foreground">
                        رقم البوليصة
                    </label>
                    <input
                        id="policy_number"
                        name="policy_number"
                        type="text"
                        value="{{ old('policy_number') }}"
                        required
                        maxlength="50"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('policy_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="insurance_company" class="mb-1 block text-sm font-medium text-foreground">
                        شركة التأمين
                    </label>
                    <input
                        id="insurance_company"
                        name="insurance_company"
                        type="text"
                        value="{{ old('insurance_company') }}"
                        required
                        maxlength="150"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('insurance_company')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="start_date" class="mb-1 block text-sm font-medium text-foreground">
                            تاريخ البدء
                        </label>
                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ old('start_date') }}"
                            required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_date" class="mb-1 block text-sm font-medium text-foreground">
                            تاريخ الانتهاء
                        </label>
                        <input
                            id="end_date"
                            name="end_date"
                            type="date"
                            value="{{ old('end_date') }}"
                            required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="value" class="mb-1 block text-sm font-medium text-foreground">
                        قيمة البوليصة
                    </label>
                    <input
                        id="value"
                        name="value"
                        type="text"
                        inputmode="decimal"
                        value="{{ old('value') }}"
                        placeholder="0.00"
                        x-mask:function="$money"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('value')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل التأمين
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
