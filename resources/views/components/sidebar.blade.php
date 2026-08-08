<aside
    x-data="sidebar"
    aria-label="القائمة الجانبية"
    class="sticky top-0 flex h-screen shrink-0 flex-col border-e border-border bg-surface transition-[width] duration-200"
    :class="collapsed ? 'w-16' : 'w-64'"
>
    {{-- Brand --}}
    <a
        href="{{ route('home') }}"
        class="flex h-16 shrink-0 items-center gap-2 border-b border-border px-4"
        :class="collapsed ? 'justify-center' : 'justify-start'"
    >
        @if ($logoUrl)
            <img
                src="{{ $logoUrl }}"
                alt="{{ $appName }}"
                class="size-8 shrink-0 rounded-full border border-border object-contain bg-background"
            >
        @endif
        <span x-show="!collapsed" x-cloak class="truncate text-lg font-semibold text-foreground">
            {{ $appName }}
        </span>
    </a>

    {{-- Navigation: each item declares the permission it requires and is
         removed from the DOM entirely when the user lacks it. --}}
    <nav class="flex-1 space-y-1 overflow-y-auto p-3">
        <x-sidebar-link
            href="{{ route('home') }}"
            :active="request()->routeIs('home')"
            label="لوحة التحكم"
            icon="dashboard"
        />

        @can('users.view')
            <x-sidebar-link
                href="{{ route('admin.users.index') }}"
                :active="request()->routeIs('admin.users.*')"
                label="المستخدمون"
                icon="users"
            />
        @endcan

        @can('roles.view')
            <x-sidebar-link
                href="{{ route('admin.roles.index') }}"
                :active="request()->routeIs('admin.roles.*')"
                label="الأدوار"
                icon="roles"
            />
        @endcan

        @can('settings.edit')
            <x-sidebar-link
                href="{{ route('admin.settings.edit') }}"
                :active="request()->routeIs('admin.settings.*')"
                label="الإعدادات"
                icon="settings"
            />
        @endcan
    </nav>

    {{-- Collapse toggle --}}
    <button
        type="button"
        @click="toggle"
        :aria-label="collapsed ? 'توسيع القائمة الجانبية' : 'طيّ القائمة الجانبية'"
        class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        :class="collapsed ? 'mx-3 mb-3 justify-center' : 'mx-3 mb-3 justify-start'"
    >
        <svg x-show="!collapsed" class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m9 18 6-6-6-6"></path>
        </svg>
        <svg x-show="collapsed" x-cloak class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m15 18-6-6 6-6"></path>
        </svg>
        <span x-show="!collapsed" x-cloak>طيّ القائمة</span>
    </button>
</aside>
