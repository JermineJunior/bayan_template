<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Force-logout a deactivated user on any request, including requests made
     * from a session that was open before the account was deactivated.
     *
     * Runs inside the web group (after StartSession), so the authenticated user
     * is already resolvable from the session. Deactivation therefore takes
     * effect immediately — the very next request logs the user out and returns
     * them to the login screen with a clear message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'username' => 'تم تعطيل هذا الحساب. يرجى التواصل مع المسؤول.',
            ])->redirectTo(route('login'));
        }

        return $next($request);
    }
}
