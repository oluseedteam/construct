<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'name', 'email', 'phone', 'project_type', 'project_location', 'estimated_budget',
    'preferred_start_date', 'description', 'document_path', 'document_name', 'status',
    'internal_notes', 'ip_address', 'admin_notified_at', 'customer_notified_at',
])]
class QuoteRequest extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'quoted' => 'Quoted',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
    ];

    public const PROJECT_TYPES = [
        'Residential', 'Commercial', 'Infrastructure', 'Renovation', 'Design & Build', 'Other',
    ];

    public const BUDGETS = [
        'Under $50,000', '$50,000 - $150,000', '$150,000 - $500,000',
        '$500,000 - $1,000,000', 'Over $1,000,000', 'Not sure yet',
    ];

    protected function casts(): array
    {
        return [
            'preferred_start_date' => 'date',
            'admin_notified_at' => 'datetime',
            'customer_notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (QuoteRequest $quote) {
            $quote->reference ??= static::generateReference();
        });
    }

    /** Unique, human-friendly reference such as RSQ-20261018-K7P2QX. */
    public static function generateReference(): string
    {
        do {
            $reference = 'RSQ-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuoteResponse::class)->latest();
    }
}
