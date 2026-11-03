<?php

namespace App\Http\Controllers;

use App\Models\AdmissionApplication;
use App\Models\AdmissionLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdmissionApplicationController extends Controller
{
    private function schoolId(): int
    {
        return (int) session('school_id', 1);
    }

    private function campusId(): ?int
    {
        if (session('campus_id')) {
            return (int) session('campus_id');
        }

        return DB::table('campuses')
            ->where('school_id', $this->schoolId())
            ->value('id');
    }

    private function ensureSchoolAccess(AdmissionApplication $application): void
    {
        abort_unless(
            (int) $application->school_id === $this->schoolId(),
            404
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Application Dashboard
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $schoolId = $this->schoolId();

        $query = AdmissionApplication::with('lead')
            ->where('school_id', $schoolId);

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('application_number', 'like', "%{$search}%")
                    ->orWhere('applicant_name', 'like', "%{$search}%")
                    ->orWhere('parent_name', 'like', "%{$search}%")
                    ->orWhere('parent_phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('grade')) {
            $query->where('applying_grade', $request->grade);
        }

        $applications = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => AdmissionApplication::where('school_id', $schoolId)->count(),

            'submitted' => AdmissionApplication::where('school_id', $schoolId)
                ->where('status', 'submitted')->count(),

            'under_review' => AdmissionApplication::where('school_id', $schoolId)
                ->where('status', 'under_review')->count(),

            'approved' => AdmissionApplication::where('school_id', $schoolId)
                ->where('status', 'approved')->count(),

            'rejected' => AdmissionApplication::where('school_id', $schoolId)
                ->where('status', 'rejected')->count(),
        ];

        return view('admission-applications.index', compact(
            'applications',
            'stats'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Create Application
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $schoolId = $this->schoolId();

        $leads = AdmissionLead::where('school_id', $schoolId)
            ->whereNotIn('status', ['converted', 'lost'])
            ->latest()
            ->get();

        $academicYears = DB::table('academic_years')
            ->where('school_id', $schoolId)
            ->orderByDesc('id')
            ->get();

        $selectedLead = null;

        if ($request->filled('lead')) {
            $selectedLead = AdmissionLead::where('school_id', $schoolId)
                ->findOrFail($request->lead);
        }

        return view('admission-applications.create', compact(
            'leads',
            'academicYears',
            'selectedLead'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Store Application
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'admission_lead_id' => [
                'nullable',
                Rule::exists('admission_leads', 'id')
                    ->where('school_id', $this->schoolId()),
            ],

            'academic_year_id' => [
                'required',
                Rule::exists('academic_years', 'id')
                    ->where('school_id', $this->schoolId()),
            ],

            'applicant_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'applying_grade' => ['required', 'string', 'max:255'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:30'],
            'parent_email' => ['nullable', 'email', 'max:255'],
        ]);

        $lead = null;

        if (!empty($validated['admission_lead_id'])) {
            $lead = AdmissionLead::where('school_id', $this->schoolId())
                ->findOrFail($validated['admission_lead_id']);
        }

        $application = DB::transaction(function () use ($validated, $lead) {
            $application = AdmissionApplication::create([
                ...$validated,

                'school_id' => $this->schoolId(),
                'campus_id' => $this->campusId(),

                'application_number' => 'APP-'
                    . now()->format('Ymd')
                    . '-'
                    . Str::upper(Str::random(5)),

                'status' => 'submitted',
            ]);

            if ($lead) {
                $lead->update([
                    'status' => 'application_started',
                ]);
            }

            return $application;
        });

        return redirect()
            ->route('admission-applications.show', $application)
            ->with('success', 'Admission application submitted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Application Profile
    |--------------------------------------------------------------------------
    */

    public function show(AdmissionApplication $admissionApplication)
    {
        $this->ensureSchoolAccess($admissionApplication);

        $admissionApplication->load([
            'lead',
            'assessments.assessor',
            'decisions.decisionMaker',
        ]);

        return view('admission-applications.show', [
            'application' => $admissionApplication,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Application Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        AdmissionApplication $admissionApplication
    ) {
        $this->ensureSchoolAccess($admissionApplication);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'submitted',
                    'under_review',
                    'approved',
                    'rejected',
                    'waitlisted',
                    'documents_pending',
                ]),
            ],

            'review_notes' => ['nullable', 'string'],
        ]);

        $admissionApplication->update([
            'status' => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        if (
            $validated['status'] === 'approved'
            && $admissionApplication->lead
        ) {
            $admissionApplication->lead->update([
                'status' => 'converted',
            ]);
        }

        return back()->with(
            'success',
            'Application status updated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Upload Supporting Document
    |--------------------------------------------------------------------------
    */

    public function uploadDocument(
        Request $request,
        AdmissionApplication $admissionApplication
    ) {
        $this->ensureSchoolAccess($admissionApplication);

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:80'],

            'document_file' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
        ]);

        $file = $request->file('document_file');

        $directory = 'admission-documents/'
            . $admissionApplication->school_id
            . '/'
            . $admissionApplication->id;

        $storedPath = $file->store($directory, 'local');

        $documents = $admissionApplication->documents ?? [];

        $documents[] = [
            'type' => $validated['document_type'],
            'name' => $file->getClientOriginalName(),
            'path' => $storedPath,
            'uploaded_at' => now()->toDateTimeString(),
        ];

        $admissionApplication->update([
            'documents' => $documents,
        ]);

        return back()->with(
            'success',
            'Document uploaded successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Secure Document Download
    |--------------------------------------------------------------------------
    */

    public function downloadDocument(
        AdmissionApplication $admissionApplication,
        int $documentIndex
    ) {
        $this->ensureSchoolAccess($admissionApplication);

        $documents = $admissionApplication->documents ?? [];

        abort_unless(isset($documents[$documentIndex]), 404);

        $document = $documents[$documentIndex];

        $expectedDirectory = 'admission-documents/'
            . $admissionApplication->school_id
            . '/'
            . $admissionApplication->id
            . '/';

        $path = $document['path'] ?? '';

        abort_unless(
            is_string($path)
            && str_starts_with($path, $expectedDirectory),
            404
        );

        abort_unless(
            Storage::disk('local')->exists($path),
            404
        );

        return Storage::disk('local')->download(
            $path,
            $document['name'] ?? 'admission-document'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record Admission Assessment
    |--------------------------------------------------------------------------
    */

    public function storeAssessment(
        Request $request,
        AdmissionApplication $admissionApplication
    ) {
        $this->ensureSchoolAccess($admissionApplication);

        $validated = $request->validate([
            'assessment_type' => [
                'required',
                'string',
                'max:255',
            ],

            'assessment_date' => [
                'nullable',
                'date',
            ],

            'score' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'max_score' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'result' => [
                'nullable',
                'string',
                'max:255',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);

        if (
            isset($validated['score'], $validated['max_score'])
            && $validated['score'] > $validated['max_score']
        ) {
            return back()
                ->withErrors([
                    'score' => 'Score cannot exceed maximum marks.',
                ])
                ->withInput();
        }

        $admissionApplication->assessments()->create([
            'assessment_type' => $validated['assessment_type'],

            'assessment_date' => $validated['assessment_date'] ?? null,

            'score' => $validated['score'] ?? null,

            // Database column is maximum_score, not max_score.
            'maximum_score' => $validated['max_score'] ?? null,

            'result' => $validated['result'] ?? null,

            'remarks' => $validated['remarks'] ?? null,

            'assessed_by' => auth()->id(),
        ]);

        return back()->with(
            'success',
            'Assessment recorded successfully.'
        );
    }

    
    /*
    |--------------------------------------------------------------------------
    | Record Admission Decision
    |--------------------------------------------------------------------------
    */

    public function storeDecision(
        Request $request,
        AdmissionApplication $admissionApplication
    ) {
        $this->ensureSchoolAccess($admissionApplication);

        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in([
                    'approved',
                    'rejected',
                    'waitlisted',
                ]),
            ],

            'remarks' => [
                'nullable',
                'string',
            ],

            'offer_expires_at' => [
                'nullable',
                'date',
            ],
        ]);

        DB::transaction(function () use (
            $validated,
            $admissionApplication
        ) {
            // Save admission decision history
            $admissionApplication->decisions()->create([
                'decision' => $validated['decision'],
                'remarks' => $validated['remarks'] ?? null,
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'offer_expires_at' => $validated['offer_expires_at'] ?? null,
            ]);

            // Synchronize application status
            $admissionApplication->update([
                'status' => $validated['decision'],
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Update CRM lead when admission is approved
            if (
                $validated['decision'] === 'approved'
                && $admissionApplication->lead
            ) {
                $admissionApplication->lead->update([
                    'status' => 'converted',
                ]);
            }
        });

        return back()->with(
            'success',
            'Admission decision recorded successfully.'
        );
    }

}
