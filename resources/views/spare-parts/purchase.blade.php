@extends('layouts.app')

@section('title', 'شراء قطعة غيار — '.$sparePart->name)

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
                تسجيل شراء قطعة
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
                action="{{ route('spare-parts.purchase.store', $sparePart) }}"
                class="space-y-6"
            >
                @csrf

                <x-form-section title="بيانات الشراء">
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <label for="quantity" class="mb-1 block text-sm font-medium text-foreground">
                                الكمية
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
                            <label for="supplier_id" class="mb-1 block text-sm font-medium text-foreground">
                                المورد
                            </label>
                            <select
                                id="supplier_id"
                                name="supplier_id"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                @php
                                    $defaultSupplierId = old('supplier_id', $sparePart->default_supplier_id);
                                @endphp
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected($defaultSupplierId == $supplier->id)>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="unit_price" class="mb-1 block text-sm font-medium text-foreground">
                                سعر الوحدة
                            </label>
                            <input
                                id="unit_price"
                                name="unit_price"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('unit_price') }}"
                                placeholder="0.00"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('unit_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
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
                        تسجيل الشراء
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
