@php
    $isEdit = filled($vehicle);
@endphp
<form
    method="POST"
    action="{{ $isEdit ? route('vehicles.update', $vehicle) : route('vehicles.store') }}"
    enctype="multipart/form-data"
    class="max-w-3xl space-y-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="grid gap-6 sm:grid-cols-2">
        <div>
            <label for="internal_number" class="mb-1 block text-sm font-medium text-foreground">
                الرقم الداخلي
            </label>
            <input
                id="internal_number"
                name="internal_number"
                type="text"
                value="{{ old('internal_number', $vehicle?->internal_number) }}"
                required
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('internal_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="plate_number" class="mb-1 block text-sm font-medium text-foreground">
                رقم اللوحة
            </label>
            <input
                id="plate_number"
                name="plate_number"
                type="text"
                value="{{ old('plate_number', $vehicle?->plate_number) }}"
                required
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('plate_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="type" class="mb-1 block text-sm font-medium text-foreground">
                النوع
            </label>
            <input
                id="type"
                name="type"
                type="text"
                value="{{ old('type', $vehicle?->type) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="category" class="mb-1 block text-sm font-medium text-foreground">
                الفئة
            </label>
            <input
                id="category"
                name="category"
                type="text"
                value="{{ old('category', $vehicle?->category) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('category')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="model" class="mb-1 block text-sm font-medium text-foreground">
                الموديل
            </label>
            <input
                id="model"
                name="model"
                type="text"
                value="{{ old('model', $vehicle?->model) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('model')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="manufacture_year" class="mb-1 block text-sm font-medium text-foreground">
                سنة الصنع
            </label>
            <input
                id="manufacture_year"
                name="manufacture_year"
                type="number"
                min="1950"
                max="{{ date('Y') + 1 }}"
                value="{{ old('manufacture_year', $vehicle?->manufacture_year) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('manufacture_year')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="color" class="mb-1 block text-sm font-medium text-foreground">
                اللون
            </label>
            <input
                id="color"
                name="color"
                type="text"
                value="{{ old('color', $vehicle?->color) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('color')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="chassis_number" class="mb-1 block text-sm font-medium text-foreground">
                رقم الهيكل
            </label>
            <input
                id="chassis_number"
                name="chassis_number"
                type="text"
                value="{{ old('chassis_number', $vehicle?->chassis_number) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('chassis_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="engine_number" class="mb-1 block text-sm font-medium text-foreground">
                رقم المحرك
            </label>
            <input
                id="engine_number"
                name="engine_number"
                type="text"
                value="{{ old('engine_number', $vehicle?->engine_number) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('engine_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="fuel_type" class="mb-1 block text-sm font-medium text-foreground">
                نوع الوقود
            </label>
            <select
                id="fuel_type"
                name="fuel_type"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">اختر النوع</option>
                <option value="gasoline" @selected(old('fuel_type', $vehicle?->fuel_type) === 'gasoline')>بنزين</option>
                <option value="diesel" @selected(old('fuel_type', $vehicle?->fuel_type) === 'diesel')>ديزل</option>
            </select>
            @error('fuel_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="engine_capacity" class="mb-1 block text-sm font-medium text-foreground">
                سعة المحرك
            </label>
            <input
                id="engine_capacity"
                name="engine_capacity"
                type="text"
                value="{{ old('engine_capacity', $vehicle?->engine_capacity) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('engine_capacity')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="management_id" class="mb-1 block text-sm font-medium text-foreground">
                الإدارة
            </label>
            <select
                id="management_id"
                name="management_id"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="">بدون إدارة</option>
                @foreach ($managements as $management)
                    <option
                        value="{{ $management->id }}"
                        @selected(old('management_id', $vehicle?->management_id) == $management->id)
                    >
                        {{ $management->name }}
                    </option>
                @endforeach
            </select>
            @error('management_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="status" class="mb-1 block text-sm font-medium text-foreground">
                الحالة
            </label>
            <select
                id="status"
                name="status"
                required
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
                <option value="active" @selected(old('status', $vehicle?->status) === 'active')>نشط</option>
                <option value="maintenance" @selected(old('status', $vehicle?->status) === 'maintenance')>صيانة</option>
                <option value="stopped" @selected(old('status', $vehicle?->status) === 'stopped')>متوقفة</option>
                <option value="sold" @selected(old('status', $vehicle?->status) === 'sold')>مباعة</option>
                <option value="out_of_service" @selected(old('status', $vehicle?->status) === 'out_of_service')>خارج الخدمة</option>
            </select>
            @error('status')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @unless ($isEdit)
            <div>
                <label for="initial_odometer" class="mb-1 block text-sm font-medium text-foreground">
                    القراءة الحالية عند الإضافة
                </label>
                <input
                    id="initial_odometer"
                    name="initial_odometer"
                    type="number"
                    step="0.01"
                    min="0"
                    value="{{ old('initial_odometer') }}"
                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
                @error('initial_odometer')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endunless

        <div>
            <label for="operating_hours" class="mb-1 block text-sm font-medium text-foreground">
                ساعات التشغيل
            </label>
            <input
                id="operating_hours"
                name="operating_hours"
                type="number"
                step="0.01"
                min="0"
                value="{{ old('operating_hours', $vehicle?->operating_hours) }}"
                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
            >
            @error('operating_hours')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="image" class="mb-1 block text-sm font-medium text-foreground">
            صورة المركبة
        </label>

        @if ($vehicle?->image_url)
            <div class="mb-2">
                <img
                    src="{{ $vehicle->image_url }}"
                    alt="{{ $vehicle->internal_number }}"
                    class="h-24 w-32 rounded-md border border-border object-cover"
                >
            </div>
        @endif

        <input
            id="image"
            name="image"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            class="block w-full text-sm text-foreground file:me-3 file:rounded-md file:border-0 file:bg-muted file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-muted/70"
        >
        @error('image')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
        >
            {{ $isEdit ? 'حفظ المركبة' : 'إنشاء المركبة' }}
        </button>

        <a
            href="{{ route('vehicles.index') }}"
            class="rounded-md border border-border px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
        >
            إلغاء
        </a>
    </div>
</form>
