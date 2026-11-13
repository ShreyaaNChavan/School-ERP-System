@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>Approval History</h1><div class="ac-sub">Immutable action trail for requests in this school</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><h3>Decision History</h3><div style="overflow:auto"><table class="ac-table"><thead><tr><th>Date & Time</th><th>Request</th><th>Title</th><th>Action</th><th>User</th><th>Remarks</th></tr></thead><tbody>@forelse($actions as $a)<tr><td>{{ \Carbon\Carbon::parse($a->created_at)->format('d M Y, h:i A') }}</td><td>{{ $a->request_number }}</td><td>{{ $a->title }}</td><td>{{ ucfirst($a->action) }}</td><td>#{{ $a->user_id }}</td><td>{{ $a->remarks ?: '—' }}</td></tr>@empty<tr><td colspan="6">No approval history found.</td></tr>@endforelse</tbody></table></div>{{ $actions->links() }}</div>

</div>
@endsection
