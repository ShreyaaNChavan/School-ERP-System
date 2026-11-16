<?php

namespace App\Http\Controllers\Parent;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PtmController extends ParentController
{
    public function index()
    {
        $context = $this->parentContext();
        $meetings = DB::table('ptm_meetings as m')
            ->join('student_profiles as s', 's.id', '=', 'm.student_id')
            ->leftJoin('staff_profiles as t', 't.id', '=', 'm.teacher_id')
            ->whereIn('m.student_id', $context['childIds'] ?: [0])
            ->select(
                'm.*', 's.first_name as student_first_name', 's.last_name as student_last_name',
                't.first_name as teacher_first_name', 't.last_name as teacher_last_name'
            )
            ->orderByDesc('m.meeting_date')->orderByDesc('m.start_time')
            ->paginate(12);

        $teachers = $this->availableTeachers($context);
        return view('parent.ptm.index', compact('meetings', 'teachers', 'context'));
    }

    private function availableTeachers(array $context)
    {
        $schoolIds = $context['children']->pluck('school_id')->unique()->values()->all() ?: [0];

        return DB::table('staff_profiles as sp')
            ->join('ptm_teacher_availabilities as av', 'av.teacher_id', '=', 'sp.id')
            ->whereIn('sp.school_id', $schoolIds)
            ->whereNull('sp.deleted_at')
            ->where('sp.status', 'active')
            ->where('av.is_active', true)
            ->select('sp.id', 'sp.school_id', 'sp.first_name', 'sp.last_name', 'sp.designation')
            ->distinct()->orderBy('sp.first_name')->get();
    }

    public function store(Request $request)
    {
        $context = $this->parentContext();

        $data = $request->validate([
            'student_id' => 'required|integer',
            'teacher_id' => 'required|integer',
            'meeting_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:10|max:60',
            'meeting_type' => 'required|in:Academic,Behavioural,Attendance,General,Career Guidance',
            'purpose' => 'required|string|max:1000',
        ]);

        $child = $context['children']->firstWhere('id', (int) $data['student_id']);
        abort_unless($child, 403, 'You cannot request a meeting for this student.');

        $teacher = DB::table('staff_profiles')
            ->where('id', $data['teacher_id'])
            ->where('school_id', $child->school_id)
            ->whereNull('deleted_at')
            ->where('status', 'active')
            ->first();
        abort_unless($teacher, 422, 'Please select a valid teacher for this school.');

        $date = Carbon::parse($data['meeting_date']);
        $start = $data['start_time'] . ':00';
        $end = Carbon::createFromFormat('H:i', $data['start_time'])
            ->addMinutes((int) $data['duration_minutes'])->format('H:i:s');

        $availability = DB::table('ptm_teacher_availabilities')
            ->where('school_id', $child->school_id)
            ->where('teacher_id', $teacher->id)
            ->where('weekday', $date->format('l'))
            ->where('is_active', true)
            ->where('start_time', '<=', $start)
            ->where('end_time', '>=', $end)
            ->first();

        if (!$availability) {
            throw ValidationException::withMessages([
                'start_time' => 'The selected teacher is not available for this day and time. Please choose an available slot.',
            ]);
        }

        $overlap = DB::table('ptm_meetings')
            ->where('teacher_id', $teacher->id)
            ->where('meeting_date', $date->toDateString())
            ->whereNotIn('status', ['cancelled'])
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'start_time' => 'This time slot has already been requested. Please choose another slot.',
            ]);
        }

        DB::table('ptm_meetings')->insert([
            'school_id' => $child->school_id,
            'campus_id' => $child->campus_id,
            'student_id' => $child->id,
            'teacher_id' => $teacher->id,
            'parent_name' => $context['guardian']->full_name,
            'parent_phone' => $context['guardian']->phone,
            'meeting_date' => $date->toDateString(),
            'start_time' => $start,
            'end_time' => $end,
            'duration_minutes' => $data['duration_minutes'],
            'meeting_type' => $data['meeting_type'],
            'purpose' => $data['purpose'],
            'status' => 'pending',
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('parent.ptm.index')->with('success', 'Your meeting request has been submitted.');
    }
}
