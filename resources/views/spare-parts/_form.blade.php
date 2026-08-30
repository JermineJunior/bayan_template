@php
    $isEdit = filled($sparePart);
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('spare-parts.update', $sparePart) : route('spare-parts.store') }}"
    class="max-w-3xl space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="part_number" class="mb-1 block text-sm font-medium text-foreground">
                رقم القطعة
            </label>
            <input
                id="part_number"
                name="part_number"
                type="text"
                value="{{ old('part_number', $sparePart?->part_number) }}"
                @if (!$isEdit) disabled placeholder="يُولَّد تلقائيًا" @endif
                maxlength="50"
                class="w-full rounded-md border border-border bg-muted/50 px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary disabled:cursor-not-allowed disabled:opacity-60"
            >
            @if (!$isEdit)
                <p class="mt-1 text-xs text-muted-foreground">
                    يُولَّد رقم القطعة تلقائيًا عند الحفظ (SP-00001 …).
                </p>
            @endif
            @error('part_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="name" class="mb-1 block text-sm font-medium text-foreground">
                الاسم
            </label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $sparePart?->name) }}"
                required
                maxlength="150"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="mb-1 block text-sm font-medium text-foreground">
                التصنيف
            </label>
            <input
                id="category"
                name="category"
                type="text"
                value="{{ old('category', $sparePart?->category) }}"
                maxlength="100"
                placeholder="مثال: إطارات، شمعات، ..."
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="default_supplier_id" class="mb-1 block text-sm font-medium text-foreground">
                المورد الافتراضي
            </label>
            <select
                id="default_supplier_id"
                name="default_supplier_id"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">— بدون مورد —</option>
                @foreach ($suppliers as $supplier)
                    <option
                        value="{{ $supplier->id }}"
                        @selected(old('default_supplier_id', $sparePart?->default_supplier_id) == $supplier->id)
                    >
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
            @error('default_supplier_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="purchase_price" class="mb-1 block text-sm font-medium text-foreground">
                سعر الشراء
            </label>
            <input
                id="purchase_price"
                name="purchase_price"
                type="number"
                step="0.01"
                min="0"
                value="{{ old('purchase_price', $sparePart?->purchase_price) }}"
                placeholder="0.00"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('purchase_price')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="minimum_quantity" class="mb-1 block text-sm font-medium text-foreground">
                الحد الأدنى للمخزون
            </label>
            <input
                id="minimum_quantity"
                name="minimum_quantity"
                type="number"
                step="0.01"
                min="0"
                value="{{ old('minimum_quantity', $sparePart?->minimum_quantity ?? 0) }}"
                required
                placeholder="0.00"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('minimum_quantity')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ القطعة' : 'إنشاء القطعة' }}
        </button>

        <a
            href="{{ $isEdit ? route('spare-parts.show', $sparePart) : route('spare-parts.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
