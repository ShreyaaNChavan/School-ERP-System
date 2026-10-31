<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\ClassSubject;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SubjectController extends Controller
{
    /**
     * Subject directory.
     */
    public function index(Request $request)
    {
        $subjects = Subject::with(['school', 'classSubjects.schoolClass'])
            ->when($request->filled('school_id'), function ($query) use ($request) {
                $query->where('school_id', $request->school_id);
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->status === 'active', fn ($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn ($q) => $q->where('is_active', false))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = School::orderBy('name')->get();

        $stats = [
            'total' => Subject::count(),
            'active' => Subject::where('is_active', true)->count(),
            'inactive' => Subject::where('is_active', false)->count(),
            'assigned' => Subject::has('classSubjects')->count(),
        ];

        return view('subjects.index', compact('subjects', 'schools', 'stats'));
    }

    /**
     * Show subject creation form.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();

        return view('subjects.create', compact('schools'));
    }

    /**
     * Store a new subject.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $request->school_id),
            ],
            'subject_type' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);

        Subject::create($validated);

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    /**
     * Show subject details.
     */
    public function show(Subject $subject)
    {
        $subject->load([
            'school',
            'classSubjects.schoolClass.school',
            'classSubjects.teachingAssignments',
        ]);

        $classes = SchoolClass::where('school_id', $subject->school_id)
            ->where('is_active', true)
            ->orderBy('grade_level')
            ->get();

        return view('subjects.show', compact('subject', 'classes'));
    }

    /**
     * Show edit form.
     */
    public function edit(Subject $subject)
    {
        $schools = School::orderBy('name')->get();

        return view('subjects.edit', compact('subject', 'schools'));
    }

    /**
     * Update subject.
     */
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:150'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('subjects', 'code')
                    ->where('school_id', $request->school_id)
                    ->ignore($subject->id),
            ],
            'subject_type' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Prevent changing the school if assignments already exist.
        if (
            $subject->school_id != $validated['school_id']
            && $subject->classSubjects()->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'school_id' => 'The school cannot be changed while this subject is assigned to classes.',
                ]);
        }

        $subject->update($validated);

        return redirect()
            ->route('subjects.show', $subject)
            ->with('success', 'Subject updated successfully.');
    }

    /**
     * Delete a subject only if it has no class assignments.
     */
    public function destroy(Subject $subject)
    {
        if ($subject->classSubjects()->exists()) {
            return back()->with(
                'error',
                'This subject cannot be deleted because it is assigned to one or more classes.'
            );
        }

        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    /**
     * Assign a subject to a class.
     */
    public function assignClass(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'weekly_periods' => ['required', 'integer', 'min:1', 'max:60'],
            'is_optional' => ['required', 'boolean'],
        ]);

        $schoolClass = SchoolClass::findOrFail($validated['school_class_id']);

        if ($schoolClass->school_id != $subject->school_id) {
            return back()->withErrors([
                'school_class_id' => 'You can only assign this subject to a class belonging to the same school.',
            ])->withInput();
        }

        $alreadyAssigned = ClassSubject::where(
            'school_class_id',
            $schoolClass->id
        )->where('subject_id', $subject->id)->exists();

        if ($alreadyAssigned) {
            return back()->withErrors([
                'school_class_id' => 'This subject is already assigned to the selected class.',
            ])->withInput();
        }

        ClassSubject::create([
            'school_class_id' => $schoolClass->id,
            'subject_id' => $subject->id,
            'weekly_periods' => $validated['weekly_periods'],
            'is_optional' => $validated['is_optional'],
        ]);

        return back()->with('success', 'Subject assigned to class successfully.');
    }

    /**
     * Update an existing class-subject assignment.
     */
    public function updateAssignment(Request $request, ClassSubject $classSubject)
    {
        $validated = $request->validate([
            'weekly_periods' => ['required', 'integer', 'min:1', 'max:60'],
            'is_optional' => ['required', 'boolean'],
        ]);

        $classSubject->update($validated);

        return back()->with('success', 'Subject assignment updated successfully.');
    }

    /**
     * Remove a subject from a class, provided it has no teaching assignments.
     */
    public function removeAssignment(ClassSubject $classSubject)
    {
        if ($classSubject->teachingAssignments()->exists()) {
            return back()->with(
                'error',
                'This assignment cannot be removed because teaching assignments are linked to it.'
            );
        }

        $classSubject->delete();

        return back()->with('success', 'Subject removed from class successfully.');
    }
}
