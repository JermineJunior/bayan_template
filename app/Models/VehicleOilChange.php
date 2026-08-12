<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class VehicleOilChange extends Model
{
     use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'oil_id',
        'last_change',
        'odometer_when_change',
        'next_change_odometer',
        'recorded_by',
    ];

    protected $casts = [
        'last_change' => 'date',
        'odometer_when_change' => 'decimal:2',
        'next_change_odometer' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function oil(): BelongsTo
    {
        return $this->belongsTo(Oil::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Log a new oil change. This is the ONLY place next_change_odometer gets computed —
     * always create records through this method, never Model::create() directly, or the
     * stored next_change_odometer won't reflect the oil's lifespan at the time of change.
     */
    public static function record(
        Vehicle $vehicle,
        Oil $oil,
        string $lastChangeDate,
        float $odometerWhenChange,
        User $recordedBy,
    ): self {
        return static::create([
            'vehicle_id' => $vehicle->id,
            'oil_id' => $oil->id,
            'last_change' => $lastChangeDate,
            'odometer_when_change' => $odometerWhenChange,
            'next_change_odometer' => $odometerWhenChange + (float) $oil->oil_life,
            'recorded_by' => $recordedBy->id,
        ]);
    }
     /**
     * Km remaining until the next change, based on the vehicle's CURRENT odometer.
     * Computed live every time — never stored, since it changes whenever the vehicle
     * odometer moves, unlike next_change_odometer which is fixed at the time of change.
     * Negative means overdue.
     */
    public function getRemainingChangeAttribute(): float
    {
        return (float) $this->next_change_odometer - (float) $this->vehicle->current_odometer;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->remaining_change < 0;
    }
}
