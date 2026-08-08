@extends('layouts.app')

@section('title', 'الأدوار')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                إدارة الأدوار
            </h1>

            @can('create', Spatie\Permission\Models\Role::class)
                <a
                    href="{{ route('admin.roles.create') }}"
                    class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                >
                    إنشاء دور
                </a>
            @endcan
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-border bg-surface shadow-sm">
            <table class="min-w-full divide-y divide-border text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الدور
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                            الصلاحيات
                        </th>
                        <th class="bg-muted/50 px-4 py-3 text-end font-medium">
                            إجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-4 py-3 font-medium text-foreground">
                                {{ $role->name }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ $role->permissions_count }} صلاحية
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @can('update', $role)
                                        <a
                                            href="{{ route('admin.roles.edit', $role) }}"
                                            class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-foreground transition-colors hover:bg-muted"
                                        >
                                            تعديل دور
                                        </a>
                                    @endcan

                                    @can('delete', $role)
                                    @if (!auth()->user()->hasRole($role)) <!-- prevent deleting role if user has it (admin) -->
                                        <form
                                            method="POST"
                                            action="{{ route('admin.roles.destroy', $role) }}"
                                            onsubmit="return confirm('هل تريد حذف هذا الدور؟')"
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
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-muted-foreground">
                                لا توجد أدوار بعد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
