@extends('layouts.app')

@section('title', 'تعديل قطعة غيار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('spare-parts.show', $sparePart) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى القطعة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل القطعة: {{ $sparePart->name }}
            </h1>
        </div>

        @include('spare-parts._form', [
            'sparePart' => $sparePart,
            'suppliers' => $suppliers,
        ])
    </div>
@endsection
