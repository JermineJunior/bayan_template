@php
    $isEdit = filled($management);
@endphp
<!-- one form for both create and edit -->
<form
    method="POST"
    action="{{ $isEdit ? route('managements.update', $management) : route('managements.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="number" class="mb-1 block text-sm font-medium text-foreground">
            الرقم
        </label>
        <input
            id="number"
            name="number"
            type="number"
            value="{{ old('number', $management?->number) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('number')
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
            value="{{ old('name', $management?->name) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ الإدارة' : 'إنشاء الإدارة' }}
        </button>

        <a
            href="{{ route('managements.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
