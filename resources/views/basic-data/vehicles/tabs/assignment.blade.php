<div x-show="tab === 'assign'" x-cloak role="tabpanel">
    <div class="grid gap-6 lg:grid-cols-3">
        <div>
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    السائق الحالي
                </h2>

                @if ($currentDriver)
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-foreground">
                                <a href="{{ route('drivers.show', $currentDriver) }}" class="text-primary hover:underline">
                                    {{ $currentDriver->full_name }}
                                </a>
                            </p>
                            <p class="mt-0.5 text-sm text-muted-foreground">
                                {{ $currentDriver->national_id }}
                            </p>
                        </div>

                        @can('vehicles.end-assignment')
                            <form
                                method="POST"
                                action="{{ route('assignments.destroy', $vehicle->currentAssignment) }}"
                                onsubmit="return confirmForm(this, 'هل تريد إنهاء إسناد هذا السائق؟', 'نعم، إنهاء الإسناد')"
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition-colors hover:bg-red-50"
                                >
                                    إنهاء الإسناد
                                </button>
                            </form>
                        @endcan
                    </div>
                @else
                    <p class="mb-4 text-sm text-muted-foreground">
                        لا يوجد سائق مسند حاليًا.
                    </p>
                @endif

                @can('vehicles.assign')
                    <div class="{{ $currentDriver ? 'mt-6 border-t border-border py-5' : '' }}">
                        <form
                            method="POST"
                            action="{{ route('vehicles.assign-driver', $vehicle) }}"
                            class="flex flex-wrap items-end gap-3"
                        >
                            @csrf
                            <div class="min-w-48 flex-1">
                                <label for="driver_id" class="mb-1 block text-sm font-medium text-foreground">
                                    اختر سائقًا
                                </label>
                                <select
                                    id="driver_id"
                                    name="driver_id"
                                    required
                                    class="w-full rounded-md border border-border bg-background px-3 py-2 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                                >
                                    @foreach ($drivers as $driver)
                                        <option value="{{ $driver->id }}">
                                            {{ $driver->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button
                                type="submit"
                                class="rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                            >
                                {{ $currentDriver ? 'تغيير السائق' : 'إسناد سائق' }}
                            </button>
                        </form>
                        <p class="mt-3 text-xs text-muted-foreground">
                            إذا اخترت سائقًا مسندًا لمركبة أخرى، سيُنقل إلى هذه المركبة تلقائيًا.
                        </p>
                    </div>
                @endcan
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="rounded-xl border border-border bg-surface p-6 shadow-sm">
                <h2 class="mb-4 text-sm font-semibold text-foreground">
                    سجل الإسناد
                </h2>

                @if ($assignments->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-border text-sm">
                            <thead>
                                <tr class="text-xs uppercase tracking-wide text-muted-foreground">
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        السائق
                                    </th>
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        تاريخ الإسناد
                                    </th>
                                    <th class="bg-muted/50 px-4 py-3 text-start font-medium">
                                        تاريخ الإنهاء
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach ($assignments as $assignment)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <a
                                                href="{{ route('drivers.show', $assignment->driver) }}"
                                                class="font-medium text-foreground hover:text-primary"
                                            >
                                                {{ $assignment->driver->full_name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-muted-foreground">
                                            {{ $assignment->assignment_date?->format('Y-m-d') ?? '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($assignment->is_current)
                                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700">
                                                    حالي
                                                </span>
                                            @else
                                                <span class="text-muted-foreground">
                                                    {{ $assignment->ended_at?->format('Y-m-d') ?? '—' }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-muted-foreground">
                        لا يوجد سجل إسناد لهذه المركبة.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
