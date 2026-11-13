@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Edit Sport Record</h1><div class="sn-sub">Update the selected record</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('sports.show',$item->id) }}">← Back to Details</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('sports.update',$item->id) }}">@csrf @method('PUT')<div class="sn-form"><div class="sn-field"><label>Name</label><input type="text" name="name" value="{{ old('name', $item->name ?? '') }}"></div>
<div class="sn-field"><label>Category</label><input type="text" name="category" value="{{ old('category', $item->category ?? '') }}"></div>
<div class="sn-field sn-span"><label>Description</label><textarea name="description">{{ old('description', $item->description ?? '') }}</textarea></div>
<div class="sn-field"><label>Campus</label><select name="campus_id"><option value="">All campuses</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}" @selected(old('campus_id', $item->campus_id ?? '')==$campus->id)>{{ $campus->name }}</option>@endforeach</select></div>
<div class="sn-field"><label>Is Active</label><select name="is_active"><option value="1" @selected(old('is_active', $item->is_active ?? '')==1)>Active</option><option value="0" @selected(old('is_active', $item->is_active ?? '')==0)>Inactive</option></select></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Save Changes</button><a class="sn-btn" href="{{ route('sports.show',$item->id) }}">Cancel</a></div></form></div></div>
@endsection
