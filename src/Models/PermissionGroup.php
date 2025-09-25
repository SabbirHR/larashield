<?php

namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Larashield\Traits\CustomAuditable;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Models\Permission;
use Larashield\Traits\SerializesDateWithTimezone;

class PermissionGroup extends Model implements Auditable
{
    use CustomAuditable, SerializesDateWithTimezone;
    protected $table = 'permission_groups';

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
        'permissions'
    ];

    // Each permission group has many permission_permission_group
    public function permission_group_has_permission(): HasMany
    {
        return $this->hasMany(PermissionPermissionGroup::class);
        return $this->hasMany(
            PermissionPermissionGroup::class,
            'permission_group_id', // must match DB column
            'id'
        )->with('permission'); // eager load Permission automatically
    }
    public function roles()
    {

        return $this->permission_group_has_permission()->with('roles');
    }
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }
}
