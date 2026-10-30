<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SCHOOLNEXA_ADMIN_EMAIL');
        $password = env('SCHOOLNEXA_ADMIN_PASSWORD');

        if (!$email || !$password) {
            throw new \RuntimeException(
                'Set SCHOOLNEXA_ADMIN_EMAIL and SCHOOLNEXA_ADMIN_PASSWORD in .env'
            );
        }

        if (strlen($password) < 12) {
            throw new \RuntimeException(
                'Super Admin password must contain at least 12 characters.'
            );
        }

        DB::transaction(function () use ($email, $password) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'SchoolNexa Super Admin',
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]
            );

            DB::table('user_profiles')->updateOrInsert(
                ['user_id' => $user->id],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            $role = DB::table('roles')
                ->where('code', 'SUPER_ADMIN')
                ->first();

            if (!$role) {
                throw new \RuntimeException(
                    'SUPER_ADMIN role not found. Run RolePermissionSeeder first.'
                );
            }

            DB::table('user_role_assignments')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'organization_id' => null,
                    'school_id' => null,
                    'campus_id' => null,
                ],
                [
                    'assigned_by' => null,
                    'starts_at' => now(),
                    'expires_at' => null,
                    'is_active' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        });
    }
}
