<?php

namespace App\Http\Controllers;

use App\Models\SupplierInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SupplierPaymentController extends Controller
{
    /**
     * Record a payment against the given invoice.
     *
     * Overpayment is intentionally allowed here — the only guard is the
     * front-end confirmation dialog on the invoice page.
     */
    public function store(Request $request, SupplierInvoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['required', 'date'],
        ]);

        $invoice->payments()->create([
            'amount' => $validated['amount'],
            'paid_at' => $validated['paid_at'],
            'recorded_by' => auth()->id(),
        ]);

        flash()->success('تم تسجيل الدفعة بنجاح.');

        return redirect()->route('supplier-invoices.show', $invoice);
    }
}
