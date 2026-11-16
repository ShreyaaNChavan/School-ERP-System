<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CreateStaffRoleAccountsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            $organizationId = 2;
            $schoolId = 1;
            $campusId = 1;

            $now = now();

            /*
            |--------------------------------------------------------------------------
            | Validate organization, school and campus
            |--------------------------------------------------------------------------
            */

            $organization = DB::table('organizations')
                ->where('id', $organizationId)
                ->first();

            $school = DB::table('schools')
                ->where('id', $schoolId)
                ->where('organization_id', $organizationId)
                ->first();

            $campus = DB::table('campuses')
                ->where('id', $campusId)
                ->where('school_id', $schoolId)
                ->first();

            if (!$organization || !$school || !$campus) {
                throw new RuntimeException(
                    'Organization, school or campus configuration is invalid.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Accounts configuration
            |--------------------------------------------------------------------------
            */

            $accounts = [

                [
                    'name' => 'Librarian',
                    'first_name' => 'Librarian',
                    'last_name' => 'Staff',
                    'email' => 'librarian.test@caspa.local',
                    'role_name' => 'Librarian',
                    'role_code' => 'LIBRARIAN',
                    'scope' => 'school',
                    'module' => 'library',
                    'employee_number' => 'SNX-LIB-001',
                    'designation' => 'Librarian',
                    'department' => 'Library',
                ],

                [
                    'name' => 'Hostel Warden',
                    'first_name' => 'Hostel',
                    'last_name' => 'Warden',
                    'email' => 'warden.test@caspa.local',
                    'role_name' => 'Hostel Warden',
                    'role_code' => 'HOSTEL_WARDEN',
                    'scope' => 'school',
                    'module' => 'hostel',
                    'employee_number' => 'SNX-HOS-001',
                    'designation' => 'Hostel Warden',
                    'department' => 'Hostel',
                ],

                [
                    'name' => 'Transport Manager',
                    'first_name' => 'Transport',
                    'last_name' => 'Manager',
                    'email' => 'transport.test@caspa.local',
                    'role_name' => 'Transport Manager',
                    'role_code' => 'TRANSPORT_MANAGER',
                    'scope' => 'school',
                    'module' => 'transport',
                    'employee_number' => 'SNX-TRN-001',
                    'designation' => 'Transport Manager',
                    'department' => 'Transport',
                ],

                [
                    'name' => 'HR Manager',
                    'first_name' => 'HR',
                    'last_name' => 'Manager',
                    'email' => 'hr.test@caspa.local',
                    'role_name' => 'HR Manager',
                    'role_code' => 'HR_MANAGER',
                    'scope' => 'organization',
                    'module' => 'hr',
                    'employee_number' => 'SNX-HR-001',
                    'designation' => 'HR Manager',
                    'department' => 'Human Resources',
                ],

                [
                    'name' => 'Accountant',
                    'first_name' => 'Accountant',
                    'last_name' => 'Staff',
                    'email' => 'accountant.test@caspa.local',
                    'role_name' => 'Accountant',
                    'role_code' => 'ACCOUNTANT',
                    'scope' => 'school',
                    'module' => 'fees',
                    'employee_number' => 'SNX-ACC-001',
                    'designation' => 'Accountant',
                    'department' => 'Finance',
                ],

            ];

            /*
            |--------------------------------------------------------------------------
            | Create Hostel Warden role
            |--------------------------------------------------------------------------
            */

            DB::table('roles')->updateOrInsert(
                ['code' => 'HOSTEL_WARDEN'],
                [
                    'name' => 'Hostel Warden',
                    'scope_type' => 'school',
                    'description' => 'Manages hostel operations and records.',
                    'is_system' => 0,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Create users, staff profiles, role assignments and permissions
            |--------------------------------------------------------------------------
            */

            foreach ($accounts as $account) {

                /*
                | Find role
                */

                $role = DB::table('roles')
                    ->where('code', $account['role_code'])
                    ->first();

                if (!$role) {
                    throw new RuntimeException(
                        'Role not found: ' . $account['role_code']
                    );
                }

                /*
                | Create or update user
                */

                $userId = DB::table('users')
                    ->where('email', $account['email'])
                    ->value('id');

                $userData = [
                    'name' => $account['name'],
                    'password' => Hash::make('Caspa@123'),
                    'updated_at' => $now,
                ];

                if ($userId) {
                    DB::table('users')
                        ->where('id', $userId)
                        ->update($userData);
                } else {
                    $userData['email'] = $account['email'];
                    $userData['created_at'] = $now;

                    $userId = DB::table('users')
                        ->insertGetId($userData);
                }

                /*
                | Create or update staff profile
                */

                $profile = DB::table('staff_profiles')
                    ->where('user_id', $userId)
                    ->first();

                $profileData = [
                    'school_id' => $schoolId,
                    'campus_id' => $campusId,
                    'first_name' => $account['first_name'],
                    'last_name' => $account['last_name'],
                    'designation' => $account['designation'],
                    'department' => $account['department'],
                    'employment_type' => 'full_time',
                    'joining_date' => now()->toDateString(),
                    'status' => 'active',
                    'updated_at' => $now,
                ];

                if ($profile) {

                    DB::table('staff_profiles')
                        ->where('user_id', $userId)
                        ->update($profileData);

                } else {

                    $employeeNumberExists = DB::table('staff_profiles')
                        ->where('employee_number', $account['employee_number'])
                        ->exists();

                    if ($employeeNumberExists) {
                        throw new RuntimeException(
                            'Employee number already exists: ' .
                            $account['employee_number']
                        );
                    }

                    $profileData['user_id'] = $userId;
                    $profileData['employee_number'] =
                        $account['employee_number'];
                    $profileData['created_at'] = $now;

                    DB::table('staff_profiles')->insert($profileData);
                }

                /*
                | Assign role at correct scope
                */

                $assignmentQuery = DB::table('user_role_assignments')
                    ->where('user_id', $userId)
                    ->where('role_id', $role->id)
                    ->where('organization_id', $organizationId);

                if ($account['scope'] === 'school') {
                    $assignmentQuery
                        ->where('school_id', $schoolId)
                        ->where('campus_id', $campusId);
                } else {
                    $assignmentQuery
                        ->whereNull('school_id')
                        ->whereNull('campus_id');
                }

                $assignment = $assignmentQuery->first();

                $assignmentData = [
                    'organization_id' => $organizationId,
                    'school_id' => $account['scope'] === 'school'
                        ? $schoolId : null,
                    'campus_id' => $account['scope'] === 'school'
                        ? $campusId : null,
                    'is_active' => 1,
                    'starts_at' => null,
                    'expires_at' => null,
                    'updated_at' => $now,
                ];

                if ($assignment) {

                    DB::table('user_role_assignments')
                        ->where('id', $assignment->id)
                        ->update($assignmentData);

                } else {

                    $assignmentData['user_id'] = $userId;
                    $assignmentData['role_id'] = $role->id;
                    $assignmentData['assigned_by'] = null;
                    $assignmentData['created_at'] = $now;

                    DB::table('user_role_assignments')
                        ->insert($assignmentData);
                }

                /*
                | Attach all existing permissions for the module
                */

                $permissions = DB::table('permissions')
                    ->where('module', $account['module'])
                    ->get();

                if ($permissions->isEmpty()) {
                    throw new RuntimeException(
                        'No permissions found for module: ' .
                        $account['module']
                    );
                }

                foreach ($permissions as $permission) {

                    $exists = DB::table('role_permissions')
                        ->where('role_id', $role->id)
                        ->where('permission_id', $permission->id)
                        ->exists();

                    if (!$exists) {
                        DB::table('role_permissions')->insert([
                            'role_id' => $role->id,
                            'permission_id' => $permission->id,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                $this->command->info(
                    'Processed: ' . $account['email']
                );
            }

            $this->command->info(
                'All five staff accounts processed successfully.'
            );
        });
    }
}