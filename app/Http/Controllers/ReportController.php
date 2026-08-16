<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverViolation;
use App\Models\Expense;
use App\Models\FuelLog;
use App\Models\Incident;
use App\Models\InsurancePolicy;
use App\Models\Maintenance;
use App\Models\Management;
use App\Models\Vehicle;
use App\Models\VehicleFilterChange;
use App\Models\VehicleOilChange;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Hub page listing every report.
     */
    public function index(): View
    {
        abort_unless(Gate::any([
            'fuel.view',
            'vehicles.view',
            'oil-changes.view',
            'insurance-policies.view',
            'incidents.view',
            'expenses.view',
            'violations.view',
            'maintenance.view',
        ]), 403);

        return view('reports.index');
    }

    /*
    |--------------------------------------------------------------------------
    | 1. Fuel Consumption
    |--------------------------------------------------------------------------
    */

    public function fuelConsumptionForm(): View
    {
        return view('reports.fuel-consumption.form', [
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    public function fuelConsumptionResults(Request $request): View
    {
        $rows = $this->getFuelConsumptionData($request);

        return view('reports.fuel-consumption.results', [
            'rows' => $rows,
            'totalLiters' => $rows->sum('liters'),
            'totalValue' => $rows->sum('total_value'),
        ]);
    }

    public function fuelConsumptionPrint(Request $request): View
    {
        $rows = $this->getFuelConsumptionData($request);

        return view('reports.fuel-consumption.print', [
            'rows' => $rows,
            'totalLiters' => $rows->sum('liters'),
            'totalValue' => $rows->sum('total_value'),
        ]);
    }

    private function getFuelConsumptionData(Request $request): Collection
    {
        return FuelLog::query()
            ->with(['vehicle', 'driver'])
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('filled_at', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('filled_at', '<=', $request->query('to_date')))
            ->latest('filled_at')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Fleet Overview
    |--------------------------------------------------------------------------
    */

    public function fleetOverviewForm(): View
    {
        return view('reports.fleet-overview.form', [
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    public function fleetOverviewResults(Request $request): View
    {
        return view('reports.fleet-overview.results', [
            'rows' => $this->getFleetOverviewData($request),
        ]);
    }

    public function fleetOverviewPrint(Request $request): View
    {
        return view('reports.fleet-overview.print', [
            'rows' => $this->getFleetOverviewData($request),
        ]);
    }

    private function getFleetOverviewData(Request $request): Collection
    {
        return Vehicle::query()
            ->with(['management', 'currentAssignment.driver'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('management_id'), fn ($query) => $query->where('management_id', $request->integer('management_id')))
            ->orderBy('internal_number')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Oil & Filter Change History
    |--------------------------------------------------------------------------
    */

    public function oilFilterChangesForm(): View
    {
        return view('reports.oil-filter-changes.form', [
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    public function oilFilterChangesResults(Request $request): View
    {
        return view('reports.oil-filter-changes.results', [
            'rows' => $this->getOilFilterChangesData($request),
        ]);
    }

    public function oilFilterChangesPrint(Request $request): View
    {
        return view('reports.oil-filter-changes.print', [
            'rows' => $this->getOilFilterChangesData($request),
        ]);
    }

    private function getOilFilterChangesData(Request $request): Collection
    {
        $oilChanges = VehicleOilChange::query()
            ->with(['vehicle', 'oil'])
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('last_change', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('last_change', '<=', $request->query('to_date')))
            ->latest('last_change')
            ->get()
            ->map(fn (VehicleOilChange $change) => (object) [
                'vehicle' => $change->vehicle,
                'type_label' => 'زيت',
                'item_name' => $change->oil?->oil_name ?? '—',
                'last_change' => $change->last_change,
                'odometer_when_change' => $change->odometer_when_change,
                'next_change_odometer' => $change->next_change_odometer,
                'cost' => $change->cost,
            ]);

        $filterChanges = VehicleFilterChange::query()
            ->with(['vehicle', 'filter'])
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('last_change', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('last_change', '<=', $request->query('to_date')))
            ->latest('last_change')
            ->get()
            ->map(fn (VehicleFilterChange $change) => (object) [
                'vehicle' => $change->vehicle,
                'type_label' => 'فلتر',
                'item_name' => $change->filter?->filter_name ?? '—',
                'last_change' => $change->last_change,
                'odometer_when_change' => $change->odometer_when_change,
                'next_change_odometer' => $change->next_change_odometer,
                'cost' => $change->cost,
            ]);

        return $oilChanges
            ->concat($filterChanges)
            ->sortByDesc('last_change')
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | 4. Insurance Status
    |--------------------------------------------------------------------------
    */

    public function insuranceStatusForm(): View
    {
        return view('reports.insurance-status.form');
    }

    public function insuranceStatusResults(Request $request): View
    {
        return view('reports.insurance-status.results', [
            'rows' => $this->getInsuranceStatusData($request),
        ]);
    }

    public function insuranceStatusPrint(Request $request): View
    {
        return view('reports.insurance-status.print', [
            'rows' => $this->getInsuranceStatusData($request),
        ]);
    }

    private function getInsuranceStatusData(Request $request): Collection
    {
        return InsurancePolicy::query()
            ->with('vehicle')
            ->when($request->boolean('is_current'), fn ($query) => $query->where('is_current', true))
            ->when(
                $request->filled('expiring_within_days'),
                fn ($query) => $query->where('end_date', '<=', now()->addDays($request->integer('expiring_within_days'))->endOfDay())
            )
            ->latest('end_date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Incidents Log
    |--------------------------------------------------------------------------
    */

    public function incidentsLogForm(): View
    {
        return view('reports.incidents-log.form', [
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
            'drivers' => Driver::orderBy('full_name')->get(),
        ]);
    }

    public function incidentsLogResults(Request $request): View
    {
        return view('reports.incidents-log.results', [
            'rows' => $this->getIncidentsLogData($request),
        ]);
    }

    public function incidentsLogPrint(Request $request): View
    {
        return view('reports.incidents-log.print', [
            'rows' => $this->getIncidentsLogData($request),
        ]);
    }

    private function getIncidentsLogData(Request $request): Collection
    {
        return Incident::query()
            ->with(['vehicle', 'driver'])
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('driver_id'), fn ($query) => $query->where('driver_id', $request->integer('driver_id')))
            ->when($request->filled('claim_status'), fn ($query) => $query->where('claim_status', $request->query('claim_status')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('incident_date', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('incident_date', '<=', $request->query('to_date')))
            ->latest('incident_date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Expenses
    |--------------------------------------------------------------------------
    */

    public function expensesForm(): View
    {
        return view('reports.expenses.form', [
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    public function expensesResults(Request $request): View
    {
        $rows = $this->getExpensesData($request);

        return view('reports.expenses.results', [
            'rows' => $rows,
            'totalAmount' => $rows->sum('amount'),
        ]);
    }

    public function expensesPrint(Request $request): View
    {
        $rows = $this->getExpensesData($request);

        return view('reports.expenses.print', [
            'rows' => $rows,
            'totalAmount' => $rows->sum('amount'),
        ]);
    }

    private function getExpensesData(Request $request): Collection
    {
        return Expense::query()
            ->with('vehicle')
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('expense_type'), fn ($query) => $query->where('expense_type', $request->query('expense_type')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('expense_date', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('expense_date', '<=', $request->query('to_date')))
            ->latest('expense_date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 7. Driver Violations
    |--------------------------------------------------------------------------
    */

    public function driverViolationsForm(): View
    {
        return view('reports.driver-violations.form', [
            'drivers' => Driver::orderBy('full_name')->get(),
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    public function driverViolationsResults(Request $request): View
    {
        return view('reports.driver-violations.results', [
            'rows' => $this->getDriverViolationsData($request),
        ]);
    }

    public function driverViolationsPrint(Request $request): View
    {
        return view('reports.driver-violations.print', [
            'rows' => $this->getDriverViolationsData($request),
        ]);
    }

    private function getDriverViolationsData(Request $request): Collection
    {
        return DriverViolation::query()
            ->with(['driver', 'vehicle'])
            ->when($request->filled('driver_id'), fn ($query) => $query->where('driver_id', $request->integer('driver_id')))
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('violation_date', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('violation_date', '<=', $request->query('to_date')))
            ->latest('violation_date')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | 8. Maintenance Cost
    |--------------------------------------------------------------------------
    */

    public function maintenanceCostForm(): View
    {
        return view('reports.maintenance-cost.form', [
            'vehicles' => Vehicle::orderBy('internal_number')->get(),
        ]);
    }

    public function maintenanceCostResults(Request $request): View
    {
        $rows = $this->getMaintenanceCostData($request);

        return view('reports.maintenance-cost.results', [
            'rows' => $rows,
            'totalCost' => $rows->sum('total_cost'),
        ]);
    }

    public function maintenanceCostPrint(Request $request): View
    {
        $rows = $this->getMaintenanceCostData($request);

        return view('reports.maintenance-cost.print', [
            'rows' => $rows,
            'totalCost' => $rows->sum('total_cost'),
        ]);
    }

    private function getMaintenanceCostData(Request $request): Collection
    {
        return Maintenance::query()
            ->with('vehicle')
            ->when($request->filled('vehicle_id'), fn ($query) => $query->where('vehicle_id', $request->integer('vehicle_id')))
            ->when($request->filled('from_date'), fn ($query) => $query->whereDate('end_date', '>=', $request->query('from_date')))
            ->when($request->filled('to_date'), fn ($query) => $query->whereDate('end_date', '<=', $request->query('to_date')))
            ->orderByDesc('end_date')
            ->get();
    }
}
