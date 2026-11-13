@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>All Approval Requests</h1><div class="ac-sub">Search and manage requests submitted across the school</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><form method="GET" class="ac-form">
<div class="ac-field"><label>Search</label><input name="q" value="{{ request('q') }}" placeholder="Request ID, title or applicant"></div>
<div class="ac-field"><label>Status</label><select name="status"><option value="">All statuses</option>@foreach(['pending','approved','rejected','returned'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
<div class="ac-field"><label>Request Type</label><input name="type" value="{{ request('type') }}" placeholder="e.g. Purchase"></div>
<div class="ac-field"><label>Priority</label><select name="priority"><option value="">All priorities</option>@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
<div><button class="ac-btn primary">Apply Filters</button> <a class="ac-btn" href="{{ route('approvals.requests') }}">Reset</a></div>
</form></div>
<div class="ac-panel"><div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h3>Requests</h3><a class="ac-btn primary" href="{{ route('approvals.create') }}">New Request</a></div><div style="overflow:auto"><table class="ac-table"><thead><tr><th>Request ID</th><th>Title</th><th>Type</th><th>Department</th><th>Submitted By</th><th>Due Date</th><th>Priority</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($requests as $r)<tr><td>{{ $r->request_number }}</td><td>{{ $r->title }}</td><td>{{ $r->request_type }}</td><td>{{ $r->department ?: '—' }}</td><td>{{ $r->submitted_name }}</td><td>{{ $r->due_at ? \Carbon\Carbon::parse($r->due_at)->format('d M Y') : '—' }}</td><td>{{ ucfirst($r->priority) }}</td><td><span class="ac-badge {{ $r->status }}">{{ ucfirst($r->status) }}</span></td><td><a class="ac-btn" href="{{ route('approvals.show',$r->id) }}">View</a></td></tr>@empty<tr><td colspan="9">No matching requests.</td></tr>@endforelse
</tbody></table></div>{{ $requests->links() }}</div>

</div>
@endsection
