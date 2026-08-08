<?php

namespace App\Services;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Assign a single role to a user, replacing any previous one.
     *
     * Business rule: a user holds exactly ONE role in this application
     * (permissions are granted to roles, and roles to users 1:1). This
     * method is the enforcement point — syncRoles() detaches any existing
     * role(s) before assigning the new one, so a user can never end up
     * with two roles through this service.
     *
     * Use it everywhere a role is assigned (e.g. from seeders and the
     * upcoming user management screen) instead of calling Spatie's
     * assignRole()/syncRoles() directly.
     */
    public function assignRole(User $user, Role|string $role): User
    {
        $user->syncRoles($role);

        return $user;
    }

    /**
     * Create a user and assign it a single role.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function createUserWithRole(array $attributes, Role|string $role): User
    {
        $user = User::create($attributes);

        return $this->assignRole($user, $role);
    }

    /**
     * Update a user's attributes and reassign its single role.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function updateUserWithRole(User $user, array $attributes, Role|string $role): User
    {
        $user->update($attributes);

        return $this->assignRole($user, $role);
    }

    /**
     * Set a new password for a user directly.
     *
     * Internal admin action (locked-out users) — no email, no token. The
     * new password takes effect immediately via the `hashed` cast.
     */
    public function resetPassword(User $user, string $password): User
    {
        $user->update(['password' => $password]);

        return $user;
    }
}
