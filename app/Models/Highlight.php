<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['group', 'title', 'text', 'icon', 'is_visible', 'sort_order'])]
class Highlight extends Model
{
    public const GROUPS = [
        'why' => 'Why Choose Us cards',
        'commitments' => 'About section tick-list',
    ];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /** Visible items for a group, in display order. */
    public static function forGroup(string $group): Collection
    {
        return static::where('group', $group)->visible()->orderBy('sort_order')->orderBy('id')->get();
    }
}
