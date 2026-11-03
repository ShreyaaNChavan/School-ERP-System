<?php

namespace App\Http\Controllers;

use App\Models\ExamSubject;
use App\Models\ExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MarksEntryController extends Controller
{
    /**
     * Marks Entry directory.
     */
    public function index(Request $request)
    {
        $query = DB::table('exam_subjects as es')
            ->join('examinations as e', 'e.id', '=', 'es.examination_id')
            ->join('school_classes as sc', 'sc.id', '=', 'es.school_class_id')
            ->join('sections as sec', 'sec.id', '=', 'es.section_id')
            ->join('subjects as sub', 'sub.id', '=', 'es.subject_id')
            ->join('academic_years as ay', 'ay.id', '=', 'e.academic_year_id')
            ->select(
                'es.id',
                'es.examination_id',
                'es.school_class_id',
                'es.section_id',
                'es.subject_id',
                'es.exam_date',
                'es.maximum_marks',
                'es.passing_marks',
                'e.name as examination_name',
                'e.status as examination_status',
                'e.academic_year_id',
                'ay.name as academic_year_name',
                'sc.name as class_name',
                'sec.name as section_name',
                'sub.name as subject_name'
            )
            ->selectSub(function ($subquery) {
                $subquery->from('exam_results')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('exam_results.exam_subject_id', 'es.id');
            }, 'entered_count');

        if ($request->filled('academic_year_id')) {
            $query->where('e.academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('examination_id')) {
            $query->where('es.examination_id', $request->examination_id);
        }

        if ($request->filled('school_class_id')) {
            $query->where('es.school_class_id', $request->school_class_id);
        }

        if ($request->filled('section_id')) {
            $query->where('es.section_id', $request->section_id);
        }

        if ($request->filled('subject_id')) {
            $query->where('es.subject_id', $request->subject_id);
        }

        $examSubjects = $query
            ->orderByDesc('es.exam_date')
            ->paginate(10)
            ->withQueryString();

        $academicYears = DB::table('academic_years')
            ->orderByDesc('start_date')
            ->get();

        $examinations = DB::table('examinations')
            ->orderByDesc('start_date')
            ->get();

        $classes = DB::table('school_classes')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $sections = DB::table('sections')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $subjects = DB::table('subjects')
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => DB::table('exam_subjects')->count(),
            'published' => DB::table('exam_results')
                ->whereNotNull('published_at')
                ->distinct('exam_subject_id')
                ->count('exam_subject_id'),
            'entered' => DB::table('exam_results')->count(),
        ];

        return view('marks-entry.index', compact(
            'examSubjects',
            'academicYears',
            'examinations',
            'classes',
            'sections',
            'subjects',
            'stats'
        ));
    }

    /**
     * Display the marks sheet for one examination subject.
     */
    public function show(ExamSubject $examSubject)
    {
        $examSubject->load([
            'examination',
            'schoolClass',
            'section',
            'subject',
        ]);

        $academicYearId = $examSubject->examination->academic_year_id;

        $students = DB::table('student_enrollments as se')
            ->join('student_profiles as sp', 'sp.id', '=', 'se.student_id')
            ->leftJoin('exam_results as er', function ($join) use ($examSubject) {
                $join->on('er.student_id', '=', 'sp.id')
                    ->where('er.exam_subject_id', '=', $examSubject->id);
            })
            ->where('se.academic_year_id', $academicYearId)
            ->where('se.school_class_id', $examSubject->school_class_id)
            ->where('se.section_id', $examSubject->section_id)
            ->where('se.status', 'active')
            ->whereNull('sp.deleted_at')
            ->select(
                'sp.id as student_id',
                'sp.admission_number',
                'sp.first_name',
                'sp.middle_name',
                'sp.last_name',
                'er.id as result_id',
                'er.marks_obtained',
                'er.grade',
                'er.remarks',
                'er.published_at'
            )
            ->orderBy('sp.admission_number')
            ->get()
            ->map(function ($student) {
                $student->full_name = trim(implode(' ', array_filter([
                    $student->first_name,
                    $student->middle_name,
                    $student->last_name,
                ])));

                $student->entry_status = !$student->result_id
                    ? 'pending'
                    : ($student->grade === 'AB' ? 'absent' : 'present');

                return $student;
            });

        $totalStudents = $students->count();
        $enteredCount = $students->where('entry_status', 'present')->count();
        $absentCount = $students->where('entry_status', 'absent')->count();
        $pendingCount = $students->where('entry_status', 'pending')->count();

        $isPublished = $students->contains(function ($student) {
            return !empty($student->published_at);
        });

        $stats = [
            'total' => $totalStudents,
            'entered' => $enteredCount,
            'absent' => $absentCount,
            'pending' => $pendingCount,
        ];

        return view('marks-entry.show', compact(
            'examSubject',
            'students',
            'stats',
            'isPublished'
        ));
    }

    /**
     * Save marks as draft.
     */
    public function save(Request $request, ExamSubject $examSubject)
    {
        $this->ensureSheetIsEditable($examSubject);

        $validated = Validator::make($request->all(), [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.student_id' => ['required', 'integer', 'distinct'],
            'entries.*.status' => ['required', 'in:present,absent'],
            'entries.*.marks' => ['nullable', 'numeric', 'min:0'],
            'entries.*.remarks' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $academicYearId = $examSubject->examination()->value('academic_year_id');

        $eligibleStudentIds = DB::table('student_enrollments')
            ->where('academic_year_id', $academicYearId)
            ->where('school_class_id', $examSubject->school_class_id)
            ->where('section_id', $examSubject->section_id)
            ->where('status', 'active')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $maximumMarks = (float) $examSubject->maximum_marks;

        DB::transaction(function () use (
            $validated,
            $eligibleStudentIds,
            $maximumMarks,
            $examSubject
        ) {
            foreach ($validated['entries'] as $entry) {
                $studentId = (int) $entry['student_id'];

                if (!in_array($studentId, $eligibleStudentIds, true)) {
                    throw ValidationException::withMessages([
                        'entries' => 'One or more selected students do not belong to this examination section.',
                    ]);
                }

                if ($entry['status'] === 'present') {
                    if ($entry['marks'] === null || $entry['marks'] === '') {
                        throw ValidationException::withMessages([
                            'entries' => 'Marks are required for every student marked present.',
                        ]);
                    }

                    $marks = (float) $entry['marks'];

                    if ($marks > $maximumMarks) {
                        throw ValidationException::withMessages([
                            'entries' => "Marks cannot exceed {$maximumMarks}.",
                        ]);
                    }

                    $grade = $this->calculateGrade($marks, $maximumMarks);
                } else {
                    $marks = null;
                    $grade = 'AB';
                }

                ExamResult::updateOrCreate(
                    [
                        'exam_subject_id' => $examSubject->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'marks_obtained' => $marks,
                        'grade' => $grade,
                        'remarks' => $entry['remarks'] ?? null,
                        'entered_by' => auth()->id(),
                        'published_at' => null,
                    ]
                );
            }
        });

        return redirect()
            ->route('marks-entry.show', $examSubject->id)
            ->with('success', 'Marks saved successfully as draft.');
    }

    /**
     * Publish a completed marks sheet.
     */
    public function publish(ExamSubject $examSubject)
    {
        $this->ensureSheetIsEditable($examSubject);

        $academicYearId = $examSubject->examination()->value('academic_year_id');

        $studentIds = DB::table('student_enrollments')
            ->where('academic_year_id', $academicYearId)
            ->where('school_class_id', $examSubject->school_class_id)
            ->where('section_id', $examSubject->section_id)
            ->where('status', 'active')
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return back()->with('error', 'No active students are enrolled in this section.');
        }

        $results = ExamResult::where('exam_subject_id', $examSubject->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        $missingCount = $studentIds->filter(function ($studentId) use ($results) {
            return !$results->has($studentId);
        })->count();

        if ($missingCount > 0) {
            return back()->with(
                'error',
                "{$missingCount} student(s) still have pending entries. Enter marks or mark them absent before publishing."
            );
        }

        foreach ($results as $result) {
            if ($result->grade !== 'AB' && $result->marks_obtained === null) {
                return back()->with(
                    'error',
                    'Some students have incomplete marks. Complete all entries before publishing.'
                );
            }
        }

        DB::transaction(function () use ($examSubject, $studentIds) {
            ExamResult::where('exam_subject_id', $examSubject->id)
                ->whereIn('student_id', $studentIds)
                ->update([
                    'published_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('marks-entry.show', $examSubject->id)
            ->with('success', 'Marks sheet published successfully. Editing is now locked.');
    }

    /**
     * Prevent modifications after publication.
     */
    private function ensureSheetIsEditable(ExamSubject $examSubject): void
    {
        $alreadyPublished = ExamResult::where('exam_subject_id', $examSubject->id)
            ->whereNotNull('published_at')
            ->exists();

        if ($alreadyPublished) {
            abort(403, 'This marks sheet has already been published and is locked.');
        }
    }

    /**
     * CBSE 8-point grading scale.
     */
    private function calculateGrade(float $marks, float $maximumMarks): string
    {
        if ($maximumMarks <= 0) {
            return 'E';
        }

        $percentage = ($marks / $maximumMarks) * 100;

        return match (true) {
            $percentage >= 91 => 'A1',
            $percentage >= 81 => 'A2',
            $percentage >= 71 => 'B1',
            $percentage >= 61 => 'B2',
            $percentage >= 51 => 'C1',
            $percentage >= 41 => 'C2',
            $percentage >= 33 => 'D',
            default => 'E',
        };
    }
}