@props(['user'])

<div x-data="{ confirm: false }" class="flex flex-wrap items-center gap-2">
    @if ($user->is_active)
        @can('deactivate', $user)
            <button
                type="button"
                @click="confirm = true"
                class="rounded-md border border-amber-200 px-3 py-1.5 text-xs font-medium text-amber-700 transition-colors hover:bg-amber-50"
            >
                تعطيل
            </button>
        @endcan
    @else
        @can('activate', $user)
            <button
                type="button"
                @click="confirm = true"
                class="rounded-md border border-green-200 px-3 py-1.5 text-xs font-medium text-green-700 transition-colors hover:bg-green-50"
            >
                تفعيل
            </button>
        @endcan
    @endif

    <span
        x-show="confirm"
        x-cloak
        class="flex flex-wrap items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs text-amber-800"
    >
        <span>
            {{ $user->is_active ? 'هل تريد تعطيل' : 'هل تريد تفعيل' }} حساب {{ $user->name }}؟
        </span>

        <form
            method="POST"
            action="{{ $user->is_active ? route('users.deactivate', $user) : route('users.activate', $user) }}"
            class="inline"
        >
            @csrf
            <button
                type="submit"
                class="rounded-md bg-amber-600 px-2 py-1 text-xs font-medium text-white transition-colors hover:bg-amber-700"
            >
                {{ $user->is_active ? 'نعم، تعطيل' : 'نعم، تفعيل' }}
            </button>
        </form>

        <button
            type="button"
            @click="confirm = false"
            class="rounded-md border border-amber-200 px-2 py-1 text-xs font-medium text-amber-800 transition-colors hover:bg-amber-100"
        >
            إلغاء
        </button>
    </span>
</div>
