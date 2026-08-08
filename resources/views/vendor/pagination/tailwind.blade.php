@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" class="flex items-center justify-between">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="rounded-md border border-border px-3 py-2 text-sm text-muted-foreground">السابقة</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="rounded-md border border-border px-3 py-2 text-sm text-foreground transition-colors hover:bg-muted">السابقة</a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="rounded-md border border-border px-3 py-2 text-sm text-foreground transition-colors hover:bg-muted">التالية</a>
            @else
                <span class="rounded-md border border-border px-3 py-2 text-sm text-muted-foreground">التالية</span>
            @endif
        </div>

        <div class="hidden items-center gap-1 sm:flex">
            @if ($paginator->onFirstPage())
                <span class="flex size-9 items-center justify-center rounded-md text-muted-foreground" aria-disabled="true">&rsaquo;</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="flex size-9 items-center justify-center rounded-md border border-border text-foreground transition-colors hover:bg-muted">&rsaquo;</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="flex size-9 items-center justify-center rounded-md text-muted-foreground">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="flex size-9 items-center justify-center rounded-md bg-primary text-sm font-medium text-primary-foreground">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="flex size-9 items-center justify-center rounded-md border border-border text-sm text-foreground transition-colors hover:bg-muted">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="flex size-9 items-center justify-center rounded-md border border-border text-foreground transition-colors hover:bg-muted">&lsaquo;</a>
            @else
                <span class="flex size-9 items-center justify-center rounded-md text-muted-foreground" aria-disabled="true">&lsaquo;</span>
            @endif
        </div>
    </nav>
@endif
