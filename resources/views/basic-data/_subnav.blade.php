<div class="mb-6 flex gap-2 rounded-xl border border-border bg-surface p-1 shadow-sm">
    @can('managements.view')
        <a
            href="{{ route('managements.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('managements.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            الإدارات
        </a>
    @endcan
    @can('departments.view')
        <a
            href="{{ route('departments.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('departments.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            الأقسام
        </a>
    @endcan
    @can('drivers.view')
        <a
            href="{{ route('drivers.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('drivers.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            السائقون
        </a>
    @endcan
    @can('vehicles.view')
        <a
            href="{{ route('vehicles.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('vehicles.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            المركبات
        </a>
    @endcan
    @can('incidents.view')
        <a
            href="{{ route('incidents.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('incidents.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            الحوادث
        </a>
    @endcan
    @canany(['oils.view', 'filters.view'])
        <a
            href="{{ route('catalog.index') }}"
            class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('catalog.*') || request()->routeIs('oils.*') || request()->routeIs('filters.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
        >
            الزيوت والفلاتر
        </a>
    @endcanany
</div>
