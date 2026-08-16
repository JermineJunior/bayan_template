<!DOCTYPE html>
<html
    lang="ar"
    dir="rtl"
    data-theme="{{ $theme }}"
    data-font-size="{{ $userFontSize }}"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', $appName)</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            (function () {
                try {
                    if (JSON.parse(localStorage.getItem('sidebar-collapsed') || 'false') === true) {
                        document.documentElement.classList.add('sidebar-collapsed');
                    }
                } catch (e) {
                    // Storage unavailable — the sidebar stays expanded for the session.
                }
            })();
        </script>
    </head>

    <body class="bg-background text-foreground antialiased">
        <div x-data="sidebar" class="flex min-h-screen">
            {{-- Mobile drawer backdrop --}}
            <div
                x-show="mobileOpen"
                x-cloak
                @click="mobileOpen = false"
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
            ></div>

            <x-sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-border bg-surface px-4 sm:px-6">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            @click="mobileOpen = !mobileOpen"
                            aria-label="فتح القائمة"
                            class="rounded-md border border-border p-2 text-muted-foreground transition-colors hover:bg-muted lg:hidden"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 6h16"></path>
                                <path d="M4 12h16"></path>
                                <path d="M4 18h16"></path>
                            </svg>
                        </button>

                        <h1 class="truncate text-lg font-semibold text-foreground">
                            @yield('title', $appName)
                        </h1>
                    </div>

                    <nav class="flex items-center gap-3" aria-label="الشريط العلوي">
                        @auth
                            @php
                                $unreadCount = auth()->user()->unreadNotifications->count();
                                $recentNotifications = auth()->user()->notifications()->limit(5)->get();
                            @endphp

                            {{-- Notification bell + dropdown --}}
                            <div
                                x-data="notificationBell('{{ route('notifications.mark-all-read') }}', {{ $unreadCount }})"
                                class="relative"
                            >
                                <button
                                    type="button"
                                    @click="toggle()"
                                    aria-label="الإشعارات"
                                    class="relative rounded-md border border-border p-2 text-muted-foreground transition-colors hover:bg-muted"
                                >
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"></path>
                                        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"></path>
                                    </svg>
                                    <span
                                        x-show="unreadCount > 0"
                                        x-text="unreadCount"
                                        x-cloak
                                        class="absolute -top-1 -end-1 flex size-5 items-center justify-center rounded-full bg-red-600 text-[11px] font-bold text-white"
                                    ></span>
                                </button>

                                <div
                                    x-show="open"
                                    x-cloak
                                    x-transition
                                    @click.outside="close()"
                                    class="absolute end-0 top-full z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-border bg-surface shadow-lg"
                                >
                                    <div class="flex items-center justify-between border-b border-border px-4 py-3">
                                        <h2 class="text-sm font-semibold text-foreground">
                                            الإشعارات
                                        </h2>
                                        <button
                                            type="button"
                                            x-show="unreadCount > 0"
                                            @click="markAllRead()"
                                            class="text-xs font-medium text-primary transition-colors hover:opacity-80"
                                        >
                                            تحديد الكل كمقروء
                                        </button>
                                    </div>

                                    <ul class="max-h-96 overflow-y-auto">
                                        @forelse ($recentNotifications as $notification)
                                            <li>
                                                <button
                                                    type="button"
                                                    data-notification-item
                                                    data-read-url="{{ route('notifications.mark-read', $notification->id) }}"
                                                    @click="markRead($event.currentTarget)"
                                                    class="flex w-full items-start gap-3 px-4 py-3 text-start transition-colors hover:bg-muted {{ $notification->read_at ? '' : 'bg-primary/5' }}"
                                                >
                                                    @unless ($notification->read_at)
                                                        <span data-unread-dot class="mt-1.5 size-2 shrink-0 rounded-full bg-primary"></span>
                                                    @endunless

                                                    <span class="min-w-0 flex-1">
                                                        <span data-message class="block text-sm {{ $notification->read_at ? 'text-muted-foreground' : 'font-medium text-foreground' }}">
                                                            {{ $notification->data['message'] }}
                                                        </span>
                                                        <span class="mt-0.5 block text-xs text-muted-foreground">
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </span>
                                                </button>
                                            </li>
                                        @empty
                                            <li class="px-4 py-8 text-center text-sm text-muted-foreground">
                                                لا توجد إشعارات.
                                            </li>
                                        @endforelse
                                    </ul>

                                    <a
                                        href="{{ route('notifications.index') }}"
                                        class="block border-t border-border px-4 py-2.5 text-center text-sm font-medium text-primary transition-colors hover:bg-muted"
                                    >
                                        عرض الكل
                                    </a>
                                </div>
                            </div>

                            <div x-data="{
                                isFullscreen: localStorage.getItem('app-fullscreen') === 'true',
                                init() {
                                    this.$nextTick(() => {
                                        document.addEventListener('fullscreenchange', () => {
                                            this.isFullscreen = !!document.fullscreenElement;
                                            if (this.isFullscreen) {
                                                localStorage.setItem('app-fullscreen', 'true');
                                            } else {
                                                localStorage.removeItem('app-fullscreen');
                                            }
                                        });
                                    });
                                },
                                toggle() {
                                    if (document.fullscreenElement) {
                                        document.exitFullscreen().catch(() => {});
                                    } else {
                                        document.documentElement.requestFullscreen().catch(() => {});
                                    }
                                }
                            }">
                                <button
                                    type="button"
                                    @click="toggle()"
                                    aria-label="ملء الشاشة"
                                    class="rounded-md border border-border p-2 text-muted-foreground transition-colors hover:bg-muted"
                                    :class="{ 'bg-primary/10 text-primary border-primary': isFullscreen }"
                                >
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"></path>
                                    </svg>
                                </button>
                            </div>

                            <a
                                href="{{ route('account.preferences.edit') }}"
                                class="flex items-center gap-2 text-sm font-medium text-foreground transition-colors hover:text-primary"
                            >
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden sm:inline">{{ auth()->user()->getRoleNames()->first() }}</span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex items-center gap-2 rounded-md border border-border bg-surface px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                                >
                                    <svg class="size-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                        <path d="m16 17 5-5-5-5"></path>
                                        <path d="M21 12H9"></path>
                                    </svg>
                                    <span class="hidden sm:inline">تسجيل الخروج</span>
                                </button>
                            </form>
                        @endauth
                    </nav>
                </header>

                <main class="flex-1">
                    @yield('content')
                </main>

                <footer class="border-t border-border bg-surface">
                    <div class="px-4 py-4 text-sm text-muted-foreground sm:px-6 text-center">
                        © {{ date('Y') }} {{ $appName }}. جميع الحقوق محفوظة.
                    </div>
                </footer>
            </div>
        </div>

        <x-theme-switcher variant="floating" />
        <x-scroll-to-top />

        @stack('scripts')
    </body>
</html>
