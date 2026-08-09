@extends('layouts.app')

@section('title', 'دور جديد')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('roles.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الأدوار
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إنشاء دور جديد
            </h1>
        </div>

        @include('admin.roles._form', [
            'role' => null,
            'rolePermissions' => [],
        ])
    </div>
@endsection
