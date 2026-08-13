@extends('layouts.app')

@section('title', 'تسجيل حادث — '.$vehicle->internal_number)

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-8">
        @include('basic-data._subnav')

        <div class="mb-6">
            <a
                href="{{ route('vehicles.show', $vehicle) }}"
                class="text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                &larr; العودة إلى المركبة
            </a>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-foreground">
                تسجيل حادث
            </h1>
        </div>

        <div class="max-w-3xl space-y-6">
            <div class="rounded-xl border border-border bg-surface p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">المركبة</p>
                        <p class="text-lg font-semibold text-foreground">
                            {{ $vehicle->internal_number }}
                        </p>
                        <p class="text-sm text-muted-foreground">
                            رقم اللوحة: {{ $vehicle->plate_number }}
                        </p>
                    </div>
                    @if ($currentPolicy)
                        <div class="text-end">
                            <p class="text-sm text-muted-foreground">بوليصة التأمين الحالية</p>
                            <p class="text-sm font-medium text-foreground">
                                {{ $currentPolicy->policy_number }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ $currentPolicy->insurance_company }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <form
                method="POST"
                action="{{ route('vehicles.incidents.store', $vehicle) }}"
                enctype="multipart/form-data"
                class="space-y-6 rounded-xl border border-border bg-surface p-6 shadow-sm"
            >
                @csrf

                <div>
                    <label for="report_number" class="mb-1 block text-sm font-medium text-foreground">
                        رقم التقرير
                    </label>
                    <input
                        id="report_number"
                        name="report_number"
                        type="text"
                        value="{{ old('report_number') }}"
                        required
                        maxlength="50"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('report_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="incident_date" class="mb-1 block text-sm font-medium text-foreground">
                            تاريخ الحادث
                        </label>
                        <input
                            id="incident_date"
                            name="incident_date"
                            type="date"
                            value="{{ old('incident_date') }}"
                            required
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('incident_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="driver_id" class="mb-1 block text-sm font-medium text-foreground">
                            السائق
                        </label>
                        <select
                            id="driver_id"
                            name="driver_id"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="">بدون سائق</option>
                            @foreach ($drivers as $driver)
                                <option
                                    value="{{ $driver->id }}"
                                    @selected(old('driver_id') == $driver->id)
                                >
                                    {{ $driver->full_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('driver_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="location" class="mb-1 block text-sm font-medium text-foreground">
                        الموقع
                    </label>
                    <input
                        id="location"
                        name="location"
                        type="text"
                        value="{{ old('location') }}"
                        maxlength="255"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-1 block text-sm font-medium text-foreground">
                        الوصف
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="3"
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div>
                        <label for="repair_cost" class="mb-1 block text-sm font-medium text-foreground">
                            تكلفة الإصلاح
                        </label>
                        <input
                            id="repair_cost"
                            name="repair_cost"
                            type="text"
                            inputmode="decimal"
                            value="{{ old('repair_cost') }}"
                            placeholder="0.00"
                            x-mask:function="$money"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                        @error('repair_cost')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="claim_status" class="mb-1 block text-sm font-medium text-foreground">
                            حالة المطالبة
                        </label>
                        <select
                            id="claim_status"
                            name="claim_status"
                            class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                        >
                            <option value="">بدون مطالبة</option>
                            <option value="pending" @selected(old('claim_status') === 'pending')>قيد المراجعة</option>
                            <option value="approved" @selected(old('claim_status') === 'approved')>موافق عليه</option>
                            <option value="rejected" @selected(old('claim_status') === 'rejected')>مرفوض</option>
                            <option value="paid" @selected(old('claim_status') === 'paid')>مدفوع</option>
                        </select>
                        @error('claim_status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                @if ($currentPolicy)
                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="link_insurance_policy"
                            id="link_insurance_policy"
                            value="1"
                            @checked(old('link_insurance_policy'))
                            class="size-4 rounded border-border text-primary focus:ring-primary"
                        >
                        <label for="link_insurance_policy" class="text-sm font-medium text-foreground">
                            ربط بوليصة التأمين الحالية ({{ $currentPolicy->policy_number }})
                        </label>
                    </div>
                @endif

                <div>
                    <label for="photos" class="mb-1 block text-sm font-medium text-foreground">
                        صور الحادث
                    </label>
                    <input
                        id="photos"
                        name="photos[]"
                        type="file"
                        accept="image/*"
                        multiple
                        class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground file:mr-4 file:rounded-md file:border-0 file:bg-primary/10 file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary hover:file:bg-primary/20"
                    >
                    <p class="mt-1 text-xs text-muted-foreground">
                        يمكنك رفع حتى 10 صور، بحد أقصى 5 ميجابايت لكل صورة.
                    </p>
                    @error('photos')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('photos.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button
                        type="submit"
                        class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                    >
                        تسجيل الحادث
                    </button>

                    <a
                        href="{{ route('vehicles.show', $vehicle) }}"
                        class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    >
                        إلغاء
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
