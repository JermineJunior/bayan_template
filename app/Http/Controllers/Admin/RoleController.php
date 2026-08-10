<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RoleRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        return view('admin.roles.index', [
            'roles' => Role::orderBy('created_at', 'desc')->withCount('permissions')->get(),
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        return view('admin.roles.create', [
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(RoleRequest $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $role = Role::create([
            'name' => $request->string('name'),
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($request->input('permissions', []));

        flash()->success('تم إنشاء الدور.');

        return redirect()
            ->route('roles.index');
    }

    /**
     * Show the form for editing the given role.
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        return view('admin.roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'rolePermissions' => $role->permissions->pluck('name')->all(),
        ]);
    }

    /**
     * Update the given role.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $role->update([
            'name' => $request->string('name'),
        ]);

        $role->syncPermissions($request->input('permissions', []));

        flash()->success('تم تحديث الدور.');

        return redirect()
            ->route('roles.index');
    }

    /**
     * Remove the given role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        flash()->success('تم حذف الدور.');

        return redirect()
            ->route('roles.index');
    }

    /**
     * Permission keys grouped by feature area from config/permissions.php.
     *
     * @return array<string, array<int, string>>
     */
    protected function permissionGroups(): array
    {
        $groups = [];
        $groupLabels = config('permissions_labels.groups');

        foreach (config('permissions') as $area => $permissions) {
            $groups[$groupLabels[$area] ?? Str::title($area)] = $permissions;
        }

        return $groups;
    }
}
