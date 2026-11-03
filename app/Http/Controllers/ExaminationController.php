<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Examination;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ExaminationController extends Controller
{
    /**
     * Examination directory.
     */
    public function index(Request $request)
    {
        $query = Examination::with(['school', 'academicYear'])
            ->withCount('examSubjects');

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = trim($request->search);

            $q->where(function ($sub) use ($term) {
                $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('exam_type', 'like', "%{$term}%");
            });
        });

        $query->when(
            $request->filled('academic_year_id'),
            fn($q) => $q->where('academic_year_id', $request->academic_year_id)
        );

        $query->when(
            $request->filled('school_id'),
            fn($q) => $q->where('school_id', $request->school_id)
        );

        $query->when(
            $request->filled('status'),
            fn($q) => $q->where('status', $request->status)
        );

        $examinations = $query
            ->latest('start_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Examination::count(),
            'draft' => Examination::where('status', 'draft')->count(),
            'published' => Examination::where('status', 'published')->count(),
            'completed' => Examination::where('status', 'completed')->count(),
        ];

        $schools = School::orderBy('name')->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        return view('examinations.index', compact(
            'examinations',
            'stats',
            'schools',
            'academicYears'
        ));
    }

    /**
     * Show examination creation form.
     */
    public function create()
    {
        return view('examinations.create', $this->formData());
    }

    /**
     * Store examination and subject schedules.
     */
    public function store(Request $request)
    {
        $data = $this->validatedData($request);

        $academicYear = AcademicYear::findOrFail(
            $data['academic_year_id']
        );

        abort_unless(
            (int) $academicYear->school_id === (int) $data['school_id'],
            422,
            'The selected academic year does not belong to the selected school.'
        );

        $this->validateSubjectSchedule($data['exam_subjects']);

        if (
            $data['status'] === 'published' &&
            !auth()->user()->hasPermission('EXAMINATIONS.PUBLISH')
        ) {
            abort(403, 'You do not have permission to publish examinations.');
        }

        $examination = DB::transaction(function () use ($data) {

            $examination = Examination::create([
                'school_id' => $data['school_id'],
                'academic_year_id' => $data['academic_year_id'],
                'name' => $data['name'],
                'exam_type' => $data['exam_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'instructions' => $data['instructions'] ?? null,
            ]);

            foreach ($data['exam_subjects'] as $subject) {
                $examination->examSubjects()->create($subject);
            }

            return $examination;
        });

        return redirect()
            ->route('examinations.show', $examination)
            ->with('success', 'Examination created successfully.');
    }

    /**
     * Show examination details and schedule.
     */
    public function show(Examination $examination)
    {
        $examination->load([
            'school',
            'academicYear',
            'examSubjects.schoolClass',
            'examSubjects.section',
            'examSubjects.subject',
        ]);

        return view('examinations.show', compact('examination'));
    }

    /**
     * Show examination edit form.
     */
    public function edit(Examination $examination)
    {
        $examination->load('examSubjects');

        return view('examinations.edit', array_merge(
            $this->formData(),
            compact('examination')
        ));
    }

    /**
     * Update examination and subject schedules.
     */
    public function update(Request $request, Examination $examination)
    {
        abort_if(
            $examination->status === 'completed',
            403,
            'Completed examinations cannot be edited.'
        );

        $data = $this->validatedData($request);

        $academicYear = AcademicYear::findOrFail(
            $data['academic_year_id']
        );

        abort_unless(
            (int) $academicYear->school_id === (int) $data['school_id'],
            422,
            'The selected academic year does not belong to the selected school.'
        );

        $this->validateSubjectSchedule($data['exam_subjects']);

        if (
            $data['status'] === 'published' &&
            $examination->status !== 'published' &&
            !auth()->user()->hasPermission('EXAMINATIONS.PUBLISH')
        ) {
            abort(403, 'You do not have permission to publish examinations.');
        }

        DB::transaction(function () use ($data, $examination) {

            $examination->update([
                'school_id' => $data['school_id'],
                'academic_year_id' => $data['academic_year_id'],
                'name' => $data['name'],
                'exam_type' => $data['exam_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $data['status'],
                'instructions' => $data['instructions'] ?? null,
            ]);

            $examination->examSubjects()->delete();

            foreach ($data['exam_subjects'] as $subject) {
                $examination->examSubjects()->create($subject);
            }
        });

        return redirect()
            ->route('examinations.show', $examination)
            ->with('success', 'Examination updated successfully.');
    }

    /**
     * Publish examination.
     */
    public function publish(Examination $examination)
    {
        abort_if(
            $examination->status === 'completed',
            422,
            'A completed examination cannot be published again.'
        );

        if (!$examination->examSubjects()->exists()) {
            return back()->with(
                'error',
                'Add at least one subject before publishing.'
            );
        }

        // Verify subject dates are within the examination period.
        $invalidSchedule = $examination->examSubjects()
            ->where(function ($query) use ($examination) {
                $query->whereDate(
                    'exam_date',
                    '<',
                    $examination->start_date
                )->orWhereDate(
                        'exam_date',
                        '>',
                        $examination->end_date
                    );
            })
            ->exists();

        if ($invalidSchedule) {
            return back()->with(
                'error',
                'One or more subject dates are outside the examination period.'
            );
        }

        $examination->update([
            'status' => 'published',
        ]);

        return back()->with(
            'success',
            'Examination published successfully.'
        );
    }

    /**
     * Delete examination.
     */
    public function destroy(Examination $examination)
    {
        DB::transaction(function () use ($examination) {
            $examination->examSubjects()->delete();
            $examination->delete();
        });

        return redirect()
            ->route('examinations.index')
            ->with('success', 'Examination deleted successfully.');
    }

    /**
     * Form dropdown data.
     */
    private function formData(): array
    {
        return [
            'schools' => School::orderBy('name')->get(),

            'academicYears' => AcademicYear::orderByDesc('start_date')
                ->get(),

            'classes' => SchoolClass::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'sections' => Section::with('schoolClass')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),

            'subjects' => Subject::where('is_active', true)
                ->orderBy('name')
                ->get(),
        ];
    }

    /**
     * Validate examination and subject schedule.
     */
    private function validatedData(Request $request): array
    {
        // Normalize exam times to 24-hour H:i format.
        $examSubjects = $request->input('exam_subjects', []);

        foreach ($examSubjects as $index => $subject) {
            foreach (['start_time', 'end_time'] as $field) {
                if (!empty($subject[$field])) {
                    try {
                        $examSubjects[$index][$field] = Carbon::parse(
                            $subject[$field]
                        )->format('H:i');
                    } catch (\Throwable $e) {
                        // Leave invalid values unchanged so validation catches them.
                    }
                }
            }
        }

        $request->merge([
            'exam_subjects' => $examSubjects,
        ]);
        return $request->validate([

            'school_id' => [
                'required',
                'exists:schools,id',
            ],

            'academic_year_id' => [
                'required',
                'exists:academic_years,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'exam_type' => [
                'required',
                'string',
                'max:100',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'required',
                Rule::in(['draft', 'published', 'completed', 'archived']),
            ],

            'instructions' => [
                'nullable',
                'string',
            ],

            'exam_subjects' => [
                'required',
                'array',
                'min:1',
            ],

            'exam_subjects.*.school_class_id' => [
                'required',
                'exists:school_classes,id',
            ],

            'exam_subjects.*.section_id' => [
                'required',
                'exists:sections,id',
            ],

            'exam_subjects.*.subject_id' => [
                'required',
                'exists:subjects,id',
            ],

            'exam_subjects.*.exam_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
                'before_or_equal:end_date',
            ],

            'exam_subjects.*.start_time' => [
                'required',
                'date_format:H:i',
            ],

            'exam_subjects.*.end_time' => [
                'required',
                'date_format:H:i',
            ],

            'exam_subjects.*.maximum_marks' => [
                'required',
                'numeric',
                'min:1',
                'max:10000',
            ],

            'exam_subjects.*.passing_marks' => [
                'required',
                'numeric',
                'min:0',
            ],

            'exam_subjects.*.room_number' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);
    }

    /**
     * Verify class, section and subject combinations.
     */
    private function validateSubjectSchedule(array $subjects): void
    {
        foreach ($subjects as $index => $item) {

            $section = Section::with('schoolClass')
                ->findOrFail($item['section_id']);

            if (
                (int) $section->school_class_id !==
                (int) $item['school_class_id']
            ) {
                throw ValidationException::withMessages([
                    "exam_subjects.{$index}.section_id" =>
                        'The selected section does not belong to this class.',
                ]);
            }

            $assigned = DB::table('class_subjects')
                ->where('school_class_id', $item['school_class_id'])
                ->where('subject_id', $item['subject_id'])
                ->exists();

            if (!$assigned) {
                throw ValidationException::withMessages([
                    "exam_subjects.{$index}.subject_id" =>
                        'The selected subject is not assigned to this class.',
                ]);
            }

            if (
                (float) $item['passing_marks'] >
                (float) $item['maximum_marks']
            ) {
                throw ValidationException::withMessages([
                    "exam_subjects.{$index}.passing_marks" =>
                        'Passing marks cannot exceed maximum marks.',
                ]);
            }

            if ($item['end_time'] <= $item['start_time']) {
                throw ValidationException::withMessages([
                    "exam_subjects.{$index}.end_time" =>
                        'End time must be later than start time.',
                ]);
            }
        }
    }
}
