<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'license_plate',
        'model',
        'year',
        'fuel_type',
        'engine_capacity',
        'management_id',
        'current_driver_id',
        'status',
        'current_mileage',
        'operating_hours',
        'image_path',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    /** كل سجلات الإسناد التاريخية لهذي المركبة */
    public function driverAssignments(): HasMany
    {
        return $this->hasMany(VehicleDriver::class);
    }

    /** الإسناد الحالي فقط (قد يكون null لو المركبة عامة بلا سائق) */
    public function currentAssignment(): HasOne
    {
        return $this->hasOne(VehicleDriver::class)->where('is_current', true);
    }

    public function currentDriver(): ?Driver
    {
        return $this->currentAssignment?->driver;
    }

    // سجل سائقي المركبة (تاريخي)
    public function historicalDrivers()
    {
        return Driver::whereIn('id', $this->driverAssignments()->pluck('driver_id'))->get();
    }
}
