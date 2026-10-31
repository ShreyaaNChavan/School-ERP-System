<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClassSectionController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::with([
            'school',
            'sections.campus',
        ]);

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $classes = $query->orderBy('grade_level')
            ->paginate(10)
            ->withQueryString();

        $schools = School::orderBy('name')->get();

        $campuses = Campus::orderBy('name')->get();

        $allClasses = SchoolClass::with('school')
            ->orderBy('grade_level')
            ->get();

        $stats = [
            'total_classes' => SchoolClass::count(),
            'active_classes' => SchoolClass::where('is_active', true)->count(),
            'total_sections' => Section::count(),
            'active_sections' => Section::where('is_active', true)->count(),
        ];

       
        return view('class-sections.index', compact(
            'classes',
            'schools',
            'campuses',
            'allClasses',
            'stats'
        ));
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('school_classes', 'code')
                    ->where('school_id', $request->school_id),
            ],
            'grade_level' => ['nullable', 'integer', 'min:0', 'max:20'],
            'education_stage' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['is_active'] = true;

        SchoolClass::create($validated);

        return redirect()
            ->route('class-sections.index')
            ->with('success', 'Class created successfully.');
    }

    public function updateClass(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('school_classes', 'code')
                    ->where('school_id', $request->school_id)
                    ->ignore($schoolClass->id),
            ],
            'grade_level' => ['nullable', 'integer', 'min:0', 'max:20'],
            'education_stage' => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);

        // Prevent changing the school if existing sections
        // are assigned to campuses belonging to another school.
        if ($schoolClass->school_id != $validated['school_id']) {
            $hasOtherSchoolCampus = $schoolClass->sections()
                ->whereHas('campus', function ($query) use ($validated) {
                    $query->where('school_id', '!=', $validated['school_id']);
                })
                ->exists();

            if ($hasOtherSchoolCampus) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'school_id' => 'This class has sections assigned to campuses outside the selected school.',
                    ]);
            }
        }

        $schoolClass->update($validated);

        return redirect()
            ->route('class-sections.index')
            ->with('success', 'Class updated successfully.');
    }

    public function storeSection(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:50'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
        ]);

        $schoolClass = SchoolClass::findOrFail(
            $validated['school_class_id']
        );

        $campus = Campus::findOrFail($validated['campus_id']);

        if ($campus->school_id != $schoolClass->school_id) {
            return back()
                ->withInput()
                ->withErrors([
                    'campus_id' => 'The selected campus does not belong to the selected school.',
                ]);
        }

        $duplicate = Section::where(
            'school_class_id',
            $schoolClass->id
        )->where('campus_id', $campus->id)
            ->where('name', $validated['name'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'This section already exists for the selected class and campus.',
                ]);
        }

        $validated['is_active'] = true;

        Section::create($validated);

        return redirect()
            ->route('class-sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function updateSection(Request $request, Section $section)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'campus_id' => ['required', 'exists:campuses,id'],
            'name' => ['required', 'string', 'max:50'],
            'room_number' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $schoolClass = SchoolClass::findOrFail(
            $validated['school_class_id']
        );

        $campus = Campus::findOrFail($validated['campus_id']);

        if ($campus->school_id != $schoolClass->school_id) {
            return back()
                ->withInput()
                ->withErrors([
                    'campus_id' => 'The selected campus does not belong to the selected school.',
                ]);
        }

        $duplicate = Section::where(
            'school_class_id',
            $schoolClass->id
        )->where('campus_id', $campus->id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $section->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'This section already exists for the selected class and campus.',
                ]);
        }

        $section->update($validated);

        return redirect()
            ->route('class-sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function toggleClassStatus(SchoolClass $schoolClass)
    {
        $schoolClass->update([
            'is_active' => !$schoolClass->is_active,
        ]);

        return back()->with(
            'success',
            'Class status updated successfully.'
        );
    }

    public function toggleSectionStatus(Section $section)
    {
        $section->update([
            'is_active' => !$section->is_active,
        ]);

        return back()->with(
            'success',
            'Section status updated successfully.'
        );
    }
}
