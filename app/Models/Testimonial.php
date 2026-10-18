<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['author_name', 'author_role', 'content', 'rating', 'is_approved', 'sort_order'])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return ['is_approved' => 'boolean'];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }
}
