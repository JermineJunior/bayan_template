<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the application settings screen.
     *
     * The current app name and logo are provided by the shared view
     * composer (AppServiceProvider).
     */
    public function edit(): View
    {
        $this->authorize('settings.edit');

        return view('admin.settings.edit');
    }

    /**
     * Update the application settings.
     */
    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $this->authorize('settings.edit');

        $settings = app(SettingsService::class);
        $settings->set('app_name', $request->string('app_name')->toString());

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->storePublicly('logos', 'public');
            $settings->set('logo_path', $path);
        }

        return redirect()
            ->route('settings.edit')
            ->with('status', 'تم تحديث الإعدادات.');
    }
}
