<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', $appName) — {{ $appName }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])

        <style>
            @page {
                size: A4;
                margin: 14mm;
            }

            .print-header {
                display: flex;
                align-items: center;
                gap: 12px;
                margin-bottom: 16px;
                padding-bottom: 12px;
                border-bottom: 2px solid #172A2A;
            }
            .print-header img {
                height: 48px;
                width: 48px;
                flex-shrink: 0;
                object-fit: contain;
                border-radius: 9999px;
                border: 1px solid var(--app-border);
                background: var(--app-surface);
            }
            .print-header h1 {
                font-size: 20px;
                font-weight: 700;
                line-height: 1.3;
            }
            .report-meta {
                margin-top: 2px;
                font-size: 13px;
                color: var(--app-muted-foreground);
            }

            .total {
                margin-top: 12px;
                font-weight: 700;
                text-align: right;
            }

            @media print {
                .print-toolbar {
                    display: none !important;
                }
                body {
                    background: #FFFFFF !important;
                }
                main {
                    max-width: none !important;
                    padding: 0 !important;
                }
                table {
                    border-collapse: collapse;
                    width: 100%;
                }
                th, td {
                    border: 1px solid #9CA3AF;
                }
            }
        </style>
    </head>
    <body class="bg-background text-foreground antialiased">
        {{-- Screen-only toolbar: hidden when the page is printed. --}}
        <div class="print-toolbar sticky top-0 z-20 flex h-14 items-center justify-between border-b border-border bg-surface px-4 sm:px-6 print:hidden">
            <a
                href="{{ url()->previous() }}"
                class="rounded-md border border-border px-4 py-1.5 text-sm font-medium text-foreground transition-colors hover:bg-muted"
            >
                &rarr; رجوع
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                طباعة / حفظ PDF
            </button>
        </div>

        <main class="mx-auto max-w-5xl px-4 py-8">
            {{-- Print header: app identity (from general settings) + report title. --}}
            <header class="print-header">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                @endif
                <div>
                    <h1>@yield('title', $appName)</h1>
                    <div class="report-meta">
                        {{ $appName }} — {{ now()->format('Y-m-d H:i') }}
                    </div>
                </div>
            </header>

            @yield('content')
        </main>

        <script>
            window.addEventListener('load', function () {
                setTimeout(function () {
                    window.print();
                }, 400);
            });

            // Once the print dialog closes (printed or cancelled), return to
            // the results page that opened this printable view.
            window.addEventListener('afterprint', function () {
                if (document.referrer) {
                    window.location.href = document.referrer;
                } else {
                    window.history.back();
                }
            });
        </script>
    </body>
</html>
