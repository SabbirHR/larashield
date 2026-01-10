<?php

return [
    // Default Superadmin credentials
    'superadmin' => [
        'name' => 'Super Admin',
        'email' => 'superadmin@gmail.com',
        'password' => env('SUPERADMIN_PASSWORD', 'password'),
        'gender' => 'male',
        'phone' => '+88013xxxxxxxx',
        'role' => 'superadmin',
        'user_type' => 'sadmin'
    ],

    'admin' => [
        'name' => 'Admin',
        'email' => 'admin@gmail.com',
        'password' => env('ADMIN_PASSWORD', 'password'),
        'gender' => 'male',
        'phone' => '+88017xxxxxxxx',
        'role' => 'admin',
        'user_type' => 'admin'
    ],
    'b2b' => [
        'name' => 'B2b',
        'email' => 'b2b@gmail.com',
        'password' => env('B2B_PASSWORD', 'password'),
        'gender' => 'male',
        'phone' => '+880171xxxxxxx',
        'role' => 'b2b',
        'user_type' => 'b2b'
    ],

    'b2c' =>
    [
        'name' => 'B2C',
        'email' => 'b2c@gmail.com',
        'password' => env('B2C_PASSWORD', 'password'),
        'gender' => 'male',
        'phone' => '+88018xxxxxxxx',
        'role' => 'b2c',
        'user_type' => 'b2c'
    ],

    // System roles
    'roles' => [
        'superadmin',
        'admin',
        'b2b',
        'b2c',
    ],

    // Permission groups with permissions
    'permission_groups' => [
        [
            'name' => 'dashboard',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        [
            'name' => 'user',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        [
            'name' => 'role',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        [
            'name' => 'permission',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        [
            'name' => 'audit_log',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ],
        // Admin controllers
        [
            'name' => 'tenant',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'meter_reading',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'payment_method',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'expense',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'room_rent',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'maintenance_request',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'meter_rate',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'room',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'bill',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'property',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'payment',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'meter',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
        [
            'name' => 'room_tenant',
            'permissions' => ['create', 'read', 'update', 'delete'],
        ],
    ],

    // Mapped permissions for roles
    'role_permissions' => [
        'superadmin' => [
            // Dashboard permissions
            'create_dashboard',
            'read_dashboard',
            'update_dashboard',
            'delete_dashboard',

            // User management permissions
            'create_user',
            'read_user',
            'update_user',
            'delete_user',

            // Role management permissions
            'create_role',
            'read_role',
            'update_role',
            'delete_role',

            // Permissions management
            'create_permission',
            'read_permission',
            'update_permission',
            'delete_permission',

            // audit_log management
            'create_audit_log',
            'read_audit_log',
            'update_audit_log',
            'delete_audit_log',

            // Admin controller permissions
            'create_tenant',
            'read_tenant',
            'update_tenant',
            'delete_tenant',
            'create_meter_reading',
            'read_meter_reading',
            'update_meter_reading',
            'delete_meter_reading',
            'create_payment_method',
            'read_payment_method',
            'update_payment_method',
            'delete_payment_method',
            'create_expense',
            'read_expense',
            'update_expense',
            'delete_expense',
            'create_room_rent',
            'read_room_rent',
            'update_room_rent',
            'delete_room_rent',
            'create_maintenance_request',
            'read_maintenance_request',
            'update_maintenance_request',
            'delete_maintenance_request',
            'create_meter_rate',
            'read_meter_rate',
            'update_meter_rate',
            'delete_meter_rate',
            'create_room',
            'read_room',
            'update_room',
            'delete_room',
            'create_bill',
            'read_bill',
            'update_bill',
            'delete_bill',
            'create_property',
            'read_property',
            'update_property',
            'delete_property',
            'create_payment',
            'read_payment',
            'update_payment',
            'delete_payment',
            'create_meter',
            'read_meter',
            'update_meter',
            'delete_meter',
            'create_room_tenant',
            'read_room_tenant',
            'update_room_tenant',
            'delete_room_tenant',
        ],
        'admin' => [
            // Dashboard permissions
            'create_dashboard',
            'read_dashboard',
            'update_dashboard',
            'delete_dashboard',

            // User management permissions
            'create_user',
            'read_user',
            'update_user',
            'delete_user',
        ],
        'b2b' => [
            // Dashboard permissions
            'read_dashboard',
            'update_dashboard',
        ],
        'b2c' => [
            // Dashboard permissions
            'read_dashboard',
            'update_dashboard',
        ],
    ],

    // List of all permissions
    'permissions_list' => [
        // Dashboard permissions
        'create_dashboard',
        'read_dashboard',
        'update_dashboard',
        'delete_dashboard',

        // User management permissions
        'create_user',
        'read_user',
        'update_user',
        'delete_user',

        // Role management permissions
        'create_role',
        'read_role',
        'update_role',
        'delete_role',

        // Permissions management
        'create_permission', // Fixed suffix
        'read_permission',   // Fixed suffix
        'update_permission', // Fixed suffix
        'delete_permission', // Fixed suffix

        // Audit log permissions
        'create_audit_log',
        'read_audit_log',
        'update_audit_log',
        'delete_audit_log',

        // Admin controllers
        'create_tenant',
        'read_tenant',
        'update_tenant',
        'delete_tenant',
        'create_meter_reading',
        'read_meter_reading',
        'update_meter_reading',
        'delete_meter_reading',
        'create_payment_method',
        'read_payment_method',
        'update_payment_method',
        'delete_payment_method',
        'create_expense',
        'read_expense',
        'update_expense',
        'delete_expense',
        'create_room_rent',
        'read_room_rent',
        'update_room_rent',
        'delete_room_rent',
        'create_maintenance_request',
        'read_maintenance_request',
        'update_maintenance_request',
        'delete_maintenance_request',
        'create_meter_rate',
        'read_meter_rate',
        'update_meter_rate',
        'delete_meter_rate',
        'create_room',
        'read_room',
        'update_room',
        'delete_room',
        'create_bill',
        'read_bill',
        'update_bill',
        'delete_bill',
        'create_property',
        'read_property',
        'update_property',
        'delete_property',
        'create_payment',
        'read_payment',
        'update_payment',
        'delete_payment',
        'create_meter',
        'read_meter',
        'update_meter',
        'delete_meter',
        'create_room_tenant',
        'read_room_tenant',
        'update_room_tenant',
        'delete_room_tenant',

    ],


    'protected_roles' => [
        'superadmin',
        'admin',
        'b2b',
        'b2c',
    ],
    /*
    |--------------------------------------------------------------------------
    | Protected Permissions
    |--------------------------------------------------------------------------
    | List of permission names that cannot be deleted or modified
    |--------------------------------------------------------------------------
    */
    'protected_permissions' => [
        'superadmin_access',
        'manage_roles',
        'manage_permissions',
    ],
];
