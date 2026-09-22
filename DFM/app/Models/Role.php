<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * @return HasMany<RolePermission>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(RolePermission::class);
    }

    /**
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        // Super Admin (system role) has all permissions implicitly
        if ($this->is_system) {
            return true;
        }

        return $this->permissions()
            ->where('permission_key', $permission)
            ->exists();
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function syncPermissions(array $permissions): void
    {
        // Cannot modify system roles
        if ($this->is_system) {
            return;
        }

        $this->permissions()->delete();

        foreach ($permissions as $permission) {
            $this->permissions()->create([
                'permission_key' => $permission,
            ]);
        }
    }

    /**
     * @return array<int, string>
     */
    public function getPermissionKeys(): array
    {
        return $this->permissions()
            ->pluck('permission_key')
            ->toArray();
    }
}
