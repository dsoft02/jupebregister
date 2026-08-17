<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsService
{
    /**
     * Cache key holding the full settings map.
     */
    public const CACHE_KEY = 'settings.map';

    /**
     * Read a setting value straight from the database, bypassing the cache.
     */
    public function raw(string $key): ?string
    {
        return Setting::where('key', $key)->value('value');
    }

    /**
     * Read a setting value from the cache-backed map.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Absolute URL for an uploaded file asset (letterhead, stamp, signature).
     */
    public function fileUrl(string $key): ?string
    {
        $path = $this->get($key);

        if (blank($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * Absolute filesystem path used as a DomPDF background image.
     */
    public function filePath(string $key): ?string
    {
        $path = $this->get($key);

        if (blank($path)) {
            return null;
        }

        $full = Storage::disk('public')->path($path);

        return file_exists($full) ? $full : null;
    }

    /**
     * Base64 data-URI for an uploaded image, safe to embed directly in DomPDF.
     */
    public function fileAsDataUri(string $key): ?string
    {
        $path = $this->filePath($key);

        if (! $path) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path));
    }

    /**
     * Persist one or many settings and flush the cache.
     */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value],
            );
        }

        $this->forget();
    }

    /**
     * The cached map of every setting the application manages.
     *
     * @return array<string, string|null>
     */
    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $rows = Setting::whereIn('key', Setting::allKeys())->pluck('value', 'key');

            return collect(Setting::allKeys())
                ->mapWithKeys(fn (string $key) => [$key => $rows[$key] ?? null])
                ->all();
        });
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
