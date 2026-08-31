<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\SupplierInvoiceDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            'spareParts' => SparePart::query()
                ->orderBy('name')
                ->get(['id', 'part_number', 'name', 'category', 'purchase_price']),
        ]);
    }

    /**
     * Store a newly created invoice for the given supplier, with optional line
     * items. Line items are optional overall — a service-only invoice can carry
     * just an amount — but when a line is present all three of its fields
     * (spare_part_id, qty, price) are required.
     *
     * The invoice + its line items are created inside one DB transaction: each
     * SupplierInvoiceDetail's creation fires SupplierInvoiceDetailObserver,
     * which calls recordPurchase() and increases stock. If anything throws, the
     * whole transaction rolls back — no invoice, no line items, no stock change.
     */
    public function store(Request $request, Supplier $supplier): RedirectResponse
    {
        $items = collect($request->input('items', []))
            ->map(fn ($item) => [
                ...$item,
                'price' => $item['price'] !== null ? str_replace(',', '', $item['price']) : $item['price'],
            ])
            ->all();

        $request->merge([
            'amount' => str_replace(',', '', $request->input('amount')),
            'items' => $items,
        ]);

        $validated = $request->validate([
            'invoice_number' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('supplier_invoices')->where('supplier_id', $supplier->id),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'invoice_date' => ['required', 'date'],
            'items' => ['nullable', 'array'],
            'items.*.spare_part_id' => ['required', 'integer', Rule::exists('spare_parts', 'id')],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($supplier, $validated, &$invoice) {
                $invoice = $supplier->invoices()->create([
                    'invoice_number' => ($validated['invoice_number'] ?? '') ?: SupplierInvoice::generateInvoiceNumber($validated['invoice_date']),
                    'amount' => $validated['amount'],
                    'invoice_date' => $validated['invoice_date'],
                    'recorded_by' => auth()->id(),
                ]);

                foreach (($validated['items'] ?? []) as $index => $item) {
                    try {
                        SupplierInvoiceDetail::create([
                            'supplier_invoice_id' => $invoice->id,
                            'spare_part_id' => $item['spare_part_id'],
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                        ]);
                    } catch (\RuntimeException $e) {
                        throw new \RuntimeException($e->getMessage(), $index);
                    }
                }
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    "items.{$e->getCode()}.spare_part_id" => $e->getMessage(),
                ]);
        }

        flash()->success('تم تسجيل الفاتورة بنجاح.');

        return redirect()->route('supplier-invoices.show', $invoice);
    }

    /**
     * Display the given invoice with its payment history and line items.
     */
    public function show(SupplierInvoice $invoice): View
    {
        $invoice->load(['supplier', 'recordedBy', 'payments.recordedBy', 'details.sparePart']);

        return view('supplier-invoices.show', [
            'invoice' => $invoice,
        ]);
    }
}
