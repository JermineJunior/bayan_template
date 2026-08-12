@php
    $isEdit = filled($oil);
@endphp
<!-- one form for both create and edit -->
<form
    method="POST"
    action="{{ $isEdit ? route('oils.update', $oil) : route('oils.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="oil_name" class="mb-1 block text-sm font-medium text-foreground">
            اسم الزيت
        </label>
        <input
            id="oil_name"
            name="oil_name"
            type="text"
            value="{{ old('oil_name', $oil?->oil_name) }}"
            required
            maxlength="150"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('oil_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="oil_code" class="mb-1 block text-sm font-medium text-foreground">
            كود الزيت
        </label>
        <input
            id="oil_code"
            name="oil_code"
            type="text"
            value="{{ old('oil_code', $oil?->oil_code) }}"
            required
            maxlength="50"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('oil_code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="oil_type" class="mb-1 block text-sm font-medium text-foreground">
            نوع الزيت
        </label>
        <select
            id="oil_type"
            name="oil_type"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="" selected disabled>اختر النوع</option>
            @foreach (config('oil_types') as $value => $label)
                <option value="{{ $value }}" @selected(old('oil_type', $oil?->oil_type) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('oil_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
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
            value="{{ old('oil_life', $oil?->oil_life) }}"
            required
            placeholder="10000"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        <p class="mt-1 text-xs text-muted-foreground">
            يُستخدم لحساب كيلومتر التغيير القادم عند تسجيل تغيير الزيت.
        </p>
        @error('oil_life')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ الزيت' : 'إضافة الزيت' }}
        </button>

        <a
            href="{{ route('oils.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
