<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverViolation extends Model
{
    protected $fillable = [
        'driver_id',
        'violation_date',
        'description',
        'recorded_by',
    ];

    protected $casts = [
        'violation_date' => 'date',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
