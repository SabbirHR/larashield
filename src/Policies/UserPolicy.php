<?php

namespace Larashield\Policies;

use Larashield\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Larashield\Traits\ResolvesModel;

class UserPolicy
{
    use HandlesAuthorization, ResolvesModel;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin','admin']) || $user->can('read_user');
    }

    public function view(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b','b2c'];
        return ($user->hasRole('superadmin') || $user->can('read_user')) && !$model->hasRole($excludedRoles);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin') || $user->can('create_user');
    }

    public function update(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b','b2c','superadmin'];
        return ($user->hasRole('superadmin') || $user->can('update_user')) && !$model->hasRole($excludedRoles);
    }

    public function delete(User $user, $model): bool
    {
        $model = $this->resolveModel($model, User::class);
        $excludedRoles = ['b2b','b2c'];
        return ($user->hasRole('superadmin') || $user->can('delete_user')) && !$model->hasRole($excludedRoles);
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
