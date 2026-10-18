<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_category_id', 'title', 'path', 'is_demo', 'sort_order'])]
class GalleryImage extends Model
{
    protected function casts(): array
    {
        return ['is_demo' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProjectCategory::class, 'project_category_id');
    }
}
