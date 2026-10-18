<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title', 'slug', 'employment_type', 'location', 'summary', 'description',
    'requirements', 'is_published', 'closes_at', 'sort_order',
])]
class JobOpening extends Model
{
    public const TYPES = ['Full-time', 'Part-time', 'Contract', 'Seasonal', 'Apprenticeship'];

    protected function casts(): array
    {
        return [
            'requirements' => 'array',
            'is_published' => 'boolean',
            'closes_at' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    /** Published and not past its closing date. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn ($q) => $q->whereNull('closes_at')->orWhere('closes_at', '>=', today()));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('id');
    }

    public function isOpen(): bool
    {
        return $this->is_published && (! $this->closes_at || $this->closes_at->gte(today()));
    }
}
