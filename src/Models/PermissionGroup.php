<?php

namespace Larashield\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;

class PermissionGroup extends Model
{
    protected $table = 'permission_groups';
    protected $primaryKey = 'id'; // Explicitly set
    public $incrementing = true; // Ensure this is true
    protected $keyType = 'int'; // Ensure this is int

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

    // Each permission group has many permission_permission_group
    public function permission_group_has_permission(): HasMany
    {
        return $this->hasMany(
            PermissionPermissionGroup::class,
            'permission_group_id', // must match DB column
            'id'
        )->with('permission'); // eager load Permission automatically
    }
    // Many-to-many with Permission via pivot table
    public function permission(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_permission_group', // pivot table
            'permission_group_id',         // FK in pivot for PermissionGroup
            'permission_id'                // FK in pivot for Permission
        );
    }

    public function getRouteKeyName()
    {
        return 'id'; // This should return 'id'
    }

    /**
     * Retrieve the model for a bound value.
     * This ensures the model is properly retrieved from database
     */
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)->firstOrFail();
    }
}
