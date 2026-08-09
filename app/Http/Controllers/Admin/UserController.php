<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResetUserPasswordRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a paginated listing of the users.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', [
            'users' => User::with('roles')->orderBy('name')->paginate(10),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $role = Role::findOrFail($request->integer('role_id'));

        // The password is optional: when omitted, the system generates one and
        // shows it once in the success message (internal admin tool, no email).
        $password = $request->filled('password')
            ? $request->string('password')->toString()
            : Str::password(8);

        app(UserService::class)->createUserWithRole([
            'name' => $request->string('name')->toString(),
            'username' => $request->string('username')->toString(),
            'email' => $request->filled('email') ? $request->string('email')->toString() : null,
            'password' => $password,
        ], $role);

        $message = $request->filled('password')
            ? 'تم إنشاء المستخدم.'
            : "تم إنشاء المستخدم. كلمة المرور المولدة: {$password}";

        return redirect()
            ->route('users.index')
            ->with('status', $message);
    }

    /**
     * Show the form for editing the given user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Update the given user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $role = Role::findOrFail($request->integer('role_id'));

        app(UserService::class)->updateUserWithRole($user, [
            'name' => $request->string('name')->toString(),
            'username' => $request->string('username')->toString(),
            'email' => $request->filled('email') ? $request->string('email')->toString() : null,
        ], $role);

        return redirect()
            ->route('users.index')
            ->with('status', 'تم تحديث المستخدم.');
    }

    /**
     * Remove the given user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('status', 'تم حذف المستخدم.');
    }

    /**
     * Set a new password for the given user directly (admin reset).
     */
    public function resetPassword(ResetUserPasswordRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        app(UserService::class)->resetPassword($user, $request->string('password')->toString());

        return redirect()
            ->route('users.edit', $user)
            ->with('status', 'تم تعيين كلمة المرور الجديدة.');
    }

    /**
     * Deactivate the given user's account.
     *
     * The account record is kept — only login access is revoked, enforced at
     * the login level and by EnsureUserIsActive for open sessions.
     */
    public function deactivate(User $user): RedirectResponse
    {
        $this->authorize('deactivate', $user);

        $user->update(['is_active' => false]);

        return redirect()
            ->route('users.index')
            ->with('status', "تم تعطيل حساب {$user->name}.");
    }

    /**
     * Reactivate the given user's account.
     */
    public function activate(User $user): RedirectResponse
    {
        $this->authorize('activate', $user);

        $user->update(['is_active' => true]);

        return redirect()
            ->route('users.index')
            ->with('status', "تم تفعيل حساب {$user->name}.");
    }

    /**
     * Roles available for assignment, ordered by name.
     *
     * @return Collection<int, Role>
     */
    protected function roles(): Collection
    {
        return Role::orderBy('name')->get();
    }
}
