<?php

namespace App\Models;

use App\Support\Site;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class PageContent extends Model
{
    public const CACHE_KEY = 'page_contents.all';

    protected static function booted(): void
    {
        static::saved(fn () => self::flushCache());
        static::deleted(fn () => self::flushCache());
    }

    /**
     * Forget the cached copy. Bulk query deletes (where()->delete()) fire no model
     * events, so callers that use them must call this themselves.
     */
    public static function flushCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        app(Site::class)->flush();
    }

    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());
    }
}
