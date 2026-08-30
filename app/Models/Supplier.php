<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;
    protected $fillable = ['name','phone','address'];
    public function invoices(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    public function payments(): HasManyThrough
    {
        return $this->hasManyThrough(SupplierPayment::class, SupplierInvoice::class);
    }

    /** Total invoiced across all invoices, computed live */
    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->invoices()->sum('amount');
    }

    /** Total paid across all invoices, computed live */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('supplier_payments.amount');
    }

    /** What's still owed to this supplier, computed live */
    public function getBalanceAttribute(): float
    {
        return $this->total_invoiced - $this->total_paid;
    }
}
