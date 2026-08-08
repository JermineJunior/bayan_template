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
    </head>

    <body class="bg-background text-foreground antialiased">
        <div class="flex min-h-screen">
            <x-sidebar />

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-4 border-b border-border bg-surface px-4 sm:px-6">
                    <div class="min-w-0">
                        <h1 class="truncate text-lg font-semibold text-foreground">
                            @yield('title', $appName)
                        </h1>
                    </div>

                    <nav class="flex items-center gap-3" aria-label="الشريط العلوي">
                        @auth
                            <a
                                href="{{ route('account.preferences.edit') }}"
                                class="flex items-center gap-2 text-sm font-medium text-foreground transition-colors hover:text-primary"
                            >
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </span>
                                <span class="hidden sm:inline">{{ auth()->user()->username }}</span>
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
    </body>
</html>
