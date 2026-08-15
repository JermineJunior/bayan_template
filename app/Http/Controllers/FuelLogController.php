<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FuelLogController extends Controller
{
    /**
     * Display a paginated listing of fuel logs across all vehicles.
     */
    public function index(Request $request): View
    {
        $fuelLogs = FuelLog::query()
            ->with(['vehicle', 'driver'])
            ->when($request->filled('vehicle_id'), function ($query) use ($request) {
                $query->where('vehicle_id', (int) $request->query('vehicle_id'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('filled_at', '>=', (string) $request->query('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('filled_at', '<=', (string) $request->query('date_to'));
            })
            ->latest('filled_at')
            ->paginate(10)
            ->withQueryString();

        return view('fuel-logs.index', [
            'fuelLogs' => $fuelLogs,
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    /**
     * Show the form for creating a fuel log for the given vehicle.
     */
    public function create(Vehicle $vehicle): View
    {
        return view('fuel-logs.create', [
            'vehicle' => $vehicle,
            'lastFuelLog' => $vehicle->fuelLogs()->first(),
            'drivers' => Driver::query()
                ->with('currentAssignment.vehicle')
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get(),
        ]);
    }

    /**
     * Store a newly created fuel log.
     *
     * total_value is computed in PHP (liters * price_per_liter - discount)
     * inside FuelLog::record() and is never accepted as input. fuel_logs
     * form their own odometer sequence — this feature never touches
     * vehicles.current_odometer.
     */
    public function store(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->merge([
            'price_per_liter' => str_replace(',', '', $request->input('price_per_liter')),
            'discount' => $request->filled('discount') ? str_replace(',', '', $request->input('discount')) : null,
        ]);

        $validated = $request->validate([
            'filled_at' => ['required', 'date'],
            'fuel_type' => ['nullable', Rule::in(['gasoline', 'diesel'])],
            'liters' => ['required', 'numeric', 'min:0.01'],
            'price_per_liter' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'odometer_reading' => ['required', 'numeric'],
            'driver_id' => ['nullable', 'integer', Rule::exists('drivers', 'id')],
            'station' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
        ]);

        $lastReading = FuelLog::where('vehicle_id', $vehicle->id)
            ->orderByDesc('filled_at')
            ->value('odometer_reading');

        if ($lastReading !== null && (float) $validated['odometer_reading'] <= (float) $lastReading) {
            return back()
                ->withInput()
                ->withErrors([
                    'odometer_reading' => "قراءة العداد يجب أن تكون أكبر من قراءة آخر تعبئة مسجلة ($lastReading).",
                ]);
        }

        FuelLog::record(
            vehicle: $vehicle,
            filledAt: $validated['filled_at'],
            liters: (float) $validated['liters'],
            pricePerLiter: (float) $validated['price_per_liter'],
            odometerReading: (float) $validated['odometer_reading'],
            recordedBy: $request->user(),
            fuelType: $validated['fuel_type'] ?? null,
            discount: $validated['discount'] ?? null,
            driver: $validated['driver_id'] ? Driver::find($validated['driver_id']) : null,
            station: $validated['station'] ?? null,
            invoiceNumber: $validated['invoice_number'] ?? null,
        );

        flash()->success('تم تسجيل عملية التعبئة بنجاح.');

        return redirect()
            ->route('vehicles.show', $vehicle);
    }

    /**
     * Remove the given fuel log.
     */
    public function destroy(FuelLog $fuelLog): RedirectResponse
    {
        $fuelLog->delete();

        flash()->success('تم حذف عملية التعبئة.');

        return back();
    }
}
