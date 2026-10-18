<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'subject', 'message', 'status', 'read_at', 'ip_address', 'admin_notified_at'])]
class ContactMessage extends Model
{
    public const STATUSES = ['new' => 'New', 'read' => 'Read', 'archived' => 'Archived'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime', 'admin_notified_at' => 'datetime'];
    }
}
