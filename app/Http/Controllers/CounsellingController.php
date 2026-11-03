<?php

namespace App\Http\Controllers;

use App\Models\CounsellingRecord;
use App\Models\StudentProfile;
use App\Models\AdmissionApplication;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CounsellingController extends Controller
{
    /**
     * Current school ID.
     */
    private function schoolId()
    {
        return session('school_id', 1);
    }

    /**
     * Base query for the current school.
     */
    private function baseQuery()
    {
        return CounsellingRecord::where('school_id', $this->schoolId());
    }

    /**
     * Get counsellors.
     *
     * First load active staff linked to user accounts.
     * If none exist, use the currently logged-in user.
     */
    private function counsellors()
    {
        $userIds = StaffProfile::where('school_id', $this->schoolId())
            ->where('status', 'active')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->unique();

        $counsellors = User::whereIn('id', $userIds)
            ->orderBy('name')
            ->get();

        if ($counsellors->isEmpty()) {
            $counsellors = User::where('id', auth()->id())->get();
        }

        return $counsellors;
    }

    /**
     * Common data for create and edit forms.
     */
    private function formData()
    {
        $students = StudentProfile::where('school_id', $this->schoolId())
            ->with('guardians')
            ->orderBy('first_name')
            ->get();

        $applications = AdmissionApplication::where('school_id', $this->schoolId())
            ->whereNotIn('status', ['rejected', 'converted'])
            ->orderByDesc('id')
            ->get();

        return [
            'students' => $students,
            'applications' => $applications,
            'counsellors' => $this->counsellors(),
        ];
    }

    /**
     * Counselling dashboard.
     */
    public function index(Request $request)
    {
        $query = $this->baseQuery()
            ->with([
                'student',
                'admissionApplication',
                'counsellor',
            ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('summary', 'like', "%{$search}%")
                    ->orWhere('session_type', 'like', "%{$search}%")

                    ->orWhereHas('student', function ($studentQuery) use ($search) {
                        $studentQuery->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('admission_number', 'like', "%{$search}%");
                    })

                    ->orWhereHas('admissionApplication', function ($applicationQuery) use ($search) {
                        $applicationQuery->where('applicant_name', 'like', "%{$search}%")
                            ->orWhere('application_number', 'like', "%{$search}%");
                    })

                    ->orWhereHas('counsellor', function ($counsellorQuery) use ($search) {
                        $counsellorQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Session type filter
        if ($request->filled('session_type')) {
            $query->where('session_type', $request->session_type);
        }

        $sessions = $query
            ->orderByDesc('session_at')
            ->paginate(10)
            ->withQueryString();

        // Dashboard statistics
        $stats = [
            'total' => $this->baseQuery()->count(),

            'scheduled' => $this->baseQuery()
                ->where('status', 'open')
                ->where('session_at', '>=', now())
                ->count(),

            'completed' => $this->baseQuery()
                ->where('status', 'completed')
                ->count(),

            'followups_due' => $this->baseQuery()
                ->where('status', 'open')
                ->whereNotNull('next_session_at')
                ->where('next_session_at', '<=', now())
                ->count(),
        ];

        // Upcoming sessions
        $upcomingSessions = $this->baseQuery()
            ->with(['student', 'admissionApplication', 'counsellor'])
            ->where('status', 'open')
            ->where('session_at', '>=', now())
            ->orderBy('session_at')
            ->limit(5)
            ->get();

        // Session type breakdown
        $sessionTypes = $this->baseQuery()
            ->selectRaw('session_type, COUNT(*) as total')
            ->groupBy('session_type')
            ->orderByDesc('total')
            ->get();

        // Monthly sessions for current year
        $monthlySessions = $this->baseQuery()
            ->selectRaw('MONTH(session_at) as month_number, COUNT(*) as total')
            ->whereYear('session_at', now()->year)
            ->groupByRaw('MONTH(session_at)')
            ->orderBy('month_number')
            ->get()
            ->keyBy('month_number');

        return view('counselling.index', compact(
            'sessions',
            'stats',
            'upcomingSessions',
            'sessionTypes',
            'monthlySessions'
        ));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        return view('counselling.create', $this->formData());
    }

    /**
     * Store a counselling session.
     */
    public function store(Request $request)
    {
        $validated = $this->validateSession($request);

        $validated['school_id'] = $this->schoolId();

        CounsellingRecord::create($validated);

        return redirect()
            ->route('counselling.index')
            ->with('success', 'Counselling session scheduled successfully.');
    }

    /**
     * Show a counselling session.
     */
    public function show(CounsellingRecord $counselling)
    {
        $this->ensureSchoolAccess($counselling);

        $counselling->load([
            'student.guardians',
            'student.enrollments',
            'admissionApplication',
            'counsellor',
        ]);

        return view('counselling.show', compact('counselling'));
    }

    /**
     * Show edit form.
     */
    public function edit(CounsellingRecord $counselling)
    {
        $this->ensureSchoolAccess($counselling);

        return view('counselling.edit', array_merge(
            $this->formData(),
            compact('counselling')
        ));
    }

    /**
     * Update counselling session.
     */
    public function update(Request $request, CounsellingRecord $counselling)
    {
        $this->ensureSchoolAccess($counselling);

        $validated = $this->validateSession($request);

        $counselling->update($validated);

        return redirect()
            ->route('counselling.show', $counselling)
            ->with('success', 'Counselling session updated successfully.');
    }

    /**
     * Update session status.
     */
    public function updateStatus(Request $request, CounsellingRecord $counselling)
    {
        $this->ensureSchoolAccess($counselling);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['open', 'completed', 'cancelled']),
            ],
        ]);

        $counselling->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Session status updated successfully.');
    }

    /**
     * Delete counselling session.
     */
    public function destroy(CounsellingRecord $counselling)
    {
        $this->ensureSchoolAccess($counselling);

        $counselling->delete();

        return redirect()
            ->route('counselling.index')
            ->with('success', 'Counselling session deleted successfully.');
    }

    /**
     * Validate counselling form.
     */
    private function validateSession(Request $request)
    {
        $validated = $request->validate([
            'student_id' => [
                'nullable',
                'required_without:admission_application_id',
                Rule::exists('student_profiles', 'id')
                    ->where('school_id', $this->schoolId()),
            ],

            'admission_application_id' => [
                'nullable',
                'required_without:student_id',
                Rule::exists('admission_applications', 'id')
                    ->where('school_id', $this->schoolId()),
            ],

            'counsellor_id' => [
                'required',
                'exists:users,id',
            ],

            'session_at' => [
                'required',
                'date',
            ],

            'session_type' => [
                'required',
                'string',
                'max:100',
            ],

            'summary' => [
                'required',
                'string',
            ],

            'action_plan' => [
                'nullable',
                'string',
            ],

            'next_session_at' => [
                'nullable',
                'date',
                'after:session_at',
            ],

            'status' => [
                'required',
                Rule::in(['open', 'completed', 'cancelled']),
            ],
        ]);

        // A session must be linked to either a student or an application,
        // not both.
        if (!empty($validated['student_id']) &&
            !empty($validated['admission_application_id'])) {
            abort(422, 'Select either a student or an admission application.');
        }

        return $validated;
    }

    /**
     * Ensure the session belongs to the current school.
     */
    private function ensureSchoolAccess(CounsellingRecord $counselling)
    {
        abort_unless(
            $counselling->school_id == $this->schoolId(),
            404
        );
    }
}
