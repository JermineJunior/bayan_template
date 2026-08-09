<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view the role list.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('roles.view');
    }

    /**
     * Determine whether the user can view the given role.
     */
    public function view(User $user, Role $model): bool
    {
        return $user->can('roles.view');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('roles.create');
    }

    /**
     * Determine whether the user can update the given role.
     */
    public function update(User $user, Role $model): bool
    {
        return $user->can('roles.edit');
    }

    /**
     * Determine whether the user can delete the given role.
     */
    public function delete(User $user, Role $model): bool
    {
        return $user->can('roles.delete');
    }
}
