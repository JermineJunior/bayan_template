<?php

namespace App\Observers;

use App\Models\DriverViolation;
use App\Models\Expense;

class DriverViolationObserver
{
    /*  when a DriverViolation is logged
    * an expense with the amount is created
    */

    public function created(DriverViolation $violation): void
    {
        if ($violation->vehicle_id === null || $violation->amount === null) {
            return;
        }

        Expense::create([
            'vehicle_id' => $violation->vehicle_id,
            'expense_type' => 'violations',
            'amount' => $violation->amount,
            'expense_date' => $violation->violation_date->toDateString(),
            'description' => "مخالفة — {$violation->description}",
            'sourceable_type' => DriverViolation::class,
            'sourceable_id' => $violation->id,
            'recorded_by' => $violation->recorded_by,
        ]);
    }
}
