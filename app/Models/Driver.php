<?php

namespace App\Models;

use Database\Factories\DriverFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    /** @use HasFactory<DriverFactory> */
    use HasFactory;

    protected $fillable = [
        'full_name',
        'national_id',
        'phone_number',
        'department_id',
        'hire_date',
        'license_type',
        'license_expiry_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'license_expiry_date' => 'date',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    /** كل المركبات التي تم إسنادها لهذا السائق (تاريخي) */
    public function vehicleAssignments()
    {
        return $this->hasMany(VehicleDriver::class);
    }

    /** Violation log entries for this driver (newest first) */
    public function violations(): HasMany
    {
        return $this->hasMany(DriverViolation::class)->latest('violation_date');
    }

    /** Incidents this driver was involved in (newest first) */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->latest('incident_date');
    }

    /** المركبة الحالية فقط (قد يكون null لو السائق بلا مركبة حالياً) */
    public function currentAssignment()
    {
        return $this->hasOne(VehicleDriver::class)->where('is_current', true);
    }

    // سجل المركبة الحالية فقط (قد يكون null لو السائق بلا مركبة حالياً)

    public function currentVehicle(): ?Vehicle // could be null if the driver is not currently assigned to any vehicle
    {
        return $this->currentAssignment?->vehicle;
    }
}
