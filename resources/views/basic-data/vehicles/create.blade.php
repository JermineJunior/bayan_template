@extends('layouts.app')

@section('title', 'مركبة جديدة')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('vehicles.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبات
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إنشاء مركبة جديدة
            </h1>
        </div>

        @include('basic-data.vehicles._form', [
            'vehicle' => null,
            'managements' => $managements,
        ])
    </div>
@endsection
