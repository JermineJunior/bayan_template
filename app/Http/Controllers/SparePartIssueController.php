<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\SparePart;
use App\Models\SparePartTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartIssueController extends Controller
{
    /**
     * Show the form for logging an issue against the given spare part.
     */
    public function create(SparePart $sparePart): View
    {
        return view('spare-parts.issue', [
            'sparePart' => $sparePart,
            'maintenanceOrders' => Maintenance::query()
                ->whereIn('status', ['pending', 'in_progress'])
                ->with('vehicle')
                ->latest('created_at')
                ->get(),
        ]);
    }

    /**
     * Record an issue (parts used on a maintenance job) — always decreases stock.
     *
     * The recordIssue() method blocks the request when the requested quantity
     * exceeds what's on hand. That \RuntimeException is surfaced here as a
     * field-level error on the quantity input, not a generic 500.
     */
    public function store(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'maintenance_order_id' => ['required', 'integer', Rule::exists('maintenances', 'id')],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            SparePartTransaction::recordIssue(
                $sparePart,
                (float) $validated['quantity'],
                Maintenance::findOrFail($validated['maintenance_order_id']),
                $request->user(),
                $validated['notes'] ?? null,
            );
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['quantity' => $e->getMessage()]);
        }

        flash()->success('تم تسجيل صرف القطعة.');

        return redirect()->route('spare-parts.show', $sparePart);
    }
}
