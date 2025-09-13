<?php

namespace Larashield\Policies;

use Illuminate\Support\Facades\Log;
use Larashield\Models\PermissionGroup;
use Larashield\Models\User;

class PermissionGroupPolicy
{
    /**
     * Determine whether the user can view any permission groups.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin']);
    }

    /**
     * Determine whether the user can view a specific permission group.
     */
    public function view(User $user, PermissionGroup $permissionGroup): bool
    {
        Log::info('[Policy] view() called', [
            'user_id' => $user->id,
            'roles' => $user->getRoleNames()->toArray(),
            'permission_group_id' => $permissionGroup->id,
            'class' => get_class($permissionGroup)
        ]);
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can create permission groups.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can update a permission group.
     */
    public function update(User $user, PermissionGroup $permissionGroup): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can delete a permission group.
     */
    public function delete(User $user, PermissionGroup $permissionGroup): bool
    {
        $types = $permissionGroup
            ->permission_group_has_permission()
            ->with('permission:id,name')
            ->get()
            ->pluck('permission.name')
            ->toArray();

        $protectedPermissions = config('setup-config.protected_permissions', []);

        // Can delete if no protected permission exists AND user is superadmin
        return empty(array_intersect($types, $protectedPermissions)) && $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the permission group.
     */
    public function restore(User $user, PermissionGroup $permissionGroup): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the permission group.
     */
    public function forceDelete(User $user, PermissionGroup $permissionGroup): bool
    {
        return false;
    }
}
