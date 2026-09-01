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

    <body class="min-h-dvh bg-background text-foreground antialiased">
        @yield('content')

        <x-theme-switcher variant="floating" />
    </body>
</html>
