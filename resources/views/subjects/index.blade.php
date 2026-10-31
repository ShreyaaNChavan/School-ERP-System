
@extends('layouts.schoolnexa')

@section('content')
<div style="max-width:1400px;margin:auto;">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:24px;">
        <div>
            <h2 style="font-size:22px;font-weight:700;color:#123B5D;margin:0;">Subject Management</h2>
            <p style="font-size:13px;color:#64748B;margin:6px 0 0;">Manage academic subjects and class-wise assignments</p>
        </div>

        @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
            <a href="{{ route('subjects.create') }}" class="sub-primary">+ Add Subject</a>
        @endif
    </div>

    @if(session('success'))
        <div class="sub-alert sub-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="sub-alert sub-danger">{{ session('error') }}</div>
    @endif

    <div class="sub-stats">
        <div class="sub-stat">
            <span>Total Subjects</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="sub-stat">
            <span>Active Subjects</span>
            <strong>{{ $stats['active'] }}</strong>
        </div>
        <div class="sub-stat">
            <span>Inactive Subjects</span>
            <strong>{{ $stats['inactive'] }}</strong>
        </div>
        <div class="sub-stat">
            <span>Assigned Subjects</span>
            <strong>{{ $stats['assigned'] }}</strong>
        </div>
    </div>

    <div class="sub-panel">

        <form method="GET" action="{{ route('subjects.index') }}" class="sub-filters">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search subject name or code...">

            <select name="school_id">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">All Status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="sub-filter-btn">Apply Filters</button>
            <a href="{{ route('subjects.index') }}" class="sub-reset-btn">Reset</a>
        </form>

        <div class="sub-table-wrap">
            <table class="sub-table">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Code</th>
                        <th>School</th>
                        <th>Type</th>
                        <th>Assigned Classes</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($subjects as $subject)
                        <tr>
                            <td>
                                <strong>{{ $subject->name }}</strong>
                                <small>Subject ID: {{ $subject->id }}</small>
                            </td>
                            <td>{{ $subject->code ?: '—' }}</td>
                            <td>{{ $subject->school->name ?? '—' }}</td>
                            <td>
                                <span class="sub-type">
                                    {{ ucwords(str_replace('_',' ',$subject->subject_type)) }}
                                </span>
                            </td>
                            <td>{{ $subject->classSubjects->count() }}</td>
                            <td>
                                <span class="sub-status {{ $subject->is_active ? 'active' : 'inactive' }}">
                                    {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="sub-actions">
                                    <a href="{{ route('subjects.show',$subject) }}">View</a>

                                    @if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                        <a href="{{ route('subjects.edit',$subject) }}">Edit</a>
                                    @endif

                                    @if(auth()->user()->hasPermission('ACADEMICS.DELETE') && $subject->classSubjects->isEmpty())
                                        <form method="POST" action="{{ route('subjects.destroy',$subject) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this subject?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="sub-empty">
                                No subjects found. Add your first subject to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sub-pagination">
            {{ $subjects->links() }}
        </div>
    </div>
</div>

<style>
.sub-stats {
    display:grid;grid-template-columns:repeat(4,minmax(0,1fr));
    gap:16px;margin-bottom:22px;
}
.sub-stat {
    background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:19px;
}
.sub-stat span {display:block;color:#64748B;font-size:12px;font-weight:500;}
.sub-stat strong {display:block;color:#123B5D;font-size:25px;margin-top:8px;}
.sub-panel {background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;}
.sub-filters {display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
.sub-filters input,.sub-filters select {
    height:39px;border:1px solid #CBD5E1;border-radius:9px;
    padding:0 12px;font-size:13px;color:#25313C;background:#fff;
}
.sub-filters input {flex:1;min-width:200px;}
.sub-filters select {min-width:145px;}
.sub-filter-btn,.sub-reset-btn,.sub-primary {
    display:inline-flex;align-items:center;justify-content:center;
    height:39px;padding:0 16px;border-radius:9px;font-size:13px;
    font-weight:600;text-decoration:none;cursor:pointer;
}
.sub-filter-btn,.sub-primary {background:#123B5D;color:#fff;border:1px solid #123B5D;}
.sub-reset-btn {background:#fff;border:1px solid #CBD5E1;color:#123B5D;}
.sub-table-wrap {overflow-x:auto;}
.sub-table {width:100%;border-collapse:collapse;min-width:850px;}
.sub-table th {
    background:#F8FAFC;color:#64748B;text-align:left;font-size:11px;
    text-transform:uppercase;letter-spacing:.4px;padding:13px 12px;
    border-bottom:1px solid #CBD5E1;
}
.sub-table td {padding:15px 12px;border-bottom:1px solid #E2E8F0;font-size:13px;color:#25313C;}
.sub-table td strong {display:block;color:#123B5D;font-size:13px;}
.sub-table td small {display:block;color:#94A3B8;font-size:11px;margin-top:4px;}
.sub-type {background:#F1F5F9;color:#475569;padding:5px 9px;border-radius:6px;font-size:11px;}
.sub-status {padding:5px 9px;border-radius:20px;font-size:11px;font-weight:600;}
.sub-status.active {background:#DCFCE7;color:#166534;}
.sub-status.inactive {background:#FEE2E2;color:#991B1B;}
.sub-actions {display:flex;align-items:center;gap:12px;white-space:nowrap;}
.sub-actions a,.sub-actions button {
    color:#176B87;text-decoration:none;font-size:12px;font-weight:600;
    background:none;border:0;padding:0;cursor:pointer;
}
.sub-actions form {margin:0;}
.sub-actions button {color:#DC2626;}
.sub-empty {text-align:center;padding:35px!important;color:#64748B!important;}
.sub-pagination {margin-top:18px;}
.sub-alert {padding:12px 15px;border-radius:9px;margin-bottom:18px;font-size:13px;}
.sub-success {background:#DCFCE7;color:#166534;}
.sub-danger {background:#FEE2E2;color:#991B1B;}
@media(max-width:800px) {.sub-stats{grid-template-columns:repeat(2,minmax(0,1fr));}}
@media(max-width:480px) {.sub-stats{grid-template-columns:1fr;}}
</style>
@endsection
