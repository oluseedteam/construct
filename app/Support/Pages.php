<?php

namespace App\Support;

use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Cached list of pages for navigation, footer and publish checks.
 * Plain arrays are cached (never Eloquent models) so any cache driver can store them.
 */
class Pages
{
    public const CACHE_KEY = 'pages.all';

    private static ?array $rows = null;

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        self::$rows = null;
    }

    public static function all(): Collection
    {
        if (self::$rows === null) {
            try {
                self::$rows = Cache::rememberForever(self::CACHE_KEY, fn () => Page::ordered()->get()->map(fn (Page $p) => [
                    'key' => $p->key,
                    'title' => $p->title,
                    'slug' => $p->slug,
                    'nav_label' => $p->nav_label,
                    'is_builtin' => $p->is_builtin,
                    'is_published' => $p->is_published,
                    'show_in_nav' => $p->show_in_nav,
                    'show_in_footer' => $p->show_in_footer,
                    'url' => $p->url(),
                ])->all());
            } catch (Throwable) {
                self::$rows = [];
            }

            // Before the pages table is seeded (fresh install) fall back to the built-in defaults, all published.
            if (self::$rows === []) {
                self::$rows = collect(Page::BUILTIN)->map(fn (array $def, string $key) => [
                    'key' => $key,
                    'title' => $def[0],
                    'slug' => $def[1] === '' ? 'home' : $def[1],
                    'nav_label' => $def[2],
                    'is_builtin' => true,
                    'is_published' => true,
                    'show_in_nav' => $key !== 'quote',
                    'show_in_footer' => true,
                    'url' => route($def[3]),
                ])->values()->all();
            }
        }

        return collect(self::$rows);
    }

    public static function published(): Collection
    {
        return self::all()->where('is_published', true)->values();
    }

    public static function nav(): Collection
    {
        return self::published()->where('show_in_nav', true)->values();
    }

    public static function footer(): Collection
    {
        return self::published()->where('show_in_footer', true)->values();
    }

    /** Is a built-in page (by key) visible? Unknown keys and an empty table count as published. */
    public static function isPublished(string $key): bool
    {
        $row = self::all()->firstWhere('key', $key);

        return $row === null ? true : (bool) $row['is_published'];
    }

    /** URL of the first published page among the given keys, or null if none is visible. */
    public static function firstUrl(array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! self::isPublished($key)) {
                continue;
            }
            $row = self::all()->firstWhere('key', $key);

            return $row['url'] ?? (isset(Page::BUILTIN[$key]) ? route(Page::BUILTIN[$key][3]) : null);
        }

        return null;
    }
}
