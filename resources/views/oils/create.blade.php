@extends('layouts.app')

@section('title', 'إضافة زيت')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('oils.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الزيوت
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة زيت جديد
            </h1>
        </div>

        @include('oils._form', [
            'oil' => null,
        ])
    </div>
@endsection
