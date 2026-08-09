<div class="mb-6 flex gap-2 rounded-xl border border-border bg-surface p-1 shadow-sm">
    <a
        href="{{ route('managements.index') }}"
        class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('managements.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
    >
        الإدارات
    </a>
    <a
        href="{{ route('departments.index') }}"
        class="flex-1 rounded-lg px-4 py-2 text-center text-sm font-medium transition-colors {{ request()->routeIs('departments.*') ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}"
    >
        الأقسام
    </a>
</div>

@if (session('success'))
    <div class="mb-6 rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif
