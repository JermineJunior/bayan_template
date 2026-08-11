<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'filled_at',
        'fuel_type',
        'liters',
        'price_per_liter',
        'discount',
        'odometer_reading',
        'station',
        'invoice_number',
        'recorded_by',
    ];

    protected $casts = [
        'filled_at' => 'datetime',
        'liters' => 'decimal:2',
        'price_per_liter' => 'decimal:3',
        'discount' => 'decimal:2',
        'total_value' => 'decimal:2',
        'odometer_reading' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // logic and calculations
    /**
     * آخر تعبئة سابقة لنفس المركبة قبل هذا السجل — أساس حساب الاستهلاك.
     * ملاحظة: تسلسل مستقل عن odometer_logs عمدًا (قرار مبسّط: fuel_logs تقارن بنفسها بس).
     */
    public function previousLog(): ?self
    {
        return static::where('vehicle_id', $this->vehicle_id)
            ->where('filled_at', '<', $this->filled_at)
            ->orderByDesc('filled_at')
            ->first();
    }

    /** المسافة المقطوعة منذ آخر تعبئة (null لو هذي أول تعبئة للمركبة) */
    public function getDistanceSinceLastFillAttribute(): ?float
    {
        $previous = $this->previousLog();

        return $previous ? (float) ($this->odometer_reading - $previous->odometer_reading) : null;
    }

    /** معدل الاستهلاك كم/لتر منذ آخر تعبئة (null لو ما فيه تعبئة سابقة أو المسافة صفر) */
    public function getConsumptionRateAttribute(): ?float
    {
        $distance = $this->distance_since_last_fill;

        if ($distance === null || $distance <= 0 || $this->liters <= 0) {
            return null;
        }

        return round($distance / (float) $this->liters, 2);
    }
}
