<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverViolation;
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
        ]);
    }

    /**
     * Log a new violation for the given driver.
     */
    public function store(Request $request, Driver $driver): RedirectResponse
    {
        $validated = $request->validate([
            'violation_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        DriverViolation::create([
            'driver_id' => $driver->id,
            'violation_date' => $validated['violation_date'],
            'description' => $validated['description'],
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
        ]);
    }

    /**
     * Update the given violation.
     */
    public function update(Request $request, Driver $driver, DriverViolation $violation): RedirectResponse
    {
        abort_if($violation->driver_id !== $driver->id, 404);

        $validated = $request->validate([
            'violation_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $violation->update([
            'violation_date' => $validated['violation_date'],
            'description' => $validated['description'],
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
