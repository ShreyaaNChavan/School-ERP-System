<?php

namespace App\Http\Controllers;

use App\Models\StaffProfile;
use App\Models\School;
use App\Models\Campus;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Staff / Teacher Directory
     */
    public function index(Request $request)
    {
        $query = StaffProfile::with([
            'school',
            'campus',
            'user',
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_number', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filters
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('campus_id')) {
            $query->where('campus_id', $request->campus_id);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staff = $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total' => StaffProfile::count(),
            'active' => StaffProfile::where('status', 'active')->count(),
            'inactive' => StaffProfile::where('status', 'inactive')->count(),
        ];

        $schools = School::orderBy('name')->get();

        $campuses = Campus::orderBy('name')->get();

        $departments = Department::where('is_active', true)
            ->orderBy('name')
            ->get();

        $designations = Designation::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('staff.index', compact(
            'staff',
            'stats',
            'schools',
            'campuses',
            'departments',
            'designations'
        ));
    }

    /**
     * Create Staff Form
     */
    public function create()
    {
        $formData = $this->getFormData();

        return view('staff.create', $formData);
    }

    /**
     * Store Staff Profile
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_id' => [
                'required',
                'exists:schools,id',
            ],

            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $request->school_id),
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'employee_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('staff_profiles', 'employee_number')
                    ->where('school_id', $request->school_id),
            ],

            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],

            'employment_type' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        DB::transaction(function () use ($validated) {
            StaffProfile::create($validated);
        });

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff profile created successfully.');
    }

    /**
     * Show Staff Profile
     */
    public function show(StaffProfile $staff)
    {
        $staff->load([
            'school',
            'campus',
            'user',
            'teachingAssignments.classSubject.subject',
            'teachingAssignments.classSubject.schoolClass',
            'teachingAssignments.section',
            'teachingAssignments.academicYear',
        ]);

        return view('staff.show', compact('staff'));
    }

    /**
     * Edit Staff Profile
     */
    public function edit(StaffProfile $staff)
    {
        $formData = $this->getFormData();

        return view('staff.edit', array_merge(
            $formData,
            compact('staff')
        ));
    }

    /**
     * Update Staff Profile
     */
    public function update(Request $request, StaffProfile $staff)
    {
        $validated = $request->validate([
            'school_id' => [
                'required',
                'exists:schools,id',
            ],

            'campus_id' => [
                'nullable',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $request->school_id),
            ],

            'user_id' => [
                'nullable',
                'exists:users,id',
            ],

            'employee_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('staff_profiles', 'employee_number')
                    ->where('school_id', $request->school_id)
                    ->ignore($staff->id),
            ],

            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],

            'designation' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],

            'employment_type' => ['nullable', 'string', 'max:100'],
            'joining_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        DB::transaction(function () use ($staff, $validated) {
            $staff->update($validated);
        });

        return redirect()
            ->route('staff.show', $staff)
            ->with('success', 'Staff profile updated successfully.');
    }

    /**
     * Soft Delete Staff Profile
     */
    public function destroy(StaffProfile $staff)
    {
        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('success', 'Staff profile deleted successfully.');
    }

    /**
     * Load dropdown data for create/edit forms.
     */
    private function getFormData(): array
    {
        return [
            'schools' => School::orderBy('name')->get(),

            'campuses' => Campus::orderBy('name')->get(),

            'departments' => Department::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'designations' => Designation::where('is_active', true)
                ->orderBy('name')
                ->get(),

            'users' => User::orderBy('name')->get(),
        ];
    }
}
