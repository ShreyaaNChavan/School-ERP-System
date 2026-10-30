
@extends('layouts.schoolnexa')

@section('content')

<style>
    .std-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .std-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }

    .std-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .std-subtitle {
        font-size: 13px;
        color: #64748B;
        margin: 0;
    }

    .std-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 16px;
        border-radius: 9px;
        background: #123B5D;
        color: white;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid #123B5D;
        cursor: pointer;
    }

    .std-btn:hover {
        background: #176B87;
        color: white;
    }

    .std-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .std-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 15px;
        padding: 17px 19px;
    }

    .std-stat-label {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 9px;
    }

    .std-stat-value {
        font-size: 25px;
        font-weight: 700;
        color: #123B5D;
    }

    .std-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 15px;
        padding: 19px;
        margin-bottom: 18px;
    }

    .std-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 16px;
    }

    .std-filters {
        display: grid;
        grid-template-columns: 2fr repeat(4, minmax(130px, 1fr)) auto;
        gap: 10px;
        align-items: end;
    }

    .std-field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #64748B;
        margin-bottom: 6px;
    }

    .std-field input,
    .std-field select {
        width: 100%;
        height: 39px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 0 10px;
        font-size: 12px;
        color: #25313C;
        background: white;
        outline: none;
    }

    .std-field input:focus,
    .std-field select:focus {
        border-color: #176B87;
    }

    .std-filter-actions {
        display: flex;
        gap: 7px;
    }

    .std-filter-btn {
        height: 39px;
        padding: 0 13px;
        border-radius: 8px;
        border: 1px solid #176B87;
        background: #176B87;
        color: white;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        cursor: pointer;
    }

    .std-clear-btn {
        height: 39px;
        padding: 0 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: white;
        color: #64748B;
        font-size: 12px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .std-table-wrap {
        overflow-x: auto;
    }

    .std-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .std-table th {
        padding: 12px 13px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        color: #64748B;
        background: #F8FAFC;
        border-bottom: 1px solid #CBD5E1;
        white-space: nowrap;
    }

    .std-table td {
        padding: 13px;
        font-size: 12px;
        border-bottom: 1px solid #E2E8F0;
        vertical-align: middle;
    }

    .std-table tr:last-child td {
        border-bottom: none;
    }

    .std-student-name {
        font-weight: 700;
        color: #123B5D;
    }

    .std-muted {
        color: #64748B;
        font-size: 11px;
        margin-top: 4px;
    }

    .std-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #E0F2FE;
        color: #176B87;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }

    .std-person {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .std-badge {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .std-active {
        color: #15803D;
        background: #DCFCE7;
    }

    .std-inactive {
        color: #B45309;
        background: #FEF3C7;
    }

    .std-actions {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }

    .std-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 9px;
        border: 1px solid #CBD5E1;
        border-radius: 7px;
        color: #176B87;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        background: white;
        cursor: pointer;
        white-space: nowrap;
    }

    .std-action:hover {
        background: #F1F5F9;
    }

    .std-action-form {
        display: inline-flex;
        margin: 0;
    }

    .std-empty {
        text-align: center;
        padding: 45px 15px !important;
        color: #64748B;
    }

    .std-alert {
        padding: 12px 15px;
        border-radius: 9px;
        margin-bottom: 15px;
        font-size: 13px;
    }

    .std-success {
        background: #DCFCE7;
        color: #166534;
    }

    .std-pagination {
        padding-top: 15px;
    }

    @media(max-width: 1100px) {
        .std-filters {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media(max-width: 700px) {
        .std-stats {
            grid-template-columns: 1fr;
        }

        .std-filters {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media(max-width: 450px) {
        .std-filters {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="std-page">

    {{-- Page Header --}}
    <div class="std-header">
        <div>
            <h2 class="std-title">Student Directory</h2>
            <p class="std-subtitle">
                Manage student records, admissions and academic enrollment.
            </p>
        </div>

        @if(auth()->user()->hasPermission('STUDENTS.CREATE'))
            <a href="{{ route('students.create') }}" class="std-btn">
                + Add Student
            </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="std-alert std-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Student Statistics --}}
    <div class="std-stats">

        <div class="std-stat">
            <div class="std-stat-label">Total Students</div>
            <div class="std-stat-value">
                {{ number_format($stats['total']) }}
            </div>
        </div>

        <div class="std-stat">
            <div class="std-stat-label">Active Students</div>
            <div class="std-stat-value">
                {{ number_format($stats['active']) }}
            </div>
        </div>

        <div class="std-stat">
            <div class="std-stat-label">Inactive Students</div>
            <div class="std-stat-value">
                {{ number_format($stats['inactive']) }}
            </div>
        </div>

    </div>

    {{-- Search and Filters --}}
    <div class="std-panel">

        <h3 class="std-panel-title">Search & Filters</h3>

        <form method="GET" action="{{ route('students.index') }}">

            <div class="std-filters">

                <div class="std-field">
                    <label>Search Student</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="Name or admission number"
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="std-field">
                    <label>School</label>
                    <select name="school_id">
                        <option value="">All Schools</option>

                        @foreach($schools as $school)
                            <option
                                value="{{ $school->id }}"
                                @selected(request('school_id') == $school->id)
                            >
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="std-field">
                    <label>Campus</label>
                    <select name="campus_id">
                        <option value="">All Campuses</option>

                        @foreach($campuses as $campus)
                            <option
                                value="{{ $campus->id }}"
                                @selected(request('campus_id') == $campus->id)
                            >
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="std-field">
                    <label>Class</label>
                    <select name="class_id">
                        <option value="">All Classes</option>

                        @foreach($classes as $class)
                            <option
                                value="{{ $class->id }}"
                                @selected(request('class_id') == $class->id)
                            >
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="std-field">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>

                        <option
                            value="active"
                            @selected(request('status') === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(request('status') === 'inactive')
                        >
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="std-filter-actions">
                    <button type="submit" class="std-filter-btn">
                        Apply
                    </button>

                    <a href="{{ route('students.index') }}" class="std-clear-btn">
                        Reset
                    </a>
                </div>

            </div>

        </form>

    </div>

    {{-- Student Records --}}
    <div class="std-panel">

        <h3 class="std-panel-title">Student Records</h3>

        <div class="std-table-wrap">

            <table class="std-table">

                <thead>
                    <tr>
                        <th>STUDENT</th>
                        <th>ADMISSION NO.</th>
                        <th>SCHOOL / CAMPUS</th>
                        <th>CLASS / SECTION</th>
                        <th>ADMISSION DATE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($students as $student)

                        @php
                            $enrollment = $student->enrollments->first();
                        @endphp

                        <tr>

                            {{-- Student Details --}}
                            <td>
                                <div class="std-person">

                                    <div class="std-avatar">
                                        {{ strtoupper(
                                            substr($student->first_name, 0, 1) .
                                            substr($student->last_name, 0, 1)
                                        ) }}
                                    </div>

                                    <div>
                                        <div class="std-student-name">
                                            {{ $student->full_name }}
                                        </div>

                                        <div class="std-muted">
                                            {{ $student->gender ?: 'Gender not specified' }}
                                        </div>
                                    </div>

                                </div>
                            </td>

                            {{-- Admission Number --}}
                            <td>
                                {{ $student->admission_number }}
                            </td>

                            {{-- School and Campus --}}
                            <td>
                                <div>
                                    {{ $student->school?->name ?? '—' }}
                                </div>

                                <div class="std-muted">
                                    {{ $student->campus?->name ?? '—' }}
                                </div>
                            </td>

                            {{-- Class and Section --}}
                            <td>
                                <div>
                                    {{ $enrollment?->schoolClass?->name ?? 'Not enrolled' }}
                                </div>

                                <div class="std-muted">
                                    {{ $enrollment?->section?->name ?? '—' }}
                                </div>
                            </td>

                            {{-- Admission Date --}}
                            <td>
                                {{ $student->admission_date?->format('d M Y') ?? '—' }}
                            </td>

                            {{-- Status --}}
                            <td>
                                <span class="std-badge {{ $student->status === 'active' ? 'std-active' : 'std-inactive' }}">
                                    {{ $student->status }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td>

                                <div class="std-actions">

                                    {{-- View Profile --}}
                                    @if(auth()->user()->hasPermission('STUDENTS.VIEW'))
                                        <a
                                            href="{{ route('students.show', $student) }}"
                                            class="std-action"
                                        >
                                            View Profile
                                        </a>
                                    @endif

                                    {{-- Edit Student --}}
                                    @if(auth()->user()->hasPermission('STUDENTS.UPDATE'))
                                        <a
                                            href="{{ route('students.edit', $student) }}"
                                            class="std-action"
                                        >
                                            Edit
                                        </a>

                                        {{-- Activate / Deactivate --}}
                                        <form
                                            method="POST"
                                            action="{{ route('students.status', $student) }}"
                                            class="std-action-form"
                                            onsubmit="return confirm('Are you sure you want to change this student status?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="std-action">
                                                {{ $student->status === 'active' ? 'Deactivate' : 'Activate' }}
                                            </button>

                                        </form>
                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="std-empty">

                                <strong>No students found.</strong>

                                <div class="std-muted">
                                    Add a student or change your search filters.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        <div class="std-pagination">
            {{ $students->links() }}
        </div>

    </div>

</div>

@endsection
