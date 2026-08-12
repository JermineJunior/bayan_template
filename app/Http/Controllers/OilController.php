<?php

namespace App\Http\Controllers;

use App\Models\Oil;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OilController extends Controller
{
    /**
     * The oils + filters catalogs live on the same page now.
     */
    public function index(): RedirectResponse
    {
        return redirect()
            ->route('catalog.index');
    }

    /**
     * Show the form for creating a new oil.
     */
    public function create(): View
    {
        return view('oils.create', [
            'oil' => null,
        ]);
    }

    /**
     * Store a newly created oil.
     *
     * Supports both a normal form submit (redirect + flash) and an AJAX
     * quick-add request (returns the new oil as JSON so the caller can append
     * it to a <select> without reloading the page).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'oil_name' => ['required', 'string', 'max:150'],
            'oil_code' => ['required', 'string', 'max:50', Rule::unique('oils', 'oil_code')],
            'oil_type' => ['required', Rule::in(['engine', 'transmission', 'hydraulic', 'brake', 'differential'])],
            'oil_life' => ['required', 'numeric', 'min:0.01'],
        ]);

        $oil = Oil::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $oil->id,
                'oil_name' => $oil->oil_name,
                'oil_type' => $oil->oil_type,
            ]);
        }

        flash()->success('تم إنشاء الزيت.');

        return redirect()
            ->route('oils.index');
    }

    /**
     * Show the form for editing the given oil.
     */
    public function edit(Oil $oil): View
    {
        return view('oils.edit', [
            'oil' => $oil,
        ]);
    }

    /**
     * Update the given oil.
     */
    public function update(Request $request, Oil $oil): RedirectResponse
    {
        $validated = $request->validate([
            'oil_name' => ['required', 'string', 'max:150'],
            'oil_code' => ['required', 'string', 'max:50', Rule::unique('oils', 'oil_code')->ignore($oil->id)],
            'oil_type' => ['required', Rule::in(['engine', 'transmission', 'hydraulic', 'brake', 'differential'])],
            'oil_life' => ['required', 'numeric', 'min:0.01'],
        ]);

        $oil->update($validated);

        flash()->success('تم تحديث الزيت.');

        return redirect()
            ->route('oils.index');
    }

    /**
     * Remove the given oil.
     *
     * Blocked while change records reference it — oil-change history must
     * always keep a reference to the oil that was actually used.
     */
    public function destroy(Oil $oil): RedirectResponse
    {
        if ($oil->changes()->exists()) {
            flash()->error('لا يمكن حذف هذا الزيت لوجود عمليات تغيير مرتبطة به.');

            return redirect()
                ->route('oils.index');
        }

        $oil->delete();

        flash()->success('تم حذف الزيت.');

        return redirect()
            ->route('oils.index');
    }
}
