<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFilterChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'filter_id',
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

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function record(
        Vehicle $vehicle,
        Filter $filter,
        string $lastChangeDate,
        float $odometerWhenChange,
        User $recordedBy,
    ): self {
        return static::create([
            'vehicle_id' => $vehicle->id,
            'filter_id' => $filter->id,
            'last_change' => $lastChangeDate,
            'odometer_when_change' => $odometerWhenChange,
            'next_change_odometer' => $odometerWhenChange + (float) $filter->filter_life,
            'recorded_by' => $recordedBy->id,
        ]);
    }

    public function getRemainingChangeAttribute(): float
    {
        return (float) $this->next_change_odometer - (float) $this->vehicle->current_odometer;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->remaining_change < 0;
    }
}
