<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSelfServiceController extends Controller
{
    use ResolvesTeacher;

    public function profile()
    {
        $teacher = $this->currentTeacher();

        return view('teacher.profile.show', compact('teacher'));
    }

    public function editProfile()
    {
        $teacher = $this->currentTeacher();

        return view('teacher.profile.edit', compact('teacher'));
    }

    public function updateProfile(Request $request)
    {
        $teacher = $this->currentTeacher();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        // Teachers may update contact/name details, but not their school,
        // campus, employee number, designation, department or employment status.
        $teacher->update($data);

        return redirect()
            ->route('teacher.profile.show')
            ->with('success', 'Your profile details have been updated.');
    }

    public function attendance(Request $request)
    {
        $teacher = $this->currentTeacher();

        $filters = $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'in:present,absent,late,half_day,leave'],
        ]);

        $query = DB::table('employee_attendance')
            ->where('staff_profile_id', $teacher->id)
            ->when($filters['from_date'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($q, $date) => $q->whereDate('attendance_date', '<=', $date))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status));

        $statsQuery = clone $query;

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'present' => (clone $statsQuery)->where('status', 'present')->count(),
            'absent' => (clone $statsQuery)->where('status', 'absent')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
        ];

        $records = $query->orderByDesc('attendance_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('teacher.attendance.index', compact('teacher', 'records', 'stats', 'filters'));
    }

    public function payroll()
    {
        $teacher = $this->currentTeacher();

        $records = DB::table('payroll_records as pr')
            ->join('payroll_periods as pp', 'pp.id', '=', 'pr.payroll_period_id')
            ->where('pr.staff_profile_id', $teacher->id)
            ->where('pp.school_id', $teacher->school_id)
            ->select(
                'pr.id',
                'pr.basic_salary',
                'pr.allowances',
                'pr.deductions',
                'pr.gross_salary',
                'pr.net_salary',
                'pr.payment_status',
                'pr.payment_reference',
                'pr.paid_at',
                'pp.name as period_name',
                'pp.start_date',
                'pp.end_date',
                'pp.payment_date'
            )
            ->orderByDesc('pp.start_date')
            ->paginate(12);

        $summary = [
            'records' => DB::table('payroll_records')->where('staff_profile_id', $teacher->id)->count(),
            'paid' => DB::table('payroll_records')->where('staff_profile_id', $teacher->id)->where('payment_status', 'paid')->count(),
            'pending' => DB::table('payroll_records')->where('staff_profile_id', $teacher->id)->where('payment_status', 'pending')->count(),
        ];

        return view('teacher.payroll.index', compact('teacher', 'records', 'summary'));
    }

    public function payslip(int $record)
    {
        $teacher = $this->currentTeacher();

        $payslip = DB::table('payroll_records as pr')
            ->join('payroll_periods as pp', 'pp.id', '=', 'pr.payroll_period_id')
            ->where('pr.id', $record)
            ->where('pr.staff_profile_id', $teacher->id)
            ->where('pp.school_id', $teacher->school_id)
            ->select(
                'pr.*',
                'pp.name as period_name',
                'pp.start_date',
                'pp.end_date',
                'pp.payment_date'
            )
            ->first();

        abort_unless($payslip, 404);

        return view('teacher.payroll.payslip', compact('teacher', 'payslip'));
    }
}
