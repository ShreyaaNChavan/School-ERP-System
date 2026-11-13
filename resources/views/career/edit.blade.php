@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Edit Career Opportunity Record</h1><div class="sn-sub">Update the selected record</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('career.show',$item->id) }}">← Back to Details</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('career.update',$item->id) }}">@csrf @method('PUT')<div class="sn-form"><div class="sn-field"><label>Title</label><input type="text" name="title" value="{{ old('title', $item->title ?? '') }}"></div>
<div class="sn-field"><label>Organization</label><input type="text" name="organization" value="{{ old('organization', $item->organization ?? '') }}"></div>
<div class="sn-field"><label>Opportunity type</label><select name="opportunity_type">@foreach(["job","internship","scholarship","training"] as $opt)<option value="{{ $opt }}" @selected(old('opportunity_type', $item->opportunity_type ?? '')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div>
<div class="sn-field sn-span"><label>Description</label><textarea name="description">{{ old('description', $item->description ?? '') }}</textarea></div>
<div class="sn-field"><label>Location</label><input type="text" name="location" value="{{ old('location', $item->location ?? '') }}"></div>
<div class="sn-field"><label>Stipend Or Salary</label><input type="number" name="stipend_or_salary" value="{{ old('stipend_or_salary', $item->stipend_or_salary ?? '') }}"></div>
<div class="sn-field"><label>Application Deadline</label><input type="date" name="application_deadline" value="{{ old('application_deadline', $item->application_deadline ?? '') }}"></div>
<div class="sn-field sn-span"><label>Eligibility</label><textarea name="eligibility">{{ old('eligibility', $item->eligibility ?? '') }}</textarea></div>
<div class="sn-field"><label>Status</label><select name="status">@foreach(['open', 'closed', 'draft'] as $opt)<option value="{{ $opt }}" @selected(old('status', $item->status ?? '')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div>
<div class="sn-field"><label>Campus</label><select name="campus_id"><option value="">All campuses</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}" @selected(old('campus_id', $item->campus_id ?? '')==$campus->id)>{{ $campus->name }}</option>@endforeach</select></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Save Changes</button><a class="sn-btn" href="{{ route('career.show',$item->id) }}">Cancel</a></div></form></div></div>
@endsection
