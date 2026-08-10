@extends('layouts.app')

@section('title', 'المستخدمون')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                إدارة المستخدمين
            </h1>

            @can('create', App\Models\User::class)
                <a
                    href="{{ route('users.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء مستخدم
                </a>
            @endcan
        </div>

        <div class="overflow-x-auto rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            المستخدم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            اسم المستخدم
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الدور
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الحالة
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $user->name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $user->username }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md bg-muted px-2 py-1 text-xs text-foreground">
                                    {{ $user->roles->first()?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-md px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                                    {{ $user->is_active ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    @can('update', $user)
                                        <a
                                            href="{{ route('users.edit', $user) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل
                                        </a>
                                    @endcan

                                    <x-user-status-toggle :user="$user" />

                                    @can('delete', $user)
                                        <form
                                            method="POST"
                                            action="{{ route('users.destroy', $user) }}"
                                            onsubmit="return confirmForm(this, 'هل تريد حذف هذا المستخدم؟', 'نعم، احذف')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                            >
                                                حذف
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-muted-foreground">
                                لا يوجد مستخدمون بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="mt-6">
                {{ $users->links() }}
            </div>
        @endif
    </div>
@endsection
