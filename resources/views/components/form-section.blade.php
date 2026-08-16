@props(['title'])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-border bg-surface p-6 shadow-sm']) }}>
    <div class="mb-5 flex items-center gap-2 border-b border-border pb-3">
        <span class="h-4 w-1 rounded-full bg-primary" aria-hidden="true"></span>
        <h2 class="text-base font-semibold text-foreground">{{ $title }}</h2>
    </div>

    <div class="space-y-6">
        {{ $slot }}
    </div>
</section>
