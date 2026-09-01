<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    protected $fillable = [
        'part_number',
        'name',
        'category',
        'default_supplier_id',
        'purchase_price',
        'minimum_quantity',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'minimum_quantity' => 'decimal:2',
    ];

    public function defaultSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'default_supplier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(SparePartTransaction::class)->latest('created_at');
    }

    /**
     * المخزون الحالي — مجموع كل معاملات القطعة، لا يُخزن. عند الجلب عبر
     * withSum('transactions') يكون الناتج جاهزًا في transactions_sum_quantity
     * فيُعاد استخدامه بدلًا من تنفيذ استعلام SUM لكل قطعة على حدة.
     */
    public function getQuantityOnHandAttribute(): float
    {
        if (array_key_exists('transactions_sum_quantity', $this->getAttributes())) {
            return (float) $this->transactions_sum_quantity;
        }

        return (float) $this->transactions()->sum('quantity');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->quantity_on_hand <= $this->minimum_quantity;
    }

    /**
     * Auto-generate the part number before a new record is saved, unless one
     * was already provided explicitly.
     */
    protected static function booted(): void
    {
        static::creating(function (SparePart $sparePart) {
            if (blank($sparePart->part_number)) {
                $sparePart->part_number = static::generatePartNumber();
            }
        });
    }

    /**
     * Generate the next part number. Format: SP-<5-digit zero-padded sequence>,
     * e.g. SP-00001, SP-00002 … based on the highest existing part number.
     */
    public static function generatePartNumber(): string
    {
        $last = static::query()
            ->where('part_number', 'like', 'SP-%')
            ->orderByDesc('part_number')
            ->value('part_number');

        $sequence = $last ? ((int) substr($last, -5)) + 1 : 1;

        return 'SP-'.str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
