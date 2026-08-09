<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard with live stats.
     */
    public function index(): View
    {
        return view('dashboard', [
            'totalVehicles' => Vehicle::count(),
            'activeVehicles' => Vehicle::where('status', 'active')->count(),
            'maintenanceVehicles' => Vehicle::where('status', 'maintenance')->count(),
            'licensesExpiringSoon' => Driver::whereBetween('license_expiry_date', [
                now()->startOfDay(),
                now()->addDays(30)->endOfDay(),
            ])->count(),
        ]);
    }
}
