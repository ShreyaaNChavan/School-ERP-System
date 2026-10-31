
@extends('layouts.schoolnexa')

@section('content')

<style>
    .gdn-profile {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .gdn-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .gdn-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .gdn-subtitle {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .gdn-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .gdn-btn {
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

    .gdn-btn-primary {
        background: #123B5D;
        color: #fff;
        border-color: #123B5D;
    }

    .gdn-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .gdn-btn-outline {
        background: #fff;
        color: #25313C;
    }

    .gdn-btn-outline:hover {
        background: #F1F5F9;
    }

    .gdn-card {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .gdn-card-title {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 17px;
    }

    .gdn-profile-summary {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .gdn-avatar {
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

    .gdn-profile-name {
        font-size: 19px;
        font-weight: 700;
        color: #123B5D;
        margin-bottom: 5px;
    }

    .gdn-profile-meta {
        color: #64748B;
        font-size: 12px;
    }

    .gdn-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .gdn-detail-label {
        color: #64748B;
        font-size: 11px;
        margin-bottom: 6px;
    }

    .gdn-detail-value {
        font-size: 13px;
        font-weight: 600;
        color: #25313C;
        overflow-wrap: anywhere;
    }

    .gdn-student-table-wrap {
        overflow-x: auto;
    }

    .gdn-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 650px;
    }

    .gdn-table th {
        text-align: left;
        padding: 12px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
        border-bottom: 1px solid #E2E8F0;
    }

    .gdn-table td {
        padding: 14px 12px;
        font-size: 12px;
        border-bottom: 1px solid #E2E8F0;
    }

    .gdn-table tr:last-child td {
        border-bottom: none;
    }

    .gdn-student-name {
        color: #123B5D;
        font-weight: 650;
        text-decoration: none;
    }

    .gdn-student-name:hover {
        color: #176B87;
    }

    .gdn-muted {
        color: #64748B;
        font-size: 11px;
        margin-top: 4px;
    }

    .gdn-badges {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .gdn-badge {
        display: inline-flex;
        padding: 5px 8px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 650;
        background: #EAF3F8;
        color: #176B87;
    }

    .gdn-badge-success {
        background: #DCFCE7;
        color: #166534;
    }

    .gdn-empty {
        text-align: center;
        padding: 28px 12px;
        color: #64748B;
        font-size: 13px;
    }

    @media(max-width: 700px) {
        .gdn-details-grid {
            grid-template-columns: 1fr;
        }

        .gdn-card {
            padding: 15px;
        }
    }
</style>

<div class="gdn-profile">

    {{-- HEADER --}}
    <div class="gdn-header">

        <div>
            <h2 class="gdn-title">Parent Profile</h2>

            <p class="gdn-subtitle">
                View parent information and associated students.
            </p>
        </div>

        <div class="gdn-actions">

            <a href="{{ route('guardians.index') }}"
               class="gdn-btn gdn-btn-outline">
                Back to Directory
            </a>

            @if(auth()->user()->hasPermission('GUARDIANS.UPDATE'))
                <a href="{{ route('guardians.edit', $guardian) }}"
                   class="gdn-btn gdn-btn-primary">
                    Edit Profile
                </a>
            @endif

        </div>

    </div>

    {{-- PROFILE SUMMARY --}}
    <div class="gdn-card">

        <div class="gdn-profile-summary">

            <div class="gdn-avatar">
                {{ strtoupper(substr($guardian->full_name, 0, 1)) }}
            </div>

            <div>
                <div class="gdn-profile-name">
                    {{ $guardian->full_name }}
                </div>

                <div class="gdn-profile-meta">
                    {{ $guardian->relationship }} ·
                    {{ $guardian->phone }}
                </div>

                <div class="gdn-profile-meta" style="margin-top:5px;">
                    {{ $guardian->students->count() }}
                    linked student(s)
                </div>
            </div>

        </div>

    </div>

    {{-- PERSONAL INFORMATION --}}
    <div class="gdn-card">

        <h3 class="gdn-card-title">Personal Information</h3>

        <div class="gdn-details-grid">

            <div>
                <div class="gdn-detail-label">Full Name</div>
                <div class="gdn-detail-value">
                    {{ $guardian->full_name }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Relationship</div>
                <div class="gdn-detail-value">
                    {{ $guardian->relationship }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Occupation</div>
                <div class="gdn-detail-value">
                    {{ $guardian->occupation ?: 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Linked User Account</div>
                <div class="gdn-detail-value">
                    {{ $guardian->user_id ? 'Account linked' : 'No login account linked' }}
                </div>
            </div>

        </div>

    </div>

    {{-- CONTACT INFORMATION --}}
    <div class="gdn-card">

        <h3 class="gdn-card-title">Contact Information</h3>

        <div class="gdn-details-grid">

            <div>
                <div class="gdn-detail-label">Primary Phone</div>
                <div class="gdn-detail-value">
                    {{ $guardian->phone }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Alternate Phone</div>
                <div class="gdn-detail-value">
                    {{ $guardian->alternate_phone ?: 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Email Address</div>
                <div class="gdn-detail-value">
                    {{ $guardian->email ?: 'Not provided' }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Residential Address</div>
                <div class="gdn-detail-value">
                    {{ $guardian->address ?: 'Not provided' }}
                </div>
            </div>

        </div>

    </div>

    {{-- LINKED STUDENTS --}}
    <div class="gdn-card">

        <h3 class="gdn-card-title">Linked Students</h3>

        @if($guardian->students->isNotEmpty())

            <div class="gdn-student-table-wrap">

                <table class="gdn-table">

                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Admission Number</th>
                            <th>School / Campus</th>
                            <th>Guardian Preferences</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($guardian->students as $student)

                            @php
                                $enrollment = $student->enrollments
                                    ->where('status', 'active')
                                    ->sortByDesc('id')
                                    ->first();
                            @endphp

                            <tr>

                                <td>
                                    <div style="font-weight:650;color:#123B5D;">
                                        {{ $student->full_name }}
                                    </div>

                                    <div class="gdn-muted">
                                        {{ $enrollment?->schoolClass?->name ?? 'Class not assigned' }}
                                        @if($enrollment?->section)
                                            - {{ $enrollment->section->name }}
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    {{ $student->admission_number }}
                                </td>

                                <td>
                                    {{ $student->school->name ?? 'Not assigned' }}

                                    <div class="gdn-muted">
                                        {{ $student->campus->name ?? 'Not assigned' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="gdn-badges">

                                        @if($student->pivot->is_primary)
                                            <span class="gdn-badge gdn-badge-success">
                                                Primary
                                            </span>
                                        @endif

                                        @if($student->pivot->is_emergency_contact)
                                            <span class="gdn-badge">
                                                Emergency
                                            </span>
                                        @endif

                                        @if($student->pivot->can_pick_up_student)
                                            <span class="gdn-badge">
                                                Pickup
                                            </span>
                                        @endif

                                        @if($student->pivot->receives_communications)
                                            <span class="gdn-badge">
                                                Communications
                                            </span>
                                        @endif

                                        @if(
                                            !$student->pivot->is_primary &&
                                            !$student->pivot->is_emergency_contact &&
                                            !$student->pivot->can_pick_up_student &&
                                            !$student->pivot->receives_communications
                                        )
                                            <span class="gdn-muted">
                                                No preferences set
                                            </span>
                                        @endif

                                    </div>
                                </td>

                                <td>
                                    @if(auth()->user()->hasPermission('STUDENTS.VIEW'))
                                        <a href="{{ route('students.show', $student) }}"
                                           class="gdn-btn gdn-btn-outline">
                                            View Student
                                        </a>
                                    @endif
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="gdn-empty">
                No students are currently linked to this parent.
            </div>

        @endif

    </div>

    {{-- RECORD INFORMATION --}}
    <div class="gdn-card">

        <h3 class="gdn-card-title">Record Information</h3>

        <div class="gdn-details-grid">

            <div>
                <div class="gdn-detail-label">Guardian ID</div>
                <div class="gdn-detail-value">
                    #{{ $guardian->id }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Created On</div>
                <div class="gdn-detail-value">
                    {{ $guardian->created_at?->format('d M Y, h:i A') ?? 'Not available' }}
                </div>
            </div>

            <div>
                <div class="gdn-detail-label">Last Updated</div>
                <div class="gdn-detail-value">
                    {{ $guardian->updated_at?->format('d M Y, h:i A') ?? 'Not available' }}
                </div>
            </div>

        </div>

    </div>

</div>

@endsection
