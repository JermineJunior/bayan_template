<?php

namespace App\Observers;

use App\Models\SupplierInvoiceDetail;
use App\Models\SparePartTransaction;
class SupplierInvoiceDetailObserver
{
    /**
     * Handle the SupplierInvoiceDetail "created" event.
     */
    public function created(SupplierInvoiceDetail $supplierInvoiceDetail): void
    {
         SparePartTransaction::recordPurchase(
            sparePart: $supplierInvoiceDetail->sparePart,
            quantity: (float) $supplierInvoiceDetail->qty,
            supplier: $supplierInvoiceDetail->invoice->supplier,
            recordedBy: auth('web')->user(),
            unitPrice: (float) $supplierInvoiceDetail->price,
        );
    } 
}
