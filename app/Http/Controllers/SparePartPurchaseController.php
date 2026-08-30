<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartTransaction;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartPurchaseController extends Controller
{
    /**
     * Show the form for logging a purchase against the given spare part.
     */
    public function create(SparePart $sparePart): View
    {
        return view('spare-parts.purchase', [
            'sparePart' => $sparePart,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Record a purchase — always increases stock, requires a supplier.
     */
    public function store(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        SparePartTransaction::recordPurchase(
            $sparePart,
            (float) $validated['quantity'],
            Supplier::findOrFail($validated['supplier_id']),
            $request->user(),
            isset($validated['unit_price']) ? (float) $validated['unit_price'] : null,
            $validated['notes'] ?? null,
        );

        flash()->success('تم تسجيل عملية شراء القطعة.');

        return redirect()->route('spare-parts.show', $sparePart);
    }
}
