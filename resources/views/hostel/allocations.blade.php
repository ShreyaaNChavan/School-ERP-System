@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Allocations</div><div class="hst-sub">Hostel administration and records</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="POST" action="{{ route('hostel.allocations.store') }}">@csrf<div class="hst-form"><div class="hst-field"><label>Student</label><select class="hst-input" name="student_id" required>@foreach($students as $s)<option value="{{ $s->id }}">#{{ $s->id }} · {{ $s->full_name ?? trim(($s->first_name ?? '').' '.($s->last_name ?? '')) }}</option>@endforeach</select></div><div class="hst-field"><label>Available bed</label><select class="hst-input" name="hostel_bed_id" required>@foreach($beds as $b)<option value="{{ $b->id }}">Bed {{ $b->bed_number }} · Room {{ $b->hostel_room_id }}</option>@endforeach</select></div><div class="hst-field"><label>Academic year</label><select class="hst-input" name="academic_year_id" required>@foreach($years as $y)<option value="{{ $y->id }}">{{ $y->name ?? $y->year_name ?? ('Year '.$y->id) }}</option>@endforeach</select></div><div class="hst-field"><label>Start date</label><input class="hst-input" type="date" name="start_date" value="{{ today()->toDateString() }}" required></div></div><button class="hst-btn hst-primary" style="margin-top:12px">Allocate bed</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Student ID</th><th>Bed ID</th><th>Academic year</th><th>Start</th><th>End</th><th>Status / action</th></tr>@forelse($allocations as $a)<tr><td>{{ $a->student_id }}</td><td>{{ $a->hostel_bed_id }}</td><td>{{ $a->academic_year_id }}</td><td>{{ $a->start_date }}</td><td>{{ $a->end_date }}</td><td>{{ $a->status }} @if($a->status==='active')<form method="POST" action="{{ route('hostel.allocations.close',$a->id) }}" style="display:inline">@csrf @method('PATCH')<button class="hst-btn">Release</button></form>@endif</td></tr>@empty<tr><td colspan="6">No allocations.</td></tr>@endforelse</table>{{ $allocations->links() }}</div>

</div>
@endsection
