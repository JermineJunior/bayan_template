<div
    x-data="{ show: false }"
    x-init="show = window.scrollY > 400"
    @scroll.window="show = window.scrollY > 400"
    class="fixed bottom-20 end-4 z-50"
>
    <button
        type="button"
        x-show="show"
        x-cloak
        x-transition
        @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        aria-label="العودة إلى الأعلى"
        class="flex size-11 items-center justify-center rounded-full border border-border bg-surface text-foreground shadow-lg transition-colors hover:bg-muted"
    >
        <svg class="size-5 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="m18 15-6-6-6 6"></path>
        </svg>
    </button>
</div>
