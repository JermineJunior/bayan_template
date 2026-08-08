<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache key prefix. Each setting is cached individually so a single
     * update only invalidates its own entry.
     */
    public const CACHE_PREFIX = 'settings.';

    /**
     * Read a setting, falling back to the given default when the key is
     * missing or has no value. Results are cached indefinitely and the
     * cache entry is forgotten whenever the setting is written.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return Cache::rememberForever(
            self::CACHE_PREFIX.$key,
            fn (): ?string => Setting::find($key)?->value ?? $default,
        );
    }

    /**
     * Persist a setting and invalidate its cached value.
     */
    public function set(string $key, ?string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );

        $this->forget($key);
    }

    /**
     * Whether a setting has a non-null value.
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Remove the cached value for a setting.
     */
    public function forget(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX.$key);
    }
}
