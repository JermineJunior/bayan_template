<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometerLog extends Model
{
    protected $fillable = [
        'vehicle_id',
        'reading',
        'recorded_at',
        'recorded_by',
        'is_correction',
        'note',
    ];

    protected $casts = [
        'reading' => 'decimal:2',
        'recorded_at' => 'datetime',
        'is_correction' => 'boolean',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
