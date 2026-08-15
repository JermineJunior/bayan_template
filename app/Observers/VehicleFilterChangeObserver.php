<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\VehicleFilterChange;

class VehicleFilterChangeObserver
{
    /*  when a VehicleFilterChange is logged
    * an expense with the amount is created
    */
    public function created(VehicleFilterChange $change): void
    {
        Expense::create([
            'vehicle_id' => $change->vehicle_id,
            'expense_type' => 'filter',
            'amount' => $change->cost,
            'expense_date' => $change->last_change->toDateString(),
            'description' => "تغيير فلتر — {$change->filter->filter_name}",
            'sourceable_type' => VehicleFilterChange::class,
            'sourceable_id' => $change->id,
            'recorded_by' => $change->recorded_by,
        ]);
    }
}
