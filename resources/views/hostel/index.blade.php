@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Hostel List</div><div class="hst-sub">Register and manage hostel buildings</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="POST" action="{{ route('hostel.store') }}">@csrf<div class="hst-form"><div class="hst-field"><label>Name</label><input class="hst-input" name="name" required></div><div class="hst-field"><label>Type</label><select class="hst-input" name="hostel_type"><option>boys</option><option>girls</option><option>mixed</option></select></div><div class="hst-field"><label>Warden name</label><input class="hst-input" name="warden_name"></div><div class="hst-field"><label>Warden phone</label><input class="hst-input" name="warden_phone"></div><div class="hst-field"><label>Address</label><input class="hst-input" name="address"></div></div><button class="hst-btn hst-primary" style="margin-top:12px">Save hostel</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Hostel</th><th>Type</th><th>Warden</th><th>Phone</th></tr>@forelse($hostels as $h)<tr><td>{{ $h->name }}</td><td>{{ $h->hostel_type }}</td><td>{{ $h->warden_name }}</td><td>{{ $h->warden_phone }}</td></tr>@empty<tr><td colspan="4">No hostels.</td></tr>@endforelse</table>{{ $hostels->links() }}</div>

</div>
@endsection
