<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Management extends Model
{
    /** @use HasFactory<\Database\Factories\ManagementFactory> */
    use HasFactory;
    protected $fillable = [
        'number',
        'name',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
