<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InsurancePolicyController extends Controller
{
    /**
     * Show the form for registering a new/renewed insurance policy for the given vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        return view('insurance-policies.create', [
            'vehicle' => $vehicle,
            'currentPolicy' => $vehicle->currentInsurancePolicy,
        ]);
    }

    /**
     * Register a new/renewed insurance policy for the given vehicle.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->merge([
            'value' => $request->filled('value') ? str_replace(',', '', $request->input('value')) : null,
        ]);

        $validated = $request->validate([
            'policy_number' => ['required', 'string', 'max:50'],
            'insurance_company' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);

        InsurancePolicy::renew(
            $vehicle,
            $validated['policy_number'],
            $validated['insurance_company'],
            $validated['start_date'],
            $validated['end_date'],
            $validated['value'] ?? null,
            $request->user(),
        );

        flash()->success('تم تسجيل بوليصة التأمين بنجاح.');

        return redirect()
            ->route('vehicles.show', $vehicle);
    }
}
