<?php

namespace Larashield\Services;

use Larashield\Models\PermissionGroup;
use Larashield\Models\PermissionPermissionGroup;
use Spatie\Permission\Models\Permission;

class PermissionService
{
    /**
     * Create a Permission Group with CRUD permissions
     */
    public function createPermissionGroup(string $name): PermissionGroup
    {
        $group = PermissionGroup::create(['name' => $name]);
        $crud = ['create', 'read', 'update', 'delete'];

        foreach ($crud as $c) {
            $permission = Permission::create([
                'name'       => $c . '_' . $group->name,
                'guard_name' => 'web',
            ]);

            PermissionPermissionGroup::create([
                'type'                => $c,
                'permission_id'       => $permission->id,
                'permission_group_id' => $group->id,
            ]);
        }

        return $group;
    }

    /**
     * Delete a Permission Group and its related permissions
     */
    public function deletePermissionGroup(int $id): bool
    {
        $relations = PermissionPermissionGroup::where('permission_group_id', $id)->get();
        $permissionIds = $relations->pluck('permission_id');

        // Ensure audit logs if model has auditing
        $relations->each->delete();

        Permission::destroy($permissionIds);
        PermissionGroup::destroy($id);

        return true;
    }
}
