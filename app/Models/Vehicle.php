<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
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
        'stopped_at',
        'current_odometer',
        'operating_hours',
        'image_path',
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'current_odometer' => 'decimal:2',
        'operating_hours' => 'decimal:2',
        'stopped_at' => 'datetime',
    ];

    /**
     * Keep stopped_at in sync with the status field: entering "stopped" stamps
     * the timestamp, leaving it clears the timestamp. Runs on every Eloquent
     * save so it also covers paths other than the vehicle form.
     */
    protected static function booted(): void
    {
        static::saving(function (Vehicle $vehicle) {
            $wasStopped = $vehicle->exists && $vehicle->getOriginal('status') === 'stopped';

            if ($vehicle->status === 'stopped' && ! $wasStopped) {
                $vehicle->stopped_at = now();
            } elseif ($vehicle->status !== 'stopped' && $wasStopped) {
                $vehicle->stopped_at = null;
            }
        });
    }

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

    /** كل سجلات قراءة العداد التاريخية لهذي المركبة (الأحدث أولًا) */
    public function odometerLogs(): HasMany
    {
        return $this->hasMany(OdometerLog::class)->latest('recorded_at');
    }

    /** كل عمليات تعبئة الوقود لهذي المركبة (الأحدث أولًا) */
    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FuelLog::class)->latest('filled_at');
    }

    /** All oil change records for this vehicle, across all oils used (newest first) */
    public function oilChanges(): HasMany
    {
        return $this->hasMany(VehicleOilChange::class)->latest('last_change');
    }

    /**
     * الحالة الحالية لكل نوع زيت (أحدث سجل لكل نوع). عندما تكون علاقة
     * oilChanges محمّلة مسبقًا (مثلما تفعل لوحة التحكم عبر eager loading)
     * يُعاد استخدام نفس البيانات بدلًا من استعلام جديد لكل مركبة.
     */
    public function currentOilStatus(): Collection
    {
        $changes = $this->relationLoaded('oilChanges')
            ? $this->oilChanges
            : $this->oilChanges()->with('oil')->get();

        return $changes
            ->unique(fn (VehicleOilChange $change) => $change->oil->oil_type)
            ->values();
    }

    /** All filter change records for this vehicle, across all filters used (newest first) */
    public function filterChanges(): HasMany
    {
        return $this->hasMany(VehicleFilterChange::class)->latest('last_change');
    }

    /**
     * الحالة الحالية لكل نوع فلتر (أحدث سجل لكل نوع). مثل currentOilStatus()
     * يعيد استخدام علاقة filterChanges المحمّلة مسبقًا إن وُجدت.
     */
    public function currentFilterStatus(): Collection
    {
        $changes = $this->relationLoaded('filterChanges')
            ? $this->filterChanges
            : $this->filterChanges()->with('filter')->get();

        return $changes
            ->unique(fn (VehicleFilterChange $change) => $change->filter->filter_type)
            ->values();
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

    // get all the car insurance records
    public function insurancePolicies(): HasMany
    {
        return $this->hasMany(InsurancePolicy::class)->latest('start_date');
    }

    /** The active policy right now, if any */
    public function currentInsurancePolicy(): HasOne
    {
        return $this->hasOne(InsurancePolicy::class)->where('is_current', true);
    }

    /** التكلفة لكل كيلومتر بناءً على سجلات التعبئة (null لو ما فيه مسافة كافية) */
    public function fuelCostPerKilometer(): ?float
    {
        $logs = $this->fuelLogs;

        if ($logs->count() < 2) {
            return null;
        }

        $totalCost = (float) $logs->sum('total_value');
        $distance = (float) ($logs->first()->odometer_reading - $logs->last()->odometer_reading);

        if ($totalCost <= 0 || $distance <= 0) {
            return null;
        }

        return round($totalCost / $distance, 2);
    }

    /** متوسط استهلاك الوقود شهريًا باللترات (null لو ما فيه سجلات) */
    public function averageMonthlyFuelConsumption(): ?float
    {
        $logs = $this->fuelLogs;

        if ($logs->isEmpty()) {
            return null;
        }

        $totalLiters = (float) $logs->sum('liters');
        $days = (float) $logs->last()->filled_at->diffInDays($logs->first()->filled_at);
        $months = max(1.0, $days / 30.44);

        return round($totalLiters / $months, 2);
    }

    // سجل سائقي المركبة (تاريخي)
    public function historicalDrivers()
    {
        return Driver::whereIn('id', $this->driverAssignments()->pluck('driver_id'))->get();
    }

    /** All incidents recorded for this vehicle (newest first) */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class)->latest('incident_date');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class)->latest('expense_date');
    }
}
