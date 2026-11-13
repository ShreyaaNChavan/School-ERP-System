<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PtmController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    /**
     * Base meeting query with student and teacher details.
     */
    private function baseMeetings()
    {
        return DB::table('ptm_meetings as m')
            ->leftJoin('student_profiles as s', 's.id', '=', 'm.student_id')
            ->leftJoin('staff_profiles as t', 't.id', '=', 'm.teacher_id')
            ->where('m.school_id', $this->schoolId())
            ->select(
                'm.*',
                's.first_name as student_first_name',
                's.last_name as student_last_name',
                's.admission_number',
                't.first_name as teacher_first_name',
                't.last_name as teacher_last_name',
                't.designation as teacher_designation'
            );
    }

    /**
     * PTM Dashboard.
     */
    public function dashboard(Request $request)
    {
        $today = now()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $weekEnd = now()->endOfWeek()->toDateString();

        $base = DB::table('ptm_meetings')
            ->where('school_id', $this->schoolId());

        // Today's meetings
        $meetings = $this->baseMeetings()
            ->whereDate('m.meeting_date', $today)
            ->orderBy('m.start_time')
            ->get();

        // Pending requests
        $pending = $this->baseMeetings()
            ->where('m.status', 'pending')
            ->orderBy('m.meeting_date')
            ->orderBy('m.start_time')
            ->limit(8)
            ->get();

        // Teacher availability
        $availability = DB::table('ptm_teacher_availabilities as a')
            ->join('staff_profiles as t', 't.id', '=', 'a.teacher_id')
            ->where('a.school_id', $this->schoolId())
            ->where('a.is_active', 1)
            ->select(
                'a.*',
                't.first_name',
                't.last_name',
                't.designation'
            )
            ->orderBy('t.first_name')
            ->limit(10)
            ->get();

        // Recent meeting history
        $history = $this->baseMeetings()
            ->orderByDesc('m.meeting_date')
            ->orderByDesc('m.start_time')
            ->limit(8)
            ->get();

        // IMPORTANT: Keep this as a flat collection.
        // The dashboard Blade uses $calendar->first() to find a meeting.
        $calendar = $this->baseMeetings()
            ->whereBetween('m.meeting_date', [
                $weekStart,
                $weekEnd
            ])
            ->orderBy('m.meeting_date')
            ->orderBy('m.start_time')
            ->get();

        return view('ptm.dashboard', [
            'scheduled' => (clone $base)->count(),

            'completedToday' => (clone $base)
                ->whereDate('meeting_date', $today)
                ->where('status', 'completed')
                ->count(),

            'pendingCount' => (clone $base)
                ->where('status', 'pending')
                ->count(),

            'upcomingWeek' => (clone $base)
                ->whereBetween('meeting_date', [
                    $weekStart,
                    $weekEnd
                ])
                ->whereIn('status', [
                    'confirmed',
                    'pending'
                ])
                ->count(),

            'avgDuration' => (int) round(
                (clone $base)
                    ->whereNotNull('duration_minutes')
                    ->avg('duration_minutes') ?? 0
            ),

            'meetings' => $meetings,
            'pending' => $pending,
            'availability' => $availability,
            'history' => $history,
            'calendar' => $calendar,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
        ]);
    }

    /**
     * Meeting history.
     */
    public function index(Request $request)
    {
        $q = $this->baseMeetings();

        if ($request->filled('search')) {
            $term = '%' . trim($request->search) . '%';

            $q->where(function ($w) use ($term) {
                $w->where('m.parent_name', 'like', $term)
                    ->orWhere('m.purpose', 'like', $term)
                    ->orWhere('s.first_name', 'like', $term)
                    ->orWhere('s.last_name', 'like', $term)
                    ->orWhere('t.first_name', 'like', $term)
                    ->orWhere('t.last_name', 'like', $term);
            });
        }

        if ($request->filled('type')) {
            $q->where('m.meeting_type', $request->type);
        }

        if ($request->filled('status')) {
            $q->where('m.status', $request->status);
        }

        $meetings = $q
            ->orderByDesc('m.meeting_date')
            ->orderBy('m.start_time')
            ->paginate(15)
            ->withQueryString();

        return view('ptm.index', compact('meetings'));
    }

    /**
     * Form dropdown data.
     */
    private function formData(): array
    {
        return [
            'students' => DB::table('student_profiles')
                ->where('school_id', $this->schoolId())
                ->whereNull('deleted_at')
                ->orderBy('first_name')
                ->get(),

            'teachers' => DB::table('staff_profiles')
                ->where('school_id', $this->schoolId())
                ->whereNull('deleted_at')
                ->orderBy('first_name')
                ->get(),

            'types' => [
                'Academic',
                'Behavioural',
                'Attendance',
                'General',
                'Career Guidance'
            ],
        ];
    }

    /**
     * Create meeting form.
     */
    public function create()
    {
        return view('ptm.create', $this->formData());
    }

    /**
     * Validate meeting form.
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'student_id' => 'required|integer|exists:student_profiles,id',
            'teacher_id' => 'required|integer|exists:staff_profiles,id',
            'parent_name' => 'required|string|max:180',
            'parent_phone' => 'nullable|string|max:30',
            'meeting_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'duration_minutes' => 'required|integer|min:10|max:180',
            'meeting_type' => 'required|string|max:60',
            'purpose' => 'required|string|max:1000',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled,rescheduled',
            'outcome' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:3000',
        ]);
    }

    /**
     * Store meeting.
     */
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $studentExists = DB::table('student_profiles')
            ->where('id', $data['student_id'])
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->exists();

        abort_unless($studentExists, 422, 'Invalid student selected.');

        $teacherExists = DB::table('staff_profiles')
            ->where('id', $data['teacher_id'])
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->exists();

        abort_unless($teacherExists, 422, 'Invalid teacher selected.');

        $data['school_id'] = $this->schoolId();

        $data['end_time'] = date(
            'H:i:s',
            strtotime($data['start_time'] . ' +' . $data['duration_minutes'] . ' minutes')
        );

        $data['start_time'] .= ':00';
        $data['status'] = $data['status'] ?? 'pending';
        $data['created_by'] = auth()->id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $id = DB::table('ptm_meetings')->insertGetId($data);

        return redirect()
            ->route('ptm.show', $id)
            ->with('success', 'PTM request created successfully.');
    }

    /**
     * View meeting details.
     */
    public function show(int $id)
    {
        $item = $this->baseMeetings()
            ->where('m.id', $id)
            ->firstOrFail();

        return view('ptm.show', compact('item'));
    }

    /**
     * Edit meeting.
     */
    public function edit(int $id)
    {
        $item = DB::table('ptm_meetings')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->first();

        abort_unless($item, 404);

        return view(
            'ptm.edit',
            array_merge(['item' => $item], $this->formData())
        );
    }

    /**
     * Update meeting.
     */
    public function update(Request $request, int $id)
    {
        $item = DB::table('ptm_meetings')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->first();

        abort_unless($item, 404);

        $data = $this->validated($request);

        $studentExists = DB::table('student_profiles')
            ->where('id', $data['student_id'])
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->exists();

        abort_unless($studentExists, 422, 'Invalid student selected.');

        $teacherExists = DB::table('staff_profiles')
            ->where('id', $data['teacher_id'])
            ->where('school_id', $this->schoolId())
            ->whereNull('deleted_at')
            ->exists();

        abort_unless($teacherExists, 422, 'Invalid teacher selected.');

        $data['end_time'] = date(
            'H:i:s',
            strtotime($data['start_time'] . ' +' . $data['duration_minutes'] . ' minutes')
        );

        $data['start_time'] .= ':00';
        $data['updated_at'] = now();

        DB::table('ptm_meetings')
            ->where('id', $id)
            ->where('school_id', $this->schoolId())
            ->update($data);

        return redirect()
            ->route('ptm.show', $id)
            ->with('success', 'PTM meeting updated successfully.');
    }

    /**
     * Change meeting status.
     */
    public function status(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled'
        ]);

        $updated = DB::table('ptm_meetings')
            ->where('id', $id)
            ->where('school_id', $this->schoolId())
            ->update([
                'status' => $data['status'],
                'updated_at' => now()
            ]);

        abort_unless($updated, 404);

        return back()->with('success', 'Meeting status updated successfully.');
    }

    /**
     * Delete meeting.
     */
    public function destroy(int $id)
    {
        $deleted = DB::table('ptm_meetings')
            ->where('school_id', $this->schoolId())
            ->where('id', $id)
            ->delete();

        abort_unless($deleted, 404);

        return redirect()
            ->route('ptm.index')
            ->with('success', 'Meeting deleted successfully.');
    }
}
