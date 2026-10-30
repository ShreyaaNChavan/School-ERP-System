<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\School;
use App\Models\Campus;
use App\Models\Guardian;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $students = StudentProfile::with([
            'school',
            'campus',
            'enrollments.academicYear',
            'enrollments.schoolClass',
            'enrollments.section',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('middle_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('school_id'), function ($query) use ($request) {
                $query->where('school_id', $request->school_id);
            })
            ->when($request->filled('campus_id'), function ($query) use ($request) {
                $query->where('campus_id', $request->campus_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->whereHas('enrollments', function ($q) use ($request) {
                    $q->where('school_class_id', $request->class_id)
                        ->where('status', 'active');
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get();

        $campuses = Campus::where('status', 'active')
            ->orderBy('name')
            ->get();

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $stats = [
            'total' => StudentProfile::count(),
            'active' => StudentProfile::where('status', 'active')->count(),
            'inactive' => StudentProfile::where('status', 'inactive')->count(),
        ];

        return view('students.index', compact(
            'students',
            'schools',
            'campuses',
            'classes',
            'stats'
        ));
    }

    public function create()
    {
        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get();

        $campuses = Campus::where('status', 'active')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $sections = Section::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('students.create', compact(
            'schools',
            'campuses',
            'academicYears',
            'classes',
            'sections'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateStudent($request);

        DB::transaction(function () use ($validated) {

            $student = StudentProfile::create([
                'school_id' => $validated['school_id'],
                'campus_id' => $validated['campus_id'],
                'admission_number' => $validated['admission_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
                'status' => 'active',
            ]);

            $student->enrollments()->create([
                'academic_year_id' => $validated['academic_year_id'],
                'school_class_id' => $validated['school_class_id'],
                'section_id' => $validated['section_id'],
                'enrollment_date' => $validated['enrollment_date'] ?? now()->toDateString(),
                'status' => 'active',
            ]);
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Student admitted successfully.');
    }

    public function edit(StudentProfile $student)
    {
        $schools = School::where('status', 'active')
            ->orderBy('name')
            ->get();

        $campuses = Campus::where('status', 'active')
            ->orderBy('name')
            ->get();

        $academicYears = AcademicYear::orderByDesc('start_date')->get();

        $classes = SchoolClass::where('is_active', true)
            ->orderBy('name')
            ->get();

        $sections = Section::where('is_active', true)
            ->orderBy('name')
            ->get();

        $student->load([
            'enrollments' => function ($query) {
                $query->where('status', 'active')->latest();
            }
        ]);

        $enrollment = $student->enrollments->first();

        return view('students.edit', compact(
            'student',
            'enrollment',
            'schools',
            'campuses',
            'academicYears',
            'classes',
            'sections'
        ));
    }

    public function update(Request $request, StudentProfile $student)
    {
        $validated = $this->validateStudent($request, $student);

        DB::transaction(function () use ($validated, $student) {

            $student->update([
                'school_id' => $validated['school_id'],
                'campus_id' => $validated['campus_id'],
                'admission_number' => $validated['admission_number'],
                'first_name' => $validated['first_name'],
                'middle_name' => $validated['middle_name'] ?? null,
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'admission_date' => $validated['admission_date'] ?? null,
            ]);

            $enrollment = $student->enrollments()
                ->where('status', 'active')
                ->latest()
                ->first();

            $enrollmentData = [
                'academic_year_id' => $validated['academic_year_id'],
                'school_class_id' => $validated['school_class_id'],
                'section_id' => $validated['section_id'],
                'enrollment_date' => $validated['enrollment_date'] ?? now()->toDateString(),
                'status' => 'active',
            ];

            if ($enrollment) {
                $enrollment->update($enrollmentData);
            } else {
                $student->enrollments()->create($enrollmentData);
            }
        });

        return redirect()
            ->route('students.index')
            ->with('success', 'Student details updated successfully.');
    }

    public function toggleStatus(StudentProfile $student)
    {
        $student->update([
            'status' => $student->status === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()
            ->route('students.index')
            ->with('success', 'Student status updated successfully.');
    }

    private function validateStudent(
        Request $request,
        ?StudentProfile $student = null
    ): array {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],

            'campus_id' => [
                'required',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $request->school_id),
            ],

            'admission_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('student_profiles', 'admission_number')
                    ->where('school_id', $request->school_id)
                    ->ignore($student?->id),
            ],

            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],

            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'string', 'max:30'],
            'blood_group' => ['nullable', 'string', 'max:5'],
            'admission_date' => ['nullable', 'date'],

            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')
                    ->where('school_id', $request->school_id),
            ],

            'school_class_id' => [
                'required',
                Rule::exists('school_classes', 'id')
                    ->where('school_id', $request->school_id),
            ],

            'section_id' => [
                'required',
                Rule::exists('sections', 'id')
                    ->where('campus_id', $request->campus_id),
            ],

            'enrollment_date' => ['nullable', 'date'],
        ]);

        $sectionMatchesClass = Section::where('id', $validated['section_id'])
            ->whereHas('schoolClass', function ($query) use ($validated) {
                $query->where('id', $validated['school_class_id']);
            })
            ->exists();

        if (!$sectionMatchesClass) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'section_id' => 'The selected section does not belong to the selected class.',
            ]);
        }

        return $validated;
    }

    public function show(StudentProfile $student)
    {
        $student->load([
            'school',
            'campus',
            'enrollments' => function ($query) {
                $query->latest('id');
            },
            'enrollments.academicYear',
            'enrollments.schoolClass',
            'enrollments.section',
            'guardians',
            'emergencyContacts',
        ]);

        return view('students.show', compact('student'));
    }
}
