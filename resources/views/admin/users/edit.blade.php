@extends('layouts.app')

@section('title', 'تعديل مستخدم')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6">
            <a
                href="{{ route('users.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المستخدمين
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تعديل المستخدم: {{ $user->name }}
            </h1>
        </div>

        @include('admin.users._form', [
            'user' => $user,
        ])

        <div class="mt-10 max-w-3xl rounded-xl border border-border bg-surface p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-foreground">
                        حالة الحساب
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        <span class="rounded-md px-2 py-0.5 text-xs font-medium {{ $user->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                            {{ $user->is_active ? 'الحساب نشط' : 'الحساب معطل' }}
                        </span>
                    </p>
                </div>

                <x-user-status-toggle :user="$user" />
            </div>

            <p class="mt-4 text-xs text-muted-foreground">
                عند تعطيل الحساب يُمنع صاحبه من تسجيل الدخول فورًا (تُسجَّل خروجه من
                الجلسات المفتوحة) دون حذف بياناته. يمكن تفعيله في أي وقت لاستعادة
                الوصول.
            </p>
        </div>

        <div class="mt-10 max-w-3xl rounded-xl border border-border bg-surface p-6">
            <h2 class="text-lg font-semibold text-foreground">
                إعادة تعيين كلمة المرور
            </h2>
            <p class="mt-1 text-sm text-muted-foreground">
                حدد كلمة مرور جديدة مباشرة لهذا المستخدم. لا يُرسل أي إشعار عبر البريد الإلكتروني —
                سيعمل الحساب بكلمة المرور الجديدة فورًا.
            </p>

            <form
                method="POST"
                action="{{ route('users.reset-password', $user) }}"
                x-data="{}"
                class="mt-4"
            >
                @csrf

                <div class="flex flex-wrap items-center gap-3">
                    <label for="reset-password" class="sr-only">كلمة المرور الجديدة</label>
                    <input
                        id="reset-password"
                        name="password"
                        type="password"
                        required
                        minlength="8"
                        autocomplete="new-password"
                        placeholder="كلمة مرور جديدة"
                        class="w-full min-w-0 flex-1 rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    <button
                        type="button"
                        @click="$refs.confirmReset.hidden = false"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        إعادة تعيين
                    </button>
                </div>

                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div x-ref="confirmReset" hidden class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    <p>
                        سيتم استبدال كلمة المرور الحالية للمستخدم بهذه الكلمة الجديدة فورًا.
                        هل أنت متأكد؟
                    </p>
                    <div class="mt-3 flex items-center gap-3">
                        <button
                            type="submit"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تأكيد
                        </button>
                        <button
                            type="button"
                            @click="$refs.confirmReset.hidden = true"
                            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                        >
                            إلغاء
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
