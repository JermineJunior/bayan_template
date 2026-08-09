@extends('layouts.app')

@section('title', 'تعديل سائق')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('drivers.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى السائقين
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل السائق: {{ $driver->full_name }}
            </h1>
        </div>

        @include('basic-data.drivers._form', [
            'driver' => $driver,
            'departments' => $departments,
        ])
    </div>
@endsection
