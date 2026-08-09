@php
    $isEdit = filled($role);
@endphp
<!-- one form for both create and edit , changes with alpine -->
<form
    method="POST"
    action="{{ $isEdit ? route('roles.update', $role) : route('roles.store') }}"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-foreground">
            اسم الدور
        </label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $role?->name) }}"
            required
            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
        >
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <span class="mb-3 block text-sm font-medium text-foreground">
            الصلاحيات
        </span>

        <div class="space-y-4">
            @foreach ($permissionGroups as $area => $permissions)
                <fieldset class="rounded-xl border border-border bg-background p-4">
                    <legend class="px-2 text-sm font-semibold text-foreground">
                        {{ $area }}
                    </legend>

                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($permissions as $permission)
                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm text-muted-foreground hover:bg-muted">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission }}"
                                    @checked(in_array($permission, old('permissions', $rolePermissions), true))
                                    class="size-4 rounded border-border text-primary focus:ring-primary"
                                >
                                <code class="text-xs">{{ $permission }}</code>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>

        @error('permissions')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ الدور' : 'إنشاء الدور' }}
        </button>

        <a
            href="{{ route('roles.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
