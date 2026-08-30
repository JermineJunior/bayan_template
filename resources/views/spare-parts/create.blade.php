@extends('layouts.app')

@section('title', 'قطعة غيار جديدة')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('spare-parts.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى قطع الغيار
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة قطعة غيار جديدة
            </h1>
        </div>

        @include('spare-parts._form', [
            'sparePart' => null,
            'suppliers' => $suppliers,
        ])
    </div>
@endsection
