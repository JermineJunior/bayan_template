<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Maintenance extends Model
{
    protected $fillable = [
        'maintenance_number',
        'vehicle_id',
        'date',
        'start_date',
        'end_date',
        'odometer_reading',
        'reason',
        'workshop',
        'technical',
        'labor_cost',
        'spare_cost',
        'total_cost',
        'type',
        'status',
        'note',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'odometer_reading' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'spare_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public static function generateMaintenanceNumber($date = null): string
    {
        $date = $date ? Date::parse($date) : now();

        $prefix = 'MO-' . $date->format('Y');

        $last = self::query()
            ->where('maintenance_number', 'like', $prefix . '-%')
            ->orderByDesc('maintenance_number')
            ->first();

        $sequence = $last ? ((int) substr($last->maintenance_number, -5)) + 1 : 1;

        return $prefix . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
