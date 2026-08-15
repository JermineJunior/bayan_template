<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Oil extends Model
{
    protected $table = 'oils';

    protected $fillable = [
        'oil_name',
        'oil_code',
        'oil_type',
        'oil_life',
    ];

    protected $casts = [
        'oil_life' => 'decimal:2',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(VehicleOilChange::class);
    }
}
