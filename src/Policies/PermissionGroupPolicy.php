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
        return $user->hasRole(['superadmin', 'admin'], 'web') || $user->hasPermissionTo('read_permission', 'web');
    }

    public function view(User $user, $model): bool
    {
        $model = $this->resolveModel($model, PermissionGroup::class);
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('read_permission', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('create_permission', 'web');
    }

    public function update(User $user, $model): bool
    {
        $model = $this->resolveModel($model, PermissionGroup::class);
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('update_permission', 'web');
    }

    public function delete(User $user, $model): bool
    {
        $model = $this->resolveModel($model, PermissionGroup::class);

        $types = $model
            ->permission_group_has_permission()
            ->with('permission:id,name')
            ->get()
            ->pluck('permission.name')
            ->toArray();

        $protectedPermissions = config('setup-config.protected_permissions', []);

        if (!empty(array_intersect($types, $protectedPermissions))) {
            return false;
        }

        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('delete_permission', 'web');
    }

    public function restore(User $user, $model): bool
    {
        return false;
    }

    public function forceDelete(User $user, $model): bool
    {
        return false;
    }
}
