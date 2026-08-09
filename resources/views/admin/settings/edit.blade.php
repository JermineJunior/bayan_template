@extends('layouts.app')

@section('title', 'إعدادات التطبيق')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('home') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الرئيسية
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إعدادات التطبيق
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                تُطبَّق هذه الإعدادات على جميع المستخدمين فور حفظها.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('settings.update') }}"
            enctype="multipart/form-data"
            class="max-w-3xl space-y-6"
        >
            @csrf
            @method('PUT')

            <div>
                <label for="app_name" class="mb-1 block text-sm font-medium text-foreground">
                    اسم التطبيق
                </label>
                <input
                    id="app_name"
                    name="app_name"
                    type="text"
                    value="{{ old('app_name', $appName) }}"
                    required
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                <p class="mt-1 text-xs text-muted-foreground">
                    يظهر في شريط التنقل وتذييل الصفحة وكل الشاشات.
                </p>
                @error('app_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="logo" class="mb-1 block text-sm font-medium text-foreground">
                    شعار التطبيق <span class="text-muted-foreground">(اختياري)</span>
                </label>

                @if ($logoUrl)
                    <div class="mb-2 flex items-center gap-3">
                        <img
                            src="{{ $logoUrl }}"
                            alt="شعار التطبيق"
                            class="h-10 w-10 rounded-full border border-border object-contain bg-background"
                        >
                        <span class="text-sm text-muted-foreground">
                            الشعار الحالي — ارفع صورة جديدة لاستبداله.
                        </span>
                    </div>
                @endif

                <input
                    id="logo"
                    name="logo"
                    type="file"
                    accept="image/*"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground file:mr-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-foreground"
                >
                <p class="mt-1 text-xs text-muted-foreground">
                    صورة بحد أقصى 2 ميجابايت. تُعرض في شريط التنقل والتذييل.
                </p>
                @error('logo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    حفظ الإعدادات
                </button>

                <a
                    href="{{ route('home') }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection
