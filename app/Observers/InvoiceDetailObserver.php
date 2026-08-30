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

    /**
     * Handle the InvoiceDetail "updated" event.
     */
    public function updated(InvoiceDetail $invoiceDetail): void
    {
        //
    }

    /**
     * Handle the InvoiceDetail "deleted" event.
     */
    public function deleted(InvoiceDetail $invoiceDetail): void
    {
        //
    }

    /**
     * Handle the InvoiceDetail "restored" event.
     */
    public function restored(InvoiceDetail $invoiceDetail): void
    {
        //
    }

    /**
     * Handle the InvoiceDetail "force deleted" event.
     */
    public function forceDeleted(InvoiceDetail $invoiceDetail): void
    {
        //
    }
}
