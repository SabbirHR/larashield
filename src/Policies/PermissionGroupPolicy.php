<?php

namespace Larashield\Policies;

use Larashield\Models\PermissionGroup;
use Larashield\Models\User;
use Larashield\Traits\ResolvesModel;

class PermissionGroupPolicy
{
    use ResolvesModel;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin']) || $user->can('read_permission');
    }

    public function view(User $user, $permissionGroup): bool
    {
        $permissionGroup = $this->resolveModel($permissionGroup, PermissionGroup::class);
        return $user->hasRole('superadmin') || $user->can('read_permission');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->can('create_permission');
    }

    public function update(User $user, $permissionGroup): bool
    {
        $permissionGroup = $this->resolveModel($permissionGroup, PermissionGroup::class);
        return $user->hasRole('superadmin') || $user->can('update_permission');
    }

    public function delete(User $user, $permissionGroup): bool
    {
        $permissionGroup = $this->resolveModel($permissionGroup, PermissionGroup::class);

        $types = $permissionGroup
            ->permission_group_has_permission()
            ->with('permission:id,name')
            ->get()
            ->pluck('permission.name')
            ->toArray();

        $protectedPermissions = config('setup-config.protected_permissions', []);

        if (!empty(array_intersect($types, $protectedPermissions))) {
            return false;
        }
        
        return $user->hasRole('superadmin') || $user->can('delete_permission');
    }

    public function restore(User $user, $permissionGroup): bool
    {
        return false;
    }

    public function forceDelete(User $user, $permissionGroup): bool
    {
        return false;
    }
}
