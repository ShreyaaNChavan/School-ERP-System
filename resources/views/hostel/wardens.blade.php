@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Wardens</div><div class="hst-sub">Hostel administration and records</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="POST" action="{{ route('hostel.wardens.store') }}">@csrf<div class="hst-form"><div class="hst-field"><label>Hostel</label><select class="hst-input" name="hostel_id">@foreach($hs as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach</select></div><div class="hst-field"><label>Staff profile (optional)</label><select class="hst-input" name="staff_profile_id"><option value="">Not linked</option>@foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->employee_number }} · {{ $s->first_name }} {{ $s->last_name }}</option>@endforeach</select></div><div class="hst-field"><label>Name</label><input class="hst-input" name="name" required></div><div class="hst-field"><label>Phone</label><input class="hst-input" name="phone"></div><div class="hst-field"><label>Email</label><input class="hst-input" name="email" type="email"></div><div class="hst-field"><label>Shift</label><select class="hst-input" name="shift"><option>general</option><option>morning</option><option>evening</option><option>night</option></select></div></div><button class="hst-btn hst-primary" style="margin-top:12px">Assign warden</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Name</th><th>Hostel</th><th>Phone</th><th>Email</th><th>Shift</th><th>Status</th></tr>@forelse($wardens as $w)<tr><td>{{ $w->name }}</td><td>{{ $w->hostel_id }}</td><td>{{ $w->phone }}</td><td>{{ $w->email }}</td><td>{{ $w->shift }}</td><td>{{ $w->status }}</td></tr>@empty<tr><td colspan="6">No wardens.</td></tr>@endforelse</table>{{ $wardens->links() }}</div>

</div>
@endsection
