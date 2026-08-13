@extends('layouts.app')

@section('title', 'امر صيانة جديد')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('maintenance.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى اوامر الصيانة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                إنشاء امر صيانة جديد
            </h1>
        </div>

        @include('maintenance._form', [
            'maintenance' => null,
        ])
    </div>
@endsection


