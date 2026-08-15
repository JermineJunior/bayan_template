<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Maintenance;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function create(Maintenance $maintenance)
    {
        return view('maintenance.invoice.create', [
            'maintenance' => $maintenance,
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'invoice_number' => ['required', 'unique:invoices,invoice_number'],
                'date' => 'date|required|before_or_equal:now',
                'details.*.spare' => 'required',
                'details.*.price' => 'required|numeric',
            ]);

            $invoice = Invoice::create([
                'maintenance_id' => $request->maintenance_id,
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'supplier' => $request->supplier,
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->details as $index => $item) {
                InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'spare' => $item['spare'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'row_sub_total' => $item['qty'] * $item['price'],
                ]);
            }

            $maintenance = Maintenance::find($request->maintenance_id);

            $maintenance->update([
                'spare_cost' => $maintenance->spare_cost + $request->total_amount,
                'total_cost' => $maintenance->total_cost + $request->total_amount,
            ]);

            DB::commit();

            flash()->success('تم ادخال قطع الغيار بنجاح');

            return redirect()->route('maintenance.show', $maintenance);
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withErrors($e->getMessage());
        }
    }

    public function show(Invoice $invoice)
    {
        return view('maintenance.invoice.show', [
            'invoice' => $invoice->load(['details', 'maintenance']),
        ]);
    }

    public function edit(Invoice $invoice)
    {
        return view('maintenance.invoice.edit', [
            'invoice' => $invoice->load(['details']),
        ]);
    }

    public function update(Invoice $invoice, Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'invoice_number' => ['required', Rule::unique('invoices', 'invoice_number')->ignore($invoice->id)],
                'date' => 'date|required|before_or_equal:now',
                'details.*.spare' => 'required',
                'details.*.price' => 'required|numeric',
            ]);

            $old_maintenance = Maintenance::find($invoice->maintenance_id);

            $old_maintenance->update([
                'spare_cost' => $old_maintenance->spare_cost - $invoice->total_amount,
                'total_cost' => $old_maintenance->total_cost - $invoice->total_amount,
            ]);

            $invoice->delete();

            $new_invoice = Invoice::create([
                'maintenance_id' => $request->maintenance_id,
                'invoice_number' => $request->invoice_number,
                'date' => $request->date,
                'supplier' => $request->supplier,
                'total_amount' => $request->total_amount,
            ]);

            foreach ($request->details as $index => $item) {
                InvoiceDetail::create([
                    'invoice_id' => $new_invoice->id,
                    'spare' => $item['spare'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'row_sub_total' => $item['qty'] * $item['price'],
                ]);
            }

            $maintenance = Maintenance::find($request->maintenance_id);

            $maintenance->update([
                'spare_cost' => $maintenance->spare_cost + $request->total_amount,
                'total_cost' => $maintenance->total_cost + $request->total_amount,
            ]);

            DB::commit();

            flash()->success('تم تعديل قطع الغيار بنجاح');

            return redirect()->route('maintenance.show', $maintenance);
        } catch (Exception $e) {
            DB::rollBack();

            return back()->withErrors($e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        flash()->success('تم حذف قطع الغيار بنجاح');

        return back();
    }
}
