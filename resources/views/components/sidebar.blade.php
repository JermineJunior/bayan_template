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

        @can('basic-data.view')
            <div x-data="{ open: {{ request()->routeIs('managements.*') || request()->routeIs('departments.*') ? 'true' : 'true' }} }">
                <button
                    type="button"
                    @click="open = !open"
                    :class="collapsed ? 'w-full justify-center' : 'w-full justify-start'"
                    :aria-expanded="open ? 'true' : 'false'"
                    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('managements.*') || request()->routeIs('departments.*') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                >
                    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                        <path d="M10 6h4"></path>
                        <path d="M10 10h4"></path>
                        <path d="M10 14h4"></path>
                        <path d="M10 18h4"></path>
                    </svg>
                    <span x-show="!collapsed" x-cloak class="truncate">البيانات الأساسية</span>
                    <svg
                        x-show="!collapsed"
                        x-cloak
                        class="ms-auto size-4 shrink-0 transition-transform"
                        :class="open ? 'rotate-90' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="m9 18 6-6-6-6"></path>
                    </svg>
                </button>

                <div x-show="open && !collapsed" x-cloak class="mt-1 space-y-1">
                    <a
                        href="{{ route('managements.index') }}"
                        class="flex items-center gap-3 rounded-md ps-10 pe-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('managements.*') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                    >
                        الإدارات
                    </a>
                    <a
                        href="{{ route('departments.index') }}"
                        class="flex items-center gap-3 rounded-md ps-10 pe-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('departments.*') ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
                    >
                        الأقسام
                    </a>
                </div>
            </div>
        @endcan

        @can('users.view')
            <x-sidebar-link
                href="{{ route('users.index') }}"
                :active="request()->routeIs('users.*')"
                label="المستخدمون"
                icon="users"
            />
        @endcan

        @can('roles.view')
            <x-sidebar-link
                href="{{ route('roles.index') }}"
                :active="request()->routeIs('roles.*')"
                label="الأدوار"
                icon="roles"
            />
        @endcan

        @can('settings.edit')
            <x-sidebar-link
                href="{{ route('settings.edit') }}"
                :active="request()->routeIs('settings.*')"
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
