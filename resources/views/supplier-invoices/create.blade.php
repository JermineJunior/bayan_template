@extends('layouts.app')

@section('title', 'فاتورة جديدة')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('suppliers.show', $supplier) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى {{ $supplier->name }}
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة فاتورة — {{ $supplier->name }}
            </h1>
        </div>

        <form
            method="POST"
            action="{{ route('suppliers.invoices.store', $supplier) }}"
            class="max-w-3xl space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
        >
            @csrf

            <div>
                <label for="invoice_number" class="mb-1 block text-sm font-medium text-foreground">
                    رقم الفاتورة
                </label>
                <input
                    id="invoice_number"
                    name="invoice_number"
                    type="text"
                    value="{{ old('invoice_number') }}"
                    required
                    maxlength="50"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                @error('invoice_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
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
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="invoice_date" class="mb-1 block text-sm font-medium text-foreground">
                        تاريخ الفاتورة
                    </label>
                    <input
                        id="invoice_date"
                        name="invoice_date"
                        type="date"
                        value="{{ old('invoice_date', now()->toDateString()) }}"
                        required
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('invoice_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    حفظ الفاتورة
                </button>

                <a
                    href="{{ route('suppliers.show', $supplier) }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection
