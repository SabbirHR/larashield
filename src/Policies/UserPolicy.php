<?php

namespace Larashield\Policies;

use Larashield\Models\User;
use Larashield\Traits\ResolvesModel;

class UserPolicy
{
    use ResolvesModel;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin', 'admin'], 'web') || $user->hasPermissionTo('read_user', 'web');
    }

    public function view(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b', 'b2c'];
        return ($user->hasRole('superadmin', 'web') || $user->hasPermissionTo('read_user', 'web')) && !$model->hasRole($excludedRoles, 'web');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin', 'web') || $user->hasPermissionTo('create_user', 'web');
    }

    public function update(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b', 'b2c', 'superadmin'];
        return ($user->hasRole('superadmin', 'web') || $user->hasPermissionTo('update_user', 'web')) && !$model->hasRole($excludedRoles, 'web');
    }

    public function delete(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b', 'b2c'];
        return ($user->hasRole('superadmin', 'web') || $user->hasPermissionTo('delete_user', 'web')) && !$model->hasRole($excludedRoles, 'web');
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
