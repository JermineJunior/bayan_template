@php
    $quickRanges = [
        'today' => ['label' => 'اليوم', 'from' => now()->toDateString(), 'to' => now()->toDateString()],
        'yesterday' => ['label' => 'أمس', 'from' => now()->subDay()->toDateString(), 'to' => now()->subDay()->toDateString()],
        'this_month' => ['label' => 'هذا الشهر', 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()],
        'this_year' => ['label' => 'هذه السنة', 'from' => now()->startOfYear()->toDateString(), 'to' => now()->endOfYear()->toDateString()],
    ];
@endphp

<div class="flex flex-wrap items-center gap-2">
    @foreach ($quickRanges as $key => $range)
        <button
            type="button"
            data-quick-range
            data-from="{{ $range['from'] }}"
            data-to="{{ $range['to'] }}"
            class="rounded-md border border-border bg-background px-3 py-1.5 text-sm font-medium text-foreground transition-colors hover:border-primary hover:text-primary"
        >
            {{ $range['label'] }}
        </button>
    @endforeach
</div>
