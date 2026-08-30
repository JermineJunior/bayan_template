<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
class SparePartTransaction extends Model
{
    protected $fillable = [
        'spare_part_id',
        'type',
        'quantity',
        'maintenance_order_id',
        'supplier_id',
        'unit_price',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function maintenanceOrder(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class, 'maintenance_order_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Record a purchase — always increases stock. Requires a supplier.
     */
    public static function recordPurchase(
        SparePart $sparePart,
        float $quantity,
        Supplier $supplier,
        User $recordedBy,
        ?float $unitPrice = null,
        ?string $notes = null,
    ): self {
        return static::create([
            'spare_part_id' => $sparePart->id,
            'type' => 'purchase',
            'quantity' => abs($quantity),
            'supplier_id' => $supplier->id,
            'unit_price' => $unitPrice,
            'notes' => $notes,
            'recorded_by' => $recordedBy->id,
        ]);
    }

    /**
     * Record an issue (parts used on a maintenance job) — always decreases stock.
     * Requires a maintenance record. Blocks if the requested quantity exceeds what's
     * currently on hand — unlike the supplier-payment overpayment case, this isn't a
     * "warn but allow" situation: you cannot physically hand out parts that don't exist
     * in the warehouse.
     */
    public static function recordIssue(
        SparePart $sparePart,
        float $quantity,
        Maintenance $maintenanceOrder,
        User $recordedBy,
        ?float $unitPrice = null,
        ?string $notes = null,
    ): self {
        $quantity = abs($quantity);

        if ($quantity > $sparePart->quantity_on_hand) {
            throw new \RuntimeException(
                "الكمية المطلوبة ({$quantity}) أكبر من المتوفر بالمخزون ({$sparePart->quantity_on_hand})."
            );
        }

        return static::create([
            'spare_part_id' => $sparePart->id,
            'type' => 'issue',
            'quantity' => -$quantity,
            'maintenance_order_id' => $maintenanceOrder->id,
            'unit_price' => $unitPrice,
            'recorded_by' => $recordedBy->id,
            'notes' => $notes,
        ]);
    }

    /**
     * Record a stocktake — the user enters the physically-counted quantity, this method
     * computes and stores the SIGNED DELTA between that count and the system's current
     * total, so quantity_on_hand (a plain SUM) stays correct without special-casing
     * stocktake rows anywhere else in the codebase.
     */
    public static function recordStocktake(
        SparePart $sparePart,
        float $countedQuantity,
        User $recordedBy,
        ?string $notes = null,
    ): self {
        $delta = $countedQuantity - $sparePart->quantity_on_hand;

        return static::create([
            'spare_part_id' => $sparePart->id,
            'type' => 'stocktake',
            'quantity' => $delta,
            'recorded_by' => $recordedBy->id,
            'notes' => $notes,
        ]);
    }
}
