@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="flex min-h-dvh flex-col-reverse lg:flex-row">
        {{-- Form — right column on desktop (RTL flex-row), bottom section on mobile --}}
        <main class="flex flex-1 items-center justify-center bg-background p-6 lg:w-1/2 lg:flex-none lg:p-12">
            <div class="w-full max-w-md">
                <div class="mb-8 block lg:hidden">
                    <div class="mb-8 flex flex-col items-center gap-3">
                        @if ($logoUrl)
                            <img
                                src="{{ $logoUrl }}"
                                alt="{{ $appName }}"
                                class="h-12 w-12 rounded-full border border-border object-contain bg-background p-1"
                            >
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        سجّل الدخول إلى حسابك
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        استخدم اسم المستخدم وكلمة المرور للمتابعة.
                    </p>
                </div>

                <div class="mb-8 hidden lg:block">
                    <h1 class="text-2xl font-bold tracking-tight text-foreground">
                        سجّل الدخول إلى حسابك
                    </h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        استخدم اسم المستخدم وكلمة المرور للمتابعة.
                    </p>
                </div>

                @if ($errors->any())
                    <div class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <ul class="list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="username" class="mb-1 block text-sm font-medium text-foreground">
                            اسم المستخدم
                        </label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-foreground">
                            كلمة المرور
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <label for="remember" class="flex items-center gap-2 text-sm text-muted-foreground">
                            <input
                                id="remember"
                                name="remember"
                                type="checkbox"
                                class="size-4 rounded border-border text-primary focus:ring-primary"
                            >
                            تذكرني
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل الدخول
                    </button>
                </form>
            </div>
        </main>

        {{-- Hero — left column on desktop (second child in RTL flex-row), top banner on mobile --}}
        <aside class="auth-hero relative flex flex-1 flex-col justify-between overflow-hidden p-8 text-white lg:w-1/2 lg:flex-none lg:p-12">
            {{-- Decorative glows --}}
            <div class="pointer-events-none absolute -start-20 -top-20 size-64 rounded-full bg-white/10 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -end-16 size-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    @if ($logoUrl)
                        <img
                            src="{{ $logoUrl }}"
                            alt="{{ $appName }}"
                            class="h-12 w-12 rounded-full border border-white/25 bg-white/10 object-contain p-1"
                        >
                    @endif
                    <span class="text-xl font-bold">{{ $appName }}</span>
                </div>

                <h2 class="mt-10 max-w-xl text-3xl font-bold leading-snug lg:mt-16 lg:text-4xl">
                    إدارة أسطولك بذكاء في مكان واحد
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-white/80">
                    تتبّع المركبات والوقود والصيانة والتأمين وقطع الغيار والموردين
                    والتقارير — من لوحة موحّدة وسهلة الاستخدام.
                </p>
            </div>

            <div class="relative mt-12">
                <div class="flex flex-wrap gap-2">
                    @foreach (['الوقود', 'الصيانة', 'قطع الغيار', 'التأمين', 'التقارير'] as $feature)
                        <span
                            class="rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white/90"
                        >
                            {{ $feature }}
                        </span>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
@endsection