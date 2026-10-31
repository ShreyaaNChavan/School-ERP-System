
@extends('layouts.schoolnexa')

@section('content')

<style>
    .stf-profile {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .stf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .stf-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .stf-subtitle {
        font-size: 13px;
        color: #64748B;
        margin: 0;
    }

    .stf-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .stf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 9px 15px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #CBD5E1;
        cursor: pointer;
    }

    .stf-btn-primary {
        background: #123B5D;
        color: #fff;
        border-color: #123B5D;
    }

    .stf-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .stf-btn-outline {
        background: #fff;
        color: #25313C;
    }

    .stf-btn-outline:hover {
        background: #F1F5F9;
    }

    .stf-card {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .stf-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 17px;
    }

    .stf-summary {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stf-avatar {
        width: 62px;
        height: 62px;
        border-radius: 50%;
        background: #EAF3F8;
        color: #176B87;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .stf-profile-name {
        font-size: 19px;
        font-weight: 700;
        color: #123B5D;
        margin-bottom: 5px;
    }

    .stf-meta {
        color: #64748B;
        font-size: 12px;
        margin-top: 4px;
    }

    .stf-status {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .stf-status-active {
        background: #DCFCE7;
        color: #166534;
    }

    .stf-status-inactive {
        background: #F1F5F9;
        color: #475569;
    }

    .stf-status-other {
        background: #FEF3C7;
        color: #92400E;
    }

    .stf-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .stf-detail-label {
        color: #64748B;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .stf-detail-value {
        font-size: 13px;
        font-weight: 600;
        color: #25313C;
        overflow-wrap: anywhere;
    }

    .stf-table-wrap {
        overflow-x: auto;
    }

    .stf-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }

    .stf-table th {
        text-align: left;
        padding: 12px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        border-bottom: 1px solid #E2E8F0;
    }

    .stf-table td {
        padding: 14px 12px;
        font-size: 12px;
        border-bottom: 1px solid #E2E8F0;
        vertical-align: middle;
    }

    .stf-table tr:last-child td {
        border-bottom: none;
    }

    .stf-muted {
        color: #64748B;
        font-size: 11px;
        margin-top: 4px;
    }

    .stf-empty {
        text-align: center;
        padding: 30px 12px;
        color: #64748B;
        font-size: 13px;
    }

    .stf-record-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    @media(max-width: 700px) {
        .stf-details-grid,
        .stf-record-grid {
            grid-template-columns: 1fr;
        }

        .stf-card {
            padding: 15px;
        }
    }
</style>

<div class="stf-profile">

    {{-- HEADER --}}
    <div class="stf-header">

        <div>
            <h2 class="stf-title">Staff Profile</h2>

            <p class="stf-subtitle">
                View employee information and teaching assignments.
            </p>
        </div>

        <div class="stf-actions">

            <a href="{{ route('staff.index') }}"
               class="stf-btn stf-btn-outline">
                Back to Directory
            </a>

            @if(auth()->user()->hasPermission('STAFF.UPDATE'))
                <a href="{{ route('staff.edit', $staff) }}"
                   class="stf-btn stf-btn-primary">
                    Edit Profile
                </a>
            @endif

        </div>

    </div>

    {{-- PROFILE SUMMARY --}}
    <div class="stf-card">

        <div class="stf-summary">

            <div class="stf-avatar">
                {{ strtoupper(substr($staff->first_name, 0, 1)) }}
            </div>

            <div>

                <div class="stf-profile-name">
                    {{ $staff->full_name }}
                </div>

                <div class="stf-meta">
                    {{ $staff->designation ?: 'Designation not assigned' }}
                    ·
                    {{ $staff->department ?: 'Department not assigned' }}
                </div>

                <div class="stf-meta">
                    Employee No: {{ $staff->employee_number }}
                </div>

                <div style="margin-top:8px;">
                    @php
                        $statusClass = match(strtolower($staff->status ?? '')) {
                            'active' => 'stf-status-active',
                            'inactive' => 'stf-status-inactive',
                            default => 'stf-status-other',
                        };
                    @endphp

                    <span class="stf-status {{ $statusClass }}">
                        {{ $staff->status ?: 'Unknown' }}
                    </span>
                </div>

            </div>

        </div>

    </div>

    {{-- PERSONAL INFORMATION --}}
    <div class="stf-card">

        <h3 class="stf-card-title">Personal Information</h3>

        <div class="stf-details-grid">

            <div>
                <div class="stf-detail-label">First Name</div>
                <div class="stf-detail-value">
                    {{ $staff->first_name }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Middle Name</div>
                <div class="stf-detail-value">
                    {{ $staff->middle_name ?: 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Last Name</div>
                <div class="stf-detail-value">
                    {{ $staff->last_name }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Employee Number</div>
                <div class="stf-detail-value">
                    {{ $staff->employee_number }}
                </div>
            </div>

        </div>

    </div>

    {{-- CONTACT INFORMATION --}}
    <div class="stf-card">

        <h3 class="stf-card-title">Contact Information</h3>

        <div class="stf-details-grid">

            <div>
                <div class="stf-detail-label">Phone Number</div>
                <div class="stf-detail-value">
                    {{ $staff->phone ?: 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Email Address</div>
                <div class="stf-detail-value">
                    {{ $staff->user?->email ?: 'No login account linked' }}
                </div>
            </div>

        </div>

    </div>

    {{-- EMPLOYMENT INFORMATION --}}
    <div class="stf-card">

        <h3 class="stf-card-title">Employment Information</h3>

        <div class="stf-details-grid">

            <div>
                <div class="stf-detail-label">Designation</div>
                <div class="stf-detail-value">
                    {{ $staff->designation ?: 'Not assigned' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Department</div>
                <div class="stf-detail-value">
                    {{ $staff->department ?: 'Not assigned' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Employment Type</div>
                <div class="stf-detail-value">
                    {{ $staff->employment_type
                        ? ucfirst(str_replace('-', ' ', $staff->employment_type))
                        : 'Not specified' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Joining Date</div>
                <div class="stf-detail-value">
                    {{ $staff->joining_date?->format('d M Y') ?? 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Employment Status</div>
                <div class="stf-detail-value">
                    {{ ucfirst($staff->status ?? 'Unknown') }}
                </div>
            </div>

        </div>

    </div>

    {{-- SCHOOL AND CAMPUS --}}
    <div class="stf-card">

        <h3 class="stf-card-title">School & Campus Information</h3>

        <div class="stf-details-grid">

            <div>
                <div class="stf-detail-label">School</div>
                <div class="stf-detail-value">
                    {{ $staff->school?->name ?? 'Not assigned' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Campus</div>
                <div class="stf-detail-value">
                    {{ $staff->campus?->name ?? 'Not assigned' }}
                </div>
            </div>

        </div>

    </div>

    {{-- TEACHING ASSIGNMENTS --}}
    <div class="stf-card">

        <h3 class="stf-card-title">Teaching Assignments</h3>

        @if($staff->teachingAssignments->isNotEmpty())

            <div class="stf-table-wrap">

                <table class="stf-table">

                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Academic Year</th>
                            <th>Weekly Periods</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($staff->teachingAssignments as $assignment)

                            <tr>

                                <td>
                                    <div style="font-weight:650;color:#123B5D;">
                                        {{ $assignment->classSubject?->subject?->name
                                            ?? 'Subject not assigned' }}
                                    </div>

                                    <div class="stf-muted">
                                        {{ $assignment->classSubject?->subject?->code ?? '' }}
                                    </div>
                                </td>

                                <td>
                                    {{ $assignment->classSubject?->schoolClass?->name
                                        ?? 'Not assigned' }}
                                </td>

                                <td>
                                    {{ $assignment->section?->name ?? 'Not assigned' }}
                                </td>

                                <td>
                                    {{ $assignment->academicYear?->name ?? 'Not assigned' }}
                                </td>

                                <td>
                                    {{ $assignment->classSubject?->weekly_periods
                                        ?? 'Not specified' }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="stf-empty">
                No teaching assignments have been linked to this staff member yet.
            </div>

        @endif

    </div>

    {{-- RECORD INFORMATION --}}
    <div class="stf-card">

        <h3 class="stf-card-title">Record Information</h3>

        <div class="stf-record-grid">

            <div>
                <div class="stf-detail-label">Staff ID</div>
                <div class="stf-detail-value">
                    #{{ $staff->id }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Linked User ID</div>
                <div class="stf-detail-value">
                    {{ $staff->user_id ?: 'No account linked' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Created On</div>
                <div class="stf-detail-value">
                    {{ $staff->created_at?->format('d M Y, h:i A') ?? 'Not available' }}
                </div>
            </div>

            <div>
                <div class="stf-detail-label">Last Updated</div>
                <div class="stf-detail-value">
                    {{ $staff->updated_at?->format('d M Y, h:i A') ?? 'Not available' }}
                </div>
            </div>

        </div>

    </div>

</div>

@endsection
