<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared policy for admin-managed content. Active staff (admin or editor) can
 * view, create and edit; deleting is reserved for administrators.
 */
class ContentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessAdmin();
    }

    public function view(User $user, Model $model): bool
    {
        return $user->canAccessAdmin();
    }

    public function create(User $user): bool
    {
        return $user->canAccessAdmin();
    }

    public function update(User $user, Model $model): bool
    {
        return $user->canAccessAdmin();
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->isAdmin() && $user->is_active;
    }
}
