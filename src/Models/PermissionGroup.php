<?php

namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class PermissionGroup extends Model
{
    protected $fillable = [
        'name',
    ];
    public $allowedFields = [
        'id',
        'name',

    ];
    public $allowedFilters = [
        'id',
        'name',

    ];
    public $allowedSorts = [
        'id',
        'name',
    ];
    public $allowedIncludes = [
        'permission_group_has_permission',
        'permission'
    ];

    public function permission_group_has_permission(): HasMany
    {
        return $this->hasMany(PermissionPermissionGroup::class,'permission_group_id', 'id');
    }
    public function roles()
    {

        return $this->permission_group_has_permission()->with('roles');
    }
    public function permission(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
