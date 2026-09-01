<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class InvoiceDetail extends Model
{
    protected $fillable = ['invoice_id', 'spare_part_id', 'qty', 'price'];

    protected $casts = [
        'qty' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    /** Derived, never stored — qty * price */
    public function getRowSubTotalAttribute(): float
    {
        return (float) $this->qty * (float) $this->price;
    }
}
