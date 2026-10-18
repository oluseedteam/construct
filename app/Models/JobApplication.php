<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'reference', 'job_opening_id', 'position', 'name', 'email', 'phone', 'city', 'experience',
    'available_from', 'profile_url', 'cover_letter', 'resume_path', 'resume_name', 'status',
    'internal_notes', 'ip_address', 'admin_notified_at', 'applicant_notified_at',
])]
class JobApplication extends Model
{
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Reviewing',
        'interview' => 'Interview',
        'hired' => 'Hired',
        'rejected' => 'Not selected',
    ];

    public const EXPERIENCE = ['Less than 1 year', '1-3 years', '3-5 years', '5-10 years', '10+ years'];

    public const GENERAL = 'General application';

    protected function casts(): array
    {
        return [
            'available_from' => 'date',
            'admin_notified_at' => 'datetime',
            'applicant_notified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (JobApplication $application) {
            $application->reference ??= static::generateReference();
        });
    }

    public static function generateReference(): string
    {
        do {
            $reference = 'APP-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (static::where('reference', $reference)->exists());

        return $reference;
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class, 'job_opening_id');
    }
}
