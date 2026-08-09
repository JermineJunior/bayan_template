@php
    $isEdit = filled($driver);
@endphp
<!-- one form for both create and edit -->
<form
    method="POST"
    action="{{ $isEdit ? route('drivers.update', $driver) : route('drivers.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="full_name" class="mb-1 block text-sm font-medium text-foreground">
            الاسم الكامل
        </label>
        <input
            id="full_name"
            name="full_name"
            type="text"
            value="{{ old('full_name', $driver?->full_name) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('full_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="national_id" class="mb-1 block text-sm font-medium text-foreground">
            الرقم الوطني
        </label>
        <input
            id="national_id"
            name="national_id"
            type="text"
            value="{{ old('national_id', $driver?->national_id) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('national_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="phone_number" class="mb-1 block text-sm font-medium text-foreground">
                رقم الهاتف
            </label>
            <input
                id="phone_number"
                name="phone_number"
                type="text"
                value="{{ old('phone_number', $driver?->phone_number) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('phone_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department_id" class="mb-1 block text-sm font-medium text-foreground">
                القسم
            </label>
            <select
                id="department_id"
                name="department_id"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">بدون قسم</option>
                @foreach ($departments as $department)
                    <option
                        value="{{ $department->id }}"
                        @selected(old('department_id', $driver?->department_id) == $department->id)
                    >
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="hire_date" class="mb-1 block text-sm font-medium text-foreground">
                تاريخ التعيين
            </label>
            <input
                id="hire_date"
                name="hire_date"
                type="date"
                value="{{ old('hire_date', $driver?->hire_date?->format('Y-m-d')) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('hire_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="license_expiry_date" class="mb-1 block text-sm font-medium text-foreground">
                تاريخ انتهاء الرخصة
            </label>
            <input
                id="license_expiry_date"
                name="license_expiry_date"
                type="date"
                value="{{ old('license_expiry_date', $driver?->license_expiry_date?->format('Y-m-d')) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('license_expiry_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="license_type" class="mb-1 block text-sm font-medium text-foreground">
                نوع الرخصة
            </label>
            <select
                id="license_type"
                name="license_type"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">اختر النوع</option>
                <option value="general" @selected(old('license_type', $driver?->license_type) === 'general')>عامة</option>
                <option value="private" @selected(old('license_type', $driver?->license_type) === 'private')>خاصة</option>
                <option value="other" @selected(old('license_type', $driver?->license_type) === 'other')>أخرى</option>
            </select>
            @error('license_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-foreground">
                الحالة
            </label>
            <select
                id="status"
                name="status"
                required
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="active" @selected(old('status', $driver?->status) === 'active')>نشط</option>
                <option value="inactive" @selected(old('status', $driver?->status) === 'inactive')>غير نشط</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ السائق' : 'إنشاء السائق' }}
        </button>

        <a
            href="{{ route('drivers.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
