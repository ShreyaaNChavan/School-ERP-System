@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Room Types</div><div class="hst-sub">Configure room categories and default rates</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="POST" action="{{ route('hostel.room-types.store') }}">@csrf<div class="hst-form"><div class="hst-field"><label>Name</label><input class="hst-input" name="name" required></div><div class="hst-field"><label>Capacity</label><input class="hst-input" type="number" name="default_capacity" value="2"></div><div class="hst-field"><label>Fee per term</label><input class="hst-input" type="number" step=".01" name="default_fee_per_term" value="0"></div><div class="hst-field"><label>Description</label><input class="hst-input" name="description"></div></div><button class="hst-btn hst-primary" style="margin-top:12px">Create type</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Name</th><th>Capacity</th><th>Fee</th><th>Description</th></tr>@forelse($types as $t)<tr><td>{{ $t->name }}</td><td>{{ $t->default_capacity }}</td><td>{{ $t->default_fee_per_term }}</td><td>{{ $t->description }}</td></tr>@empty<tr><td colspan="4">No room types.</td></tr>@endforelse</table>{{ $types->links() }}</div>

</div>
@endsection
