<?php

namespace App\Observers;

use App\Models\InvoiceDetail;
use App\Models\SparePartTransaction;
class InvoiceDetailObserver
{
    /**
     * Handle the InvoiceDetail "created" event.
     */
    public function created(InvoiceDetail $detail): void
    {
        SparePartTransaction::recordIssue(
            sparePart: $detail->sparePart,
            quantity: (float) $detail->qty,
            maintenanceOrder: $detail->invoice->maintenance,
            recordedBy: auth('web')->user(),
            unitPrice: (float) $detail->price,
        );
    } 
}
