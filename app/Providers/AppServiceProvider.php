<?php

namespace App\Providers;

use App\Policies\RolePolicy;
use App\Services\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->shareThemeWithLayouts();
        $this->shareAppSettingsWithLayouts();

        // Spatie's Role lives outside App\Models, so its policy is not picked up
        // by Laravel's convention-based discovery and is registered explicitly.
        Gate::policy(Role::class, RolePolicy::class);
    }

    /**
     * Share the active theme (from the theme cookie) with every view,
     * including layouts and their Blade components.
     */
    protected function shareThemeWithLayouts(): void
    {
        $labels = [
            'light' => 'فاتح',
            'dark' => 'داكن',
        ];

        ViewFacade::composer('*', function (View $view) use ($labels): void {
            $themes = config('themes.themes', ['light', 'dark']);
            $theme = request()->cookie(config('themes.cookie', 'theme'), config('themes.default', 'light'));

            if (! in_array($theme, $themes, true)) {
                $theme = config('themes.default', 'light');
            }

            $view->with([
                'theme' => $theme,
                'themes' => $themes,
                'themeLabels' => collect($themes)->mapWithKeys(
                    fn (string $name): array => [$name => $labels[$name] ?? $name],
                )->all(),
            ]);
        });
    }

    /**
     * Share app-wide settings (name, logo) and the authenticated user's
     * font-size preference with every view.
     *
     * Settings are cached by SettingsService; the font size is read straight
     * from the logged-in user (guests always fall back to the default size,
     * which is what the guest layout renders).
     */
    protected function shareAppSettingsWithLayouts(): void
    {
        ViewFacade::composer('*', function (View $view): void {
            $settings = app(SettingsService::class);
            $logoPath = $settings->get('logo_path');

            $view->with([
                'appName' => $settings->get('app_name', config('app.name', 'Vibe')),
                'logoUrl' => $logoPath !== null ? Storage::disk('public')->url($logoPath) : null,
                'userFontSize' => optional(auth()->user())->font_size ?? 'default',
            ]);
        });
    }
}
