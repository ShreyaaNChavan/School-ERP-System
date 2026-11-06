@extends('layouts.schoolnexa')
@section('content')
@include('hostel._style')
<div class="hst"><div class="hst-head"><div><div class="hst-title">Attendance</div><div class="hst-sub">Hostel administration and records</div></div><a class="hst-btn" href="{{ route('hostel.dashboard') }}">Hostel Dashboard</a></div>
@if(session('success'))<div class="hst-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="hst-card hst-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="hst-card"><form method="GET" action="{{ route('hostel.attendance') }}" style="display:flex;gap:10px;align-items:end"><div class="hst-field"><label>Date</label><input class="hst-input" type="date" name="date" value="{{ request('date',today()->toDateString()) }}"></div><button class="hst-btn">View</button></form></div>
<div class="hst-card"><table class="hst-table"><tr><th>Student ID</th><th>Student</th><th>Attendance</th></tr>@forelse($students as $s)@php($rec=$records->firstWhere('student_id',$s->id))<tr><td>{{ $s->id }}</td><td>{{ $s->full_name ?? trim(($s->first_name ?? '').' '.($s->last_name ?? '')) }}</td><td><form method="POST" action="{{ route('hostel.attendance.store') }}" style="display:flex;gap:7px;flex-wrap:wrap">@csrf<input type="hidden" name="student_id" value="{{ $s->id }}"><input type="hidden" name="attendance_date" value="{{ request('date',today()->toDateString()) }}"><select class="hst-input" style="width:140px" name="status">@foreach(['present','absent','leave','outing','medical','late_return'] as $st)<option value="{{ $st }}" @selected(($rec->status??'present')===$st)>{{ ucfirst(str_replace('_',' ',$st)) }}</option>@endforeach</select><input class="hst-input" style="width:115px" type="time" name="check_in_time" value="{{ $rec->check_in_time??'' }}"><input class="hst-input" style="width:115px" type="time" name="check_out_time" value="{{ $rec->check_out_time??'' }}"><button class="hst-btn hst-primary">Save</button></form></td></tr>@empty<tr><td colspan="3">No allocated hostel residents. Allocate students first.</td></tr>@endforelse</table></div>

</div>
@endsection
