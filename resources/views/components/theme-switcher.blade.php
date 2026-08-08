@props(['variant' => 'dropdown'])

<div
    x-data="themeSwitcher"
    data-themes="{{ json_encode($themes) }}"
    data-labels="{{ json_encode($themeLabels) }}"
    data-cookie-name="{{ config('themes.cookie') }}"
    data-default-theme="{{ config('themes.default') }}"
    class="{{ $variant === 'floating' ? 'fixed bottom-4 end-4 z-50' : 'relative' }}"
>
    @if ($variant === 'floating')
        <button
            type="button"
            @click="toggleTheme"
            aria-label="تبديل المظهر"
            class="flex size-11 items-center justify-center rounded-full border border-border bg-surface text-foreground shadow-lg transition-colors hover:bg-muted"
        >
            <svg x-show="current === 'dark'" x-cloak class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
            </svg>

            <svg x-show="current !== 'dark'" x-cloak class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="4" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
            </svg>
        </button>
    @else
        <button
            type="button"
            @click="toggle"
            :aria-expanded="open"
            aria-haspopup="menu"
            class="flex items-center gap-2 rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            <svg x-show="current === 'dark'" x-cloak class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
            </svg>

            <svg x-show="current !== 'dark'" x-cloak class="size-4 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="4" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
            </svg>

            <span x-text="labels[current]" class="text-foreground">المظهر</span>

            <svg class="size-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
            </svg>
        </button>

        <div
            x-show="open"
            @click.outside="open = false"
            x-cloak
            x-transition
            role="menu"
            class="absolute end-0 z-50 mt-2 w-40 rounded-md border border-border bg-surface py-1 shadow-lg"
        >
            <template x-for="theme in themes" :key="theme">
                <button
                    type="button"
                    role="menuitem"
                    @click="setTheme(theme)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-sm text-foreground transition-colors hover:bg-muted"
                    :class="{ 'bg-muted font-medium': current === theme }"
                >
                    <span x-text="labels[theme]"></span>
                    <svg
                        x-show="current === theme"
                        x-cloak
                        class="ms-auto size-4 text-primary"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                    </svg>
                </button>
            </template>
        </div>
    @endif
</div>
