<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Curriculum;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\ValidationException;

class CurriculumController extends Controller
{
    /**
     * Curriculum directory.
     */
    public function index(Request $request)
    {
        $curricula = Curriculum::with([
            'school',
            'academicYear',
            'schoolClass',
            'subject',
            'chapters.topics',
        ])
            ->when($request->filled('school_id'), fn($q) =>
                $q->where('school_id', $request->school_id))
            ->when($request->filled('academic_year_id'), fn($q) =>
                $q->where('academic_year_id', $request->academic_year_id))
            ->when($request->filled('school_class_id'), fn($q) =>
                $q->where('school_class_id', $request->school_class_id))
            ->when($request->filled('status'), fn($q) =>
                $q->where('status', $request->status))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;

                $q->where(function ($sub) use ($search) {
                    $sub->where('title', 'like', "%{$search}%")
                        ->orWhereHas('subject', fn($s) =>
                            $s->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('schoolClass', fn($c) =>
                            $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = School::orderBy('name')->get();
        $academicYears = AcademicYear::with('school')->orderByDesc('start_date')->get();
        $classes = SchoolClass::orderBy('grade_level')->get();

        $stats = [
            'total' => Curriculum::count(),
            'published' => Curriculum::where('status', 'published')->count(),
            'draft' => Curriculum::where('status', 'draft')->count(),
            'archived' => Curriculum::where('status', 'archived')->count(),
        ];

        return view('curricula.index', compact(
            'curricula',
            'schools',
            'academicYears',
            'classes',
            'stats'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        $academicYears = AcademicYear::with('school')->orderByDesc('start_date')->get();
        $classes = SchoolClass::with('school')->where('is_active', true)
            ->orderBy('grade_level')->get();
        $subjects = Subject::where('is_active', true)->with('school')
            ->orderBy('name')->get();

        return view('curricula.create', compact(
            'schools',
            'academicYears',
            'classes',
            'subjects'
        ));
    }

    /**
     * Store curriculum.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->validateAcademicCombination($validated);

        $curriculum = Curriculum::create([
            ...$validated,
            'status' => 'draft',
        ]);

        return redirect()
            ->route('curricula.show', $curriculum)
            ->with('success', 'Curriculum created successfully. You can now add chapters and topics.');
    }

    /**
     * Show curriculum details.
     */
    public function show(Curriculum $curriculum)
    {
        $curriculum->load([
            'school',
            'academicYear',
            'schoolClass',
            'subject',
            'chapters.topics',
        ]);

        return view('curricula.show', compact('curriculum'));
    }

    /**
     * Edit curriculum.
     */
    public function edit(Curriculum $curriculum)
    {
        if ($curriculum->status !== 'draft') {
            return back()->with('error', 'Only draft curricula can be edited.');
        }

        $schools = School::orderBy('name')->get();
        $academicYears = AcademicYear::with('school')->orderByDesc('start_date')->get();
        $classes = SchoolClass::with('school')->where('is_active', true)
            ->orderBy('grade_level')->get();
        $subjects = Subject::where('is_active', true)->with('school')
            ->orderBy('name')->get();

        return view('curricula.edit', compact(
            'curriculum',
            'schools',
            'academicYears',
            'classes',
            'subjects'
        ));
    }

    /**
     * Update curriculum.
     */
    public function update(Request $request, Curriculum $curriculum)
    {
        if ($curriculum->status !== 'draft') {
            return back()->with('error', 'Only draft curricula can be edited.');
        }

        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $this->validateAcademicCombination($validated, $curriculum->id);

        $curriculum->update($validated);

        return redirect()
            ->route('curricula.show', $curriculum)
            ->with('success', 'Curriculum updated successfully.');
    }

    /**
     * Delete curriculum.
     */
    public function destroy(Curriculum $curriculum)
    {
        if ($curriculum->status === 'published') {
            return back()->with('error', 'Published curricula cannot be deleted. Archive them instead.');
        }

        $curriculum->delete();

        return redirect()
            ->route('curricula.index')
            ->with('success', 'Curriculum deleted successfully.');
    }

    /**
     * Add a chapter.
     */
    public function storeChapter(Request $request, Curriculum $curriculum)
    {
        if ($curriculum->status !== 'draft') {
            return back()->with('error', 'Chapters can only be added to draft curricula.');
        }

        $validated = $request->validate([
            'chapter_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('curriculum_chapters', 'chapter_number')
                    ->where('curriculum_id', $curriculum->id),
            ],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'estimated_periods' => ['required', 'integer', 'min:0', 'max:500'],
        ]);

        $validated['sequence'] = $validated['chapter_number'];

        $curriculum->chapters()->create($validated);

        return back()->with('success', 'Chapter added successfully.');
    }

    /**
     * Update a chapter.
     */
    public function updateChapter(Request $request, CurriculumChapter $chapter)
    {
        if ($chapter->curriculum->status !== 'draft') {
            return back()->with('error', 'Published or archived curriculum chapters cannot be edited.');
        }

        $validated = $request->validate([
            'chapter_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('curriculum_chapters', 'chapter_number')
                    ->where('curriculum_id', $chapter->curriculum_id)
                    ->ignore($chapter->id),
            ],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'estimated_periods' => ['required', 'integer', 'min:0', 'max:500'],
        ]);

        $validated['sequence'] = $validated['chapter_number'];

        $chapter->update($validated);

        return back()->with('success', 'Chapter updated successfully.');
    }

    /**
     * Delete a chapter.
     */
    public function destroyChapter(CurriculumChapter $chapter)
    {
        if ($chapter->curriculum->status !== 'draft') {
            return back()->with('error', 'Only draft curriculum chapters can be deleted.');
        }

        $chapter->delete();

        return back()->with('success', 'Chapter and its topics deleted successfully.');
    }

    /**
     * Add a topic.
     */
    public function storeTopic(Request $request, CurriculumChapter $chapter)
    {
        if ($chapter->curriculum->status !== 'draft') {
            return back()->with('error', 'Topics can only be added to draft curricula.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:3000'],
            'estimated_periods' => ['required', 'integer', 'min:0', 'max:200'],
        ]);

        $validated['sequence'] = ($chapter->topics()->max('sequence') ?? 0) + 1;

        $chapter->topics()->create($validated);

        return back()->with('success', 'Topic added successfully.');
    }

    /**
     * Update topic progress.
     */

    /**
     * Update topic progress.
     */
    public function updateTopic(Request $request, CurriculumTopic $topic)
    {
        $curriculum = $topic->chapter->curriculum;

        if ($curriculum->status === 'archived') {
            return back()->with(
                'error',
                'Archived curriculum progress cannot be changed.'
            );
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['planned', 'in_progress', 'completed'])],
        ]);

        $topic->update([
            'status' => $validated['status'],
            'completed_at' => $validated['status'] === 'completed'
                ? now()->toDateString()
                : null,
        ]);

        // Automatically update the parent chapter status.
        $this->refreshChapterStatus($topic->chapter);

        return back()->with(
            'success',
            'Topic progress updated successfully.'
        );
    }


    /**
     * Delete topic.
     */
    public function destroyTopic(CurriculumTopic $topic)
    {
        if ($topic->chapter->curriculum->status !== 'draft') {
            return back()->with('error', 'Topics cannot be deleted after curriculum publication.');
        }

        $chapter = $topic->chapter;
        $topic->delete();

        $this->refreshChapterStatus($chapter);

        return back()->with('success', 'Topic deleted successfully.');
    }

    /**
     * Publish curriculum.
     */
    public function publish(Curriculum $curriculum)
    {
        if ($curriculum->status !== 'draft') {
            return back()->with('error', 'Only draft curricula can be published.');
        }

        if (!$curriculum->chapters()->exists()) {
            return back()->with('error', 'Add at least one chapter before publishing.');
        }

        $curriculum->update([
            'status' => 'published',
            'published_at' => now()->toDateString(),
        ]);

        return back()->with('success', 'Curriculum published successfully.');
    }

    /**
     * Archive curriculum.
     */
    public function archive(Curriculum $curriculum)
    {
        if ($curriculum->status !== 'published') {
            return back()->with('error', 'Only published curricula can be archived.');
        }

        $curriculum->update(['status' => 'archived']);

        return back()->with('success', 'Curriculum archived successfully.');
    }

    /**
     * Download curriculum PDF.
     */
    public function downloadPdf(Curriculum $curriculum)
    {
        $curriculum->load([
            'school',
            'academicYear',
            'schoolClass',
            'subject',
            'chapters.topics',
        ]);

        $pdf = Pdf::loadView('curricula.pdf', compact('curriculum'))
            ->setPaper('a4', 'portrait');

        $filename = 'Curriculum-' .
            preg_replace('/[^A-Za-z0-9_-]/', '-', $curriculum->title) .
            '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Verify school, year, class and subject belong together.
     */

    /**
     * Verify school, year, class and subject belong together.
     */
    private function validateAcademicCombination(array $data, ?int $ignoreId = null): void
    {
        $year = AcademicYear::findOrFail($data['academic_year_id']);
        $schoolClass = SchoolClass::findOrFail($data['school_class_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        // Check whether all selected records belong to the same school.
        if (
            $year->school_id != $data['school_id'] ||
            $schoolClass->school_id != $data['school_id'] ||
            $subject->school_id != $data['school_id']
        ) {
            throw ValidationException::withMessages([
                'school_id' => 'The selected school, academic year, class and subject must belong to the same school.'
            ]);
        }

        // Check whether the subject is assigned to the selected class.
        $assigned = DB::table('class_subjects')
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subject->id)
            ->exists();

        if (!$assigned) {
            throw ValidationException::withMessages([
                'subject_id' => 'Assign this subject to the selected class in Subject Management first.'
            ]);
        }

        // Prevent duplicate curricula.
        $duplicate = Curriculum::where('academic_year_id', $year->id)
            ->where('school_class_id', $schoolClass->id)
            ->where('subject_id', $subject->id)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'subject_id' => 'A curriculum already exists for this academic year, class and subject.'
            ]);
        }
    }


    /**
     * Keep chapter status in sync with its topics.
     */
    private function refreshChapterStatus(CurriculumChapter $chapter): void
    {
        $total = $chapter->topics()->count();

        $completed = $chapter->topics()
            ->where('status', 'completed')
            ->count();

        $inProgress = $chapter->topics()
            ->where('status', 'in_progress')
            ->exists();

        if ($total > 0 && $completed === $total) {
            $chapter->update([
                'status' => 'completed',
                'completed_at' => $chapter->completed_at ?? now()->toDateString(),
            ]);
        } elseif ($completed > 0 || $inProgress) {
            $chapter->update([
                'status' => 'in_progress',
                'completed_at' => null,
            ]);
        } else {
            $chapter->update([
                'status' => 'planned',
                'completed_at' => null,
            ]);
        }
    }
}
