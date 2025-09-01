<?php
namespace Larashield\Policies;

use Larashield\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasRole(['superadmin','admin']);
    }

    public function view(User $user, User $model): bool
    {
        $excludedRoles = ['b2b','b2c'];
        return $user->hasRole('superadmin') && !$model->hasRole($excludedRoles);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('superadmin');
    }

    public function update(User $user, User $model): bool
    {
        $excludedRoles = ['b2b','b2c','superadmin'];
        return $user->hasRole('superadmin') && !$model->hasRole($excludedRoles);
    }

    public function delete(User $user, User $model): bool
    {
        $excludedRoles = ['b2b','b2c','superadmin'];
        return $user->hasRole('superadmin') && !$model->hasRole($excludedRoles);
    }

    public function restore(User $user, User $model): bool { return false; }
    public function forceDelete(User $user, User $model): bool { return false; }
}
