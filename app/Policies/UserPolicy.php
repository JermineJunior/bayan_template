<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the user list.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can view the given user.
     */
    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    /**
     * Determine whether the user can update the given user.
     */
    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }

    /**
     * Determine whether the user can delete the given user.
     *
     * An admin can never delete their own account.
     */
    public function delete(User $user, User $model): bool
    {
        return ! $user->is($model) && $user->can('users.delete');
    }

    /**
     * Determine whether the user can deactivate the given user.
     *
     * Uses the same users.edit permission as editing. Deactivating one's own
     * account is forbidden to avoid locking the last admin out with no way to
     * get back in.
     */
    public function deactivate(User $user, User $model): bool
    {
        return ! $user->is($model) && $user->can('users.edit');
    }

    /**
     * Determine whether the user can reactivate the given user.
     */
    public function activate(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }
}
