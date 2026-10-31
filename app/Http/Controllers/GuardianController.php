<?php

namespace App\Http\Controllers;

use App\Models\Guardian;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GuardianController extends Controller
{
    /**
     * Parent / Guardian Directory
     */
    public function index(Request $request)
    {
        $query = Guardian::withCount('students')
            ->with('students');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('relationship', 'like', "%{$search}%")
                    ->orWhereHas('students', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('middle_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('admission_number', 'like', "%{$search}%");
                    });
            });
        }

        $guardians = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => Guardian::count(),
            'linked' => Guardian::has('students')->count(),
            'unlinked' => Guardian::doesntHave('students')->count(),
        ];

        return view('guardians.index', compact(
            'guardians',
            'stats'
        ));
    }

    /**
     * Create Guardian Form
     */
    public function create()
    {
        $students = StudentProfile::with('school', 'campus')
            ->orderBy('first_name')
            ->get();

        return view('guardians.create', compact('students'));
    }

    /**
     * Store Guardian
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],

            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [
                'integer',
                'distinct',
                'exists:student_profiles,id',
            ],

            'is_primary' => ['nullable', 'boolean'],
            'is_emergency_contact' => ['nullable', 'boolean'],
            'can_pick_up_student' => ['nullable', 'boolean'],
            'receives_communications' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $guardian = Guardian::create([
                'full_name' => $validated['full_name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
                'user_id' => null,
            ]);

            $guardian->students()->sync(
                $this->studentPivotData(
                    $request->input('student_ids', []),
                    $request
                )
            );
        });

        return redirect()
            ->route('guardians.index')
            ->with('success', 'Parent / Guardian created successfully.');
    }

    /**
     * Show Guardian Profile
     */
    public function show(Guardian $guardian)
    {
        $guardian->load([
            'students.school',
            'students.campus',
            'students.enrollments.academicYear',
            'students.enrollments.schoolClass',
            'students.enrollments.section',
        ]);

        return view('guardians.show', compact('guardian'));
    }

    /**
     * Edit Guardian
     */
    public function edit(Guardian $guardian)
    {
        $guardian->load('students');

        $students = StudentProfile::with('school', 'campus')
            ->orderBy('first_name')
            ->get();

        return view('guardians.edit', compact(
            'guardian',
            'students'
        ));
    }

    /**
     * Update Guardian
     */
    public function update(Request $request, Guardian $guardian)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'alternate_phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'occupation' => ['nullable', 'string', 'max:255'],

            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [
                'integer',
                'distinct',
                'exists:student_profiles,id',
            ],

            'is_primary' => ['nullable', 'boolean'],
            'is_emergency_contact' => ['nullable', 'boolean'],
            'can_pick_up_student' => ['nullable', 'boolean'],
            'receives_communications' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($guardian, $validated, $request) {
            $guardian->update([
                'full_name' => $validated['full_name'],
                'relationship' => $validated['relationship'],
                'phone' => $validated['phone'],
                'alternate_phone' => $validated['alternate_phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'occupation' => $validated['occupation'] ?? null,
            ]);

            $guardian->students()->sync(
                $this->studentPivotData(
                    $request->input('student_ids', []),
                    $request
                )
            );
        });

        return redirect()
            ->route('guardians.show', $guardian)
            ->with('success', 'Parent / Guardian updated successfully.');
    }

    /**
     * Delete Guardian
     */
    public function destroy(Guardian $guardian)
    {
        DB::transaction(function () use ($guardian) {
            $guardian->students()->detach();
            $guardian->delete();
        });

        return redirect()
            ->route('guardians.index')
            ->with('success', 'Parent / Guardian deleted successfully.');
    }

    /**
     * Prepare student relationship pivot data.
     */
    private function studentPivotData(array $studentIds, Request $request): array
    {
        $pivot = [];

        foreach ($studentIds as $studentId) {
            $pivot[$studentId] = [
                'is_primary' => $request->boolean('is_primary'),
                'is_emergency_contact' => $request->boolean('is_emergency_contact'),
                'can_pick_up_student' => $request->boolean('can_pick_up_student'),
                'receives_communications' => $request->boolean('receives_communications'),
            ];
        }

        return $pivot;
    }
}
