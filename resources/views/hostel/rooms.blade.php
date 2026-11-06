@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Hostel Rooms</div><div class="hst-sub">Manage rooms and beds</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="POST" action="{{ route('hostel.rooms.store') }}">@csrf<div class="hst-form"><div class="hst-field"><label>Hostel</label><select class="hst-input" name="hostel_id">@foreach($hs as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach</select></div><div class="hst-field"><label>Room number</label><input class="hst-input" name="room_number" required></div><div class="hst-field"><label>Capacity</label><input class="hst-input" type="number" name="capacity" value="2" min="1"></div><div class="hst-field"><label>Room type</label><input class="hst-input" name="room_type"></div><div class="hst-field"><label>Fee per term</label><input class="hst-input" name="fee_per_term" type="number" step=".01" value="0"></div></div><button class="hst-btn hst-primary" style="margin-top:12px">Add room</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Hostel</th><th>Room</th><th>Type</th><th>Capacity</th><th>Fee</th><th>Add bed</th></tr>@forelse($rooms as $r)<tr><td>{{ $r->hostel_name }}</td><td>{{ $r->room_number }}</td><td>{{ $r->room_type }}</td><td>{{ $r->capacity }}</td><td>{{ $r->fee_per_term }}</td><td><form method="POST" action="{{ route('hostel.beds.store',$r->id) }}">@csrf<input class="hst-input" style="width:90px;display:inline" name="bed_number" placeholder="Bed no." required><button class="hst-btn">Add</button></form></td></tr>@empty<tr><td colspan="6">No rooms.</td></tr>@endforelse</table>{{ $rooms->links() }}</div>

</div>
@endsection
