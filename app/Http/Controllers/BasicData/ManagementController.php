<?php

namespace App\Http\Controllers\BasicData;

use App\Http\Controllers\Controller;
use App\Models\Management;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagementController extends Controller
{
    /**
     * Display a paginated listing of the managements.
     */
    public function index(): View
    {
        return view('basic-data.managements.index', [
            'managements' => Management::withCount('departments', 'vehicles')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new management.
     */
    public function create(): View
    {
        return view('basic-data.managements.create', [
            'management' => null,
        ]);
    }

    /**
     * Store a newly created management.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', Rule::unique('management')],
            'name' => ['required', 'string', 'max:100'],
        ]);

        Management::create($validated);

        return redirect()
            ->route('managements.index')
            ->with('success', 'تم إنشاء الإدارة.');
    }

    /**
     * Show the form for editing the given management.
     */
    public function edit(Management $management): View
    {
        return view('basic-data.managements.edit', [
            'management' => $management,
        ]);
    }

    /**
     * Update the given management.
     */
    public function update(Request $request, Management $management): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', Rule::unique('management')->ignore($management->id)],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $management->update($validated);

        return redirect()
            ->route('managements.index')
            ->with('success', 'تم تحديث الإدارة.');
    }

    /**
     * Remove the given management.
     */
    public function destroy(Management $management): RedirectResponse
    {
        if ($management->departments()->exists() || $management->vehicles()->exists()) {
            return redirect()
                ->route('managements.index')
                ->with('error', 'لا يمكن حذف هذه الإدارة لوجود أقسام أو مركبات مرتبطة بها.');
        }

        $management->delete();

        return redirect()
            ->route('managements.index')
            ->with('success', 'تم حذف الإدارة.');
    }
}
