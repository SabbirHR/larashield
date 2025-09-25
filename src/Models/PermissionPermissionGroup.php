<?php

namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Larashield\Traits\CustomAuditable;
use Larashield\Traits\SerializesDateWithTimezone;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionPermissionGroup extends Model implements Auditable
{
    use CustomAuditable, SerializesDateWithTimezone;
    protected $guarded = [];
    protected $table = 'permission_permission_group';
    protected $hidden = ['created_at', 'updated_at'];

    // Belongs to a Permission
    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class, 'permission_id');
    }

    // Many-to-many with Role
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions', 'permission_id', 'role_id');
    }
}
