<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\ResolvesTeacher;
use App\Models\ExamResult;
use App\Models\ExamSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TeacherMarksEntryController extends Controller
{
    use ResolvesTeacher;

    private function assignedSheetQuery($teacher)
    {
        return DB::table('exam_subjects as es')
            ->join('examinations as e', 'e.id', '=', 'es.examination_id')
            ->join('school_classes as sc', 'sc.id', '=', 'es.school_class_id')
            ->join('sections as sec', 'sec.id', '=', 'es.section_id')
            ->join('subjects as sub', 'sub.id', '=', 'es.subject_id')
            ->leftJoin('academic_years as ay', 'ay.id', '=', 'e.academic_year_id')
            ->where('sc.school_id', $teacher->school_id)
            ->whereExists(function ($q) use ($teacher) {
                $q->selectRaw('1')
                    ->from('teaching_assignments as ta')
                    ->join('class_subjects as cs', 'cs.id', '=', 'ta.class_subject_id')
                    ->where('ta.staff_profile_id', $teacher->id)
                    ->whereColumn('ta.section_id', 'es.section_id')
                    ->whereColumn('ta.academic_year_id', 'e.academic_year_id')
                    ->whereColumn('cs.school_class_id', 'es.school_class_id')
                    ->whereColumn('cs.subject_id', 'es.subject_id');
            })
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
            ->selectSub(function ($q) {
                $q->from('exam_results')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('exam_results.exam_subject_id', 'es.id');
            }, 'entered_count')
            ->selectSub(function ($q) {
                $q->from('exam_results')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('exam_results.exam_subject_id', 'es.id')
                    ->whereNotNull('exam_results.published_at');
            }, 'published_count');
    }

    private function ensureAssigned(ExamSubject $examSubject, $teacher): void
    {
        $assigned = $this->assignedSheetQuery($teacher)
            ->where('es.id', $examSubject->id)
            ->exists();

        abort_unless($assigned, 403, 'This examination subject is not assigned to you.');
    }

    private function ensureEditable(ExamSubject $examSubject): void
    {
        $published = ExamResult::where('exam_subject_id', $examSubject->id)
            ->whereNotNull('published_at')
            ->exists();

        abort_if($published, 403, 'This marks sheet has already been published and is locked.');
    }

    public function index(Request $request)
    {
        $teacher = $this->currentTeacher();
        $query = $this->assignedSheetQuery($teacher);

        foreach (['academic_year_id', 'examination_id', 'school_class_id', 'section_id', 'subject_id'] as $filter) {
            if ($request->filled($filter)) {
                $column = $filter === 'examination_id' ? 'es.examination_id'
                    : ($filter === 'academic_year_id' ? 'e.academic_year_id' : 'es.' . $filter);
                $query->where($column, $request->input($filter));
            }
        }

        $examSubjects = $query->orderByDesc('es.exam_date')->paginate(12)->withQueryString();

        return view('teacher.marks.index', compact('teacher', 'examSubjects'));
    }

    public function show(ExamSubject $examSubject)
    {
        $teacher = $this->currentTeacher();
        $this->ensureAssigned($examSubject, $teacher);

        $examSubject->load(['examination', 'schoolClass', 'section', 'subject']);
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

        $isPublished = $students->contains(fn ($student) => !empty($student->published_at));

        return view('teacher.marks.show', compact('teacher', 'examSubject', 'students', 'isPublished'));
    }

    public function save(Request $request, ExamSubject $examSubject)
    {
        $teacher = $this->currentTeacher();
        $this->ensureAssigned($examSubject, $teacher);
        $this->ensureEditable($examSubject);

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

        DB::transaction(function () use ($validated, $eligibleStudentIds, $maximumMarks, $examSubject) {
            foreach ($validated['entries'] as $entry) {
                $studentId = (int) $entry['student_id'];

                if (!in_array($studentId, $eligibleStudentIds, true)) {
                    throw ValidationException::withMessages([
                        'entries' => 'A selected student is not enrolled in this examination section.',
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
                    ['exam_subject_id' => $examSubject->id, 'student_id' => $studentId],
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

        return redirect()->route('teacher.marks.show', $examSubject->id)
            ->with('success', 'Marks saved as draft.');
    }

    public function publish(ExamSubject $examSubject)
    {
        $teacher = $this->currentTeacher();
        $this->ensureAssigned($examSubject, $teacher);
        $this->ensureEditable($examSubject);

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

        $missingCount = $studentIds->filter(fn ($studentId) => !$results->has($studentId))->count();

        if ($missingCount > 0) {
            return back()->with('error', "{$missingCount} student(s) still have pending entries.");
        }

        foreach ($results as $result) {
            if ($result->grade !== 'AB' && $result->marks_obtained === null) {
                return back()->with('error', 'Complete all marks before publishing.');
            }
        }

        ExamResult::where('exam_subject_id', $examSubject->id)
            ->whereIn('student_id', $studentIds)
            ->update(['published_at' => now(), 'updated_at' => now()]);

        return redirect()->route('teacher.marks.show', $examSubject->id)
            ->with('success', 'Marks published successfully. Editing is now locked.');
    }

    private function calculateGrade(float $marks, float $maximumMarks): string
    {
        if ($maximumMarks <= 0) return 'E';

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
