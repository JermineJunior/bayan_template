<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierInvoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierInvoiceController extends Controller
{
    /**
     * Show the form for logging a new invoice for the given supplier.
     */
    public function create(Supplier $supplier): View
    {
        return view('supplier-invoices.create', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Store a newly created invoice for the given supplier.
     */
    public function store(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('supplier_invoices')->where('supplier_id', $supplier->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'invoice_date' => ['required', 'date'],
        ]);

        $supplier->invoices()->create([
            'invoice_number' => $validated['invoice_number'],
            'amount' => $validated['amount'],
            'invoice_date' => $validated['invoice_date'],
            'recorded_by' => auth()->id(),
        ]);

        flash()->success('تم تسجيل الفاتورة بنجاح.');

        return redirect()->route('suppliers.show', $supplier);
    }

    /**
     * Display the given invoice with its payment history.
     */
    public function show(SupplierInvoice $invoice): View
    {
        $invoice->load(['supplier', 'recordedBy', 'payments.recordedBy']);

        return view('supplier-invoices.show', [
            'invoice' => $invoice,
        ]);
    }
}
