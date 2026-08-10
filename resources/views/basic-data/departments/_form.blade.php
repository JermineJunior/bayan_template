@php
    $isEdit = filled($department);
@endphp
<!-- one form for both create and edit -->
<form
    method="POST"
    action="{{ $isEdit ? route('departments.update', $department) : route('departments.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-foreground">
            الاسم
        </label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $department?->name) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('name')
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
            <option value="active" @selected(old('status', $department?->status) === 'active')>نشط</option>
            <option value="inactive" @selected(old('status', $department?->status) === 'inactive')>غير نشط</option>
        </select>
        @error('status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="management_id" class="mb-1 block text-sm font-medium text-foreground">
            الإدارة
        </label>
        <select
            id="management_id"
            name="management_id"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="" selected disabled>اختر الإدارة</option>
            @foreach ($managements as $management)
                <option
                    value="{{ $management->id }}"
                    @selected(old('management_id', $department?->management_id) == $management->id)
                >
                    {{ $management->name }}
                </option>
            @endforeach
        </select>
        @error('management_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ القسم' : 'إنشاء القسم' }}
        </button>

        <a
            href="{{ route('departments.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
