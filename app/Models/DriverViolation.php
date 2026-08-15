<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverViolation extends Model
{
    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'violation_date',
        'description',
        'amount',
        'recorded_by',
    ];

    protected $casts = [
        'violation_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
