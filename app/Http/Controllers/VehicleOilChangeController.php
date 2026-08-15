<?php

namespace App\Http\Controllers;

use App\Models\Oil;
use App\Models\Vehicle;
use App\Models\VehicleOilChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleOilChangeController extends Controller
{
    /**
     * Show the form for logging an oil change for the given vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        return view('oil-changes.create', [
            'vehicle' => $vehicle,
            'oils' => Oil::orderBy('oil_name')->get(),
        ]);
    }

    /**
     * Log an oil change for the given vehicle.
     *
     * next_change_odometer is computed and stored inside VehicleOilChange::record()
     * (odometer_when_change + oil.oil_life) — never here, and never by hand.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->merge([
            'cost' => $request->filled('cost') ? str_replace(',', '', $request->input('cost')) : null,
        ]);

        $validated = $request->validate([
            'oil_id' => ['required', 'integer', Rule::exists('oils', 'id')],
            'last_change' => ['required', 'date', 'before_or_equal:today'],
            'odometer_when_change' => ['required', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
        ]);

        VehicleOilChange::record(
            $vehicle,
            Oil::findOrFail($validated['oil_id']),
            $validated['last_change'],
            (float) $validated['odometer_when_change'],
            $request->user(),
            (float) $validated['cost'],
        );

        flash()->success('تم تسجيل تغيير الزيت بنجاح.');

        return redirect()
            ->route('vehicles.show', $vehicle);
    }
}
