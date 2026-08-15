<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Vehicle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $maintenances = Maintenance::query()
            ->when(filled('search'), function ($q) use ($request) {
                $q->where('maintenance_number', 'like', "%{$request->search}%");
            })
            ->when($request->type, function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->status, function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByDesc('maintenance_number')
            ->paginate(10);

        return view('maintenance.index', [
            'maintenances' => $maintenances,
        ]);
    }

    public function create()
    {
        return view('maintenance.create', [
            'maintenance' => null,
            'vehicles' => Vehicle::where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validate([
                'maintenance_number' => ['required', 'string', 'max:255', 'unique:maintenances,maintenance_number'],
                'vehicle_id' => ['required', 'exists:vehicles,id'],
                'start_date' => ['required', 'date', 'after_or_equal:date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'odometer_reading' => ['required', 'numeric', 'min:0'],
                'reason' => ['nullable', 'string', 'max:255'],
                'workshop' => ['nullable', 'string', 'max:255'],
                'technical' => ['nullable', 'string', 'max:255'],
                'labor_cost' => ['nullable', 'numeric', 'min:0'],
                'spare_cost' => ['nullable', 'numeric', 'min:0'],
                'total_cost' => ['nullable', 'numeric', 'min:0'],
                'type' => ['required', 'in:periodic,preventive,emergency'],
                'status' => ['required', 'in:draft,pending,in_progress,completed,cancelled'],
                'note' => ['nullable', 'string'],
            ]);

            $validated['created_by'] = Auth::id();
            $validated['date'] = today();

            Maintenance::create($validated);

            Vehicle::find($validated['vehicle_id'])->update(['status' => 'maintenance']);

            DB::commit();

            return redirect()->route('maintenance.index')->with('success', 'تم إنشاء أمر الصيانة بنجاح.');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors('حدث خطا ما ');
        }
    }

    public function show(Maintenance $maintenance)
    {
        $maintenance = $maintenance->load(['vehicle']);

        return view('maintenance.show', [
            'maintenance' => $maintenance->load(['invoices', 'vehicle']),
        ]);
    }

    public function edit(Maintenance $maintenance)
    {
        return view('maintenance.edit', [
            'maintenance' => $maintenance,
            'vehicles' => Vehicle::where('status', 'active')->get(),
        ]);
    }

    public function update(Maintenance $maintenance, Request $request)
    {
        try {
            DB::beginTransaction();
            $validated = $request->validate([
                'maintenance_number' => ['required', 'string', 'max:255', Rule::unique('maintenances', 'maintenance_number')->ignore($maintenance)],
                'vehicle_id' => ['required', 'exists:vehicles,id'],
                'start_date' => ['required', 'date', 'after_or_equal:date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'odometer_reading' => ['required', 'numeric', 'min:0'],
                'reason' => ['nullable', 'string', 'max:255'],
                'workshop' => ['nullable', 'string', 'max:255'],
                'technical' => ['nullable', 'string', 'max:255'],
                'labor_cost' => ['nullable', 'numeric', 'min:0'],
                'spare_cost' => ['nullable', 'numeric', 'min:0'],
                'total_cost' => ['nullable', 'numeric', 'min:0'],
                'type' => ['required', 'in:periodic,preventive,emergency'],
                'status' => ['required', 'in:draft,pending,in_progress,completed,cancelled'],
                'note' => ['nullable', 'string'],
            ]);

            $validated['created_by'] = Auth::id();
            $validated['date'] = today();

            $maintenance->update($validated);

            Vehicle::find($validated['vehicle_id'])->update(['status' => 'maintenance']);

            return redirect()->route('maintenance.index')->with('success', 'تم تعديل امر الصيانة بنجاح');
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withInput()->withErrors('حدث خطا ما ');
        }
    }

    public function destroy(Maintenance $maintenance)
    {
        $maintenance->delete();

        return back()->with('success', 'تم حذف امر الصيانة بنجاح');
    }

    public function getOdometer(Vehicle $vehicle)
    {
        return response()->json([
            'odometer' => $vehicle->current_odometer,
        ]);
    }
}
