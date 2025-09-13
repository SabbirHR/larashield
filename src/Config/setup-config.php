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
        'phone' => '+88017xxxxxxxx',
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
            'name' => 'permissions',
            'permissions' => [
                'create',
                'read',
                'update',
                'delete',
            ],
        ]
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
            'create_permissions',
            'read_permissions',
            'update_permissions',
            'delete_permissions',
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
        'create_permissions',
        'read_permissions',
        'update_permissions',
        'delete_permissions',
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
    */
    'protected_permissions' => [
        'superadmin_access',
        'manage_roles',
        'manage_permissions',
    ],
];
