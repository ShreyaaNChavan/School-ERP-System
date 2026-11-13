@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Add Club Record</h1><div class="sn-sub">Enter the details below</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('clubs.index') }}">← Back to List</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('clubs.store') }}">@csrf <div class="sn-form"><div class="sn-field"><label>Name</label><input type="text" name="name" value="{{ old('name') }}"></div>
<div class="sn-field"><label>Category</label><input type="text" name="category" value="{{ old('category') }}"></div>
<div class="sn-field sn-span"><label>Description</label><textarea name="description">{{ old('description') }}</textarea></div>
<div class="sn-field"><label>Campus</label><select name="campus_id"><option value="">All campuses</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}" @selected(old('campus_id')==$campus->id)>{{ $campus->name }}</option>@endforeach</select></div>
<div class="sn-field"><label>Faculty coordinator</label><select name="faculty_coordinator_id"><option value="">Select coordinator</option>@foreach($staff as $person)<option value="{{ $person->id }}" @selected(old('faculty_coordinator_id')==$person->id)>{{ $person->first_name }} {{ $person->last_name }}</option>@endforeach</select></div>
<div class="sn-field"><label>Founded On</label><input type="date" name="founded_on" value="{{ old('founded_on') }}"></div>
<div class="sn-field"><label>Status</label><select name="status">@foreach(['active', 'inactive'] as $opt)<option value="{{ $opt }}" @selected(old('status')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Create Record</button><a class="sn-btn" href="{{ route('clubs.index') }}">Cancel</a></div></form></div></div>
@endsection
