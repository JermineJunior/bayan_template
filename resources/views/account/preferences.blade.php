@extends('layouts.app')

@section('title', 'التفضيلات')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('home') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى الرئيسية
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تفضيلاتي
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                تُطبق هذه التفضيلات على حسابك أنت فقط ولا تؤثر على المستخدمين الآخرين.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('account.preferences.update') }}"
            class="max-w-3xl space-y-6"
        >
            @csrf
            @method('PUT')

            <div>
                <span class="mb-1 block text-sm font-medium text-foreground">حجم الخط</span>

                <div class="flex flex-wrap gap-3">
                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm text-foreground has-[:checked]:border-primary has-[:checked]:ring-1 has-[:checked]:ring-primary">
                        <input
                            type="radio"
                            name="font_size"
                            value="small"
                            @checked(old('font_size', $fontSize) === 'small')
                            class="accent-primary"
                        >
                        صغير
                    </label>

                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm text-foreground has-[:checked]:border-primary has-[:checked]:ring-1 has-[:checked]:ring-primary">
                        <input
                            type="radio"
                            name="font_size"
                            value="default"
                            @checked(old('font_size', $fontSize) === 'default')
                            class="accent-primary"
                        >
                        افتراضي
                    </label>

                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-border bg-background px-4 py-2 text-sm text-foreground has-[:checked]:border-primary has-[:checked]:ring-1 has-[:checked]:ring-primary">
                        <input
                            type="radio"
                            name="font_size"
                            value="large"
                            @checked(old('font_size', $fontSize) === 'large')
                            class="accent-primary"
                        >
                        كبير
                    </label>
                </div>

                @error('font_size')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="submit"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    حفظ التفضيلات
                </button>

                <a
                    href="{{ route('home') }}"
                    class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                >
                    إلغاء
                </a>
            </div>
        </form>
    </div>
@endsection
