@extends('layouts.app')

@section('title', 'تعديل مركبة')

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
                تعديل المركبة: {{ $vehicle->internal_number }}
            </h1>
        </div>

        @include('basic-data.vehicles._form', [
            'vehicle' => $vehicle,
            'managements' => $managements,
        ])
    </div>
@endsection
