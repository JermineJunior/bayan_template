<div x-show="tab === 'odometer'" x-cloak role="tabpanel">
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-1 text-sm font-semibold text-foreground">
                    قراءة العداد
                </h2>
                <p class="text-xs text-muted-foreground">القراءة الحالية</p>
                <p class="mt-1 text-4xl font-bold tracking-tight text-foreground">
                    {{ $vehicle->current_odometer !== null ? number_format((float) $vehicle->current_odometer, 0).' كم' : '—' }}
                </p>
                <p class="mt-2 text-xs text-muted-foreground">
                    تاريخ آخر تحديث للعداد
                </p>
                <p class="mt-1 text-2xl font-bold tracking-tight text-foreground">
                    {{ $vehicle->odometer_last_updated_at?->format('Y-m-d') ?? $vehicle->created_at->format('Y-m-d') }}
                </p>

                @can('vehicles.edit')
                    <form
                        method="POST"
                        action="{{ route('vehicles.odometer.store', $vehicle) }}"
                        class="mt-6 space-y-4 border-t border-border pt-6"
                        x-data="{ correction: {{ old('is_correction') ? 'true' : 'false' }} }"
                    >
                        @csrf
                        <div>
                            <label for="reading" class="mb-1 block text-sm font-medium text-foreground">
                                تسجيل قراءة جديدة
                            </label>
                            <input
                                id="reading"
                                name="reading"
                                type="number"
                                step="0.01"
                                min="0"
                                value="{{ old('reading') }}"
                                required
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >
                            @error('reading')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @can('odometer.correct')
                            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                                <input
                                    type="checkbox"
                                    name="is_correction"
                                    value="1"
                                    x-model="correction"
                                    class="size-4 rounded border-border text-primary focus:ring-primary"
                                >
                                هذا تصحيح لخطأ سابق
                            </label>
                        @endcan

                        <div x-show="correction" x-cloak>
                            <label for="note" class="mb-1 block text-sm font-medium text-foreground">
                                سبب التصحيح
                            </label>
                            <textarea
                                id="note"
                                name="note"
                                rows="2"
                                :required="correction"
                                class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                            >{{ old('note') }}</textarea>
                            @error('note')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button
                            type="submit"
                            class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                        >
                            تسجيل القراءة
                        </button>
                    </form>
                @endcan
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    سجل قراءات العداد
                </h2>

                @if ($vehicle->odometerLogs->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border text-sm">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        القراءة
                                    </th>
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        التاريخ والوقت
                                    </th>
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        سجّلها
                                    </th>
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($vehicle->odometerLogs as $log)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-foreground">
                                            {{ number_format((float) $log->reading, 0) }} كم
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ $log->recorded_at?->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ $log->recordedBy?->name ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($log->is_correction)
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700"
                                                >
                                                    تصحيح ⚠
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if ($log->is_correction && $log->note)
                                        <tr>
                                            <td colspan="4" class="px-4 pb-3 text-xs text-muted-foreground">
                                                سبب التصحيح: {{ $log->note }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-muted-foreground">
                        لا يوجد سجل قراءات لهذه المركبة.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
