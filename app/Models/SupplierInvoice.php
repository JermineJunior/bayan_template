<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

class SupplierInvoice extends Model
{
    protected $fillable = [
        'supplier_id',
        'invoice_number',
        'amount',
        'invoice_date',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'invoice_date' => 'date',
    ];

    /**
     * Generate the next invoice number for new supplier invoices.
     * Format: PINV-<year>-<5-digit zero-padded sequence>, e.g. PINV-2026-00001,
     * based on the date of the invoice and the highest existing number.
     */
    public static function generateInvoiceNumber($date = null): string
    {
        $date = $date ? Date::parse($date) : now();

        $prefix = 'PINV-'.$date->format('Y');

        $last = static::query()
            ->where('invoice_number', 'like', $prefix.'-%')
            ->orderByDesc('invoice_number')
            ->first();

        $sequence = $last ? ((int) substr($last->invoice_number, -5)) + 1 : 1;

        return $prefix.'-'.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SupplierInvoiceDetail::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Sum of every line item's qty * price — never stored, always derived */
    public function getLineItemsTotalAttribute(): float
    {
        return (float) $this->details()->get()->sum('row_sub_total');
    }

    /** True when the manually-entered amount and the line-items sum differ */
    public function getAmountDiffersFromLineItemsAttribute(): bool
    {
        return abs((float) $this->amount - $this->line_items_total) > 0.005;
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return (float) $this->amount - $this->total_paid;
    }

    public function getIsPaidInFullAttribute(): bool
    {
        return $this->balance <= 0;
    }
}
