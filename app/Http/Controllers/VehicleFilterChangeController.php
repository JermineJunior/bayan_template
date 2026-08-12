<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use App\Models\Vehicle;
use App\Models\VehicleFilterChange;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VehicleFilterChangeController extends Controller
{
    /**
     * Show the form for logging a filter change for the given vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        return view('filter-changes.create', [
            'vehicle' => $vehicle,
            'filters' => Filter::orderBy('filter_name')->get(),
        ]);
    }

    /**
     * Log a filter change for the given vehicle.
     *
     * next_change_odometer is computed and stored inside VehicleFilterChange::record()
     * (odometer_when_change + filter.filter_life) — never here, and never by hand.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'filter_id' => ['required', 'integer', Rule::exists('filters', 'id')],
            'last_change' => ['required', 'date', 'before_or_equal:today'],
            'odometer_when_change' => ['required', 'numeric', 'min:0'],
        ]);

        VehicleFilterChange::record(
            $vehicle,
            Filter::findOrFail($validated['filter_id']),
            $validated['last_change'],
            (float) $validated['odometer_when_change'],
            $request->user(),
        );

        flash()->success('تم تسجيل تغيير الفلتر بنجاح.');

        return redirect()
            ->route('vehicles.show', $vehicle);
    }
}
