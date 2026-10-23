<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/**
 * Only a Super Admin manages accounts, and only Admin accounts. Super Admin
 * accounts are never managed in the app: the first one comes from the seeder.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isSuperAdmin() && $target->role === Role::Admin;
    }

    public function delete(User $user, User $target): bool
    {
        return $this->update($user, $target);
    }

    public function resetPassword(User $user, User $target): bool
    {
        return $this->update($user, $target);
    }
}
