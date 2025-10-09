<?php

namespace Larashield\database\seeders;

use Illuminate\Database\Seeder;
use Larashield\Models\PermissionGroup;
use Larashield\Models\PermissionPermissionGroup;
use Larashield\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SetupConfigSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🌱 Seeding Larashield roles, users, and permissions...');

        // Use default guard from config
        $guard = config('setup-config.default_guard', 'web');

        // 1️⃣ Create roles from config
        foreach (config('setup-config.roles', []) as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }

        // 2️⃣ Create Superadmin user
        $superadminData = config('setup-config.superadmin');
        $superadmin = User::firstOrCreate(
            ['email' => $superadminData['email']],
            [
                'name' => $superadminData['name'],
                'password' => bcrypt($superadminData['password']),
                'gender' => $superadminData['gender'] ?? null,
                'phone' => $superadminData['phone'] ?? null,
                'user_type' => $superadminData['user_type'] ?? null,
            ]
        );

        // Assign superadmin role
        $superadminRole = Role::where('name', 'superadmin')->first();
        if ($superadminRole && !$superadmin->hasRole($superadminRole)) {
            $superadmin->assignRole($superadminRole);
        }

        // 3️⃣ Create other users (admin, b2b, b2c)
        foreach (['admin', 'b2b', 'b2c'] as $type) {
            $data = config("setup-config.{$type}", []);
            if (!$data) continue;

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt($data['password']),
                    'gender' => $data['gender'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'user_type' => $data['user_type'] ?? null,
                ]
            );

            $role = Role::where('name', $type)->first();
            if ($role && !$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        // 4️⃣ Create permission groups and permissions
        foreach (config('setup-config.permission_groups', []) as $groupData) {
            $group = PermissionGroup::firstOrCreate(['name' => $groupData['name']]);
            $permissions = $groupData['permissions'] ?? []; // default empty if missing

            foreach ($permissions as $permissionName) {
                $permissionFullName = $permissionName . '_' . $groupData['name'];
                $permission = Permission::firstOrCreate([
                    'name' => $permissionFullName,
                    'guard_name' => $guard,
                ]);

                // Map permission to group
                PermissionPermissionGroup::firstOrCreate([
                    'permission_group_id' => $group->id,
                    'permission_id' => $permission->id,
                    'type' => $permissionName,
                ]);
            }
        }

        // 5️⃣ Assign permissions to roles
        foreach (config('setup-config.role_permissions', []) as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (!$role) continue;

            foreach ($permissions as $permissionName) {
                $permission = Permission::where('name', $permissionName)->first();
                if ($permission && !$role->hasPermissionTo($permission)) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        $this->command->info('✅ Larashield seeding complete!');
    }
}
