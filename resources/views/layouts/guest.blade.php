<!DOCTYPE html>
<html
    lang="ar"
    dir="rtl"
    data-theme="{{ $theme }}"
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

    <body class="flex min-h-screen items-center justify-center bg-background p-4 text-foreground antialiased">
        <main class="w-full max-w-md rounded-xl border border-border bg-surface p-8 shadow-sm">
            <div class="mb-6 flex items-center justify-center gap-2">
                @if ($logoUrl)
                    <img
                        src="{{ $logoUrl }}"
                        alt="{{ $appName }}"
                        class="h-10 w-10 rounded-full border border-border object-contain bg-background"
                    >
                @endif
                <span class="text-xl font-bold text-foreground">{{ $appName }}</span>
            </div>

            @yield('content')
        </main>

        <x-theme-switcher variant="floating" />
    </body>
</html>
