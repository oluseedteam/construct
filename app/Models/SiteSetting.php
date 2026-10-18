<?php

namespace App\Models;

use App\Support\Site;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['key', 'value'])]
class SiteSetting extends Model
{
    public const CACHE_KEY = 'site_settings.all';

    protected static function booted(): void
    {
        $flush = function () {
            Cache::forget(self::CACHE_KEY);
            app(Site::class)->flush();
        };

        static::saved($flush);
        static::deleted($flush);
    }

    /** All settings as key => value, cached until an admin changes one. */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());
    }
}
