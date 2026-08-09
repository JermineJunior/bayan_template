@extends('layouts.app')

@section('title', 'إدارة جديدة')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('managements.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الإدارات
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إنشاء إدارة جديدة
            </h1>
        </div>

        @include('basic-data.managements._form', [
            'management' => null,
        ])
    </div>
@endsection
