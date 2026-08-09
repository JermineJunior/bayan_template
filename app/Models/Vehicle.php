<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Vehicle extends Model
{
    /** @use HasFactory<VehicleFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'internal_number',
        'plate_number',
        'type',
        'category',
        'model',
        'manufacture_year',
        'color',
        'chassis_number',
        'engine_number',
        'fuel_type',
        'engine_capacity',
        'management_id',
        'status',
        'current_mileage',
        'operating_hours',
        'image_path',
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'current_mileage' => 'decimal:2',
        'operating_hours' => 'decimal:2',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
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
