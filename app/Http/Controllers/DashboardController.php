<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Platform-wide statistics
        $stats = [
            'organizations' => DB::table('organizations')->count(),
            'schools' => DB::table('schools')->count(),
            'campuses' => DB::table('campuses')->count(),
            'users' => DB::table('users')->count(),
            'students' => DB::table('student_profiles')->count(),
            'staff' => DB::table('staff_profiles')->count(),
            'admissions' => DB::table('admission_leads')->count(),
            'attendance' => DB::table('student_attendance')->count(),
            'payments' => DB::table('fee_payments')->count(),
            'vehicles' => DB::table('transport_vehicles')->count(),
            'hostel_rooms' => DB::table('hostel_rooms')->count(),
            'books' => DB::table('library_books')->count(),
            'safety_cases' => DB::table('safeguarding_cases')->count(),
            'events' => DB::table('events')->count(),
        ];

        // Schools registered under each organization
        $organizationSchoolData = DB::table('organizations')
            ->leftJoin('schools', 'organizations.id', '=', 'schools.organization_id')
            ->select(
                'organizations.name',
                DB::raw('COUNT(schools.id) as total')
            )
            ->groupBy('organizations.id', 'organizations.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        // User registrations over the last six months
        $userGrowth = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $nextMonth = $month->copy()->addMonth();

            $userGrowth[] = [
                'label' => $month->format('M'),
                'total' => DB::table('users')
                    ->where('created_at', '>=', $month)
                    ->where('created_at', '<', $nextMonth)
                    ->count(),
            ];
        }

        $user = auth()->user();

        $modules = [
            ['name' => 'Organizations', 'icon' => 'building-2', 'permission' => 'ORGANIZATIONS.VIEW'],
            ['name' => 'Schools', 'icon' => 'school', 'permission' => 'SCHOOLS.VIEW'],
            ['name' => 'Campuses', 'icon' => 'map-pin', 'permission' => 'CAMPUSES.VIEW'],
            ['name' => 'Users & Roles', 'icon' => 'users', 'permission' => 'USERS.VIEW'],
            ['name' => 'Students', 'icon' => 'graduation-cap', 'permission' => 'STUDENTS.VIEW'],
            ['name' => 'Admissions', 'icon' => 'user-plus', 'permission' => 'ADMISSIONS.VIEW'],
            ['name' => 'Finance', 'icon' => 'wallet', 'permission' => 'FINANCE.VIEW'],
            ['name' => 'HR & Payroll', 'icon' => 'briefcase', 'permission' => 'HR.VIEW'],
            ['name' => 'Transport', 'icon' => 'bus', 'permission' => 'TRANSPORT.VIEW'],
            ['name' => 'Hostel', 'icon' => 'hotel', 'permission' => 'HOSTEL.VIEW'],
            ['name' => 'Library', 'icon' => 'book-open', 'permission' => 'LIBRARY.VIEW'],
            ['name' => 'Reports', 'icon' => 'chart', 'permission' => 'REPORTS.VIEW'],
            ['name' => 'Settings', 'icon' => 'settings', 'permission' => 'SETTINGS.VIEW'],
        ];

        $modules = array_values(array_filter(
            $modules,
            fn ($module) => $user->hasPermission($module['permission'])
        ));

        return view('dashboard', compact(
            'stats',
            'modules',
            'organizationSchoolData',
            'userGrowth'
        ));
    }
}