<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // Default system roles
        $roles = [
            ['name' => 'Super Admin',       'code' => 'SUPER_ADMIN',       'scope_type' => 'platform'],
            ['name' => 'Organization Admin','code' => 'ORG_ADMIN',         'scope_type' => 'organization'],
            ['name' => 'School Admin',      'code' => 'SCHOOL_ADMIN',      'scope_type' => 'school'],
            ['name' => 'Principal',         'code' => 'PRINCIPAL',         'scope_type' => 'school'],
            ['name' => 'Teacher',           'code' => 'TEACHER',           'scope_type' => 'school'],
            ['name' => 'Accountant',        'code' => 'ACCOUNTANT',        'scope_type' => 'school'],
            ['name' => 'Librarian',         'code' => 'LIBRARIAN',         'scope_type' => 'school'],
            ['name' => 'Transport Manager', 'code' => 'TRANSPORT_MANAGER', 'scope_type' => 'school'],
            ['name' => 'HR Manager',        'code' => 'HR_MANAGER',        'scope_type' => 'organization'],
            ['name' => 'Student',           'code' => 'STUDENT',           'scope_type' => 'school'],
            ['name' => 'Parent',            'code' => 'PARENT',            'scope_type' => 'school'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['code' => $role['code']],
                [
                    'name' => $role['name'],
                    'scope_type' => $role['scope_type'],
                    'description' => $role['name'] . ' system role',
                    'is_system' => true,
                    'is_active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        // Modules and their supported actions
        $modules = [
            'dashboard' => ['view'],
            'users' => ['view', 'create', 'update', 'delete'],
            'roles' => ['view', 'create', 'update', 'delete', 'assign'],
            'organizations' => ['view', 'create', 'update', 'delete'],
            'schools' => ['view', 'create', 'update', 'delete'],
            'campuses' => ['view', 'create', 'update', 'delete'],
            'students' => ['view', 'create', 'update', 'delete', 'export'],
            'guardians' => ['view', 'create', 'update', 'delete'],
            'staff' => ['view', 'create', 'update', 'delete'],
            'academics' => ['view', 'create', 'update', 'delete'],
            'attendance' => ['view', 'create', 'update', 'export'],
            'homework' => ['view', 'create', 'update', 'delete'],
            'examinations' => ['view', 'create', 'update', 'delete', 'publish'],
            'admissions' => ['view', 'create', 'update', 'delete'],
            'fees' => ['view', 'create', 'update', 'delete', 'collect', 'refund'],
            'finance' => ['view', 'create', 'update', 'delete', 'export'],
            'hr' => ['view', 'create', 'update', 'delete'],
            'payroll' => ['view', 'create', 'update', 'process'],
            'transport' => ['view', 'create', 'update', 'delete'],
            'hostel' => ['view', 'create', 'update', 'delete'],
            'library' => ['view', 'create', 'update', 'delete'],
            'inventory' => ['view', 'create', 'update', 'delete'],
            'communications' => ['view', 'create', 'update', 'delete'],
            'reports' => ['view', 'export'],
            'settings' => ['view', 'update'],
            'audit' => ['view', 'export'],
        ];

        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $code = strtoupper($module . '.' . $action);

                DB::table('permissions')->updateOrInsert(
                    ['code' => $code],
                    [
                        'name' => ucfirst($module) . ' ' . ucfirst($action),
                        'module' => $module,
                        'action' => $action,
                        'description' => 'Permission to ' . $action . ' ' . $module,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        // Super Admin receives every system permission
        $superAdmin = DB::table('roles')
            ->where('code', 'SUPER_ADMIN')
            ->first();

        $permissions = DB::table('permissions')->get();

        foreach ($permissions as $permission) {
            DB::table('role_permissions')->updateOrInsert(
                [
                    'role_id' => $superAdmin->id,
                    'permission_id' => $permission->id,
                ],
                [
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
