<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Maintenance;
use App\Models\SparePart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /**
     * Display a paginated listing of all invoices, optionally filtered by a
     * maintenance job.
     */
    public function index(Request $request): View
    {
        $query = Invoice::query()
            ->with(['maintenance.vehicle'])
            ->latest('date');

        if ($request->filled('maintenance_id')) {
            $query->where('maintenance_id', $request->integer('maintenance_id'));
        }

        return view('invoices.index', [
            'invoices' => $query->paginate(10)->withQueryString(),
            'maintenances' => Maintenance::orderByDesc('created_at')->get(),
        ]);
    }

    /**
     * Show the form for issuing parts against a specific maintenance job.
     * The invoice always belongs to that maintenance job.
     */
    public function create(Maintenance $maintenance): View
    {
        return view('invoices.create', [
            'maintenance' => $maintenance,
            'spareParts' => SparePart::query()
                ->orderBy('name')
                ->get(['id', 'part_number', 'name', 'category', 'purchase_price']),
        ]);
    }

    /**
     * Store a new invoice and its line items, deducting stock atomically.
     *
     * The whole operation runs inside a DB transaction: each InvoiceDetail's
     * creation fires InvoiceDetailObserver, which calls recordIssue() and
     * deducts stock. If any one line runs short of stock, the exception rolls
     * the entire invoice back — no invoice, no line items, and no partial
     * stock deduction ever persists.
     */
    public function store(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $items = collect($request->input('items', []))
            ->map(fn ($item) => [
                ...$item,
                'price' => $item['price'] !== null ? str_replace(',', '', $item['price']) : $item['price'],
            ])
            ->all();

        $request->merge(['items' => $items]);

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.spare_part_id' => ['required', 'integer', Rule::exists('spare_parts', 'id')],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($maintenance, $validated, &$invoice) {
                $invoice = Invoice::create([
                    'invoice_number' => Invoice::generateInvoiceNumber($validated['date']),
                    'maintenance_id' => $maintenance->id,
                    'date' => $validated['date'],
                ]);

                foreach ($validated['items'] as $index => $item) {
                    try {
                        InvoiceDetail::create([
                            'invoice_id' => $invoice->id,
                            'spare_part_id' => $item['spare_part_id'],
                            'qty' => $item['qty'],
                            'price' => $item['price'],
                        ]);
                    } catch (\RuntimeException $e) {
                        throw new \RuntimeException(
                            $e->getMessage(),
                            $index,
                        );
                    }
                }
            });
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    "items.{$e->getCode()}.qty" => $e->getMessage(),
                ]);
        }

        $this->syncMaintenanceExpense($maintenance);

        flash()->success('تم صرف قطع الغيار وإنشاء الفاتورة بنجاح.');

        return redirect()->route('invoices.show', $invoice);
    }

    /**
     * Update the maintenance expense so it reflects labour cost plus the
     * totals of every issue invoice (spare parts) tied to the maintenance.
     *
     * The expense itself is created by MaintenanceObserver when the order is
     * created (labour only); here we keep it in sync as spare parts are added.
     */
    private function syncMaintenanceExpense(Maintenance $maintenance): void
    {
        $partsTotal = (float) $maintenance->invoices()
            ->with('details')
            ->get()
            ->sum(fn (Invoice $invoice) => (float) $invoice->total);

        $expense = Expense::where('sourceable_type', Maintenance::class)
            ->where('sourceable_id', $maintenance->id)
            ->first();

        if ($expense) {
            $expense->update([
                'amount' => (float) $maintenance->labor_cost + $partsTotal,
                'expense_date' => $maintenance->end_date?->toDateString() ?? now()->toDateString(),
            ]);

            return;
        }

        Expense::create([
            'vehicle_id' => $maintenance->vehicle_id,
            'expense_type' => 'maintenance',
            'amount' => (float) $maintenance->labor_cost + $partsTotal,
            'expense_date' => $maintenance->end_date?->toDateString() ?? now()->toDateString(),
            'description' => $maintenance->reason,
            'sourceable_type' => Maintenance::class,
            'sourceable_id' => $maintenance->id,
            'recorded_by' => $maintenance->created_by,
        ]);
    }

    /**
     * Display the given invoice with its line items.
     */
    public function show(Invoice $invoice): View
    {
        return view('invoices.show', [
            'invoice' => $invoice->load(['maintenance.vehicle', 'details.sparePart']),
        ]);
    }
}
