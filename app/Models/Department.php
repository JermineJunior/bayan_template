<?php

namespace App\Models;

use Database\Factories\DepartmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    /** @use HasFactory<DepartmentFactory> */
    use HasFactory;

    protected $fillable = [
        'number',
        'name',
        'management_id',
    ];

    protected $casts = [
        'number' => 'integer',
    ];

    public function management()
    {
        return $this->belongsTo(Management::class);
    }

    public function drivers()
    {
        return $this->hasMany(Driver::class, 'department_id');
    }
}
