@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="text-center">
        <h1 class="text-2xl font-bold tracking-tight text-foreground">
            سجّل الدخول إلى حسابك
        </h1>
        <p class="mt-2 text-sm text-muted-foreground">
            استخدم اسم المستخدم وكلمة المرور للمتابعة.
        </p>
    </div>

    @if ($errors->any())
        <div class="mt-6 rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-800">
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
@endsection
