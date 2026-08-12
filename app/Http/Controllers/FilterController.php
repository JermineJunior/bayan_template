<?php

namespace App\Http\Controllers;

use App\Models\Filter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FilterController extends Controller
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
     * Show the form for creating a new filter.
     */
    public function create(): View
    {
        return view('filters.create', [
            'filter' => null,
        ]);
    }

    /**
     * Store a newly created filter.
     *
     * Supports both a normal form submit (redirect + flash) and an AJAX
     * quick-add request (returns the new filter as JSON so the caller can
     * append it to a <select> without reloading the page).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'filter_name' => ['required', 'string', 'max:150'],
            'filter_code' => ['required', 'string', 'max:50', Rule::unique('filters', 'filter_code')],
            'filter_type' => ['required', Rule::in(['oil', 'air', 'fuel', 'ac'])],
            'filter_life' => ['required', 'numeric', 'min:0.01'],
        ]);

        $filter = Filter::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'id' => $filter->id,
                'filter_name' => $filter->filter_name,
                'filter_type' => $filter->filter_type,
            ]);
        }

        flash()->success('تم إنشاء الفلتر.');

        return redirect()
            ->route('filters.index');
    }

    /**
     * Show the form for editing the given filter.
     */
    public function edit(Filter $filter): View
    {
        return view('filters.edit', [
            'filter' => $filter,
        ]);
    }

    /**
     * Update the given filter.
     */
    public function update(Request $request, Filter $filter): RedirectResponse
    {
        $validated = $request->validate([
            'filter_name' => ['required', 'string', 'max:150'],
            'filter_code' => ['required', 'string', 'max:50', Rule::unique('filters', 'filter_code')->ignore($filter->id)],
            'filter_type' => ['required', Rule::in(['oil', 'air', 'fuel', 'ac'])],
            'filter_life' => ['required', 'numeric', 'min:0.01'],
        ]);

        $filter->update($validated);

        flash()->success('تم تحديث الفلتر.');

        return redirect()
            ->route('filters.index');
    }

    /**
     * Remove the given filter.
     *
     * Blocked while change records reference it — filter-change history must
     * always keep a reference to the filter that was actually used.
     */
    public function destroy(Filter $filter): RedirectResponse
    {
        if ($filter->changes()->exists()) {
            flash()->error('لا يمكن حذف هذا الفلتر لوجود عمليات تغيير مرتبطة به.');

            return redirect()
                ->route('filters.index');
        }

        $filter->delete();

        flash()->success('تم حذف الفلتر.');

        return redirect()
            ->route('filters.index');
    }
}
