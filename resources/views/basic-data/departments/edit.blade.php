@extends('layouts.app')

@section('title', 'تعديل قسم')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('departments.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الأقسام
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل القسم: {{ $department->name }}
            </h1>
        </div>

        @include('basic-data.departments._form', [
            'department' => $department,
        ])
    </div>
@endsection
