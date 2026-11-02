<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('assignments as a')
            ->join('sections as sec', 'sec.id', '=', 'a.section_id')
            ->join('school_classes as sc', 'sc.id', '=', 'sec.school_class_id')
            ->join('subjects as sub', 'sub.id', '=', 'a.subject_id')
            ->join('staff_profiles as sp', 'sp.id', '=', 'a.staff_profile_id')
            ->select('a.*', 'sec.name as section_name', 'sc.name as class_name',
                'sub.name as subject_name', 'sp.first_name as teacher_first_name',
                'sp.last_name as teacher_last_name')
            ->selectSub(function ($q) {
                $q->from('assignment_submissions')->selectRaw('COUNT(*)')
                    ->whereColumn('assignment_id', 'a.id');
            }, 'submissions_count');

        $query->when($request->filled('search'), fn($q) =>
            $q->where('a.title', 'like', '%' . $request->search . '%'));
        $query->when($request->filled('section_id'), fn($q) =>
            $q->where('a.section_id', $request->section_id));
        $query->when($request->filled('subject_id'), fn($q) =>
            $q->where('a.subject_id', $request->subject_id));
        $query->when($request->filled('status'), fn($q) =>
            $q->where('a.status', $request->status));

        $assignments = $query->orderByDesc('a.created_at')->paginate(10)->withQueryString();
        $sections = DB::table('sections')->join('school_classes', 'school_classes.id', '=', 'sections.school_class_id')
            ->select('sections.*', 'school_classes.name as class_name')->where('sections.is_active', 1)->get();
        $subjects = DB::table('subjects')->where('is_active', 1)->orderBy('name')->get();

        $stats = [
            'total' => DB::table('assignments')->count(),
            'published' => DB::table('assignments')->where('status', 'published')->count(),
            'draft' => DB::table('assignments')->where('status', 'draft')->count(),
            'submissions' => DB::table('assignment_submissions')->count(),
        ];

        return view('assignments.index', compact('assignments', 'sections', 'subjects', 'stats'));
    }

    public function create()
    {
        return view('assignments.form', $this->formData() + [
            'pageTitle' => 'Create Assignment', 'assignment' => null,
            'formAction' => route('assignments.store'), 'formMethod' => 'POST',
            'submitLabel' => 'Create Assignment',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['attachment_path'] = $request->file('attachment')?->store('assignments', 'public');
        $rubrics = $data['rubrics'] ?? [];
        unset($data['rubrics']);
        $id = DB::table('assignments')->insertGetId($data + [
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->saveRubrics($id, $rubrics);
        return redirect()->route('assignments.show', $id)->with('success', 'Assignment created successfully.');
    }

    public function show(string $id)
    {
        $assignment = DB::table('assignments as a')
            ->join('sections as sec', 'sec.id', '=', 'a.section_id')
            ->join('school_classes as sc', 'sc.id', '=', 'sec.school_class_id')
            ->join('subjects as sub', 'sub.id', '=', 'a.subject_id')
            ->join('staff_profiles as sp', 'sp.id', '=', 'a.staff_profile_id')
            ->select('a.*', 'sec.name as section_name', 'sc.name as class_name',
                'sub.name as subject_name', 'sp.first_name as teacher_first_name',
                'sp.last_name as teacher_last_name')->where('a.id', $id)->first();

        abort_unless($assignment, 404);
        $rubrics = DB::table('assignment_rubrics')->where('assignment_id', $id)->orderBy('sequence')->get();
        return view('assignments.show', compact('assignment', 'rubrics'));
    }

    public function edit(string $id)
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        abort_unless($assignment, 404);
        $rubrics = DB::table('assignment_rubrics')->where('assignment_id', $id)->orderBy('sequence')->get();
        return view('assignments.form', $this->formData() + [
            'pageTitle' => 'Edit Assignment', 'assignment' => $assignment, 'rubrics' => $rubrics,
            'formAction' => route('assignments.update', $id), 'formMethod' => 'PUT',
            'submitLabel' => 'Update Assignment',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        abort_unless($assignment, 404);
        $data = $this->validated($request);
        if ($request->hasFile('attachment')) {
            if ($assignment->attachment_path) Storage::disk('public')->delete($assignment->attachment_path);
            $data['attachment_path'] = $request->file('attachment')->store('assignments', 'public');
        }
        $rubrics = $data['rubrics'] ?? [];
        unset($data['rubrics']);
        $data['updated_at'] = now();
        DB::table('assignments')->where('id', $id)->update($data);
        DB::table('assignment_rubrics')->where('assignment_id', $id)->delete();
        $this->saveRubrics((int)$id, $rubrics);
        return redirect()->route('assignments.show', $id)->with('success', 'Assignment updated successfully.');
    }

    public function destroy(string $id)
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        abort_unless($assignment, 404);
        if ($assignment->attachment_path) Storage::disk('public')->delete($assignment->attachment_path);
        DB::table('assignments')->where('id', $id)->delete();
        return redirect()->route('assignments.index')->with('success', 'Assignment deleted successfully.');
    }

    public function submissions(string $id)
    {
        $assignment = DB::table('assignments')->where('id', $id)->first();
        abort_unless($assignment, 404);
        $submissions = DB::table('assignment_submissions as s')
            ->join('student_profiles as st', 'st.id', '=', 's.student_id')
            ->select('s.*', 'st.admission_number', 'st.first_name', 'st.middle_name', 'st.last_name')
            ->where('s.assignment_id', $id)->orderBy('st.first_name')->paginate(15);
        return view('assignments.submissions', compact('assignment', 'submissions'));
    }

    public function review(Request $request, string $assignment, string $submission)
    {
        $data = $request->validate([
            'marks_obtained' => ['required', 'numeric', 'min:0'],
            'teacher_feedback' => ['nullable', 'string', 'max:5000'],
        ]);
        $record = DB::table('assignment_submissions')->where('id', $submission)
            ->where('assignment_id', $assignment)->first();
        abort_unless($record, 404);
        $max = DB::table('assignments')->where('id', $assignment)->value('maximum_marks');
        validator($data, ['marks_obtained' => ['lte:' . $max]])->validate();
        DB::table('assignment_submissions')->where('id', $submission)->update([
            'marks_obtained' => $data['marks_obtained'],
            'teacher_feedback' => $data['teacher_feedback'] ?? null,
            'reviewed_by' => auth()->id(), 'reviewed_at' => now(),
            'status' => 'graded', 'updated_at' => now(),
        ]);
        return back()->with('success', 'Submission evaluated successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'section_id' => ['required', 'exists:sections,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'staff_profile_id' => ['required', 'exists:staff_profiles,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assignment_type' => ['required', Rule::in(['individual', 'group', 'project', 'practical'])],
            'maximum_marks' => ['required', 'numeric', 'min:1'],
            'passing_marks' => ['required', 'numeric', 'min:0', 'lte:maximum_marks'],
            'start_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,ppt,pptx,jpg,jpeg,png'],
            'rubrics' => ['nullable', 'array'],
            'rubrics.*.criterion' => ['required_with:rubrics', 'string', 'max:255'],
            'rubrics.*.maximum_marks' => ['required_with:rubrics', 'numeric', 'min:0'],
        ]) + [
            'created_at' => now(), 'updated_at' => now(),
        ];
    }

    private function saveRubrics(int $id, array $rubrics): void
    {
        foreach ($rubrics as $index => $rubric) {
            DB::table('assignment_rubrics')->insert([
                'assignment_id' => $id, 'criterion' => $rubric['criterion'],
                'maximum_marks' => $rubric['maximum_marks'], 'sequence' => $index + 1,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function formData(): array
    {
        return [
            'schools' => DB::table('schools')->orderBy('name')->get(),
            'academicYears' => DB::table('academic_years')->orderByDesc('start_date')->get(),
            'sections' => DB::table('sections')->join('school_classes', 'school_classes.id', '=', 'sections.school_class_id')
                ->select('sections.*', 'school_classes.name as class_name', 'school_classes.school_id')
                ->where('sections.is_active', 1)->get(),
            'subjects' => DB::table('subjects')->where('is_active', 1)->orderBy('name')->get(),
            'teachers' => DB::table('staff_profiles')->where('status', 'active')->whereNull('deleted_at')
                ->orderBy('first_name')->get(),
        ];
    }
}
