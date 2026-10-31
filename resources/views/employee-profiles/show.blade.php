
@extends('layouts.schoolnexa')

@section('content')
<div class="ep-page">
    <div class="ep-header">
        <div>
            <h1>Employee Profile</h1>
            <p>Complete employee information and employment history.</p>
        </div>
        <a href="{{ route('employee-profiles.index') }}" class="ep-back">Back to Directory</a>
    </div>

    <div class="ep-profile-card">
        <div class="ep-avatar">
            {{ strtoupper(substr($staff->first_name, 0, 1)) }}{{ strtoupper(substr($staff->last_name, 0, 1)) }}
        </div>
        <div class="ep-identity">
            <h2>{{ $staff->full_name }}</h2>
            <p>{{ $staff->employee_number }}</p>
            <span class="ep-status {{ $staff->status === 'active' ? 'active' : 'inactive' }}">
                {{ ucfirst($staff->status) }}
            </span>
        </div>
        <div class="ep-identity-extra">
            <span>Designation</span>
            <strong>{{ $staff->designation ?: 'Not specified' }}</strong>
            <span>Department</span>
            <strong>{{ $staff->department ?: 'Not specified' }}</strong>
        </div>
    </div>

    <div class="ep-grid">
        <section class="ep-card">
            <h3>Personal Information</h3>
            <div class="ep-details">
                <div><span>First Name</span><strong>{{ $staff->first_name }}</strong></div>
                <div><span>Middle Name</span><strong>{{ $staff->middle_name ?: '—' }}</strong></div>
                <div><span>Last Name</span><strong>{{ $staff->last_name }}</strong></div>
                <div><span>Date of Birth</span><strong>{{ $staff->userProfile?->date_of_birth?->format('d M Y') ?? '—' }}</strong></div>
                <div><span>Gender</span><strong>{{ $staff->userProfile?->gender ? ucfirst($staff->userProfile->gender) : '—' }}</strong></div>
            </div>
        </section>

        <section class="ep-card">
            <h3>Contact Information</h3>
            <div class="ep-details">
                <div><span>Phone</span><strong>{{ $staff->phone ?: $staff->userProfile?->phone ?: '—' }}</strong></div>
                <div><span>Email</span><strong>{{ $staff->user?->email ?? '—' }}</strong></div>
                <div><span>Address</span><strong>{{ $staff->userProfile?->address_line ?? '—' }}</strong></div>
                <div><span>City</span><strong>{{ $staff->userProfile?->city ?? '—' }}</strong></div>
                <div><span>State</span><strong>{{ $staff->userProfile?->state ?? '—' }}</strong></div>
                <div><span>Postal Code</span><strong>{{ $staff->userProfile?->postal_code ?? '—' }}</strong></div>
            </div>
        </section>

        <section class="ep-card">
            <h3>Employment Details</h3>
            <div class="ep-details">
                <div><span>Employee Number</span><strong>{{ $staff->employee_number }}</strong></div>
                <div><span>Employment Type</span><strong>{{ $staff->employment_type ? ucwords(str_replace('-', ' ', $staff->employment_type)) : '—' }}</strong></div>
                <div><span>Joining Date</span><strong>{{ $staff->joining_date?->format('d M Y') ?? '—' }}</strong></div>
                <div><span>Designation</span><strong>{{ $staff->designation ?: '—' }}</strong></div>
                <div><span>Department</span><strong>{{ $staff->department ?: '—' }}</strong></div>
                <div><span>Status</span><strong>{{ ucfirst($staff->status) }}</strong></div>
            </div>
        </section>

        <section class="ep-card">
            <h3>School & Campus</h3>
            <div class="ep-details">
                <div><span>School</span><strong>{{ $staff->school?->name ?? '—' }}</strong></div>
                <div><span>Campus</span><strong>{{ $staff->campus?->name ?? '—' }}</strong></div>
            </div>
        </section>
    </div>

    <section class="ep-card ep-section">
        <h3>Teaching Assignments</h3>
        @if($staff->teachingAssignments->isNotEmpty())
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead>
                        <tr><th>Subject</th><th>Class</th><th>Section</th><th>Academic Year</th></tr>
                    </thead>
                    <tbody>
                        @foreach($staff->teachingAssignments as $assignment)
                            <tr>
                                <td>{{ $assignment->classSubject?->subject?->name ?? '—' }}</td>
                                <td>{{ $assignment->classSubject?->schoolClass?->name ?? '—' }}</td>
                                <td>{{ $assignment->section?->name ?? '—' }}</td>
                                <td>{{ $assignment->academicYear?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ep-muted">No teaching assignments available.</p>
        @endif
    </section>

    <section class="ep-card ep-section">
        <h3>Attendance Overview</h3>
        <div class="ep-mini-stats">
            <div><span>Total Records</span><strong>{{ $attendanceStats['total'] }}</strong></div>
            <div><span>Present</span><strong>{{ $attendanceStats['present'] }}</strong></div>
            <div><span>Absent</span><strong>{{ $attendanceStats['absent'] }}</strong></div>
            <div><span>Late</span><strong>{{ $attendanceStats['late'] }}</strong></div>
        </div>

        <h4>Recent Attendance</h4>
        @if($staff->attendanceRecords->isNotEmpty())
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Remarks</th></tr></thead>
                    <tbody>
                        @foreach($staff->attendanceRecords as $record)
                            <tr>
                                <td>{{ $record->attendance_date?->format('d M Y') }}</td>
                                <td>{{ $record->check_in?->format('h:i A') ?? '—' }}</td>
                                <td>{{ $record->check_out?->format('h:i A') ?? '—' }}</td>
                                <td>{{ ucfirst($record->status) }}</td>
                                <td>{{ $record->remarks ?: '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ep-muted">No attendance records available.</p>
        @endif
    </section>

    <section class="ep-card ep-section">
        <h3>Leave Information</h3>
        <h4>Leave Balances</h4>
        @if($staff->leaveBalances->isNotEmpty())
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead><tr><th>Leave Type</th><th>Academic Year</th><th>Allocated</th><th>Used</th><th>Adjusted</th></tr></thead>
                    <tbody>
                        @foreach($staff->leaveBalances as $balance)
                            <tr>
                                <td>{{ $balance->leaveType?->name ?? '—' }}</td>
                                <td>{{ $balance->academicYear?->name ?? '—' }}</td>
                                <td>{{ $balance->allocated_days }}</td>
                                <td>{{ $balance->used_days }}</td>
                                <td>{{ $balance->adjusted_days }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ep-muted">No leave balances available.</p>
        @endif

        <h4>Recent Leave Requests</h4>
        @if($staff->leaveRequests->isNotEmpty())
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead><tr><th>Leave Type</th><th>Start</th><th>End</th><th>Days</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($staff->leaveRequests as $leave)
                            <tr>
                                <td>{{ $leave->leaveType?->name ?? '—' }}</td>
                                <td>{{ $leave->start_date?->format('d M Y') }}</td>
                                <td>{{ $leave->end_date?->format('d M Y') }}</td>
                                <td>{{ $leave->total_days }}</td>
                                <td>{{ ucfirst($leave->status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ep-muted">No leave requests available.</p>
        @endif
    </section>

    <section class="ep-card ep-section">
        <h3>Payroll History</h3>
        @if($staff->payrollRecords->isNotEmpty())
            <div class="ep-table-wrap">
                <table class="ep-table">
                    <thead><tr><th>Payroll Period</th><th>Gross Salary</th><th>Deductions</th><th>Net Salary</th><th>Payment Status</th></tr></thead>
                    <tbody>
                        @foreach($staff->payrollRecords as $payroll)
                            <tr>
                                <td>{{ $payroll->payrollPeriod?->name ?? '—' }}</td>
                                <td>{{ number_format((float) $payroll->gross_salary, 2) }}</td>
                                <td>{{ number_format((float) $payroll->deductions, 2) }}</td>
                                <td>{{ number_format((float) $payroll->net_salary, 2) }}</td>
                                <td>{{ ucfirst($payroll->payment_status) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="ep-muted">No payroll records available.</p>
        @endif
    </section>

    <section class="ep-card ep-section">
        <h3>Account Information</h3>
        <div class="ep-details">
            <div><span>Linked User Account</span><strong>{{ $staff->user ? 'Linked' : 'Not linked' }}</strong></div>
            <div><span>Account Email</span><strong>{{ $staff->user?->email ?? '—' }}</strong></div>
            <div><span>Employee Code</span><strong>{{ $staff->userProfile?->employee_code ?? '—' }}</strong></div>
        </div>
    </section>
</div>

<style>
.ep-page{font-family:Inter,sans-serif;color:#25313C}
.ep-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.ep-header h1{font-size:23px;color:#123B5D;margin:0;font-weight:700}
.ep-header p{font-size:13px;color:#64748B;margin:6px 0 0}
/* .ep-back{color:#176B87;text-decoration:none;font-size:13px;font-weight:600} */
.ep-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 38px;
    padding: 0 16px;
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    color: #123B5D;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.ep-back:hover {
    background: #F8FAFC;
    border-color: #123B5D;
}
.ep-profile-card,.ep-card{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;margin-bottom:17px}
.ep-profile-card{display:flex;align-items:center;gap:17px}
.ep-avatar{height:62px;width:62px;border-radius:16px;background:#E0F2FE;color:#123B5D;display:flex;align-items:center;justify-content:center;font-size:21px;font-weight:700;flex-shrink:0}
.ep-identity{flex:1}
.ep-identity h2{font-size:19px;margin:0 0 5px;color:#123B5D}
.ep-identity p{font-size:12px;color:#64748B;margin:0 0 8px}
.ep-identity-extra{display:grid;grid-template-columns:1fr 1fr;gap:5px 18px;font-size:12px}
.ep-identity-extra span,.ep-details span,.ep-mini-stats span{color:#64748B;font-size:11px}
.ep-identity-extra strong{font-size:12px}
.ep-status{display:inline-block;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:600}
.ep-status.active{background:#DCFCE7;color:#15803D}
.ep-status.inactive{background:#FEE2E2;color:#B91C1C}
.ep-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.ep-card h3{font-size:15px;color:#123B5D;margin:0 0 17px;font-weight:700}
.ep-card h4{font-size:13px;color:#25313C;margin:20px 0 10px}
.ep-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px 14px}
.ep-details div{min-width:0}
.ep-details span,.ep-details strong{display:block}
.ep-details strong{font-size:12px;margin-top:5px;overflow-wrap:anywhere}
.ep-section{margin-top:2px}
.ep-mini-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.ep-mini-stats div{padding:13px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px}
.ep-mini-stats span,.ep-mini-stats strong{display:block}
.ep-mini-stats strong{font-size:20px;color:#123B5D;margin-top:5px}
.ep-table-wrap{overflow-x:auto}
.ep-table{width:100%;border-collapse:collapse;white-space:nowrap}
.ep-table th{text-align:left;padding:12px;background:#F8FAFC;color:#64748B;font-size:10px;text-transform:uppercase}
.ep-table td{padding:13px 12px;border-bottom:1px solid #E2E8F0;font-size:12px}
.ep-muted{font-size:12px;color:#64748B;margin:0}
@media(max-width:750px){.ep-grid{grid-template-columns:1fr}.ep-profile-card{flex-wrap:wrap}.ep-identity-extra{width:100%}.ep-mini-stats{grid-template-columns:repeat(2,1fr)}}
</style>
@endsection
