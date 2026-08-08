@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                لوحة التحكم
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                مرحبًا {{ auth()->user()->name }} — اختر أحد الأقسام من القائمة الجانبية.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @can('users.view')
                <a
                    href="{{ route('admin.users.index') }}"
                    class="group rounded-xl border border-border bg-surface p-6 shadow-sm transition-colors hover:border-primary"
                >
                    <h2 class="font-semibold text-foreground">المستخدمون</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        إدارة حسابات المستخدمين وأدوارهم.
                    </p>
                    <span class="mt-4 inline-block text-sm font-medium text-primary">تصفح &larr;</span>
                </a>
            @endcan

            @can('roles.view')
                <a
                    href="{{ route('admin.roles.index') }}"
                    class="group rounded-xl border border-border bg-surface p-6 shadow-sm transition-colors hover:border-primary"
                >
                    <h2 class="font-semibold text-foreground">الأدوار</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        إدارة الأدوار والصلاحيات الممنوحة لها.
                    </p>
                    <span class="mt-4 inline-block text-sm font-medium text-primary">تصفح &larr;</span>
                </a>
            @endcan

            @can('settings.edit')
                <a
                    href="{{ route('admin.settings.edit') }}"
                    class="group rounded-xl border border-border bg-surface p-6 shadow-sm transition-colors hover:border-primary"
                >
                    <h2 class="font-semibold text-foreground">الإعدادات</h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        اسم التطبيق والشعار.
                    </p>
                    <span class="mt-4 inline-block text-sm font-medium text-primary">تصفح &larr;</span>
                </a>
            @endcan
        </div>
    </div>
@endsection
