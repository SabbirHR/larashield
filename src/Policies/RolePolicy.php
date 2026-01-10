<?php

namespace Larashield\Policies;

use Larashield\Models\User;
use Spatie\Permission\Models\Role;
use Larashield\Traits\ResolvesModel;

class RolePolicy
{
    use ResolvesModel;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin'], 'web') || $user->hasPermissionTo('read_role', 'web');
    }

    public function view(User $user, $model): bool
    {
        $model = $this->resolveModel($model, Role::class);
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('read_role', 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('create_role', 'web');
    }

    public function update(User $user, $model): bool
    {
        $model = $this->resolveModel($model, Role::class);
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('update_role', 'web');
    }

    public function delete(User $user, $model): bool
    {
        $model = $this->resolveModel($model, Role::class);
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('delete_role', 'web');
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
