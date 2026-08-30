<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Display a paginated listing of the suppliers.
     */
    public function index(): View
    {
        return view('suppliers.index', [
            'suppliers' => Supplier::query()
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new supplier.
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        Supplier::create($validated);

        flash()->success('تم إنشاء المورد.');

        return redirect()->route('suppliers.index');
    }

    /**
     * Display the given supplier.
     */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', [
            'supplier' => $supplier,
            'invoices' => $supplier->invoices()->with('payments')->latest('invoice_date')->get(),
        ]);
    }

    /**
     * Show the form for editing the given supplier.
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', [
            'supplier' => $supplier,
        ]);
    }

    /**
     * Update the given supplier.
     */
    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ]);

        $supplier->update($validated);

        flash()->success('تم تحديث المورد.');

        return redirect()->route('suppliers.index');
    }

    /**
     * Remove the given supplier.
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->invoices()->exists()) {
            flash()->error('لا يمكن حذف هذا المورد لوجود فواتير مرتبطة به.');

            return redirect()->route('suppliers.index');
        }

        $supplier->delete();

        flash()->success('تم حذف المورد.');

        return redirect()->route('suppliers.index');
    }
}
