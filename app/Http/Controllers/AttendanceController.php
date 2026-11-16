<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Campus;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StudentAttendance;
use App\Models\StudentEnrollment;
use App\Models\StudentProfile;
use App\Models\Subject;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    private const STATUSES = [
        'present', 'absent', 'late', 'holiday', 'halfday', 'leave',
    ];

    /**
     * Check whether the authenticated user has the Student role.
     */
    private function isStudent(): bool
    {
        return auth()->user()
            ->roleAssignments()
            ->where('is_active', true)
            ->whereHas('role', function ($query) {
                $query->where('code', 'STUDENT')
                    ->where('is_active', true);
            })
            ->exists();
    }

    /**
     * Resolve the authenticated student's profile.
     */
    private function studentProfile(): StudentProfile
    {
        return StudentProfile::where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->firstOrFail();
    }

    /**
     * Return the student's active enrollment.
     */
    private function studentEnrollment(StudentProfile $student): StudentEnrollment
    {
        return $student->enrollments()
            ->with([
                'academicYear',
                'schoolClass',
                'section',
            ])
            ->where('status', 'active')
            ->whereHas('academicYear')
            ->orderByDesc(
                AcademicYear::select('start_date')
                    ->whereColumn(
                        'academic_years.id',
                        'student_enrollments.academic_year_id'
                    )
            )
            ->firstOrFail();
    }

    public function index(Request $request)
    {
        if ($this->isStudent()) {
            return $this->studentIndex($request);
        }

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $campuses = Campus::where('status', 'active')->orderBy('name')->get();
        $schools = School::orderBy('name')->get();

        $academicYearId = $request->integer('academic_year_id') ?: optional(
            $academicYears->firstWhere('status', 'active') ?? $academicYears->first()
        )->id;

        $endDate = $request->date('to_date')
            ? Carbon::parse($request->date('to_date'))->startOfDay()
            : now()->startOfDay();

        $startDate = $request->date('from_date')
            ? Carbon::parse($request->date('from_date'))->startOfDay()
            : $endDate->copy()->subDays(13);

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($startDate->diffInDays($endDate) > 30) {
            $startDate = $endDate->copy()->subDays(13);
        }

        $classId = $request->integer('school_class_id') ?: null;
        $sectionId = $request->integer('section_id') ?: null;
        $subjectId = $request->integer('subject_id') ?: null;

        $sections = Section::with('schoolClass')
            ->when($classId, fn($q) => $q->where('school_class_id', $classId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $subjects = Subject::where('is_active', true)
            ->when($classId, function ($q) use ($classId) {
                $q->whereIn('id', DB::table('class_subjects')
                    ->select('subject_id')
                    ->where('school_class_id', $classId));
            })
            ->orderBy('name')
            ->get();

        $enrollments = StudentEnrollment::query()
            ->join('student_profiles as sp', 'sp.id', '=', 'student_enrollments.student_id')
            ->select('student_enrollments.*')
            ->with('student')
            ->where('student_enrollments.academic_year_id', $academicYearId)
            ->where('student_enrollments.status', 'active')
            ->when($classId, fn($q) => $q->where('student_enrollments.school_class_id', $classId))
            ->when($sectionId, fn($q) => $q->where('student_enrollments.section_id', $sectionId))
            ->whereNull('sp.deleted_at')
            ->orderBy('sp.last_name')
            ->orderBy('sp.first_name')
            ->orderBy('student_enrollments.student_id')
            ->paginate(10)
            ->withQueryString();

        $dates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn($date) => $date->copy());

        $sessions = AttendanceSession::with('records')
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $endDate->toDateString())
            ->when($sectionId, fn($q) => $q->where('section_id', $sectionId))
            ->when(!$sectionId && $classId, function ($q) use ($sections) {
                $q->whereIn('section_id', $sections->pluck('id'));
            })
            ->when($academicYearId, fn($q) => $q->where('academic_year_id', $q))
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->get();

        $pageStudentIds = $enrollments->getCollection()
            ->pluck('student_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $attendanceMap = [];

        foreach ($sessions as $session) {
            foreach ($session->records->whereIn('student_id', $pageStudentIds) as $record) {
                $attendanceMap[$record->student_id][
                    Carbon::parse($session->attendance_date)->format('Y-m-d')
                ] = $record;
            }
        }

        $counts = [
            'present' => 0, 'absent' => 0, 'late' => 0,
            'halfday' => 0, 'leave' => 0, 'holiday' => 0,
        ];

        foreach ($attendanceMap as $studentRows) {
            foreach ($studentRows as $record) {
                if (isset($counts[$record->status])) {
                    $counts[$record->status]++;
                }
            }
        }

        $todaySession = $sessions->firstWhere(
            'attendance_date',
            now()->toDateString()
        );

        $todayCounts = [
            'present' => 0, 'absent' => 0, 'late' => 0,
            'leave' => 0, 'halfday' => 0, 'holiday' => 0,
        ];

        if ($todaySession) {
            foreach ($todaySession->records as $record) {
                if (isset($todayCounts[$record->status])) {
                    $todayCounts[$record->status]++;
                }
            }
        }

        return view('attendance.index', compact(
            'schools', 'campuses', 'academicYears', 'classes',
            'sections', 'subjects', 'academicYearId', 'classId',
            'sectionId', 'subjectId', 'startDate', 'endDate',
            'dates', 'enrollments', 'attendanceMap', 'counts',
            'todayCounts'
        ));
    }

    /**
     * Student attendance screen.
     */
    private function studentIndex(Request $request)
    {
        $student = $this->studentProfile();
        $enrollment = $this->studentEnrollment($student);

        $academicYear = $enrollment->academicYear;
        $class = $enrollment->schoolClass;
        $section = $enrollment->section;

        abort_unless(
            $section && $class &&
            (int) $section->school_class_id === (int) $class->id,
            403,
            'Your enrollment information is incomplete.'
        );

        $endDate = $request->date('to_date')
            ? Carbon::parse($request->date('to_date'))->startOfDay()
            : now()->startOfDay();

        $startDate = $request->date('from_date')
            ? Carbon::parse($request->date('from_date'))->startOfDay()
            : $endDate->copy()->subDays(29);

        if ($startDate->gt($endDate)) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        if ($startDate->diffInDays($endDate) > 90) {
            $startDate = $endDate->copy()->subDays(89);
        }

        $subjectId = $request->integer('subject_id') ?: null;

        $academicYears = collect([$academicYear]);
        $classes = collect([$class]);
        $sections = collect([$section]);
        $schools = School::whereKey($student->school_id)->get();
        $campuses = Campus::whereKey($student->campus_id)->get();

        $subjects = Subject::where('is_active', true)
            ->where('school_id', $student->school_id)
            ->whereIn('id', DB::table('class_subjects')
                ->select('subject_id')
                ->where('school_class_id', $class->id))
            ->orderBy('name')
            ->get();

        if ($subjectId) {
            abort_unless(
                $subjects->contains('id', $subjectId),
                403,
                'This subject is not assigned to your class.'
            );
        }

        $enrollments = StudentEnrollment::with([
                'student',
                'academicYear',
                'schoolClass',
                'section',
            ])
            ->whereKey($enrollment->id)
            ->paginate(10)
            ->withQueryString();

        $dates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn($date) => $date->copy());

        $sessions = AttendanceSession::with([
                'records' => function ($query) use ($student) {
                    $query->where('student_id', $student->id);
                },
            ])
            ->where('section_id', $section->id)
            ->where('academic_year_id', $academicYear->id)
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $endDate->toDateString())
            ->when($subjectId, fn($q) => $q->where('subject_id', $subjectId))
            ->get();

        $attendanceMap = [];

        foreach ($sessions as $session) {
            foreach ($session->records as $record) {
                $attendanceMap[$student->id][
                    Carbon::parse($session->attendance_date)->format('Y-m-d')
                ] = $record;
            }
        }

        $counts = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'halfday' => 0,
            'leave' => 0,
            'holiday' => 0,
        ];

        foreach ($attendanceMap[$student->id] ?? [] as $record) {
            if (isset($counts[$record->status])) {
                $counts[$record->status]++;
            }
        }

        $todayCounts = [
            'present' => 0,
            'absent' => 0,
            'late' => 0,
            'leave' => 0,
            'halfday' => 0,
            'holiday' => 0,
        ];

        foreach ($sessions->where('attendance_date', now()->toDateString()) as $session) {
            foreach ($session->records as $record) {
                if (isset($todayCounts[$record->status])) {
                    $todayCounts[$record->status]++;
                }
            }
        }

        $classId = $class->id;
        $sectionId = $section->id;
        $academicYearId = $academicYear->id;

        return view('attendance.index', compact(
            'schools',
            'campuses',
            'academicYears',
            'classes',
            'sections',
            'subjects',
            'academicYearId',
            'classId',
            'sectionId',
            'subjectId',
            'startDate',
            'endDate',
            'dates',
            'enrollments',
            'attendanceMap',
            'counts',
            'todayCounts'
        ));
    }

    public function store(Request $request)
    {
        abort_if($this->isStudent(), 403, 'Students cannot mark attendance.');

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'attendance_date' => ['required', 'date'],
            'subject_id' => ['nullable', 'exists:subjects,id'],
            'attendance' => ['required', 'array', 'min:1'],
            'attendance.*.status' => ['required', Rule::in(self::STATUSES)],
            'attendance.*.remarks' => ['nullable', 'string', 'max:1000'],
            'attendance.*.check_in_time' => ['nullable', 'date_format:H:i'],
        ]);

        $section = Section::with('schoolClass')->findOrFail($validated['section_id']);

        abort_unless(
            (int) $section->school_class_id === (int) $validated['school_class_id']
            && (int) $section->campus_id === (int) $validated['campus_id']
            && (int) $section->schoolClass->school_id === (int) $validated['school_id'],
            422,
            'The selected school, campus, class and section do not match.'
        );

        $subjectId = $validated['subject_id'] ?? null;

        if ($subjectId) {
            $validSubject = DB::table('class_subjects')
                ->where('school_class_id', $validated['school_class_id'])
                ->where('subject_id', $subjectId)
                ->exists();

            abort_unless(
                $validSubject,
                422,
                'The selected subject is not assigned to this class.'
            );
        }

        $allowedStudentIds = StudentEnrollment::where(
                'academic_year_id',
                $validated['academic_year_id']
            )
            ->where('school_class_id', $validated['school_class_id'])
            ->where('section_id', $validated['section_id'])
            ->where('status', 'active')
            ->pluck('student_id')
            ->map(fn($id) => (int) $id)
            ->all();

        $submittedIds = array_map(
            'intval',
            array_keys($validated['attendance'])
        );

        abort_if(
            array_diff($submittedIds, $allowedStudentIds),
            422,
            'One or more students are not enrolled in the selected section.'
        );

        $sessionName = $subjectId ? 'Subject:' . $subjectId : 'Full Day';

        DB::transaction(function () use ($validated, $subjectId, $sessionName) {
            $session = AttendanceSession::firstOrNew([
                'section_id' => $validated['section_id'],
                'attendance_date' => $validated['attendance_date'],
                'session_name' => $sessionName,
            ]);

            $session->fill([
                'school_id' => $validated['school_id'],
                'campus_id' => $validated['campus_id'],
                'academic_year_id' => $validated['academic_year_id'],
                'subject_id' => $subjectId,
                'recorded_by' => auth()->id(),
                'status' => 'closed',
            ]);

            $session->save();

            foreach ($validated['attendance'] as $studentId => $row) {
                StudentAttendance::updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'status' => $row['status'],
                        'remarks' => $row['remarks'] ?? null,
                        'check_in_time' => $row['check_in_time'] ?? null,
                        'marked_by' => auth()->id(),
                    ]
                );
            }
        });

        return redirect()->route('attendance.index', [
            'academic_year_id' => $validated['academic_year_id'],
            'school_class_id' => $validated['school_class_id'],
            'section_id' => $validated['section_id'],
            'subject_id' => $subjectId,
            'from_date' => $validated['attendance_date'],
            'to_date' => $validated['attendance_date'],
        ])->with('success', 'Attendance saved successfully.');
    }

    public function report(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => ['nullable', 'exists:academic_years,id'],
            'student_id' => ['nullable', 'exists:student_profiles,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ]);

        $query = StudentAttendance::with([
                'student',
                'session.section.schoolClass',
                'session.subject',
            ])
            ->whereHas('session', function ($q) use ($validated) {
                $q->when(
                    $validated['academic_year_id'] ?? null,
                    fn($x, $v) => $x->where('academic_year_id', $v)
                )
                ->when(
                    $validated['from_date'] ?? null,
                    fn($x, $v) => $x->whereDate('attendance_date', '>=', $v)
                )
                ->when(
                    $validated['to_date'] ?? null,
                    fn($x, $v) => $x->whereDate('attendance_date', '<=', $v)
                );
            })
            ->when(
                $validated['student_id'] ?? null,
                fn($q, $v) => $q->where('student_id', $v)
            );

        if ($this->isStudent()) {
            $student = $this->studentProfile();

            $query->where('student_id', $student->id);

            $validated['student_id'] = $student->id;

            $allowedAcademicYears = $student->enrollments()
                ->pluck('academic_year_id');

            $query->whereHas('session', function ($q) use ($allowedAcademicYears) {
                $q->whereIn('academic_year_id', $allowedAcademicYears);
            });
        }

        $records = $query->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        if ($this->isStudent()) {
            $student = $this->studentProfile();

            $academicYears = AcademicYear::whereIn(
                'id',
                $student->enrollments()->pluck('academic_year_id')
            )->orderByDesc('start_date')->get();
        }

        return view('attendance.report', compact(
            'records',
            'academicYears'
        ));
    }
}
