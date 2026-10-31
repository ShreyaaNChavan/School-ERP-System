<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\CurriculumTopic;
use App\Models\LessonPlan;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\StaffProfile;
use App\Models\Term;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LessonPlanController extends Controller
{

    public function index(Request $request)
    {
        $query = LessonPlan::with([
            'academicYear',
            'classSubject.schoolClass',
            'classSubject.subject',
            'section',
            'teacher',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scheduled_date')) {
            $query->whereDate('scheduled_date', $request->scheduled_date);
        }

        $lessonPlans = $query
            ->orderByDesc('scheduled_date')
            ->paginate(10)
            ->withQueryString();

        $selectedLesson = null;

        if ($request->filled('selected')) {
            $selectedLesson = LessonPlan::with([
                'academicYear',
                'term',
                'classSubject.schoolClass',
                'classSubject.subject',
                'section',
                'teacher',
                'curriculumTopic',
                'objectives',
                'activities',
                'resources',
            ])->find($request->integer('selected'));
        }

        if (!$selectedLesson) {
            $selectedLesson = $lessonPlans->first();

            if ($selectedLesson) {
                $selectedLesson->load([
                    'term',
                    'curriculumTopic',
                    'objectives',
                    'activities',
                    'resources',
                ]);
            }
        }

        $statistics = [
            'total' => LessonPlan::count(),
            'draft' => LessonPlan::where('status', 'draft')->count(),
            'published' => LessonPlan::where('status', 'published')->count(),
            'completed' => LessonPlan::where('status', 'completed')->count(),
        ];

        return view('lesson-plans.index', compact(
            'lessonPlans',
            'selectedLesson',
            'statistics'
        ));
    }


    public function create()
    {
        return view('lesson-plans.create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateLessonPlan($request);

        DB::transaction(function () use ($request, $validated) {
            $classSubject = ClassSubject::with('schoolClass')
                ->findOrFail($validated['class_subject_id']);

            $lessonPlan = LessonPlan::create([
                'school_id' => $classSubject->schoolClass->school_id,
                'academic_year_id' => $validated['academic_year_id'],
                'term_id' => $validated['term_id'] ?? null,
                'class_subject_id' => $validated['class_subject_id'],
                'section_id' => $validated['section_id'],
                'staff_profile_id' => $validated['staff_profile_id'],
                'curriculum_topic_id' => $validated['curriculum_topic_id'] ?? null,
                'created_by' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'scheduled_date' => $validated['scheduled_date'],
                'duration_minutes' => $validated['duration_minutes'],
                'teaching_method' => $validated['teaching_method'] ?? null,
                'teacher_notes' => $validated['teacher_notes'] ?? null,
                'status' => 'draft',
            ]);

            $this->saveLessonPlanItems($request, $lessonPlan);
        });

        return redirect()
            ->route('lesson-plans.index')
            ->with('success', 'Lesson plan created successfully.');
    }

    public function show(LessonPlan $lessonPlan)
    {
        $lessonPlan->load([
            'academicYear',
            'term',
            'classSubject.schoolClass',
            'classSubject.subject',
            'section',
            'teacher',
            'curriculumTopic',
            'creator',
            'objectives',
            'activities',
            'resources',
        ]);

        return view('lesson-plans.show', compact('lessonPlan'));
    }

    public function edit(LessonPlan $lessonPlan)
    {
        if (
            $lessonPlan->status === 'completed' ||
            $lessonPlan->status === 'cancelled'
        ) {
            return back()->with('error', 'This lesson plan cannot be edited.');
        }

        $lessonPlan->load(['objectives', 'activities', 'resources']);

        return view('lesson-plans.edit', array_merge(
            $this->formData(),
            compact('lessonPlan')
        ));
    }

    public function update(Request $request, LessonPlan $lessonPlan)
    {
        if (in_array($lessonPlan->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This lesson plan cannot be edited.');
        }

        $validated = $this->validateLessonPlan($request, $lessonPlan);

        DB::transaction(function () use ($request, $validated, $lessonPlan) {
            $classSubject = ClassSubject::with('schoolClass')
                ->findOrFail($validated['class_subject_id']);

            $lessonPlan->update([
                'school_id' => $classSubject->schoolClass->school_id,
                'academic_year_id' => $validated['academic_year_id'],
                'term_id' => $validated['term_id'] ?? null,
                'class_subject_id' => $validated['class_subject_id'],
                'section_id' => $validated['section_id'],
                'staff_profile_id' => $validated['staff_profile_id'],
                'curriculum_topic_id' => $validated['curriculum_topic_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'scheduled_date' => $validated['scheduled_date'],
                'duration_minutes' => $validated['duration_minutes'],
                'teaching_method' => $validated['teaching_method'] ?? null,
                'teacher_notes' => $validated['teacher_notes'] ?? null,
            ]);

            $this->saveLessonPlanItems($request, $lessonPlan, true);
        });

        return redirect()
            ->route('lesson-plans.show', $lessonPlan)
            ->with('success', 'Lesson plan updated successfully.');
    }

    public function destroy(LessonPlan $lessonPlan)
    {
        DB::transaction(function () use ($lessonPlan) {
            foreach ($lessonPlan->resources as $resource) {
                if ($resource->file_path) {
                    Storage::disk('public')->delete($resource->file_path);
                }
            }

            $lessonPlan->delete();
        });

        return redirect()
            ->route('lesson-plans.index')
            ->with('success', 'Lesson plan deleted successfully.');
    }

    public function updateStatus(Request $request, LessonPlan $lessonPlan)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,published,in_progress,completed,cancelled',
        ]);

        $status = $validated['status'];

        $updates = ['status' => $status];

        if ($status === 'published' && !$lessonPlan->published_at) {
            $updates['published_at'] = now();
        }

        if ($status === 'completed') {
            $updates['completed_at'] = now();
        }

        $lessonPlan->update($updates);

        return back()->with('success', 'Lesson plan status updated.');
    }

    public function pdf(LessonPlan $lessonPlan)
    {
        $lessonPlan->load([
            'academicYear',
            'term',
            'classSubject.schoolClass',
            'classSubject.subject',
            'section',
            'teacher',
            'curriculumTopic',
            'objectives',
            'activities',
            'resources',
        ]);

        $pdf = Pdf::loadView('lesson-plans.pdf', compact('lessonPlan'));

        return $pdf->download('lesson-plan-' . $lessonPlan->id . '.pdf');
    }

    private function formData(): array
    {
        return [
            'schools' => School::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderByDesc('start_date')->get(),
            'classSubjects' => ClassSubject::with([
                'schoolClass',
                'subject',
            ])->get(),
            'sections' => Section::where('is_active', true)->get(),
            'teachers' => StaffProfile::where('status', 'active')
                ->orderBy('first_name')
                ->get(),
            'terms' => Term::orderBy('sequence_number')->get(),
            'curriculumTopics' => CurriculumTopic::with([
                'chapter.curriculum',
            ])->get(),
        ];
    }

    private function validateLessonPlan(
        Request $request,
        ?LessonPlan $lessonPlan = null
    ): array {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_subject_id' => 'required|exists:class_subjects,id',
            'section_id' => 'required|exists:sections,id',
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'curriculum_topic_id' => 'nullable|exists:curriculum_topics,id',

            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'scheduled_date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1|max:600',
            'teaching_method' => 'nullable|string|max:100',
            'teacher_notes' => 'nullable|string',

            'objectives' => 'nullable|array',
            'objectives.*.title' => 'required_with:objectives|string|max:255',
            'objectives.*.description' => 'nullable|string',

            'activities' => 'nullable|array',
            'activities.*.title' => 'required_with:activities|string|max:255',
            'activities.*.description' => 'nullable|string',
            'activities.*.activity_type' => 'nullable|string|max:50',
            'activities.*.duration_minutes' => 'nullable|integer|min:0|max:600',

            'resources' => 'nullable|array',
            'resources.*.title' => 'required_with:resources|string|max:255',
            'resources.*.resource_type' => 'required_with:resources|string|max:50',
            'resources.*.description' => 'nullable|string',
            'resources.*.external_url' => 'nullable|url|max:2000',
            'resources.*.file' => 'nullable|file|max:10240',
        ]);

        $classSubject = ClassSubject::with('schoolClass')->findOrFail(
            $validated['class_subject_id']
        );

        $schoolClass = $classSubject->schoolClass;

        $academicYear = AcademicYear::findOrFail(
            $validated['academic_year_id']
        );

        if ($academicYear->school_id !== $schoolClass->school_id) {
            throw ValidationException::withMessages([
                'academic_year_id' => 'The academic year does not belong to the selected school.',
            ]);
        }

        $section = Section::with('schoolClass')->findOrFail(
            $validated['section_id']
        );

        if ($section->school_class_id !== $schoolClass->id) {
            throw ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to this class.',
            ]);
        }

        $teacher = StaffProfile::findOrFail($validated['staff_profile_id']);

        if ($teacher->school_id !== $schoolClass->school_id) {
            throw ValidationException::withMessages([
                'staff_profile_id' => 'The selected teacher does not belong to this school.',
            ]);
        }

        if (!empty($validated['term_id'])) {
            $term = Term::findOrFail($validated['term_id']);

            if ($term->academic_year_id !== $academicYear->id) {
                throw ValidationException::withMessages([
                    'term_id' => 'The selected term does not belong to this academic year.',
                ]);
            }
        }

        if (!empty($validated['curriculum_topic_id'])) {
            $topic = CurriculumTopic::with('chapter.curriculum')
                ->findOrFail($validated['curriculum_topic_id']);

            $curriculum = $topic->chapter->curriculum;

            if (
                $curriculum->school_id !== $schoolClass->school_id ||
                $curriculum->academic_year_id !== $academicYear->id ||
                $curriculum->school_class_id !== $schoolClass->id ||
                $curriculum->subject_id !== $classSubject->subject_id
            ) {
                throw ValidationException::withMessages([
                    'curriculum_topic_id' => 'The selected topic does not match this class, subject and academic year.',
                ]);
            }
        }

        return $validated;
    }

    private function saveLessonPlanItems(
        Request $request,
        LessonPlan $lessonPlan,
        bool $replace = false
    ): void {
        if ($replace) {
            foreach ($lessonPlan->resources as $resource) {
                if ($resource->file_path) {
                    Storage::disk('public')->delete($resource->file_path);
                }
            }

            $lessonPlan->objectives()->delete();
            $lessonPlan->activities()->delete();
            $lessonPlan->resources()->delete();
        }

        foreach ($request->input('objectives', []) as $index => $objective) {
            if (empty($objective['title'])) {
                continue;
            }

            $lessonPlan->objectives()->create([
                'title' => $objective['title'],
                'description' => $objective['description'] ?? null,
                'sequence' => $index,
            ]);
        }

        foreach ($request->input('activities', []) as $index => $activity) {
            if (empty($activity['title'])) {
                continue;
            }

            $lessonPlan->activities()->create([
                'title' => $activity['title'],
                'description' => $activity['description'] ?? null,
                'activity_type' => $activity['activity_type'] ?? null,
                'duration_minutes' => $activity['duration_minutes'] ?? 0,
                'sequence' => $index,
            ]);
        }

        foreach ($request->input('resources', []) as $index => $resource) {
            if (empty($resource['title'])) {
                continue;
            }

            $filePath = null;

            if ($request->hasFile("resources.{$index}.file")) {
                $filePath = $request->file("resources.{$index}.file")
                    ->store('lesson-plan-resources', 'public');
            }

            $lessonPlan->resources()->create([
                'title' => $resource['title'],
                'resource_type' => $resource['resource_type'],
                'description' => $resource['description'] ?? null,
                'external_url' => $resource['external_url'] ?? null,
                'file_path' => $filePath,
            ]);
        }
    }
}
