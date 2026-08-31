<?php

namespace App\Http\Controllers;

use App\Models\SparePart;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SparePartController extends Controller
{
    /**
     * Display a paginated, filterable listing of spare parts.
     */
    public function index(Request $request): View
    {
        $query = SparePart::query()
            ->with('defaultSupplier');

        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('part_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('low_stock')) {
            $query->whereRaw('(SELECT COALESCE(SUM(quantity), 0) FROM spare_part_transactions WHERE spare_part_transactions.spare_part_id = spare_parts.id) <= spare_parts.minimum_quantity');
        }

        $categories = SparePart::query()
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('spare-parts.index', [
            'spareParts' => $query->orderBy('name')->paginate(10)->withQueryString(),
            'categories' => $categories,
        ]);
    }

    /**
     * Show the form for creating a new spare part.
     */
    public function create(): View
    {
        return view('spare-parts.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created spare part.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        SparePart::create($validated);

        flash()->success('تم إنشاء قطعة الغيار.');

        return redirect()->route('spare-parts.index');
    }

    /**
     * Display the given spare part with its transaction history.
     */
    public function show(SparePart $sparePart): View
    {
        return view('spare-parts.show', [
            'sparePart' => $sparePart,
            'transactions' => $sparePart->transactions()
                ->with(['maintenanceOrder.vehicle', 'supplier', 'recordedBy'])
                ->get(),
        ]);
    }

    /**
     * Show the form for editing the given spare part.
     */
    public function edit(SparePart $sparePart): View
    {
        return view('spare-parts.edit', [
            'sparePart' => $sparePart,
            'suppliers' => Supplier::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the given spare part.
     */
    public function update(Request $request, SparePart $sparePart): RedirectResponse
    {
        $validated = $this->validated($request, $sparePart);

        $sparePart->update($validated);

        flash()->success('تم تحديث قطعة الغيار.');

        return redirect()->route('spare-parts.show', $sparePart);
    }

    /**
     * Remove the given spare part.
     *
     * Blocked while the part has any transactions — history must always stay
     * attached to the part that produced it.
     */
    public function destroy(SparePart $sparePart): RedirectResponse
    {
        if ($sparePart->transactions()->exists()) {
            flash()->error('لا يمكن حذف هذه القطعة لوجود حركات مخزون مرتبطة بها.');

            return redirect()->route('spare-parts.show', $sparePart);
        }

        $sparePart->delete();

        flash()->success('تم حذف قطعة الغيار.');

        return redirect()->route('spare-parts.index');
    }

    /**
     * Shared validation for create/update.
     */
    private function validated(Request $request, ?SparePart $sparePart = null): array
    {
        $request->merge([
            'purchase_price' => $request->filled('purchase_price')
                ? str_replace(',', '', $request->input('purchase_price'))
                : null,
        ]);

        return $request->validate([
            'part_number' => ['nullable', 'string', 'max:50', Rule::unique('spare_parts', 'part_number')->ignore($sparePart?->id)],
            'name' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'default_supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'minimum_quantity' => ['required', 'numeric', 'min:0'],
        ]);
    }
}
