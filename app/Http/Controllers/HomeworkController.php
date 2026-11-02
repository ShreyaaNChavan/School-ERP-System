<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\HomeworkAssignment;
use App\Models\HomeworkSubmission;
use App\Models\School;
use App\Models\Section;
use App\Models\SchoolClass;
use App\Models\StaffProfile;
use App\Models\StudentEnrollment;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HomeworkController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeworkAssignment::with(['section.schoolClass', 'subject', 'teacher', 'academicYear'])
            ->withCount('submissions')
            ->withCount(['submissions as reviewed_count' => fn($q) => $q->whereIn('status', ['reviewed', 'graded'])]);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = trim($request->search);
            $q->where(function ($sub) use ($term) {
                $sub->where('title', 'like', "%{$term}%")
                    ->orWhere('instructions', 'like', "%{$term}%");
            });
        });
        $query->when($request->filled('academic_year_id'), fn($q) => $q->where('academic_year_id', $request->academic_year_id));
        $query->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id));
        $query->when($request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id));
        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));

        $assignments = $query->latest('assigned_date')->latest('id')->paginate(10)->withQueryString();

        $stats = [
            'total' => HomeworkAssignment::count(),
            'published' => HomeworkAssignment::where('status', 'published')->count(),
            'draft' => HomeworkAssignment::where('status', 'draft')->count(),
            'overdue' => HomeworkAssignment::whereNotNull('due_date')->whereDate('due_date', '<', today())
                ->where('status', 'published')->count(),
            'submissions' => HomeworkSubmission::count(),
        ];

        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        $classes = SchoolClass::where('is_active', true)->orderBy('name')->get();
        $sections = Section::with('schoolClass')->where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('is_active', true)->orderBy('name')->get();

        return view('homework.index', compact(
            'assignments', 'stats', 'academicYears', 'classes', 'sections', 'subjects'
        ));
    }

    public function create()
    {
        return view('homework.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['school_id'] = Section::with('schoolClass')->findOrFail($data['section_id'])->schoolClass->school_id;
        $data['staff_profile_id'] = $this->teacherProfileId($request);
        $data['attachment_path'] = $request->file('attachment')?->store('homework/attachments', 'public');
        unset($data['attachment']);

        $assignment = HomeworkAssignment::create($data);

        return redirect()->route('homework.show', $assignment)->with('success', 'Homework created successfully.');
    }

    public function show(HomeworkAssignment $homework)
    {
        $homework->load(['section.schoolClass', 'subject', 'teacher', 'academicYear']);
        $homework->loadCount('submissions');
        $enrolledIds = StudentEnrollment::where('academic_year_id', $homework->academic_year_id)
            ->where('section_id', $homework->section_id)->where('status', 'active')->pluck('student_id');

        $submissions = $homework->submissions()->with('student')
            ->orderByRaw("CASE status WHEN 'submitted' THEN 1 WHEN 'late' THEN 2 WHEN 'pending' THEN 3 ELSE 4 END")
            ->paginate(10);

        $submittedCount = $homework->submissions()->whereNotNull('submitted_at')->count();
        $reviewedCount = $homework->submissions()->whereIn('status', ['reviewed', 'graded'])->count();

        return view('homework.show', compact('homework', 'submissions', 'enrolledIds', 'submittedCount', 'reviewedCount'));
    }

    public function edit(HomeworkAssignment $homework)
    {
        return view('homework.edit', array_merge($this->formData(), compact('homework')));
    }

    public function update(Request $request, HomeworkAssignment $homework)
    {
        $data = $this->validatedData($request);
        $section = Section::with('schoolClass')->findOrFail($data['section_id']);
        $data['school_id'] = $section->schoolClass->school_id;
        $data['staff_profile_id'] = $this->teacherProfileId($request);
        unset($data['attachment']);

        if ($request->hasFile('attachment')) {
            if ($homework->attachment_path) Storage::disk('public')->delete($homework->attachment_path);
            $data['attachment_path'] = $request->file('attachment')->store('homework/attachments', 'public');
        }

        $homework->update($data);

        return redirect()->route('homework.show', $homework)->with('success', 'Homework updated successfully.');
    }

    public function review(Request $request, HomeworkSubmission $submission)
    {
        $data = $request->validate([
            'marks_obtained' => ['nullable', 'numeric', 'min:0', 'max:1000'],
            'teacher_feedback' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(['reviewed', 'graded', 'needs_correction'])],
        ]);

        $submission->update([
            ...$data,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Submission review saved.');
    }

    public function destroy(HomeworkAssignment $homework)
    {
        if ($homework->attachment_path) Storage::disk('public')->delete($homework->attachment_path);
        $homework->delete();

        return redirect()->route('homework.index')->with('success', 'Homework deleted successfully.');
    }

    private function formData(): array
    {
        return [
            'schools' => School::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'classes' => SchoolClass::where('is_active', true)->orderBy('name')->get(),
            'sections' => Section::with('schoolClass')->where('is_active', true)->orderBy('name')->get(),
            'subjects' => Subject::where('is_active', true)->orderBy('name')->get(),
            'teachers' => StaffProfile::where('status', 'active')->whereNull('deleted_at')
                ->orderBy('first_name')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'staff_profile_id' => ['required', 'exists:staff_profiles,id'],
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'assigned_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:assigned_date'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png', 'max:10240'],
        ]);

        $section = Section::with('schoolClass')->findOrFail($data['section_id']);
        abort_unless(
            DB::table('class_subjects')->where('school_class_id', $section->school_class_id)
                ->where('subject_id', $data['subject_id'])->exists(),
            422,
            'The selected subject is not assigned to this class.'
        );

        abort_unless(
            StaffProfile::whereKey($data['staff_profile_id'])->where('school_id', $section->schoolClass->school_id)
                ->where('status', 'active')->exists(),
            422,
            'The selected teacher is not active in this school.'
        );

        return $data;
    }

    private function teacherProfileId(Request $request): int
    {
        return (int) $request->input('staff_profile_id');
    }
}
