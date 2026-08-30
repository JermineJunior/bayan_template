<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\SparePartTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SparePartStocktakeController extends Controller
{
    /**
     * Show the form for logging a stocktake against the given spare part.
     */
    public function create(SparePart $sparePart): View
    {
        return view('spare-parts.stocktake', [
            'sparePart' => $sparePart,
        ]);
    }

    /**
     * Record a stocktake.
     *
     * The user enters the physically-counted total; recordStocktake() computes
     * the signed delta against the system count internally. We pass the raw
     * counted number, never a pre-computed difference.
     */
    public function store(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $request->validate([
            'counted_quantity' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        SparePartTransaction::recordStocktake(
            $sparePart,
            (float) $validated['counted_quantity'],
            $request->user(),
            $validated['notes'] ?? null,
        );

        flash()->success('تم تسجيل جرد المخزون.');

        return redirect()->route('spare-parts.show', $sparePart);
    }
}
