<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\FuelLog;
use App\Models\InsurancePolicy;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard with live stats.
     */
    public function index(): View
    {
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('status', 'active')->count();
        $maintenanceVehicles = Vehicle::where('status', 'maintenance')->count();
        $stoppedVehicles = Vehicle::where('status', 'stopped')->count();
        $licensesExpiringSoon = Driver::whereBetween('license_expiry_date', [
            now()->startOfDay(),
            now()->addDays(30)->endOfDay(),
        ])->count();
        $insurancesExpiringSoon = InsurancePolicy::where('is_current', true)
            ->where('end_date', '<=', now()->addDays(30)->endOfDay())
            ->count();

        $dueOilChanges = Vehicle::whereIn('id', function ($query) {
            $query->select('vehicle_id')
                ->from('vehicle_oil_changes')
                ->join('vehicles', 'vehicles.id', '=', 'vehicle_oil_changes.vehicle_id')
                ->whereNotNull('vehicles.current_odometer')
                ->whereRaw('vehicle_oil_changes.next_change_odometer < vehicles.current_odometer')
                ->distinct();
        })->count();

        $fleetFuelCost = FuelLog::whereMonth('filled_at', now()->month)
            ->whereYear('filled_at', now()->year)
            ->sum(DB::raw('COALESCE(liters, 0) * COALESCE(price_per_liter, 0) - COALESCE(discount, 0)'));

        $vehicleStatusData = Vehicle::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $monthNames = [
            '01' => 'يناير',
            '02' => 'فبراير',
            '03' => 'مارس',
            '04' => 'أبريل',
            '05' => 'مايو',
            '06' => 'يونيو',
            '07' => 'يوليو',
            '08' => 'أغسطس',
            '09' => 'سبتمبر',
            '10' => 'أكتوبر',
            '11' => 'نوفمبر',
            '12' => 'ديسمبر',
        ];

        $monthlyFuelConsumption = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('m');
            $monthLabel = $monthNames[$monthKey] ?? $date->format('M');
            $liters = FuelLog::whereMonth('filled_at', $date->month)
                ->whereYear('filled_at', $date->year)
                ->sum('liters');
            $monthlyFuelConsumption[$monthLabel] = (float) $liters;
        }

        $expiringPolicies = InsurancePolicy::where('is_current', true)
            ->where('end_date', '<=', now()->addDays(30)->endOfDay())
            ->with('vehicle')
            ->orderBy('end_date')
            ->get();

        return view('dashboard', [
            'totalVehicles' => $totalVehicles,
            'activeVehicles' => $activeVehicles,
            'maintenanceVehicles' => $maintenanceVehicles,
            'stoppedVehicles' => $stoppedVehicles,
            'licensesExpiringSoon' => $licensesExpiringSoon,
            'insurancesExpiringSoon' => $insurancesExpiringSoon,
            'dueOilChanges' => $dueOilChanges,
            'fleetFuelCost' => $fleetFuelCost,
            'vehicleStatusData' => $vehicleStatusData,
            'monthlyFuelConsumption' => $monthlyFuelConsumption,
            'expiringPolicies' => $expiringPolicies,
        ]);
    }
}
