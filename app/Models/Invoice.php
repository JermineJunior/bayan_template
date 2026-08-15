<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'maintenance_id', 'supplier', 'total_amount', 'date'];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'date' => 'date',
    ];

    public static function generateInvoiceNumber($date = null)
    {
        $date = $date ? Date::parse($date) : now();

        $prefix = 'INV-'.$date->format('Y');

        $last = self::where('invoice_number', 'like', $prefix.'-%')
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = $last ? substr($last->invoice_number, -5) + 1 : 1;

        return $prefix.'-'.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function maintenance()
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function details()
    {
        return $this->hasMany(InvoiceDetail::class);
    }
}
