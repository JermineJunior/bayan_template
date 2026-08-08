@php
    $isEdit = filled($user);
    $selectedRoleId = old('role_id', $user?->roles->first()?->id);
@endphp

<form
    method="POST"
    action="{{ $isEdit ? route('admin.users.update', $user) : route('admin.users.store') }}"
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
            value="{{ old('name', $user?->name) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="username" class="mb-1 block text-sm font-medium text-foreground">
            اسم المستخدم
        </label>
        <input
            id="username"
            name="username"
            type="text"
            value="{{ old('username', $user?->username) }}"
            required
            dir="rtl"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('username')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="mb-1 block text-sm font-medium text-foreground">
            البريد الإلكتروني <span class="text-muted-foreground">(اختياري)</span>
        </label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $user?->email) }}"
            dir="ltr"
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @unless ($isEdit)
        <div>
            <label for="password" class="mb-1 block text-sm font-medium text-foreground">
                كلمة المرور <span class="text-muted-foreground">(اختياري)</span>
            </label>
            <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                autocomplete="new-password"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            <p class="mt-1 text-xs text-muted-foreground">
                اتركها فارغة لتوليد كلمة مرور عشوائية تظهر لك مرة واحدة بعد الحفظ.
            </p>
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endunless

    <div>
        <label for="role_id" class="mb-1 block text-sm font-medium text-foreground">
            الدور
        </label>
        <select
            id="role_id"
            name="role_id"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
            <option value="" disabled @selected($selectedRoleId === null)>اختر دورًا...</option>
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected((int) $selectedRoleId === $role->id)>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        @error('role_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ المستخدم' : 'إنشاء المستخدم' }}
        </button>

        <a
            href="{{ route('admin.users.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
