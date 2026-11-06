
@extends('layouts.schoolnexa')

@section('title', 'Employee Attendance')

@section('content')
@include('hr._style')

@php
    $statusLabels = [
        'present' => 'Present',
        'late' => 'Late',
        'half_day' => 'Half Day',
        'absent' => 'Absent',
        'leave' => 'On Leave'
    ];

    $statusShort = [
        'present' => 'P',
        'late' => 'L',
        'half_day' => '½',
        'absent' => 'A',
        'leave' => 'LV'
    ];
@endphp

<div class="hr-attendance-page">

    {{-- Header --}}
    <div class="hr-att-header">
        <div>
            <h1>Employee Attendance</h1>
            <p>Track, manage and review employee attendance and check-in records.</p>
        </div>

        <div class="hr-att-actions">
            <a href="{{ route('hr.attendance-rules') }}" class="att-outline">
                Attendance Rules
            </a>
            <a href="{{ route('hr.dashboard') }}" class="att-outline">
                Back to HR
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="att-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="att-alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Date Filter --}}
    <div class="att-filter-panel">
        <form method="GET" action="{{ url()->current() }}">
            <div class="att-filter-grid">

                <div>
                    <label>Attendance Date</label>
                    <input type="date" name="date" value="{{ $date }}">
                </div>

                <div class="att-filter-actions">
                    <button type="submit" class="att-primary">
                        Apply Date
                    </button>

                    <a href="{{ url()->current() }}" class="att-outline">
                        Reset
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="att-summary-grid">

        <div class="att-stat">
            <span>Total Employees</span>
            <strong>{{ $staff->count() }}</strong>
            <small>Available employees</small>
        </div>

        <div class="att-stat">
            <span>Attendance Records</span>
            <strong>{{ $records->total() }}</strong>
            <small>Selected date</small>
        </div>

    </div>

    {{-- Attendance Register --}}
    <section class="att-panel">

        <div class="att-panel-head">
            <div>
                <h2>Employee Attendance Register</h2>
                <p>
                    {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                    · {{ $records->total() }} attendance records
                </p>
            </div>

            <div class="att-legend">
                <span><b class="present">P</b> Present</span>
                <span><b class="late">L</b> Late</span>
                <span><b class="halfday">½</b> Half Day</span>
                <span><b class="absent">A</b> Absent</span>
                <span><b class="leave">LV</b> Leave</span>
            </div>
        </div>

        <div class="att-table-wrap">
            <table class="att-table">

                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Working Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($records as $rec)

                        <tr>
                            <td>
                                <strong class="employee-id">
                                    {{ $rec->staff->employee_number ?? '—' }}
                                </strong>
                            </td>

                            <td>
                                <strong class="employee-name">
                                    {{ $rec->staff->full_name ?? '—' }}
                                </strong>
                            </td>

                            <td>{{ $rec->check_in ?? '—' }}</td>

                            <td>{{ $rec->check_out ?? '—' }}</td>

                            <td>
                                <span class="att-pill {{ $rec->status }}">
                                    {{ $statusShort[$rec->status] ?? '?' }}
                                    {{ $statusLabels[$rec->status] ?? ucfirst($rec->status) }}
                                </span>
                            </td>

                            <td>{{ $rec->remarks ?: '—' }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="att-empty">
                                No attendance records found for this date.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="att-pagination">
            <div class="att-pagination-info">
                Showing {{ $records->firstItem() ?? 0 }}–{{ $records->lastItem() ?? 0 }}
                of {{ $records->total() }} records
            </div>

            {{ $records->withQueryString()->links() }}
        </div>

    </section>

    {{-- Mark / Update Attendance --}}
    <section class="att-panel att-mark-panel">

        <div class="att-panel-head">
            <div>
                <h2>Record / Update Attendance</h2>
                <p>Select an employee and enter their daily attendance details.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('hr.attendance.store') }}">
            @csrf

            <div class="att-mark-toolbar">

                <div class="employee-field">
                    <label>Employee</label>

                    <select name="staff_profile_id" required>
                        <option value="">Select Employee</option>

                        @foreach($staff as $person)
                            <option value="{{ $person->id }}"
                                @selected(old('staff_profile_id') == $person->id)>
                                {{ $person->employee_number }} · {{ $person->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Attendance Date</label>
                    <input
                        type="date"
                        name="attendance_date"
                        value="{{ old('attendance_date', $date) }}"
                        required
                    >
                </div>

                <div>
                    <label>Check In</label>
                    <input type="time" name="check_in" value="{{ old('check_in') }}">
                </div>

                <div>
                    <label>Check Out</label>
                    <input type="time" name="check_out" value="{{ old('check_out') }}">
                </div>

                <div>
                    <label>Attendance Status</label>

                    <select name="status" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('status', 'present') == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="remarks-field">
                    <label>Remarks</label>
                    <input
                        type="text"
                        name="remarks"
                        value="{{ old('remarks') }}"
                        placeholder="Optional note"
                    >
                </div>

            </div>

            <div class="att-form-footer">
                <button type="submit" class="att-primary">
                    Save Attendance
                </button>
            </div>

        </form>

    </section>

</div>

{{-- Styles --}}
<style>
.hr-attendance-page {
    font-family: Inter, sans-serif;
    color: #25313C;
    padding: 22px 24px;
}

.hr-att-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}

.hr-att-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #123B5D;
    margin: 0;
}

.hr-att-header p {
    font-size: 13px;
    color: #64748B;
    margin: 5px 0 0;
}

.hr-att-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.att-filter-panel,
.att-panel {
    background: #fff;
    border: 1px solid #CBD5E1;
    border-radius: 14px;
    margin-bottom: 17px;
}

.att-filter-panel {
    padding: 17px;
}

.att-filter-grid {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex-wrap: wrap;
}

.att-filter-grid > div:first-child {
    min-width: 230px;
}

.att-filter-grid label,
.att-mark-toolbar label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.att-filter-grid input,
.att-mark-toolbar input,
.att-mark-toolbar select {
    width: 100%;
    height: 38px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    padding: 0 10px;
    background: #fff;
    color: #25313C;
    font: 400 12px Inter, sans-serif;
    box-sizing: border-box;
}

.att-filter-actions {
    display: flex;
    gap: 8px;
}

.att-primary,
.att-outline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 39px;
    padding: 0 14px;
    border-radius: 9px;
    font: 600 13px Inter, sans-serif;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
}

.att-primary {
    border: 1px solid #123B5D;
    background: #123B5D;
    color: #fff;
}

.att-primary:hover {
    background: #176B87;
    border-color: #176B87;
    color: #fff;
}

.att-outline {
    border: 1px solid #CBD5E1;
    background: #fff;
    color: #123B5D;
}

.att-outline:hover {
    background: #F1F5F9;
}

.att-summary-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 17px;
}

.att-stat {
    background: #fff;
    border: 1px solid #CBD5E1;
    border-radius: 12px;
    padding: 15px 17px;
}

.att-stat span {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #64748B;
    text-transform: uppercase;
}

.att-stat strong {
    display: block;
    font-size: 26px;
    font-weight: 700;
    color: #123B5D;
    margin-top: 5px;
}

.att-stat small {
    font-size: 11px;
    color: #94A3B8;
}

.att-panel {
    overflow: hidden;
}

.att-panel-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    padding: 16px 18px;
    border-bottom: 1px solid #E2E8F0;
}

.att-panel-head h2 {
    font-size: 16px;
    font-weight: 650;
    color: #123B5D;
    margin: 0;
}

.att-panel-head p {
    font-size: 12px;
    color: #64748B;
    margin: 4px 0 0;
}

.att-legend {
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
}

.att-legend span {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    color: #64748B;
}

.att-legend b {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 23px;
    height: 23px;
    border-radius: 5px;
    font-size: 10px;
}

.att-table-wrap {
    overflow-x: auto;
}

.att-table {
    width: 100%;
    min-width: 800px;
    border-collapse: collapse;
}

.att-table th {
    background: #F8FAFC;
    text-align: left;
    padding: 12px 14px;
    font-size: 11px;
    color: #64748B;
    border-bottom: 1px solid #E2E8F0;
}

.att-table td {
    padding: 12px 14px;
    border-bottom: 1px solid #E2E8F0;
    font-size: 12px;
    color: #475569;
}

.employee-id {
    color: #176B87;
    font-weight: 600;
}

.employee-name {
    color: #123B5D;
    font-weight: 600;
}

.att-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 6px 9px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}

.present {
    background: #DCFCE7;
    color: #166534;
}

.late {
    background: #FEF3C7;
    color: #92400E;
}

.absent {
    background: #FEE2E2;
    color: #B91C1C;
}

.half_day {
    background: #EDE9FE;
    color: #5B21B6;
}

.leave {
    background: #DBEAFE;
    color: #1D4ED8;
}

.att-empty {
    text-align: center;
    padding: 30px !important;
    color: #64748B !important;
}

.att-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    padding: 13px 17px;
    border-top: 1px solid #E2E8F0;
}

.att-pagination-info {
    font-size: 12px;
    color: #64748B;
}

.att-pagination nav {
    margin-left: auto;
}

.att-pagination svg {
    width: 16px;
    height: 16px;
}

.att-mark-panel .att-panel-head {
    border-bottom: 0;
}

.att-mark-toolbar {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
    padding: 0 18px 15px;
}

.att-form-footer {
    display: flex;
    justify-content: flex-end;
    padding: 14px 18px;
    border-top: 1px solid #E2E8F0;
}

.att-alert-success,
.att-alert-error {
    padding: 12px 15px;
    border-radius: 9px;
    margin-bottom: 15px;
    font-size: 13px;
}

.att-alert-success {
    background: #F0FDF4;
    border: 1px solid #BBF7D0;
    color: #166534;
}

.att-alert-error {
    background: #FEF2F2;
    border: 1px solid #FECACA;
    color: #B91C1C;
}

@media(max-width: 900px) {
    .att-mark-toolbar {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media(max-width: 650px) {
    .hr-attendance-page {
        padding: 14px;
    }

    .att-summary-grid,
    .att-mark-toolbar {
        grid-template-columns: 1fr;
    }

    .att-filter-grid {
        display: grid;
        grid-template-columns: 1fr;
    }

    .att-filter-grid > div:first-child {
        min-width: 0;
    }

    .att-filter-actions {
        flex-wrap: wrap;
    }
}
</style>

@endsection
