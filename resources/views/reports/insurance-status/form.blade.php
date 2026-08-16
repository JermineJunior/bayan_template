@extends('layouts.app')

@section('title', 'حالة التأمينات')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                حالة التأمينات
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                اعرض البوليصات الحالية أو التي تنتهي خلال فترة محددة.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('reports.insurance-status.results') }}"
            class="mb-6 rounded-xl border border-border bg-surface p-4 shadow-sm"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="flex items-end">
                    <label for="is_current" class="flex items-center gap-2 text-sm font-medium text-foreground">
                        <input
                            id="is_current"
                            name="is_current"
                            type="checkbox"
                            value="1"
                            @checked(request()->boolean('is_current'))
                            class="size-4 rounded border-border text-primary focus:ring-primary"
                        >
                        البوليصات السارية فقط
                    </label>
                </div>

                <div>
                    <label for="expiring_within_days" class="mb-1 block text-sm font-medium text-foreground">
                        تنتهي خلال (أيام)
                    </label>
                    <input
                        id="expiring_within_days"
                        name="expiring_within_days"
                        type="number"
                        min="0"
                        step="1"
                        value="{{ request('expiring_within_days') }}"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                </div>

                <div class="flex items-end gap-2">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-1.5 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        إنشاء التقرير
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
