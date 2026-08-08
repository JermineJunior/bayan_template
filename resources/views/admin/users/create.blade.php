@extends('layouts.app')

@section('title', 'مستخدم جديد')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('admin.users.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المستخدمين
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إنشاء مستخدم جديد
            </h1>
        </div>

        @include('admin.users._form', [
            'user' => null,
        ])
    </div>
@endsection
