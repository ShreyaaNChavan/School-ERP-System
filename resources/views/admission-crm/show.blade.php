
@extends('layouts.schoolnexa')

@section('content')
@php
    $stageLabels = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'interested' => 'Interested',
        'follow_up' => 'Follow-up',
        'application_started' => 'Application Started',
        'documents_pending' => 'Documents Pending',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ];

    $stageColors = [
        'new' => '#3B82F6',
        'contacted' => '#6366F1',
        'interested' => '#8B5CF6',
        'follow_up' => '#D97706',
        'application_started' => '#0D9488',
        'documents_pending' => '#EA580C',
        'converted' => '#16A34A',
        'lost' => '#DC2626',
    ];

    $currentColor = $stageColors[$lead->status] ?? '#64748B';
@endphp

<style>
    .profile-page {font-family:Inter,sans-serif;color:#25313C;}
    .profile-title {font-size:22px;font-weight:700;color:#123B5D;}
    .profile-subtitle {font-size:13px;color:#64748B;margin-top:5px;}
    .profile-card {background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:20px;}
    .profile-section {font-size:16px;font-weight:700;color:#123B5D;margin-bottom:16px;}
    .profile-label {font-size:11px;font-weight:650;color:#64748B;text-transform:uppercase;letter-spacing:.3px;}
    .profile-value {font-size:13px;font-weight:550;color:#25313C;margin-top:6px;overflow-wrap:anywhere;}
    .profile-btn {display:inline-flex;align-items:center;justify-content:center;height:38px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;white-space:nowrap;}
    .profile-btn:hover {background:#F8FAFC;}
    .profile-primary {background:#123B5D;color:#fff;border-color:#123B5D;}
    .profile-primary:hover {background:#194D73;color:#fff;}
    .profile-input {width:100%;height:40px;border:1px solid #CBD5E1;border-radius:9px;padding:0 10px;font-size:13px;background:#fff;color:#25313C;}
    .profile-textarea {width:100%;min-height:85px;border:1px solid #CBD5E1;border-radius:9px;padding:10px;font-size:13px;}
    .profile-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px;}
    .profile-field {min-width:0;}
    .profile-status {display:inline-flex;padding:6px 11px;border-radius:20px;font-size:11px;font-weight:650;}
    .profile-table {width:100%;border-collapse:collapse;min-width:550px;}
    .profile-table th {text-align:left;padding:12px;background:#F8FAFC;color:#64748B;font-size:11px;text-transform:uppercase;}
    .profile-table td {padding:13px 12px;border-top:1px solid #F1F5F9;font-size:12px;}
    .profile-error {color:#DC2626;font-size:11px;margin-top:5px;}
    @media(max-width:700px) {.profile-grid {grid-template-columns:1fr;gap:16px;}}
</style>

<div class="profile-page">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;">
        <div>
            <div class="profile-title">Admission Lead Profile</div>
            <div class="profile-subtitle">
                {{ $lead->lead_number }} · Created {{ $lead->created_at?->format('d M Y, h:i A') }}
            </div>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <a href="{{ route('admission-crm.index') }}" class="profile-btn">← Back</a>

            @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
                <a href="{{ route('admission-crm.edit', $lead) }}" class="profile-btn">Edit Lead</a>
            @endif
        </div>
    </div>

    {{-- Lead Summary --}}
    <div class="profile-card" style="margin-bottom:18px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;flex-wrap:wrap;">
            <div>
                <div style="font-size:20px;font-weight:700;color:#123B5D;">{{ $lead->student_name }}</div>
                <div class="profile-subtitle">
                    {{ $lead->prospective_grade ?: 'Grade not specified' }}
                    · {{ $lead->source ?: 'Source not specified' }}
                </div>
                <div style="margin-top:12px;">
                    <span class="profile-status" style="background:{{ $currentColor }}18;color:{{ $currentColor }};">
                        {{ $stageLabels[$lead->status] ?? ucfirst(str_replace('_',' ',$lead->status)) }}
                    </span>
                </div>
            </div>

            <div style="text-align:right;">
                <div class="profile-label">Lead Reference</div>
                <div style="font-size:17px;font-weight:700;color:#123B5D;margin-top:5px;">
                    {{ $lead->lead_number }}
                </div>
            </div>
        </div>

        @if(auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
            <div style="border-top:1px solid #E2E8F0;margin-top:20px;padding-top:18px;">
                <form method="POST" action="{{ route('admission-crm.stage', $lead) }}"
                      style="display:flex;align-items:end;gap:10px;flex-wrap:wrap;">
                    @csrf
                    @method('PATCH')

                    <div style="width:240px;max-width:100%;">
                        <label class="profile-label" style="display:block;margin-bottom:7px;">Update Pipeline Stage</label>
                        <select name="status" class="profile-input">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($lead->status === $status)>
                                    {{ $stageLabels[$status] ?? ucfirst(str_replace('_',' ',$status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="profile-btn profile-primary">Update Stage</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Student and Parent Information --}}
    <div class="profile-grid" style="margin-bottom:18px;">
        <div class="profile-card">
            <div class="profile-section">Student Information</div>
            <div class="profile-field" style="margin-bottom:18px;">
                <div class="profile-label">Student Name</div>
                <div class="profile-value">{{ $lead->student_name }}</div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Interested Class / Grade</div>
                <div class="profile-value">{{ $lead->prospective_grade ?: '—' }}</div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-section">Parent / Guardian Information</div>
            <div class="profile-field" style="margin-bottom:18px;">
                <div class="profile-label">Parent Name</div>
                <div class="profile-value">{{ $lead->parent_name ?: '—' }}</div>
            </div>
            <div class="profile-field" style="margin-bottom:18px;">
                <div class="profile-label">Mobile Number</div>
                <div class="profile-value">{{ $lead->phone ?: '—' }}</div>
            </div>
            <div class="profile-field">
                <div class="profile-label">Email Address</div>
                <div class="profile-value">{{ $lead->email ?: '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Assignment and Enquiry --}}
    <div class="profile-grid" style="margin-bottom:18px;">
        <div class="profile-card">
            <div class="profile-section">Assignment Details</div>
            <div style="margin-bottom:17px;">
                <div class="profile-label">Assigned To</div>
                <div class="profile-value">{{ $lead->assignedUser?->name ?: 'Unassigned' }}</div>
            </div>
            <div>
                <div class="profile-label">Enquiry Source</div>
                <div class="profile-value">{{ $lead->source ?: '—' }}</div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-section">Initial Enquiry Notes</div>
            <div style="font-size:13px;line-height:1.7;color:#475569;">
                {{ $lead->notes ?: 'No initial enquiry notes recorded.' }}
            </div>
        </div>
    </div>

    {{-- Follow-up Management --}}
    <div class="profile-card" style="margin-bottom:18px;">
        <div class="profile-section">Follow-up Management</div>

        @if(auth()->user()->hasPermission('ADMISSIONS.CREATE'))
            <form method="POST" action="{{ route('admission-crm.followups.store', $lead) }}"
                  style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:11px;padding:16px;margin-bottom:20px;">
                @csrf

                <div class="profile-grid">
                    <div>
                        <label class="profile-label" style="display:block;margin-bottom:7px;">Follow-up Type *</label>
                        <select name="followup_type" class="profile-input" required>
                            <option value="call">Phone Call</option>
                            <option value="email">Email</option>
                            <option value="meeting">Meeting</option>
                            <option value="message">Message</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="profile-label" style="display:block;margin-bottom:7px;">Schedule Date & Time</label>
                        <input type="datetime-local" name="scheduled_at" class="profile-input"
                               value="{{ old('scheduled_at') }}">
                        @error('scheduled_at') <div class="profile-error">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="profile-label" style="display:block;margin-bottom:7px;">Assign Follow-up To</label>
                        <select name="assigned_to" class="profile-input">
                            <option value="">Use no specific assignee</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="margin-top:15px;">
                    <label class="profile-label" style="display:block;margin-bottom:7px;">Notes *</label>
                    <textarea name="notes" class="profile-textarea" required
                              placeholder="Record discussion points or the next action...">{{ old('notes') }}</textarea>
                    @error('notes') <div class="profile-error">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="profile-btn profile-primary" style="margin-top:12px;">
                    + Add Follow-up
                </button>
            </form>
        @endif

        <div style="overflow-x:auto;">
            <table class="profile-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Notes</th>
                        <th>Scheduled</th>
                        <th>Assigned To</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lead->followups->sortByDesc('scheduled_at') as $followup)
                        <tr>
                            <td style="font-weight:650;text-transform:capitalize;">
                                {{ $followup->followup_type }}
                            </td>
                            <td style="max-width:260px;">{{ $followup->notes }}</td>
                            <td>{{ $followup->scheduled_at?->format('d M Y, h:i A') ?: 'Not scheduled' }}</td>
                            <td>{{ $followup->assignedUser?->name ?: '—' }}</td>
                            <td>
                                <span class="profile-status"
                                      style="background:{{ $followup->status === 'completed' ? '#DCFCE7' : '#FEF3C7' }};color:{{ $followup->status === 'completed' ? '#166534' : '#92400E' }};">
                                    {{ ucfirst($followup->status) }}
                                </span>
                            </td>
                            <td>
                                @if($followup->status !== 'completed' && auth()->user()->hasPermission('ADMISSIONS.UPDATE'))
                                    <form method="POST" action="{{ route('admission-crm.followups.complete', $followup) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="profile-btn" style="height:31px;">
                                            Mark Done
                                        </button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;color:#94A3B8;padding:25px;">
                                No follow-ups recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Linked Applications --}}
    <div class="profile-card" style="margin-bottom:18px;">
        <div class="profile-section">Admission Applications</div>

        @forelse($lead->applications as $application)
            <div style="padding:13px 0;border-top:1px solid #F1F5F9;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;">
                <div>
                    <div style="font-size:13px;font-weight:650;color:#123B5D;">
                        {{ $application->application_number }}
                    </div>
                    <div class="profile-subtitle">
                        {{ $application->applicant_name }} · {{ $application->applying_grade }}
                    </div>
                </div>
                <span class="profile-status" style="background:#EFF6FF;color:#1D4ED8;">
                    {{ ucfirst($application->status) }}
                </span>
            </div>
        @empty
            <div class="profile-subtitle">No admission application linked to this lead yet.</div>
        @endforelse
    </div>

    {{-- Delete --}}
    @if(auth()->user()->hasPermission('ADMISSIONS.DELETE'))
        <div style="display:flex;justify-content:flex-end;">
            <form method="POST" action="{{ route('admission-crm.destroy', $lead) }}"
                  onsubmit="return confirm('Are you sure you want to delete this admission lead?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="profile-btn"
                        style="color:#DC2626;border-color:#FECACA;">
                    Delete Lead
                </button>
            </form>
        </div>
    @endif

</div>
@endsection
