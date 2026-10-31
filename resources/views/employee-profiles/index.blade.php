
@extends('layouts.schoolnexa')

@section('content')
<div class="ep-page">
    <div class="ep-header">
        <div>
            <h1>Employee Directory</h1>
            <p>View employee records and access detailed employee profiles.</p>
        </div>
    </div>

    <div class="ep-stats">
        <div class="ep-stat">
            <span>Total Employees</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="ep-stat">
            <span>Active Employees</span>
            <strong class="ep-green">{{ $stats['active'] }}</strong>
        </div>
        <div class="ep-stat">
            <span>Inactive Employees</span>
            <strong class="ep-orange">{{ $stats['inactive'] }}</strong>
        </div>
    </div>

    <div class="ep-panel">
        <form method="GET" action="{{ route('employee-profiles.index') }}" class="ep-filters">
            <input type="text" name="search"
                   placeholder="Search employee name, number or phone..."
                   value="{{ request('search') }}">

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="ep-button">Search</button>
            <a href="{{ route('employee-profiles.index') }}" class="ep-reset">Reset</a>
        </form>

        <div class="ep-table-wrap">
            <table class="ep-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee Number</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <th>School / Campus</th>
                        <th>Employment Type</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <strong>{{ $employee->full_name }}</strong>
                                <small>{{ $employee->phone ?: 'No phone provided' }}</small>
                            </td>
                            <td>{{ $employee->employee_number }}</td>
                            <td>{{ $employee->designation ?: '—' }}</td>
                            <td>{{ $employee->department ?: '—' }}</td>
                            <td>
                                {{ $employee->school?->name ?? '—' }}
                                <small>{{ $employee->campus?->name ?? 'No campus' }}</small>
                            </td>
                            <td>{{ $employee->employment_type ? ucwords(str_replace('-', ' ', $employee->employment_type)) : '—' }}</td>
                            <td>
                                <span class="ep-status {{ $employee->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('employee-profiles.show', $employee) }}" class="ep-view">
                                    View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="ep-empty">
                                No employee records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ep-pagination">
            {{ $employees->links() }}
        </div>
    </div>
</div>

<style>
.ep-page{color:#25313C;font-family:Inter,sans-serif}
.ep-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
.ep-header h1{font-size:23px;font-weight:700;margin:0;color:#123B5D}
.ep-header p{margin:6px 0 0;color:#64748B;font-size:13px}
.ep-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:15px;margin-bottom:20px}
.ep-stat,.ep-panel{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:19px}
.ep-stat span{display:block;font-size:12px;color:#64748B;margin-bottom:9px}
.ep-stat strong{font-size:25px;color:#123B5D}
.ep-green{color:#16A34A!important}.ep-orange{color:#D97706!important}
.ep-filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.ep-filters input,.ep-filters select{height:41px;border:1px solid #CBD5E1;border-radius:9px;padding:0 12px;font-size:13px;color:#25313C;background:#fff}
.ep-filters input{flex:1;min-width:220px}
.ep-button{background:#123B5D;color:#fff;border:0;border-radius:9px;padding:0 20px;height:41px;font-weight:600;cursor:pointer}
.ep-reset{display:flex;align-items:center;padding:0 12px;color:#64748B;text-decoration:none;font-size:13px}
.ep-table-wrap{overflow-x:auto}
.ep-table{width:100%;border-collapse:collapse;white-space:nowrap}
.ep-table th{text-align:left;padding:13px 12px;background:#F8FAFC;color:#64748B;font-size:11px;text-transform:uppercase;letter-spacing:.04em}
.ep-table td{padding:15px 12px;border-bottom:1px solid #E2E8F0;font-size:13px}
.ep-table td strong,.ep-table td small{display:block}
.ep-table td small{font-size:11px;color:#64748B;margin-top:4px}
.ep-status{display:inline-block;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:600}
.ep-status.active{background:#DCFCE7;color:#15803D}
.ep-status.inactive{background:#FEE2E2;color:#B91C1C}
.ep-view{color:#176B87;text-decoration:none;font-weight:600}
.ep-empty{text-align:center;color:#64748B;padding:35px!important}
.ep-pagination{margin-top:18px}
@media(max-width:700px){.ep-stats{grid-template-columns:1fr}.ep-header{align-items:flex-start}}
</style>
@endsection
