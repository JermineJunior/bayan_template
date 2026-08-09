<?php

namespace App\Http\Controllers\BasicData;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\VehicleDriver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehicleDriverController extends Controller
{
    /**
     * Assign a driver to the given vehicle.
     *
     * The driver may already be assigned to another vehicle — reassignment is
     * a supported, expected action and is handled atomically by
     * VehicleDriver::assign().
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'driver_id' => ['required', 'integer', Rule::exists('drivers', 'id')],
        ]);

        $driver = Driver::findOrFail($validated['driver_id']);

        $reassigned = $driver->currentAssignment()->exists();

        VehicleDriver::assign($vehicle, $driver, $request->user()->id);

        if ($reassigned) {
            flash()->success("تم إسناد السائق {$driver->full_name} للمركبة بنجاح (تم نقله من مركبته السابقة).");
        } else {
            flash()->success('تم إسناد السائق للمركبة بنجاح.');
        }

        return redirect()
            ->route('vehicles.show', $vehicle);
    }

    /**
     * End a current assignment without a replacement.
     */
    public function destroy(VehicleDriver $assignment): RedirectResponse
    {
        if (! $assignment->is_current) {
            flash()->error('لا يمكن إنهاء إسناد منتهي بالفعل.');

            return back();
        }

        $assignment->endAssignment();

        flash()->success('تم إنهاء الإسناد بنجاح.');

        return back();
    }
}
