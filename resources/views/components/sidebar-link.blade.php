@props(['href', 'active' => false, 'label', 'icon'])

@php
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>',
        'users' => '<path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9.5" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>',
        'roles' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="m9 12 2 2 4-4"></path>',
        'settings' => '<path d="M4 21v-7"></path><path d="M4 10V3"></path><path d="M12 21v-9"></path><path d="M12 8V3"></path><path d="M20 21v-5"></path><path d="M20 12V3"></path><path d="M1 14h6"></path><path d="M9 8h6"></path><path d="M17 16h6"></path>',
    ];

    $idle = 'text-muted-foreground hover:bg-muted hover:text-foreground';
    $current = 'bg-primary/10 text-primary';
@endphp

<a
    href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $active ? $current : $idle }}"
    :class="collapsed ? 'justify-center' : 'justify-start'"
>
    <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        {!! $icons[$icon] ?? '' !!}
    </svg>
    <span x-show="!collapsed" x-cloak class="truncate">{{ $label }}</span>
</a>
