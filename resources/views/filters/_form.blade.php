@php
    $isEdit = filled($filter);
@endphp
<!-- one form for both create and edit -->
<form
    method="POST"
    action="{{ $isEdit ? route('filters.update', $filter) : route('filters.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="filter_name" class="mb-1 block text-sm font-medium text-foreground">
            اسم الفلتر
        </label>
        <input
            id="filter_name"
            name="filter_name"
            type="text"
            value="{{ old('filter_name', $filter?->filter_name) }}"
            required
            maxlength="150"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('filter_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="filter_code" class="mb-1 block text-sm font-medium text-foreground">
            كود الفلتر
        </label>
        <input
            id="filter_code"
            name="filter_code"
            type="text"
            value="{{ old('filter_code', $filter?->filter_code) }}"
            required
            maxlength="50"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('filter_code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="filter_type" class="mb-1 block text-sm font-medium text-foreground">
            نوع الفلتر
        </label>
        <select
            id="filter_type"
            name="filter_type"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="" selected disabled>اختر النوع</option>
            @foreach (config('filter_types') as $value => $label)
                <option value="{{ $value }}" @selected(old('filter_type', $filter?->filter_type) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('filter_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
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
            value="{{ old('filter_life', $filter?->filter_life) }}"
            required
            placeholder="10000"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        <p class="mt-1 text-xs text-muted-foreground">
            يُستخدم لحساب كيلومتر التغيير القادم عند تسجيل تغيير الفلتر.
        </p>
        @error('filter_life')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ الفلتر' : 'إضافة الفلتر' }}
        </button>

        <a
            href="{{ route('filters.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
