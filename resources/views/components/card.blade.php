@props(['title'])

<div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
    <h2 class="font-semibold text-foreground">{{ $title }}</h2>
    <p class="mt-2 text-sm text-muted-foreground">{{ $slot }}</p>
</div>
