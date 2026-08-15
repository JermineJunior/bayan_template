<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverViolation;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverViolationController extends Controller
{
    /**
     * Show the form for logging a new violation for the given driver.
     */
    public function create(Driver $driver): View
    {
        return view('violations.create', [
            'driver' => $driver,
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    /**
     * Log a new violation for the given driver.
     */
    public function store(Request $request, Driver $driver): RedirectResponse
    {
        $request->merge([
            'amount' => $request->filled('amount') ? str_replace(',', '', $request->input('amount')) : null,
        ]);

        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'violation_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        DriverViolation::create([
            'driver_id' => $driver->id,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'violation_date' => $validated['violation_date'],
            'description' => $validated['description'],
            'amount' => $validated['amount'] ?? null,
            'recorded_by' => $request->user()->id,
        ]);

        flash()->success('تم تسجيل المخالفة بنجاح.');

        return redirect()
            ->route('drivers.show', $driver);
    }

    /**
     * Show the form for editing the given violation.
     */
    public function edit(Driver $driver, DriverViolation $violation): View
    {
        abort_if($violation->driver_id !== $driver->id, 404);

        return view('violations.edit', [
            'driver' => $driver,
            'violation' => $violation,
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    /**
     * Update the given violation.
     */
    public function update(Request $request, Driver $driver, DriverViolation $violation): RedirectResponse
    {
        abort_if($violation->driver_id !== $driver->id, 404);

        $request->merge([
            'amount' => $request->filled('amount') ? str_replace(',', '', $request->input('amount')) : null,
        ]);

        $validated = $request->validate([
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'violation_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $violation->update([
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'violation_date' => $validated['violation_date'],
            'description' => $validated['description'],
            'amount' => $validated['amount'] ?? null,
        ]);

        flash()->success('تم تحديث المخالفة بنجاح.');

        return redirect()
            ->route('drivers.show', $driver);
    }

    /**
     * Remove the given violation.
     */
    public function destroy(DriverViolation $violation): RedirectResponse
    {
        $violation->delete();

        flash()->success('تم حذف المخالفة.');

        return back();
    }
}
