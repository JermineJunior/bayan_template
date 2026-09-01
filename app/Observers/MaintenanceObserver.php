<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\Maintenance;

class MaintenanceObserver
{
    /**
     * Record the maintenance expense as soon as the order is created. The
     * amount starts as the labour cost only; it is later augmented by the
     * totals of any issue invoices (spare parts) tied to this maintenance.
     */
    public function created(Maintenance $maintenance): void
    {
        Expense::create([
            'vehicle_id' => $maintenance->vehicle_id,
            'expense_type' => 'maintenance',
            'amount' => (float) $maintenance->labor_cost,
            'expense_date' => $maintenance->start_date?->toDateString() ?? now()->toDateString(),
            'description' => $maintenance->reason,
            'sourceable_type' => Maintenance::class,
            'sourceable_id' => $maintenance->id,
            'recorded_by' => $maintenance->created_by,
        ]);
    }
}
