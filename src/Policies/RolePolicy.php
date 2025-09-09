<?php
namespace Larashield\Policies;

class RolePolicy
{
    public function viewAny($user) { return $user->hasRole(['superadmin','admin']); }
    public function view($user,$model) { return $user->hasRole('superadmin'); }
    public function create($user) { return $user->hasRole('superadmin'); }
    public function update($user,$model) { return $user->hasRole('superadmin'); }
    public function delete($user,$model) { return $user->hasRole('superadmin'); }
}
