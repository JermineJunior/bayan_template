@extends('layouts.app')

@section('title', 'تعديل فلتر')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('filters.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الفلاتر
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل الفلتر: {{ $filter->filter_name }}
            </h1>
        </div>

        @include('filters._form', [
            'filter' => $filter,
        ])
    </div>
@endsection
