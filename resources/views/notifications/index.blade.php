@extends('layouts.app')

@section('title', 'الإشعارات')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                الإشعارات
            </h1>

            @if (auth()->user()->unreadNotifications->isNotEmpty())
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    @method('PATCH')
                    <button
                        type="submit"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        تحديد الكل كمقروء
                    </button>
                </form>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
            <ul class="divide-y divide-border">
                @forelse ($notifications as $notification)
                    <li class="flex items-start gap-3 px-4 py-4 {{ $notification->read_at ? '' : 'bg-primary/5' }}">
                        @unless ($notification->read_at)
                            <span data-unread-dot class="mt-2 size-2 shrink-0 rounded-full bg-primary"></span>
                        @endunless

                        <div class="min-w-0 flex-1">
                            @if ($notification->read_at)
                                <p data-message class="text-sm text-muted-foreground">
                                    {{ $notification->data['message'] }}
                                </p>
                            @else
                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        data-message
                                        class="text-start text-sm font-medium text-foreground transition-colors hover:text-primary"
                                    >
                                        {{ $notification->data['message'] }}
                                    </button>
                                </form>
                            @endif

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-12 text-center text-sm text-muted-foreground">
                        لا توجد إشعارات بعد.
                    </li>
                @endforelse
            </ul>
        </div>

        @if ($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
@endsection
