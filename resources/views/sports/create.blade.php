@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Add Sport Record</h1><div class="sn-sub">Enter the details below</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('sports.index') }}">← Back to List</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('sports.store') }}">@csrf <div class="sn-form"><div class="sn-field"><label>Name</label><input type="text" name="name" value="{{ old('name') }}"></div>
<div class="sn-field"><label>Category</label><input type="text" name="category" value="{{ old('category') }}"></div>
<div class="sn-field sn-span"><label>Description</label><textarea name="description">{{ old('description') }}</textarea></div>
<div class="sn-field"><label>Campus</label><select name="campus_id"><option value="">All campuses</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}" @selected(old('campus_id')==$campus->id)>{{ $campus->name }}</option>@endforeach</select></div>
<div class="sn-field"><label>Is Active</label><select name="is_active"><option value="1" @selected(old('is_active')==1)>Active</option><option value="0" @selected(old('is_active')==0)>Inactive</option></select></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Create Record</button><a class="sn-btn" href="{{ route('sports.index') }}">Cancel</a></div></form></div></div>
@endsection
