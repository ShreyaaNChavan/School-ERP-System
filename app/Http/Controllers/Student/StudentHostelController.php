<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class StudentHostelController extends Controller
{
    private function getStudent()
    {
        $user = auth()->user();

        $isStudent = $user->roleAssignments()
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('code', 'STUDENT')
                    ->where('is_active', true);
            })
            ->exists();

        abort_unless($isStudent, 403, 'Student access only.');

        $student = $user->studentProfile;

        abort_unless($student, 403, 'Student profile not found.');

        return $student;
    }

    public function index()
    {
        $student = $this->getStudent();

        $allocation = DB::table('hostel_allocations as ha')
            ->join('hostel_beds as hb', 'hb.id', '=', 'ha.hostel_bed_id')
            ->join('hostel_rooms as hr', 'hr.id', '=', 'hb.hostel_room_id')
            ->join('hostels as h', 'h.id', '=', 'hr.hostel_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'ha.academic_year_id')
            ->where('ha.student_id', $student->id)
            ->where('h.school_id', $student->school_id)
            ->where('ha.status', 'active')
            ->where(function ($query) {
                $query->whereNull('ha.start_date')
                    ->orWhereDate('ha.start_date', '<=', today());
            })
            ->where(function ($query) {
                $query->whereNull('ha.end_date')
                    ->orWhereDate('ha.end_date', '>=', today());
            })
            ->select([
                'ha.id',
                'ha.status',
                'ha.start_date',
                'ha.end_date',
                'h.name as hostel_name',
                'h.hostel_type',
                'h.warden_name',
                'h.warden_phone',
                'h.address as hostel_address',
                'hr.room_number',
                'hr.room_type',
                'hr.capacity as room_capacity',
                'hb.bed_number',
                'ay.name as academic_year_name',
            ])
            ->latest('ha.id')
            ->first();

        return view('hostel.student', compact('student', 'allocation'));
    }
}
