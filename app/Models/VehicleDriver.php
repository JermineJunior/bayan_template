<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\DB;

class VehicleDriver extends Pivot
{
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'assignment_date',
        'assigned_by',
        'is_current',
        'ended_at',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'is_current' => 'boolean',
        'ended_at' => 'datetime',
    ];

    /**
     * Assign the given driver to the given vehicle, atomically ending any
     * current assignment on both sides before creating the new one.
     *
     * The DB enforces a single current assignment per vehicle and per driver
     * via generated-column unique indexes, so this must remain the only way
     * rows are created or updated.
     */
    public static function assign(Vehicle $vehicle, Driver $driver, int $assignedByUserId): self
    {
        return DB::transaction(function () use ($vehicle, $driver, $assignedByUserId) {
            $vehicle->currentAssignment()->lockForUpdate()->first()?->endAssignment();
            $driver->currentAssignment()->lockForUpdate()->first()?->endAssignment();

            return self::create([
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'assignment_date' => now()->toDateString(),
                'assigned_by' => $assignedByUserId,
                'is_current' => true,
            ]);
        });
    }

    /**
     * Release this assignment without replacing it.
     */
    public function endAssignment(): void
    {
        $this->update([
            'is_current' => false,
            'ended_at' => now(),
        ]);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
