<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = [
        'report_number',
        'vehicle_id',
        'driver_id',
        'incident_date',
        'location',
        'description',
        'repair_cost',
        'insurance_policy_id',
        'claim_status',
        'maintenance_order_id',
        'recorded_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'repair_cost' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function insurancePolicy(): BelongsTo
    {
        return $this->belongsTo(InsurancePolicy::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(IncidentPhoto::class);
    }

    public function getHasClaimAttribute(): bool
    {
        return $this->claim_status !== null;
    }
}
