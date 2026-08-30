<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Invoice extends Model
{
    protected $fillable = ['invoice_number', 'maintenance_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public static function generateInvoiceNumber($date = null)
    {
        $date = $date ? Date::parse($date) : now();

        $prefix = 'INV-' . $date->format('Y');

        $last = self::where('invoice_number', 'like', $prefix . '-%')
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = $last ? substr($last->invoice_number, -5) + 1 : 1;

        return $prefix . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    /** Sum of every line item's row total — never stored, always derived */
    public function getTotalAmountAttribute(): float
    {
        return (float) $this->details()->get()->sum('row_sub_total');
    }
