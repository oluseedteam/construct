<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['label', 'icon', 'value', 'prefix', 'suffix', 'is_visible', 'sort_order'])]
class CompanyStat extends Model
{
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'value' => 'integer'];
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order');
    }
}
