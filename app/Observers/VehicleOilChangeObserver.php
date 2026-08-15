<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\VehicleOilChange;

class VehicleOilChangeObserver
{
    /*  when a VehicleOilChange is logged
    * an expense with the amount is created
    */
    public function created(VehicleOilChange $change): void
    {
        Expense::create([
            'vehicle_id' => $change->vehicle_id,
            'expense_type' => 'oil',
            'amount' => $change->cost,
            'expense_date' => $change->last_change->toDateString(),
            'description' => "تغيير زيت — {$change->oil->oil_name}",
            'sourceable_type' => VehicleOilChange::class,
            'sourceable_id' => $change->id,
            'recorded_by' => $change->recorded_by,
        ]);
    }
}
