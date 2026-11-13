@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>Approval Request Details</h1><div class="ac-sub">Review request information, history and take an action</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><div style="display:flex;justify-content:space-between;gap:15px;flex-wrap:wrap"><div><div class="ac-muted">{{ $approval->request_number }}</div><h2 style="color:#123b5d;margin:5px 0">{{ $approval->title }}</h2><span class="ac-badge {{ $approval->status }}">{{ ucfirst($approval->status) }}</span></div><div><strong>Priority:</strong> {{ ucfirst($approval->priority) }}</div></div>
<hr style="border:0;border-top:1px solid #e2e8f0;margin:16px 0">
<div class="ac-form"><div><span class="ac-muted">Request Type</span><p>{{ $approval->request_type }}</p></div><div><span class="ac-muted">Department</span><p>{{ $approval->department ?: '—' }}</p></div><div><span class="ac-muted">Submitted By</span><p>{{ $approval->submitted_name }}</p></div><div><span class="ac-muted">Submitted On</span><p>{{ \Carbon\Carbon::parse($approval->created_at)->format('d M Y, h:i A') }}</p></div><div style="grid-column:1/-1"><span class="ac-muted">Description</span><p style="white-space:pre-wrap">{{ $approval->description }}</p></div></div></div>
<div class="ac-panel"><h3>Approval Progress</h3><div class="ac-flow"><div class="ac-node done"><strong>Submitted</strong><small>Received</small></div><div class="ac-arrow done"></div><div class="ac-node {{ $approval->status==='pending'?'active':($approval->status==='approved'?'done':'wait') }}"><strong>Review</strong><small>{{ ucfirst($approval->status) }}</small></div><div class="ac-arrow {{ $approval->status==='approved'?'done':'' }}"></div><div class="ac-node {{ $approval->status==='approved'?'done':'wait' }}"><strong>Completed</strong><small>Final state</small></div></div></div>
@if($approval->status==='pending')<div class="ac-panel"><h3>Record Decision</h3><form method="POST" action="{{ route('approvals.decide',$approval->id) }}">@csrf<div class="ac-form"><div class="ac-field"><label>Decision</label><select name="decision" required><option value="approved">Approve</option><option value="rejected">Reject</option><option value="returned">Return for Correction</option><option value="forwarded">Forward to Another Approver</option></select></div><div class="ac-field"><label>Forward to User ID (if forwarding)</label><input type="number" name="assigned_to" min="1"></div><div class="ac-field" style="grid-column:1/-1"><label>Remarks</label><textarea name="remarks"></textarea></div></div><button class="ac-btn primary" style="margin-top:13px">Save Decision</button></form></div>@endif
<div class="ac-panel"><h3>Approval Timeline</h3><div class="ac-timeline">@forelse($actions as $a)<div class="ac-event"><strong>{{ ucfirst($a->action) }}</strong><div class="ac-muted">{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }} · User #{{ $a->user_id }}</div><p>{{ $a->remarks ?: 'No remarks added.' }}</p></div>@empty<div class="ac-muted">No actions recorded.</div>@endforelse</div></div>

</div>
@endsection
