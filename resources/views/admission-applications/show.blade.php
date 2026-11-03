@extends('layouts.schoolnexa')

@section('content')
    @php
        $statuses = [
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'documents_pending' => 'Documents Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'waitlisted' => 'Waitlisted',
        ];

        $colors = [
            'submitted' => ['#EFF6FF', '#2563EB'],
            'under_review' => ['#F5F3FF', '#7C3AED'],
            'documents_pending' => ['#FFF7ED', '#C2410C'],
            'approved' => ['#F0FDF4', '#16A34A'],
            'rejected' => ['#FEF2F2', '#DC2626'],
            'waitlisted' => ['#FFFBEB', '#B45309'],
        ];

        $currentStatus = $colors[$application->status] ?? ['#F1F5F9', '#475569'];
        $documents = $application->documents ?? [];
    @endphp

    <style>
        .application-profile {
            font-family: Inter, sans-serif;
            color: #25313C;
        }

        .ap-title {
            font-size: 22px;
            font-weight: 700;
            color: #123B5D;
        }

        .ap-subtitle {
            font-size: 13px;
            color: #64748B;
            margin-top: 5px;
        }

        .ap-card {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            padding: 20px;
            margin-bottom: 18px;
        }

        .ap-section {
            font-size: 16px;
            font-weight: 700;
            color: #123B5D;
            margin-bottom: 17px;
        }

        .ap-label {
            font-size: 11px;
            font-weight: 650;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: .3px;
        }

        .ap-value {
            font-size: 13px;
            font-weight: 550;
            color: #25313C;
            margin-top: 6px;
            overflow-wrap: anywhere;
        }

        .ap-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .ap-field {
            min-width: 0;
        }

        .ap-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 39px;
            padding: 0 13px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #fff;
            color: #123B5D;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .ap-primary {
            background: #123B5D;
            color: #fff;
            border-color: #123B5D;
        }

        .ap-primary:hover {
            background: #194D73;
            color: #fff;
        }

        .ap-input {
            width: 100%;
            height: 40px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            padding: 0 10px;
            font-size: 13px;
            background: #fff;
            color: #25313C;
        }

        .ap-file {
            width: 100%;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            padding: 9px;
            font-size: 12px;
            background: #fff;
            color: #25313C;
        }

        .ap-textarea {
            width: 100%;
            min-height: 90px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            padding: 10px;
            font-size: 13px;
        }

        .ap-badge {
            display: inline-flex;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 650;
        }

        .ap-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 550px;
        }

        .ap-table th {
            text-align: left;
            padding: 12px;
            background: #F8FAFC;
            color: #64748B;
            font-size: 11px;
            text-transform: uppercase;
        }

        .ap-table td {
            padding: 13px 12px;
            border-top: 1px solid #F1F5F9;
            font-size: 12px;
        }

        .ap-muted {
            font-size: 12px;
            color: #64748B;
        }

        .ap-document {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-top: 1px solid #F1F5F9;
        }

        .ap-document-name {
            font-size: 13px;
            font-weight: 650;
            color: #123B5D;
            overflow-wrap: anywhere;
        }

        .ap-error {
            font-size: 12px;
            color: #DC2626;
            margin-top: 5px;
        }

        @media(max-width: 650px) {
            .ap-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .ap-document {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    <div class="application-profile">

        {{-- Header --}}
        <div
            style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;">
            <div>
                <div class="ap-title">Application Profile</div>

                <div class="ap-subtitle">
                    {{ $application->application_number }}
                    · Submitted {{ $application->created_at?->format('d M Y') }}
                </div>
            </div>

            <a href="{{ route('admission-applications.index') }}" class="ap-btn">
                ← Back to Applications
            </a>
        </div>

        {{-- Summary --}}
        <div class="ap-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:20px;font-weight:700;color:#123B5D;">
                        {{ $application->applicant_name }}
                    </div>

                    <div class="ap-subtitle">
                        {{ $application->applying_grade }}
                        · {{ $application->application_number }}
                    </div>

                    <div style="margin-top:12px;">
                        <span class="ap-badge" style="background:{{ $currentStatus[0] }};color:{{ $currentStatus[1] }};">
                            {{ $statuses[$application->status] ?? ucfirst(str_replace('_', ' ', $application->status)) }}
                        </span>
                    </div>
                </div>

                <div style="text-align:right;">
                    <div class="ap-label">Application Reference</div>

                    <div style="font-size:17px;font-weight:700;color:#123B5D;margin-top:5px;">
                        {{ $application->application_number }}
                    </div>
                </div>
            </div>

            @if($application->lead)
                <div style="margin-top:18px;padding-top:15px;border-top:1px solid #E2E8F0;">
                    <span class="ap-label">Linked CRM Lead</span>

                    <div style="margin-top:6px;">
                        <a href="{{ route('admission-crm.show', $application->lead) }}"
                            style="font-size:13px;font-weight:650;color:#176B87;text-decoration:none;">
                            {{ $application->lead->lead_number }}
                            — {{ $application->lead->student_name }}
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Student and Academic Information --}}
        <div class="ap-grid">
            <div class="ap-card">
                <div class="ap-section">Student Information</div>

                <div style="margin-bottom:18px;">
                    <div class="ap-label">Applicant Name</div>
                    <div class="ap-value">{{ $application->applicant_name }}</div>
                </div>

                <div>
                    <div class="ap-label">Date of Birth</div>
                    <div class="ap-value">
                        {{ $application->date_of_birth?->format('d M Y') ?: 'Not provided' }}
                    </div>
                </div>
            </div>

            <div class="ap-card">
                <div class="ap-section">Academic Information</div>

                <div style="margin-bottom:18px;">
                    <div class="ap-label">Applying For</div>
                    <div class="ap-value">{{ $application->applying_grade }}</div>
                </div>

                <div>
                    <div class="ap-label">Academic Year ID</div>
                    <div class="ap-value">{{ $application->academic_year_id }}</div>
                </div>
            </div>
        </div>

        {{-- Parent Information --}}
        <div class="ap-card">
            <div class="ap-section">Parent / Guardian Information</div>

            <div class="ap-grid">
                <div class="ap-field">
                    <div class="ap-label">Parent Name</div>
                    <div class="ap-value">{{ $application->parent_name ?: '—' }}</div>
                </div>

                <div class="ap-field">
                    <div class="ap-label">Contact Number</div>
                    <div class="ap-value">{{ $application->parent_phone ?: '—' }}</div>
                </div>

                <div class="ap-field">
                    <div class="ap-label">Email Address</div>
                    <div class="ap-value">{{ $application->parent_email ?: '—' }}</div>
                </div>
            </div>
        </div>

        {{-- Supporting Documents --}}
        <div class="ap-card">
            <div class="ap-section">Supporting Documents</div>

            {{-- Upload Form --}}
            @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
                <form method="POST" action="{{ route('admission-applications.documents.upload', $application) }}"
                    enctype="multipart/form-data"
                    style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:11px;padding:16px;margin-bottom:20px;">

                    @csrf

                    <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:13px;">
                        Upload New Document
                    </div>

                    <div class="ap-grid">
                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Document Type
                            </label>

                            <select name="document_type" class="ap-input" required>
                                <option value="">Select document type</option>

                                <option value="Birth Certificate" @selected(old('document_type') === 'Birth Certificate')>
                                    Birth Certificate
                                </option>

                                <option value="Previous School Report" @selected(old('document_type') === 'Previous School Report')>
                                    Previous School Report
                                </option>

                                <option value="Transfer Certificate" @selected(old('document_type') === 'Transfer Certificate')>
                                    Transfer Certificate
                                </option>

                                <option value="Address Proof" @selected(old('document_type') === 'Address Proof')>
                                    Address Proof
                                </option>

                                <option value="Identity Proof" @selected(old('document_type') === 'Identity Proof')>
                                    Identity Proof
                                </option>

                                <option value="Photograph" @selected(old('document_type') === 'Photograph')>
                                    Photograph
                                </option>

                                <option value="Other" @selected(old('document_type') === 'Other')>
                                    Other
                                </option>
                            </select>

                            @error('document_type')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Select File
                            </label>

                            <input type="file" name="document_file" class="ap-file" accept=".pdf,.jpg,.jpeg,.png" required>

                            @error('document_file')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="ap-muted" style="margin-top:10px;">
                        Accepted formats: PDF, JPG, JPEG and PNG.
                        Maximum file size: 5 MB.
                    </div>

                    <button type="submit" class="ap-btn ap-primary" style="margin-top:14px;">
                        Upload Document
                    </button>
                </form>
            @endif

            {{-- Uploaded Documents --}}
            <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:5px;">
                Uploaded Documents ({{ count($documents) }})
            </div>

            @forelse($documents as $index => $document)
                <div class="ap-document">
                    <div style="min-width:0;">
                        <div class="ap-document-name">
                            {{ $document['name'] ?? $document['original_name'] ?? 'Uploaded Document' }}
                        </div>

                        <div class="ap-muted" style="margin-top:5px;">
                            {{ $document['type'] ?? 'Supporting Document' }}

                            @if(!empty($document['uploaded_at']))
                                · {{ \Carbon\Carbon::parse($document['uploaded_at'])->format('d M Y, h:i A') }}
                            @endif
                        </div>
                    </div>

                    <div>
                        @if(!empty($document['path']))
                            <a href="{{ route('admission-applications.documents.download', [$application, $index]) }}"
                                class="ap-btn">
                                Download
                            </a>
                        @else
                            <span class="ap-muted">
                                Download unavailable for this older record
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="ap-muted" style="padding:15px 0;">
                    No supporting documents uploaded yet.
                </div>
            @endforelse
        </div>

        {{-- Application Review and Status --}}
        @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
            <div class="ap-card">
                <div class="ap-section">Application Review & Decision</div>

                <form method="POST" action="{{ route('admission-applications.status', $application) }}">

                    @csrf
                    @method('PATCH')

                    <div class="ap-grid">
                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Application Status
                            </label>

                            <select name="status" class="ap-input" required>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" @selected($application->status === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Reviewed By
                            </label>

                            <div class="ap-input" style="display:flex;align-items:center;background:#F8FAFC;">
                                {{ $application->reviewed_by
                ? 'User ID ' . $application->reviewed_by
                : 'Not reviewed yet' }}
                            </div>
                        </div>
                    </div>

                    <div style="margin-top:17px;">
                        <label class="ap-label" style="display:block;margin-bottom:7px;">
                            Review Remarks
                        </label>

                        <textarea name="review_notes" class="ap-textarea"
                            placeholder="Enter verification remarks, admission decision reasons or pending requirements...">{{ old('review_notes', $application->review_notes) }}</textarea>
                    </div>

                    <button type="submit" class="ap-btn ap-primary" style="margin-top:14px;">
                        Save Review & Status
                    </button>
                </form>
            </div>
        @endif

        {{-- Assessment Management --}}
        <div class="ap-card">
            <div class="ap-section">Assessment Management</div>

            {{-- New Assessment Form --}}
            @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
                <form method="POST" action="{{ route('admission-applications.assessments.store', $application) }}"
                    style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:11px;padding:16px;margin-bottom:20px;">

                    @csrf

                    <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:14px;">
                        Record New Assessment
                    </div>

                    <div class="ap-grid">
                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Assessment Type
                            </label>

                            <select name="assessment_type" class="ap-input" required>
                                <option value="">Select assessment</option>
                                <option value="Entrance Examination">Entrance Examination</option>
                                <option value="Student Interview">Student Interview</option>
                                <option value="Academic Assessment">Academic Assessment</option>
                                <option value="Oral Assessment">Oral Assessment</option>
                                <option value="Other">Other</option>
                            </select>

                            @error('assessment_type')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Assessment Date
                            </label>

                            <input type="date" name="assessment_date" class="ap-input" value="{{ old('assessment_date') }}">

                            @error('assessment_date')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Score
                            </label>

                            <input type="number" name="score" class="ap-input" step="0.01" min="0" value="{{ old('score') }}"
                                placeholder="Enter score">

                            @error('score')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Maximum Score
                            </label>

                            <input type="number" name="max_score" class="ap-input" step="0.01" min="0.01"
                                value="{{ old('max_score') }}" placeholder="Enter maximum marks">

                            @error('max_score')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Result
                            </label>

                            <select name="result" class="ap-input">
                                <option value="">Select result</option>
                                <option value="Passed">Passed</option>
                                <option value="Failed">Failed</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Not Qualified">Not Qualified</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top:15px;">
                        <label class="ap-label" style="display:block;margin-bottom:7px;">
                            Remarks
                        </label>

                        <textarea name="remarks" class="ap-textarea"
                            placeholder="Enter assessment remarks...">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="ap-btn ap-primary" style="margin-top:14px;">
                        Save Assessment
                    </button>
                </form>
            @endif

            {{-- Assessment History --}}
            <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:10px;">
                Assessment History
            </div>

            <div style="overflow-x:auto;">
                <table class="ap-table">
                    <thead>
                        <tr>
                            <th>Assessment</th>
                            <th>Date</th>
                            <th>Score</th>
                            <th>Result</th>
                            <th>Assessed By</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($application->assessments as $assessment)
                            <tr>
                                <td>{{ $assessment->assessment_type }}</td>

                                <td>
                                    {{ $assessment->assessment_date?->format('d M Y') ?: '—' }}
                                </td>

                                <td>
                                    {{ $assessment->score ?? '—' }}

                                    @if($assessment->maximum_score !== null)
                                        / {{ $assessment->maximum_score }}
                                    @endif
                                </td>

                                <td>{{ $assessment->result ?: '—' }}</td>

                                <td>{{ $assessment->assessor?->name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align:center;color:#94A3B8;padding:22px;">
                                    No assessments recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Admission Decision History --}}

        {{-- Admission Decision Management --}}
        <div class="ap-card">
            <div class="ap-section">Admission Decision Management</div>

            {{-- Record Decision --}}
            @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
                <form method="POST" action="{{ route('admission-applications.decision.store', $application) }}"
                    style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:11px;padding:16px;margin-bottom:20px;">

                    @csrf

                    <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:14px;">
                        Record New Admission Decision
                    </div>

                    <div class="ap-grid">
                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Admission Decision
                            </label>

                            <select name="decision" class="ap-input" required>
                                <option value="">Select decision</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="waitlisted">Waitlisted</option>
                            </select>

                            @error('decision')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="ap-label" style="display:block;margin-bottom:7px;">
                                Offer Expiry Date
                            </label>

                            <input type="date" name="offer_expires_at" class="ap-input" value="{{ old('offer_expires_at') }}">

                            @error('offer_expires_at')
                                <div class="ap-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div style="margin-top:15px;">
                        <label class="ap-label" style="display:block;margin-bottom:7px;">
                            Decision Remarks
                        </label>

                        <textarea name="remarks" class="ap-textarea"
                            placeholder="Enter reason for admission decision...">{{ old('remarks') }}</textarea>

                        @error('remarks')
                            <div class="ap-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="ap-btn ap-primary" style="margin-top:14px;">
                        Save Admission Decision
                    </button>
                </form>
            @endif

            {{-- Decision History --}}
            <div style="font-size:13px;font-weight:650;color:#123B5D;margin-bottom:10px;">
                Decision History
            </div>

            @forelse($application->decisions as $decision)
                <div style="padding:15px 0;border-top:1px solid #F1F5F9;">

                    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                        <div style="font-size:13px;font-weight:650;color:#123B5D;">
                            {{ ucfirst(str_replace('_', ' ', $decision->decision)) }}
                        </div>

                        <span class="ap-muted">
                            {{ $decision->decided_at?->format('d M Y, h:i A') ?: 'Date not available' }}
                        </span>
                    </div>

                    @if($decision->remarks)
                        <div style="font-size:12px;color:#475569;margin-top:8px;">
                            {{ $decision->remarks }}
                        </div>
                    @endif

                    @if($decision->offer_expires_at)
                        <div class="ap-muted" style="margin-top:7px;">
                            Offer expires:
                            {{ $decision->offer_expires_at->format('d M Y') }}
                        </div>
                    @endif

                    <div class="ap-muted" style="margin-top:7px;">
                        Decision recorded by:
                        {{ $decision->decisionMaker?->name ?? 'Unknown' }}
                    </div>
                </div>
            @empty
                <div class="ap-muted" style="padding:12px 0;">
                    No admission decisions recorded yet.
                </div>
            @endforelse
        </div>


    </div>
@endsection