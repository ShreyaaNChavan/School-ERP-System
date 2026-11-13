@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>Approval Delegations</h1><div class="ac-sub">Temporarily delegate approval responsibilities</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><h3>Create Delegation</h3><form method="POST" action="{{ route('approvals.delegations.store') }}">@csrf<div class="ac-form">
<div class="ac-field"><label>Delegate User ID</label><input type="number" min="1" name="delegate_user_id" required></div>
<div class="ac-field"><label>Reason</label><input name="reason" maxlength="500"></div>
<div class="ac-field"><label>Starts At</label><input type="datetime-local" name="starts_at" required></div>
<div class="ac-field"><label>Ends At</label><input type="datetime-local" name="ends_at" required></div>
</div><button class="ac-btn primary" style="margin-top:13px">Save Delegation</button></form></div>
<div class="ac-panel"><h3>Existing Delegations</h3><div style="overflow:auto"><table class="ac-table"><thead><tr><th>Delegate User</th><th>Starts</th><th>Ends</th><th>Reason</th></tr></thead><tbody>@forelse($delegations as $d)<tr><td>#{{ $d->delegate_user_id }}</td><td>{{ $d->starts_at }}</td><td>{{ $d->ends_at }}</td><td>{{ $d->reason ?: '—' }}</td></tr>@empty<tr><td colspan="4">No delegations configured.</td></tr>@endforelse</tbody></table></div>{{ $delegations->links() }}</div>

</div>
@endsection
