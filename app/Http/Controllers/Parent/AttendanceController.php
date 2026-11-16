<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends ParentController
{
    public function index(Request $request)
    {
        $context = $this->parentContext();

        $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = DB::table('student_attendance as sa')
            ->join('attendance_sessions as ats', 'ats.id', '=', 'sa.attendance_session_id')
            ->join('student_profiles as sp', 'sp.id', '=', 'sa.student_id')
            ->whereIn('sa.student_id', $context['childIds'] ?: [0]);

        if ($request->filled('date_from')) {
            $query->whereDate('ats.attendance_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('ats.attendance_date', '<=', $request->date_to);
        }

        $records = $query->select(
            'sp.id as student_id',
            'sp.first_name',
            'sp.last_name',
            'sp.admission_number',
            'ats.attendance_date',
            'ats.session_name',
            'sa.status',
            'sa.check_in_time',
            'sa.check_out_time',
            'sa.remarks'
        )
        ->orderByDesc('ats.attendance_date')
        ->orderBy('sp.first_name')
        ->get();

        $present = $records->filter(
            fn ($record) => strtolower($record->status) === 'present'
        )->count();

        $absent = $records->filter(
            fn ($record) => strtolower($record->status) === 'absent'
        )->count();

        $late = $records->filter(
            fn ($record) => strtolower($record->status) === 'late'
        )->count();

        $total = $records->count();

        $attendanceRate = $total > 0
            ? round(($present + $late) / $total * 100, 1)
            : 0;

        $stats = [
            'present' => $present,
            'absent' => $absent,
            'late' => $late,
            'total' => $total,
            'rate' => $attendanceRate,
        ];

        return view('parent.attendance.index', array_merge($context, [
            'title' => 'Children Attendance',
            'records' => $records,
            'stats' => $stats,
            'dateFrom' => $request->date_from,
            'dateTo' => $request->date_to,
        ]));
    }
}
