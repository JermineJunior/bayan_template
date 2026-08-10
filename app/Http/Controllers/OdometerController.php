<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidOdometerReadingException;
use App\Models\Vehicle;
use App\Services\OdometerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OdometerController extends Controller
{
    /**
     * Record a new odometer reading for the given vehicle.
     *
     * The only allowed writer of vehicles.current_odometer is
     * OdometerService::record(); the vehicle is never updated directly here.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $validated = $request->validate([
            'reading' => ['required', 'numeric'],
            'is_correction' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'required_if:is_correction,true', 'max:255'],
        ]);

        try {
            app(OdometerService::class)->record(
                $vehicle,
                (float) $validated['reading'],
                $request->user(),
                $request->boolean('is_correction'),
                $validated['note'] ?? null,
            );
        } catch (InvalidOdometerReadingException $e) {
            return back()->withErrors(['reading' => $e->getMessage()]);
        }

        flash()->success('تم تسجيل القراءة بنجاح');

        return back();
    }
}
