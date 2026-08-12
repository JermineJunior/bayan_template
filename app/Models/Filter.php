<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Filter extends Model
{
    protected $fillable = [
        'filter_name',
        'filter_code',
        'filter_type',
        'filter_life',
    ];

    protected $casts = [
        'filter_life' => 'decimal:2',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(VehicleFilterChange::class);
    }
}
