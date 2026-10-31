<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use Illuminate\Http\Request;

class EmployeeProfileController extends Controller
{
    public function index(Request $request)
    {
        $query = StaffProfile::with([
            'school',
            'campus',
            'user',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('employee_number', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query
            ->orderBy('first_name')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => StaffProfile::count(),
            'active' => StaffProfile::where('status', 'active')->count(),
            'inactive' => StaffProfile::where('status', 'inactive')->count(),
        ];

        return view('employee-profiles.index', compact(
            'employees',
            'stats'
        ));
    }

    public function show(StaffProfile $staff)
    {
        $staff->load([
            'school',
            'campus',
            'user',
            'userProfile',
            'attendanceRecords' => function ($query) {
                $query->orderByDesc('attendance_date')
                    ->limit(10);
            },
            'leaveRequests' => function ($query) {
                $query->with('leaveType')
                    ->orderByDesc('start_date')
                    ->limit(10);
            },
            'leaveBalances.leaveType',
            'leaveBalances.academicYear',
            'payrollRecords' => function ($query) {
                $query->with([
                    'payrollPeriod',
                    'components',
                ])->orderByDesc('created_at')->limit(10);
            },
            'teachingAssignments.classSubject.subject',
            'teachingAssignments.classSubject.schoolClass',
            'teachingAssignments.section',
            'teachingAssignments.academicYear',
        ]);

        $attendanceStats = [
            'total' => $staff->attendanceRecords()->count(),
            'present' => $staff->attendanceRecords()
                ->where('status', 'present')->count(),
            'absent' => $staff->attendanceRecords()
                ->where('status', 'absent')->count(),
            'late' => $staff->attendanceRecords()
                ->where('status', 'late')->count(),
        ];

        return view('employee-profiles.show', compact(
            'staff',
            'attendanceStats'
        ));
    }
}
