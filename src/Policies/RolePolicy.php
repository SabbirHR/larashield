<?php

namespace Larashield\Policies;

use Spatie\Permission\Models\Role;
use Larashield\Traits\ResolvesModel;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization, ResolvesModel;

    public function viewAny($user): bool
    { 
        return $user->hasRole(['superadmin','admin']); 
    }
    
    public function view($user, $modelOrId): bool
    { 
        $model = $this->resolveModel($modelOrId, Role::class);
        
        if (!$model) {
            return false;
        }
        
        return $user->hasRole('superadmin'); 
    }
    
    public function create($user): bool
    { 
        return $user->hasRole('superadmin'); 
    }
    
    public function update($user, $modelOrId): bool
    { 
        $model = $this->resolveModel($modelOrId, Role::class);
        
        if (!$model) {
            return false;
        }
        
        return $user->hasRole('superadmin'); 
    }
    
    public function delete($user, $modelOrId): bool
    { 
        $model = $this->resolveModel($modelOrId, Role::class);
        
        if (!$model) {
            return false;
        }
        
        return $user->hasRole('superadmin'); 
    }
}
