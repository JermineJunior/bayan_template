<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\Maintenance;

class MaintenanceObserver
{
    public function created(Maintenance $maintenance): void
    {
        Expense::create([
            'vehicle_id' => $maintenance->vehicle_id,
            'expense_type' => 'maintenance',
            'amount' => $maintenance->total_cost,
            'expense_date' => $maintenance->end_at?->toDateString() ?? now()->toDateString(),
            'description' => $maintenance->reason, 
            'sourceable_id' => $maintenance->id,
            'recorded_by' => $maintenance->created_by,
        ]);
    }
}
