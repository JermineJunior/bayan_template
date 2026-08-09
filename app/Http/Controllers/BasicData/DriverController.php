<?php

namespace App\Http\Controllers\BasicData;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    /**
     * Display a paginated listing of the drivers.
     */
    public function index(Request $request): View
    {
        $drivers = Driver::query()
            ->with(['department'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->string('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('national_id', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array((string) $request->string('status'), ['active', 'inactive'], true),
                function ($query) use ($request) {
                    $query->where('status', (string) $request->string('status'));
                }
            )
            ->when((string) $request->string('license') === 'expired', function ($query) {
                $query->where('license_expiry_date', '<', now()->startOfDay());
            })
            ->when((string) $request->string('license') === 'expiring', function ($query) {
                $query->whereBetween('license_expiry_date', [
                    now()->startOfDay(),
                    now()->addDays(30)->endOfDay(),
                ]);
            })
            ->orderBy('full_name')
            ->paginate(10)
            ->withQueryString();

        return view('basic-data.drivers.index', [
            'drivers' => $drivers,
        ]);
    }

    /**
     * Show the form for creating a new driver.
     */
    public function create(): View
    {
        return view('basic-data.drivers.create', [
            'driver' => null,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created driver.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:200'],
            'national_id' => ['required', 'string', 'max:50', Rule::unique('drivers')],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['nullable', 'date'],
            'license_type' => ['nullable', Rule::in(['general', 'private', 'other'])],
            'license_expiry_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        Driver::create($validated);

        flash()->success('تم إنشاء السائق.');

        return redirect()
            ->route('drivers.index');
    }

    /**
     * Show the given driver.
     */
    public function show(Driver $driver): View
    {
        $driver->load(['department', 'currentAssignment.vehicle']);

        return view('basic-data.drivers.show', [
            'driver' => $driver,
        ]);
    }

    /**
     * Show the form for editing the given driver.
     */
    public function edit(Driver $driver): View
    {
        return view('basic-data.drivers.edit', [
            'driver' => $driver,
            'departments' => Department::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the given driver.
     */
    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:200'],
            'national_id' => ['required', 'string', 'max:50', Rule::unique('drivers')->ignore($driver->id)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            'hire_date' => ['nullable', 'date'],
            'license_type' => ['nullable', Rule::in(['general', 'private', 'other'])],
            'license_expiry_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $driver->update($validated);

        flash()->success('تم تحديث السائق.');

        return redirect()
            ->route('drivers.index');
    }

    /**
     * Remove the given driver.
     */
    public function destroy(Driver $driver): RedirectResponse
    {
        if ($driver->vehicleAssignments()->exists()) {
            flash()->error('لا يمكن حذف هذا السائق لوجود مركبات مخصصة له.');

            return redirect()
                ->route('drivers.index');
        }

        $driver->delete();

        flash()->success('تم حذف السائق.');

        return redirect()
            ->route('drivers.index');
    }
}
