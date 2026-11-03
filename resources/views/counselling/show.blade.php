
@extends('layouts.schoolnexa')

@section('content')

@php
    $student = $counselling->student;
    $application = $counselling->admissionApplication;
    $personName = $student?->full_name ?? $application?->applicant_name ?? 'Unknown';
    $guardian = $student?->guardians?->firstWhere('pivot.is_primary', 1)
        ?? $student?->guardians?->first();
@endphp

<style>
    .cs-page{font-family:'Inter',sans-serif;color:#25313C;max-width:1100px}
    .cs-header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap}
    .cs-header h1{font-size:22px;font-weight:700;color:#123B5D;margin:0 0 5px}
    .cs-header p{font-size:13px;color:#64748B;margin:0}
    .cs-btn{display:inline-flex;align-items:center;justify-content:center;min-height:39px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;background:white;color:#123B5D;font-size:12px;font-weight:600;text-decoration:none;cursor:pointer}
    .cs-btn:hover{background:#F8FAFC;color:#123B5D}
    .cs-primary{background:#123B5D;color:white;border-color:#123B5D}
    .cs-primary:hover{background:#176B87;color:white}
    .cs-danger{color:#DC2626}
    .cs-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(260px,1fr);gap:16px}
    .cs-card{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:19px;margin-bottom:16px;min-width:0}
    .cs-title{font-size:15px;font-weight:700;color:#123B5D;margin:0 0 17px}
    .cs-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:19px 15px}
    .cs-label{font-size:11px;color:#94A3B8;font-weight:600;margin-bottom:5px}
    .cs-value{font-size:13px;color:#25313C;font-weight:600;overflow-wrap:anywhere}
    .cs-muted{font-size:11px;color:#64748B;margin-top:4px}
    .cs-badge{display:inline-flex;padding:5px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:capitalize}
    .cs-open{background:#FEF3C7;color:#92400E}
    .cs-completed{background:#DCFCE7;color:#166534}
    .cs-cancelled{background:#FEE2E2;color:#991B1B}
    .cs-note{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:15px;font-size:13px;line-height:1.7;white-space:pre-wrap;color:#475569;overflow-wrap:anywhere}
    .cs-actions{display:flex;gap:8px;flex-wrap:wrap}
    .cs-actions form{display:inline-flex;margin:0}
    .cs-divider{height:1px;background:#E2E8F0;margin:17px 0}
    .cs-info{font-size:12px;color:#64748B;line-height:1.7}
    @media(max-width:750px){.cs-grid{grid-template-columns:1fr}}
    @media(max-width:500px){.cs-details{grid-template-columns:1fr}.cs-card{padding:15px}}
</style>

<div class="cs-page">

    <div class="cs-header">
        <div>
            <h1>Counselling Session Details</h1>
            <p>{{ $counselling->session_number }} · {{ $personName }}</p>
        </div>

        <div class="cs-actions">
            <a href="{{ route('counselling.index') }}" class="cs-btn">← Back</a>
            <a href="{{ route('counselling.edit', $counselling) }}" class="cs-btn cs-primary">Edit Session</a>
        </div>
    </div>

    <div class="cs-grid">

        <div>

            {{-- SESSION INFORMATION --}}
            <div class="cs-card">
                <h3 class="cs-title">Session Information</h3>

                <div class="cs-details">
                    <div>
                        <div class="cs-label">Session ID</div>
                        <div class="cs-value">{{ $counselling->session_number }}</div>
                    </div>

                    <div>
                        <div class="cs-label">Status</div>
                        <span class="cs-badge cs-{{ $counselling->status }}">
                            {{ $counselling->status }}
                        </span>
                    </div>

                    <div>
                        <div class="cs-label">Session Type</div>
                        <div class="cs-value">
                            {{ ucwords(str_replace('_', ' ', $counselling->session_type)) }}
                        </div>
                    </div>

                    <div>
                        <div class="cs-label">Session Date</div>
                        <div class="cs-value">{{ $counselling->session_at?->format('d M Y') }}</div>
                        <div class="cs-muted">{{ $counselling->session_at?->format('h:i A') }}</div>
                    </div>

                    <div>
                        <div class="cs-label">Assigned Counsellor</div>
                        <div class="cs-value">{{ $counselling->counsellor?->name ?? 'Not assigned' }}</div>
                    </div>

                    <div>
                        <div class="cs-label">Next Follow-up</div>
                        <div class="cs-value">
                            {{ $counselling->next_session_at?->format('d M Y') ?? 'Not scheduled' }}
                        </div>
                        @if($counselling->next_session_at)
                            <div class="cs-muted">{{ $counselling->next_session_at->format('h:i A') }}</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- SESSION SUMMARY --}}
            <div class="cs-card">
                <h3 class="cs-title">Counselling Summary</h3>
                <div class="cs-note">{{ $counselling->summary }}</div>

                <div class="cs-divider"></div>

                <h3 class="cs-title">Action Plan</h3>

                @if($counselling->action_plan)
                    <div class="cs-note">{{ $counselling->action_plan }}</div>
                @else
                    <div class="cs-info">No action plan has been recorded.</div>
                @endif
            </div>

            {{-- SESSION STATUS --}}
            <div class="cs-card">
                <h3 class="cs-title">Update Session Status</h3>

                <form method="POST" action="{{ route('counselling.status', $counselling) }}">
                    @csrf
                    @method('PATCH')

                    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                        <select name="status" class="cs-btn" style="min-width:160px;" required>
                            <option value="open" @selected($counselling->status === 'open')>Open</option>
                            <option value="completed" @selected($counselling->status === 'completed')>Completed</option>
                            <option value="cancelled" @selected($counselling->status === 'cancelled')>Cancelled</option>
                        </select>

                        <button type="submit" class="cs-btn cs-primary">Update Status</button>
                    </div>
                </form>
            </div>

        </div>

        <div>

            {{-- STUDENT / APPLICANT --}}
            <div class="cs-card">
                <h3 class="cs-title">Student / Applicant</h3>

                <div class="cs-label">Name</div>
                <div class="cs-value">{{ $personName }}</div>

                <div class="cs-divider"></div>

                @if($student)
                    <div class="cs-label">Admission Number</div>
                    <div class="cs-value">{{ $student->admission_number ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Date of Birth</div>
                    <div class="cs-value">{{ $student->date_of_birth?->format('d M Y') ?? '—' }}</div>
                @elseif($application)
                    <div class="cs-label">Application Number</div>
                    <div class="cs-value">{{ $application->application_number }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Applying Grade</div>
                    <div class="cs-value">{{ $application->applying_grade }}</div>
                @endif
            </div>

            {{-- PARENT INFORMATION --}}
            <div class="cs-card">
                <h3 class="cs-title">Parent / Guardian</h3>

                @if($student && $guardian)
                    <div class="cs-label">Name</div>
                    <div class="cs-value">{{ $guardian->full_name ?? $guardian->name ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Relationship</div>
                    <div class="cs-value">{{ $guardian->relationship ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Phone</div>
                    <div class="cs-value">{{ $guardian->phone ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Email</div>
                    <div class="cs-value">{{ $guardian->email ?? '—' }}</div>

                @elseif($application)
                    <div class="cs-label">Name</div>
                    <div class="cs-value">{{ $application->parent_name ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Phone</div>
                    <div class="cs-value">{{ $application->parent_phone ?? '—' }}</div>

                    <div class="cs-divider"></div>

                    <div class="cs-label">Email</div>
                    <div class="cs-value">{{ $application->parent_email ?? '—' }}</div>
                @else
                    <div class="cs-info">No parent or guardian information is available.</div>
                @endif
            </div>

            {{-- RECORD INFORMATION --}}
            <div class="cs-card">
                <h3 class="cs-title">Record Information</h3>

                <div class="cs-label">Created On</div>
                <div class="cs-value">{{ $counselling->created_at?->format('d M Y, h:i A') }}</div>

                <div class="cs-divider"></div>

                <div class="cs-label">Last Updated</div>
                <div class="cs-value">{{ $counselling->updated_at?->format('d M Y, h:i A') }}</div>
            </div>

        </div>
    </div>

</div>

@endsection
