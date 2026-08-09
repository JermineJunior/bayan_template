<?php

namespace App\Http\Controllers\BasicData;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Management;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    /**
     * Display a paginated listing of the departments.
     */
    public function index(): View
    {
        return view('basic-data.departments.index', [
            'departments' => Department::with(['management'])
                ->withCount('drivers')
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new department.
     */
    public function create(): View
    {
        return view('basic-data.departments.create', [
            'department' => null,
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created department.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', Rule::unique('departments')],
            'name' => ['required', 'string', 'max:100'],
            'management_id' => ['required', 'integer', Rule::exists('management', 'id')],
        ]);

        Department::create($validated);

        flash()->success('تم إنشاء القسم.');

        return redirect()
            ->route('departments.index');
    }

    /**
     * Show the form for editing the given department.
     */
    public function edit(Department $department): View
    {
        return view('basic-data.departments.edit', [
            'department' => $department,
            'managements' => Management::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the given department.
     */
    public function update(Request $request, Department $department): RedirectResponse
    {
        $validated = $request->validate([
            'number' => ['required', 'integer', Rule::unique('departments')->ignore($department->id)],
            'name' => ['required', 'string', 'max:100'],
            'management_id' => ['required', 'integer', Rule::exists('management', 'id')],
        ]);

        $department->update($validated);

        flash()->success('تم تحديث القسم.');

        return redirect()
            ->route('departments.index');
    }

    /**
     * Remove the given department.
     */
    public function destroy(Department $department): RedirectResponse
    {
        if ($department->drivers()->exists()) {
            flash()->error('لا يمكن حذف هذا القسم لوجود سائقين مرتبطين به.');

            return redirect()
                ->route('departments.index');
        }

        $department->delete();

        flash()->success('تم حذف القسم.');

        return redirect()
            ->route('departments.index');
    }
}
