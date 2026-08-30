@extends('layouts.app')

@section('title', 'مورد جديد')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('suppliers.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الموردين
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إضافة مورد جديد
            </h1>
        </div>

        @include('suppliers._form', [
            'supplier' => null,
        ])
    </div>
@endsection
