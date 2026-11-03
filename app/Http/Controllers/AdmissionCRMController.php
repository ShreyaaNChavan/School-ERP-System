<?php

namespace App\Http\Controllers;

use App\Models\AdmissionLead;
use App\Models\AdmissionFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdmissionCrmController extends Controller
{
    private array $statuses = [
        'new',
        'contacted',
        'interested',
        'follow_up',
        'application_started',
        'documents_pending',
        'converted',
        'lost',
    ];

    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function campusId(): ?int
    {
        $campusId = session('campus_id');

        if ($campusId) {
            return (int) $campusId;
        }

        return DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->value('id');
    }

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();

        $query = AdmissionLead::with(['assignedUser'])
            ->where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') &&
            in_array($request->status, $this->statuses, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $leads = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => AdmissionLead::where('school_id', $schoolId)->count(),
            'new' => AdmissionLead::where('school_id', $schoolId)
                ->where('status', 'new')->count(),
            'followups_due' => AdmissionFollowup::whereHas('lead', function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            })
                ->where('status', 'pending')
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '<=', now())
                ->count(),
            'converted' => AdmissionLead::where('school_id', $schoolId)
                ->where('status', 'converted')->count(),
        ];

        $users = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admission-crm.index', compact(
            'leads',
            'stats',
            'users'
        ));
    }

    public function create()
    {
        $users = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admission-crm.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'prospective_grade' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['school_id'] = $this->schoolId();
        $validated['campus_id'] = $this->campusId();
        $validated['status'] = 'new';

        $lead = AdmissionLead::create($validated);

        return redirect()
            ->route('admission-crm.show', $lead)
            ->with('success', 'Admission lead created successfully.');
    }

    public function show(AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $admissionLead->load([
            'assignedUser',
            'followups.assignedUser',
            'applications',
        ]);

        $users = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admission-crm.show', [
            'lead' => $admissionLead,
            'users' => $users,
            'statuses' => $this->statuses,
        ]);
    }

    public function edit(AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $users = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admission-crm.edit', [
            'lead' => $admissionLead,
            'users' => $users,
        ]);
    }

    public function update(Request $request, AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'prospective_grade' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:100'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $admissionLead->update($validated);

        return redirect()
            ->route('admission-crm.show', $admissionLead)
            ->with('success', 'Lead details updated successfully.');
    }

    public function updateStage(Request $request, AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $validated = $request->validate([
            'status' => ['required', Rule::in($this->statuses)],
        ]);

        $admissionLead->update([
            'status' => $validated['status'],
        ]);

        return back()->with('success', 'Lead stage updated successfully.');
    }

    public function storeFollowup(Request $request, AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $validated = $request->validate([
            'followup_type' => [
                'required',
                Rule::in(['call', 'email', 'meeting', 'message', 'other']),
            ],
            'notes' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $validated['admission_lead_id'] = $admissionLead->id;
        $validated['status'] = 'pending';

        AdmissionFollowup::create($validated);

        if ($admissionLead->status === 'new') {
            $admissionLead->update(['status' => 'contacted']);
        }

        return back()->with('success', 'Follow-up added successfully.');
    }

    public function completeFollowup(AdmissionFollowup $followup)
    {
        $followup->load('lead');

        abort_unless($followup->lead, 404);
        $this->ensureSchoolAccess($followup->lead);

        $followup->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Follow-up marked as completed.');
    }

    public function destroy(AdmissionLead $admissionLead)
    {
        $this->ensureSchoolAccess($admissionLead);

        $hasApplications = DB::table('admission_applications')
            ->where('admission_lead_id', $admissionLead->id)
            ->exists();

        if ($hasApplications) {
            return back()->with(
                'error',
                'This lead has an admission application and cannot be deleted.'
            );
        }

        DB::transaction(function () use ($admissionLead) {
            $admissionLead->followups()->delete();
            $admissionLead->delete();
        });

        return redirect()
            ->route('admission-crm.index')
            ->with('success', 'Admission lead deleted successfully.');
    }

    private function ensureSchoolAccess(AdmissionLead $lead): void
    {
        abort_unless(
            (int) $lead->school_id === $this->schoolId(),
            404
        );
    }
}
