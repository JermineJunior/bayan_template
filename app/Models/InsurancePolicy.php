<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class InsurancePolicy extends Model
{
    protected $fillable = [
        'vehicle_id',
        'policy_number',
        'insurance_company',
        'start_date',
        'end_date',
        'value',
        'is_current',
        'recorded_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'value' => 'decimal:2',
        'is_current' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public static function renew(
        Vehicle $vehicle,
        string $policyNumber,
        string $insuranceCompany,
        string $startDate,
        string $endDate,
        ?float $value,
        User $recordedBy,
    ): self {
        return DB::transaction(function () use ($vehicle, $policyNumber, $insuranceCompany, $startDate, $endDate, $value, $recordedBy) {
            static::where('vehicle_id', $vehicle->id)
                ->where('is_current', true)
                ->update(['is_current' => false]); // insure only one policy could be applied at a given time per vehicle

            return static::create([
                'vehicle_id' => $vehicle->id,
                'policy_number' => $policyNumber,
                'insurance_company' => $insuranceCompany,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'value' => $value,
                'is_current' => true,
                'recorded_by' => $recordedBy->id,
            ]);
        });
    }

    public function getIsExpiredAttribute(): bool
    {
        if (! $this->end_date) {
            return false;
        }

        return $this->end_date->isPast();
    }

    /** Days until expiry — negative means already expired */
    public function getDaysUntilExpiryAttribute(): int
    {
        return now()->diffInDays($this->end_date, false);
    }
}
