@extends('layouts.app')

@section('title', 'حادث — '.$incident->report_number)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('incidents.index') }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى قائمة الحوادث
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                {{ $incident->report_number }}
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    تفاصيل الحادث
                </h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-muted-foreground">المركبة</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            <a href="{{ route('vehicles.show', $incident->vehicle) }}" class="hover:text-primary">
                                {{ $incident->vehicle->internal_number }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">السائق</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{ $incident->driver?->full_name ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">تاريخ الحادث</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{ $incident->incident_date?->format('Y-m-d') ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">الموقع</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{ $incident->location ?? '—' }}
                        </dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs text-muted-foreground">الوصف</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground whitespace-pre-wrap">
                            {{ $incident->description ?? '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">تكلفة الإصلاح</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{ $incident->repair_cost !== null ? money($incident->repair_cost) : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">بوليصة التأمين</dt>
                        <dd class="mt-1 text-sm font-medium text-foreground">
                            @if ($incident->insurancePolicy)
                                <a href="{{ route('vehicles.show', $incident->insurancePolicy->vehicle) }}" class="hover:text-primary">
                                    {{ $incident->insurancePolicy->policy_number }}
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    حالة المطالبة
                </h2>
                @php
                    $claimLabels = [
                        'pending' => 'قيد المراجعة',
                        'approved' => 'موافق عليه',
                        'rejected' => 'مرفوض',
                        'paid' => 'مدفوع',
                    ];
                    $claimColors = [
                        'pending' => 'bg-amber-100 text-amber-700',
                        'approved' => 'bg-green-100 text-green-700',
                        'rejected' => 'bg-red-100 text-red-700',
                        'paid' => 'bg-blue-100 text-blue-700',
                    ];
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        @if ($incident->claim_status)
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-medium {{ $claimColors[$incident->claim_status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $claimLabels[$incident->claim_status] ?? $incident->claim_status }}
                            </span>
                        @else
                            <span class="text-muted-foreground">لا توجد مطالبة</span>
                        @endif
                    </div>
                    @can('incidents.edit')
                        <form
                            method="POST"
                            action="{{ route('incidents.update-claim-status', $incident) }}"
                            class="flex items-center gap-2"
                        >
                            @csrf
                            @method('PATCH')
                            <select
                                name="claim_status"
                                class="rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                                <option value="">بدون مطالبة</option>
                                <option value="pending" @selected($incident->claim_status === 'pending')>قيد المراجعة</option>
                                <option value="approved" @selected($incident->claim_status === 'approved')>موافق عليه</option>
                                <option value="rejected" @selected($incident->claim_status === 'rejected')>مرفوض</option>
                                <option value="paid" @selected($incident->claim_status === 'paid')>مدفوع</option>
                            </select>
                            <button
                                type="submit"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >
                                تحديث
                            </button>
                        </form>
                    @endcan
                </div>
            </div>

            @if ($incident->photos->isNotEmpty())
                <div
                    x-data="{
                        photos: @js(
                            $incident->photos->map(fn ($photo) => Storage::disk('public')->url($photo->file_path))->values()
                        ),
                        current: 0,
                        get total() {
                            return this.photos.length;
                        },
                        next() {
                            this.current = (this.current + 1) % this.total;
                        },
                        prev() {
                            this.current = (this.current - 1 + this.total) % this.total;
                        },
                        go(i) {
                            this.current = i;
                        }
                    }"
                    class="rounded-xl border border-border bg-surface p-6 shadow-sm"
                >
                    <h2 class="mb-4 text-sm font-semibold text-foreground">
                        الصور
                    </h2>

                    <div class="relative overflow-hidden rounded-lg border border-border">
                        <img
                            :src="photos[current]"
                            :alt="'صورة الحادث ' + (current + 1)"
                            class="h-96 w-full object-cover"
                        >

                        <button
                            type="button"
                            @click="prev()"
                            :disabled="total === 1"
                            aria-label="السابق"
                            class="absolute start-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70 disabled:hidden"
                        >
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m15 18-6-6 6-6"></path>
                            </svg>
                        </button>

                        <button
                            type="button"
                            @click="next()"
                            :disabled="total === 1"
                            aria-label="التالي"
                            class="absolute end-3 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition-colors hover:bg-black/70 disabled:hidden"
                        >
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"></path>
                            </svg>
                        </button>

                        <span class="absolute bottom-3 end-3 rounded-full bg-black/60 px-3 py-1 text-xs font-medium text-white">
                            <span x-text="current + 1"></span> / <span x-text="total"></span>
                        </span>
                    </div>

                    @if ($incident->photos->count() > 1)
                        <div class="mt-4 flex gap-2 overflow-x-auto">
                            <template x-for="(photo, i) in photos" :key="i">
                                <button
                                    type="button"
                                    @click="go(i)"
                                    :class="current === i ? 'ring-2 ring-primary ring-offset-2' : 'opacity-60 hover:opacity-100'"
                                    class="shrink-0 overflow-hidden rounded-md border border-border"
                                >
                                    <img
                                        :src="photo"
                                        :alt="'صورة الحادث ' + (i + 1)"
                                        class="h-16 w-24 object-cover"
                                    >
                                </button>
                            </template>
                        </div>
                    @endif

                    <div class="mt-4">
                        <a
                            :href="photos[current]"
                            target="_blank"
                            class="text-sm font-medium text-primary hover:underline"
                        >
                            فتح الصورة بالحجم الكامل
                        </a>
                    </div>
                </div>
            @endif

            <div class="flex items-center gap-3">
                @can('incidents.delete')
                    <form
                        method="POST"
                        action="{{ route('incidents.destroy', $incident) }}"
                        onsubmit="return confirmForm(this, 'هل تريد حذف هذا الحادث؟', 'نعم، احذف')"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition-colors hover:bg-red-50"
                        >
                            حذف الحادث
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </div>
@endsection
