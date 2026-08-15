<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\FuelLog;

class FuelLogObserver
{
/*  when a VehicleFuelLog is created
    * an expense with the amount is created
    */

    public function created(FuelLog $fuelLog): void
    {
        Expense::create([
            'vehicle_id' => $fuelLog->vehicle_id,
            'expense_type' => 'fuel',
            'amount' => $fuelLog->total_value,
            'expense_date' => $fuelLog->filled_at->toDateString(),
            'description' => "Fuel fill-up — {$fuelLog->liters} L",
            'sourceable_type' => FuelLog::class,
            'sourceable_id' => $fuelLog->id,
            'recorded_by' => $fuelLog->recorded_by,
        ]);
    }
}
