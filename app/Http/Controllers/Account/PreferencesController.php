<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UpdatePreferencesRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PreferencesController extends Controller
{
    /**
     * Show the authenticated user's preferences.
     */
    public function edit(): View
    {
        return view('account.preferences', [
            'fontSize' => auth()->user()->font_size,
        ]);
    }

    /**
     * Update the authenticated user's preferences.
     */
    public function update(UpdatePreferencesRequest $request): RedirectResponse
    {
        auth()->user()->update([
            'font_size' => $request->string('font_size')->toString(),
        ]);

        flash()->success('تم تحديث التفضيلات.');

        return redirect()
            ->route('account.preferences.edit');
    }
}
