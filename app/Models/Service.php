<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'name', 'slug', 'icon', 'description', 'main_image', 'content',
    'benefits', 'features', 'is_published', 'sort_order',
])]
class Service extends Model
{
    protected function casts(): array
    {
        return [
            'benefits' => 'array',
            'features' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public const NAV_CACHE_KEY = 'nav.services';

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::NAV_CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::NAV_CACHE_KEY));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}
