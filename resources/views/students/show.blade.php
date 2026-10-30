
@extends('layouts.schoolnexa')

@section('content')

@php
    $enrollment = $student->enrollments->first();

    $studentName = trim(
        $student->first_name . ' ' .
        ($student->middle_name ? $student->middle_name . ' ' : '') .
        $student->last_name
    );

    $initials = strtoupper(
        substr($student->first_name, 0, 1) .
        substr($student->last_name, 0, 1)
    );
@endphp

<style>
    .profile-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .profile-title {
        color: #123B5D;
        font-size: 23px;
        font-weight: 700;
        margin: 0;
    }

    .profile-subtitle {
        font-size: 13px;
        color: #64748B;
        margin-top: 5px;
    }

    .profile-breadcrumb {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 8px;
    }

    .profile-breadcrumb a {
        color: #176B87;
        text-decoration: none;
    }

    .profile-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .profile-btn-primary {
        background: #123B5D;
        color: white;
    }

    .profile-btn-primary:hover {
        background: #176B87;
        color: white;
    }

    .profile-btn-secondary {
        background: white;
        border: 1px solid #CBD5E1;
        color: #25313C;
    }

    .profile-hero {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 24px;
        background: white;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .profile-avatar {
        width: 76px;
        height: 76px;
        border-radius: 18px;
        background: #E0F2FE;
        color: #123B5D;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        flex-shrink: 0;
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-name {
        font-size: 21px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 6px;
    }

    .profile-meta {
        font-size: 13px;
        color: #64748B;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .profile-status {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: #DCFCE7;
        color: #15803D;
        margin-top: 10px;
    }

    .profile-layout {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .profile-card {
        background: white;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 20px;
        min-width: 0;
    }

    .profile-card.full {
        grid-column: 1 / -1;
    }

    .profile-card-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .profile-card-subtitle {
        color: #64748B;
        font-size: 12px;
        margin-bottom: 18px;
    }

    .profile-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 16px;
    }

    .profile-info-label {
        font-size: 11px;
        color: #64748B;
        margin-bottom: 5px;
    }

    .profile-info-value {
        font-size: 13px;
        color: #25313C;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .profile-table {
        width: 100%;
        border-collapse: collapse;
    }

    .profile-table th {
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        padding: 12px;
    }

    .profile-table td {
        border-top: 1px solid #E2E8F0;
        padding: 13px 12px;
        font-size: 12px;
        color: #25313C;
    }

    .profile-empty {
        padding: 22px;
        background: #F8FAFC;
        border: 1px dashed #CBD5E1;
        border-radius: 10px;
        text-align: center;
        color: #64748B;
        font-size: 12px;
    }

    .profile-empty strong {
        display: block;
        color: #25313C;
        margin-bottom: 5px;
        font-size: 13px;
    }

    .profile-tag {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 6px;
        background: #E0F2FE;
        color: #176B87;
        font-size: 11px;
        font-weight: 600;
    }

    @media(max-width: 750px) {
        .profile-layout {
            grid-template-columns: 1fr;
        }

        .profile-card.full {
            grid-column: auto;
        }

        .profile-info-grid {
            grid-template-columns: 1fr 1fr;
        }

        .profile-hero {
            align-items: flex-start;
        }
    }

    @media(max-width: 450px) {
        .profile-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profile-page">

    {{-- HEADER --}}

    <div class="profile-header">

        <div>
            <div class="profile-breadcrumb">
                <a href="{{ route('students.index') }}">Students</a>
                / Student Profile
            </div>

            <h1 class="profile-title">Student Profile</h1>

            <div class="profile-subtitle">
                View complete student information and academic details.
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">

            <a href="{{ route('students.index') }}"
               class="profile-btn profile-btn-secondary">
                Back to Directory
            </a>

            @if(auth()->user()->hasPermission('STUDENTS.UPDATE'))
                <a href="{{ route('students.edit', $student->id) }}"
                   class="profile-btn profile-btn-primary">
                    Edit Student
                </a>
            @endif

        </div>
    </div>


    {{-- STUDENT OVERVIEW --}}

    <div class="profile-hero">

        <div class="profile-avatar">

            @if($student->photo_path)
                <img
                    src="{{ asset('storage/' . $student->photo_path) }}"
                    alt="Student Photo"
                >
            @else
                {{ $initials }}
            @endif

        </div>

        <div style="flex:1; min-width:200px;">

            <h2 class="profile-name">{{ $studentName }}</h2>

            <div class="profile-meta">

                <span>
                    Admission No: {{ $student->admission_number }}
                </span>

                <span>
                    Student ID: {{ $student->id }}
                </span>

                <span>
                    {{ $student->school->name ?? 'School not assigned' }}
                </span>

            </div>

            <span class="profile-status">
                {{ ucfirst($student->status) }}
            </span>

        </div>

    </div>


    {{-- PROFILE SECTIONS --}}

    <div class="profile-layout">


        {{-- PERSONAL INFORMATION --}}

        <div class="profile-card">

            <div class="profile-card-title">
                Personal Information
            </div>

            <div class="profile-card-subtitle">
                Student's basic personal details.
            </div>

            <div class="profile-info-grid">

                <div>
                    <div class="profile-info-label">First Name</div>
                    <div class="profile-info-value">
                        {{ $student->first_name }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Middle Name</div>
                    <div class="profile-info-value">
                        {{ $student->middle_name ?: '—' }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Last Name</div>
                    <div class="profile-info-value">
                        {{ $student->last_name }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Date of Birth</div>
                    <div class="profile-info-value">
                        {{ $student->date_of_birth?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Gender</div>
                    <div class="profile-info-value">
                        {{ ucfirst($student->gender ?? '—') }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Blood Group</div>
                    <div class="profile-info-value">
                        {{ $student->blood_group ?? '—' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- ADMISSION INFORMATION --}}

        <div class="profile-card">

            <div class="profile-card-title">
                Admission Information
            </div>

            <div class="profile-card-subtitle">
                School and admission details.
            </div>

            <div class="profile-info-grid">

                <div>
                    <div class="profile-info-label">Admission Number</div>
                    <div class="profile-info-value">
                        {{ $student->admission_number }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Admission Date</div>
                    <div class="profile-info-value">
                        {{ $student->admission_date?->format('d M Y') ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">School</div>
                    <div class="profile-info-value">
                        {{ $student->school->name ?? '—' }}
                    </div>
                </div>

                <div>
                    <div class="profile-info-label">Campus</div>
                    <div class="profile-info-value">
                        {{ $student->campus->name ?? '—' }}
                    </div>
                </div>

            </div>

        </div>


        {{-- ACADEMIC INFORMATION --}}

        <div class="profile-card full">

            <div class="profile-card-title">
                Academic Information
            </div>

            <div class="profile-card-subtitle">
                Current academic enrollment and class allocation.
            </div>

            @if($enrollment)

                <div class="profile-info-grid">

                    <div>
                        <div class="profile-info-label">Academic Year</div>
                        <div class="profile-info-value">
                            {{ $enrollment->academicYear->name ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="profile-info-label">Class</div>
                        <div class="profile-info-value">
                            {{ $enrollment->schoolClass->name ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="profile-info-label">Section</div>
                        <div class="profile-info-value">
                            {{ $enrollment->section ? 'Section ' . $enrollment->section->name : '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="profile-info-label">Enrollment Date</div>
                        <div class="profile-info-value">
                            {{ $enrollment->enrollment_date?->format('d M Y') ?? '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="profile-info-label">Enrollment Status</div>
                        <div class="profile-info-value">
                            <span class="profile-tag">
                                {{ ucfirst($enrollment->status) }}
                            </span>
                        </div>
                    </div>

                </div>

            @else

                <div class="profile-empty">
                    <strong>No academic enrollment found</strong>
                    Academic enrollment information has not been assigned.
                </div>

            @endif

        </div>


        {{-- GUARDIAN INFORMATION --}}

        <div class="profile-card full">

            <div class="profile-card-title">
                Parent / Guardian Information
            </div>

            <div class="profile-card-subtitle">
                Registered parents and guardians linked to this student.
            </div>

            @if($student->guardians->count())

                <div style="overflow-x:auto;">

                    <table class="profile-table">

                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Relationship</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Primary</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($student->guardians as $guardian)

                                <tr>
                                    <td>
                                        <strong>{{ $guardian->full_name }}</strong>
                                    </td>

                                    <td>
                                        {{ ucfirst($guardian->relationship ?? '—') }}
                                    </td>

                                    <td>
                                        {{ $guardian->phone ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $guardian->email ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $guardian->pivot->is_primary ? 'Yes' : 'No' }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="profile-empty">
                    <strong>No guardian information available</strong>
                    Guardian details will appear here once linked to this student.
                </div>

            @endif

        </div>


        {{-- EMERGENCY CONTACTS --}}

        <div class="profile-card full">

            <div class="profile-card-title">
                Emergency Contacts
            </div>

            <div class="profile-card-subtitle">
                People to contact in case of an emergency.
            </div>

            @if($student->emergencyContacts->count())

                <div style="overflow-x:auto;">

                    <table class="profile-table">

                        <thead>
                            <tr>
                                <th>Contact Name</th>
                                <th>Relationship</th>
                                <th>Phone</th>
                                <th>Alternate Phone</th>
                                <th>Priority</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($student->emergencyContacts as $contact)

                                <tr>
                                    <td>
                                        <strong>{{ $contact->contact_name }}</strong>
                                    </td>

                                    <td>
                                        {{ ucfirst($contact->relationship ?? '—') }}
                                    </td>

                                    <td>
                                        {{ $contact->phone ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $contact->alternate_phone ?? '—' }}
                                    </td>

                                    <td>
                                        {{ $contact->priority ?? '—' }}
                                    </td>
                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="profile-empty">
                    <strong>No emergency contacts added</strong>
                    Emergency contact details will appear here once added.
                </div>

            @endif

        </div>


        {{-- ATTENDANCE --}}

        <div class="profile-card">

            <div class="profile-card-title">
                Attendance Overview
            </div>

            <div class="profile-card-subtitle">
                Student attendance summary.
            </div>

            <div class="profile-empty">
                <strong>Attendance module not connected yet</strong>
                Attendance statistics will appear here once the attendance module is implemented.
            </div>

        </div>


        {{-- FEES --}}

        <div class="profile-card">

            <div class="profile-card-title">
                Fee Overview
            </div>

            <div class="profile-card-subtitle">
                Student fee and payment summary.
            </div>

            <div class="profile-empty">
                <strong>Fee module not connected yet</strong>
                Fee balances and payment history will appear here once the fee module is implemented.
            </div>

        </div>

    </div>

</div>

@endsection
