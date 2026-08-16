<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a paginated listing of the current user's notifications,
     * newest first (the relation's default ordering).
     */
    public function index(): View
    {
        return view('notifications.index', [
            'notifications' => auth()->user()->notifications()->paginate(15),
        ]);
    }

    /**
     * Mark a single notification as read.
     *
     * Supports both a normal form submit (redirect back) and an AJAX request
     * (from the topbar dropdown, so the page is not reloaded).
     */
    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        auth()->user()->notifications()->findOrFail($id)->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        flash()->success('تم تحديد الإشعار كمقروء.');

        return back();
    }

    /**
     * Mark every unread notification as read.
     */
    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()->unreadNotifications->markAsRead();

        flash()->success('تم تحديد جميع الإشعارات كمقروءة.');

        return back();
    }
}
