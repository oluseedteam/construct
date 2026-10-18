<?php

namespace App\Support;

use App\Models\PageContent;
use App\Models\SiteSetting;
use Throwable;

/**
 * Request-scoped accessor for admin-managed settings and page copy.
 * Database values win; config/rsquare.php supplies the defaults.
 */
class Site
{
    private ?array $settings = null;

    private ?array $content = null;

    private ?array $defaults = null;

    /** Drop the in-memory copies so the next read sees freshly saved values. */
    public function flush(): void
    {
        $this->settings = null;
        $this->content = null;
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $this->settings ??= $this->load(fn () => SiteSetting::allCached());

        return array_key_exists($key, $this->settings) && $this->settings[$key] !== null
            ? $this->settings[$key]
            : (config('rsquare.settings', [])[$key] ?? $default);
    }

    public function content(string $key, mixed $default = null): mixed
    {
        $this->content ??= $this->load(fn () => PageContent::allCached());

        // A saved value wins. A blank saved value is treated as "use the default".
        if (array_key_exists($key, $this->content) && $this->content[$key] !== null && $this->content[$key] !== '') {
            return $this->content[$key];
        }

        // Keys such as "home.hero_title" contain dots, so read the flat array directly (config() would treat them as nesting).
        return $this->defaults()[$key] ?? $default;
    }

    /** Flat key => default text, derived from the content schema. */
    public static function schemaDefaults(): array
    {
        $flat = [];

        foreach (config('rsquare.content_schema', []) as $fields) {
            foreach ($fields as $key => $field) {
                $flat[$key] = $field[3] ?? '';
            }
        }

        return $flat;
    }

    private function defaults(): array
    {
        return $this->defaults ??= self::schemaDefaults();
    }

    private function load(callable $loader): array
    {
        try {
            return $loader();
        } catch (Throwable) {
            // e.g. before migrations have run
            return [];
        }
    }
}
