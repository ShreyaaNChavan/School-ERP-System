@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>My Approvals</h1><div class="ac-sub">Requests currently assigned to your account</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><h3>Awaiting My Decision</h3><div style="overflow:auto"><table class="ac-table"><thead><tr><th>Request ID</th><th>Title</th><th>Type</th><th>Submitted By</th><th>Priority</th><th>Action</th></tr></thead><tbody>@forelse($requests as $r)<tr><td>{{ $r->request_number }}</td><td>{{ $r->title }}</td><td>{{ $r->request_type }}</td><td>{{ $r->submitted_name }}</td><td>{{ ucfirst($r->priority) }}</td><td><a class="ac-btn" href="{{ route('approvals.show',$r->id) }}">Review</a></td></tr>@empty<tr><td colspan="6">No approvals are currently assigned to you.</td></tr>@endforelse</tbody></table></div>{{ $requests->links() }}</div>

</div>
@endsection
