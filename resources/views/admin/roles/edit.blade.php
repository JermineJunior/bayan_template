@extends('layouts.app')

@section('title', 'تعديل دور')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('admin.roles.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الأدوار
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل الدور: {{ $role->name }}
            </h1>
        </div>

        @include('admin.roles._form', [
            'role' => $role,
            'rolePermissions' => $rolePermissions,
        ])
    </div>
@endsection
