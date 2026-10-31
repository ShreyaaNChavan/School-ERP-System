
@extends('layouts.schoolnexa')

@section('content')

<style>
    .stf-page {
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
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .stf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 15px;
        border-radius: 9px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
        white-space: nowrap;
    }

    .stf-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .stf-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .stf-btn-outline {
        background: #fff;
        color: #25313C;
        border-color: #CBD5E1;
    }

    .stf-btn-outline:hover {
        background: #F1F5F9;
        color: #123B5D;
    }

    .stf-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .stf-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 17px 19px;
    }

    .stf-stat-label {
        color: #64748B;
        font-size: 12px;
        margin-bottom: 9px;
    }

    .stf-stat-value {
        color: #123B5D;
        font-size: 25px;
        font-weight: 700;
    }

    .stf-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 19px;
        margin-bottom: 18px;
    }

    .stf-panel-title {
        color: #25313C;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 15px;
    }

    .stf-filters {
        display: grid;
        grid-template-columns: 2fr repeat(3, minmax(130px, 1fr));
        gap: 11px;
    }

    .stf-control {
        width: 100%;
        padding: 10px 11px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        background: #fff;
        color: #25313C;
        font-size: 12px;
        outline: none;
        box-sizing: border-box;
    }

    .stf-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .stf-filter-actions {
        display: flex;
        gap: 9px;
        margin-top: 12px;
        flex-wrap: wrap;
    }

    .stf-table-wrap {
        overflow-x: auto;
    }

    .stf-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .stf-table th {
        text-align: left;
        padding: 12px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        border-bottom: 1px solid #E2E8F0;
    }

    .stf-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 12px;
        vertical-align: middle;
    }

    .stf-table tr:last-child td {
        border-bottom: none;
    }

    .stf-name {
        color: #123B5D;
        font-weight: 650;
        margin-bottom: 4px;
    }

    .stf-muted {
        color: #64748B;
        font-size: 11px;
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

    .stf-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
    }

    .stf-action {
        display: inline-flex;
        align-items: center;
        padding: 7px 9px;
        border-radius: 7px;
        border: 1px solid #CBD5E1;
        color: #123B5D;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        background: #fff;
    }

    .stf-action:hover {
        background: #F1F5F9;
        color: #176B87;
    }

    .stf-empty {
        text-align: center;
        padding: 38px 15px !important;
        color: #64748B;
    }

    .stf-alert {
        padding: 12px 15px;
        border-radius: 9px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .stf-alert-success {
        background: #F0FDF4;
        color: #166534;
        border: 1px solid #BBF7D0;
    }

    .stf-error {
        color: #DC2626;
        font-size: 11px;
        margin-top: 5px;
    }

    .stf-pagination {
        margin-top: 17px;
    }

    @media(max-width: 950px) {
        .stf-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 650px) {
        .stf-stats {
            grid-template-columns: 1fr;
        }

        .stf-filters {
            grid-template-columns: 1fr;
        }

        .stf-panel {
            padding: 14px;
        }
    }
</style>

<div class="stf-page">

    {{-- HEADER --}}
    <div class="stf-header">

        <div>
            <h2 class="stf-title">Staff Directory</h2>
            <p class="stf-subtitle">
                Manage teachers, employees and their employment information.
            </p>
        </div>

        @if(auth()->user()->hasPermission('STAFF.CREATE'))
            <a href="{{ route('staff.create') }}"
               class="stf-btn stf-btn-primary">
                + Add Staff
            </a>
        @endif

    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="stf-alert stf-alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- STATISTICS --}}
    <div class="stf-stats">

        <div class="stf-stat">
            <div class="stf-stat-label">Total Staff</div>
            <div class="stf-stat-value">{{ $stats['total'] }}</div>
        </div>

        <div class="stf-stat">
            <div class="stf-stat-label">Active Staff</div>
            <div class="stf-stat-value">{{ $stats['active'] }}</div>
        </div>

        <div class="stf-stat">
            <div class="stf-stat-label">Inactive Staff</div>
            <div class="stf-stat-value">{{ $stats['inactive'] }}</div>
        </div>

    </div>

    {{-- FILTERS --}}
    <div class="stf-panel">

        <h3 class="stf-panel-title">Search & Filter Staff</h3>

        <form method="GET" action="{{ route('staff.index') }}">

            <div class="stf-filters">

                <input
                    type="text"
                    name="search"
                    class="stf-control"
                    placeholder="Search name, employee number, phone..."
                    value="{{ request('search') }}"
                >

                <select name="school_id" class="stf-control">
                    <option value="">All Schools</option>

                    @foreach($schools as $school)
                        <option value="{{ $school->id }}"
                            @selected(request('school_id') == $school->id)>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>

                <select name="campus_id" class="stf-control">
                    <option value="">All Campuses</option>

                    @foreach($campuses as $campus)
                        <option value="{{ $campus->id }}"
                            @selected(request('campus_id') == $campus->id)>
                            {{ $campus->name }}
                        </option>
                    @endforeach
                </select>

                <select name="department" class="stf-control">
                    <option value="">All Departments</option>

                    @foreach($departments as $department)
                        <option value="{{ $department->name }}"
                            @selected(request('department') == $department->name)>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>

                <select name="designation" class="stf-control">
                    <option value="">All Designations</option>

                    @foreach($designations as $designation)
                        <option value="{{ $designation->name }}"
                            @selected(request('designation') == $designation->name)>
                            {{ $designation->name }}
                        </option>
                    @endforeach
                </select>

                <select name="employment_type" class="stf-control">
                    <option value="">All Employment Types</option>

                    @foreach(['permanent', 'contract', 'temporary', 'part-time', 'visiting'] as $type)
                        <option value="{{ $type }}"
                            @selected(request('employment_type') == $type)>
                            {{ ucfirst(str_replace('-', ' ', $type)) }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="stf-control">
                    <option value="">All Statuses</option>
                    <option value="active" @selected(request('status') == 'active')>
                        Active
                    </option>
                    <option value="inactive" @selected(request('status') == 'inactive')>
                        Inactive
                    </option>
                </select>

            </div>

            <div class="stf-filter-actions">

                <button type="submit" class="stf-btn stf-btn-primary">
                    Apply Filters
                </button>

                <a href="{{ route('staff.index') }}"
                   class="stf-btn stf-btn-outline">
                    Reset
                </a>

            </div>

        </form>

    </div>

    {{-- STAFF TABLE --}}
    <div class="stf-panel">

        <h3 class="stf-panel-title">Staff Records</h3>

        <div class="stf-table-wrap">

            <table class="stf-table">

                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee Number</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>School / Campus</th>
                        <th>Employment</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($staff as $member)

                        @php
                            $statusClass = match(strtolower($member->status ?? '')) {
                                'active' => 'stf-status-active',
                                'inactive' => 'stf-status-inactive',
                                default => 'stf-status-other',
                            };
                        @endphp

                        <tr>

                            <td>
                                <div class="stf-name">
                                    {{ $member->full_name }}
                                </div>

                                <div class="stf-muted">
                                    {{ $member->phone ?: 'No phone provided' }}
                                </div>
                            </td>

                            <td>
                                {{ $member->employee_number }}
                            </td>

                            <td>
                                {{ $member->designation ?: 'Not assigned' }}
                            </td>

                            <td>
                                {{ $member->department ?: 'Not assigned' }}
                            </td>

                            <td>
                                <div>{{ $member->school->name ?? 'Not assigned' }}</div>

                                <div class="stf-muted">
                                    {{ $member->campus->name ?? 'No campus' }}
                                </div>
                            </td>

                            <td>
                                {{ $member->employment_type
                                    ? ucfirst(str_replace('-', ' ', $member->employment_type))
                                    : 'Not specified' }}
                            </td>

                            <td>
                                <span class="stf-status {{ $statusClass }}">
                                    {{ $member->status ?: 'Unknown' }}
                                </span>
                            </td>

                            <td>
                                <div class="stf-actions">

                                    @if(auth()->user()->hasPermission('STAFF.VIEW'))
                                        <a href="{{ route('staff.show', $member) }}"
                                           class="stf-action">
                                            View
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('STAFF.UPDATE'))
                                        <a href="{{ route('staff.edit', $member) }}"
                                           class="stf-action">
                                            Edit
                                        </a>
                                    @endif

                                    @if(auth()->user()->hasPermission('STAFF.DELETE'))
                                        <form
                                            method="POST"
                                            action="{{ route('staff.destroy', $member) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this staff record?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="stf-action">
                                                Delete
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="stf-empty">
                                <strong>No staff records found.</strong>

                                <div class="stf-muted" style="margin-top:6px;">
                                    Add a staff member to get started.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="stf-pagination">
            {{ $staff->links() }}
        </div>

    </div>

</div>

@endsection
