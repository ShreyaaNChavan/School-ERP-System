@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Add Career Opportunity Record</h1><div class="sn-sub">Enter the details below</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('career.index') }}">← Back to List</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('career.store') }}">@csrf <div class="sn-form"><div class="sn-field"><label>Title</label><input type="text" name="title" value="{{ old('title') }}"></div>
<div class="sn-field"><label>Organization</label><input type="text" name="organization" value="{{ old('organization') }}"></div>
<div class="sn-field"><label>Opportunity type</label><select name="opportunity_type">@foreach(["job","internship","scholarship","training"] as $opt)<option value="{{ $opt }}" @selected(old('opportunity_type')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div>
<div class="sn-field sn-span"><label>Description</label><textarea name="description">{{ old('description') }}</textarea></div>
<div class="sn-field"><label>Location</label><input type="text" name="location" value="{{ old('location') }}"></div>
<div class="sn-field"><label>Stipend Or Salary</label><input type="number" name="stipend_or_salary" value="{{ old('stipend_or_salary') }}"></div>
<div class="sn-field"><label>Application Deadline</label><input type="date" name="application_deadline" value="{{ old('application_deadline') }}"></div>
<div class="sn-field sn-span"><label>Eligibility</label><textarea name="eligibility">{{ old('eligibility') }}</textarea></div>
<div class="sn-field"><label>Status</label><select name="status">@foreach(['open', 'closed', 'draft'] as $opt)<option value="{{ $opt }}" @selected(old('status')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div>
<div class="sn-field"><label>Campus</label><select name="campus_id"><option value="">All campuses</option>@foreach($campuses as $campus)<option value="{{ $campus->id }}" @selected(old('campus_id')==$campus->id)>{{ $campus->name }}</option>@endforeach</select></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Create Record</button><a class="sn-btn" href="{{ route('career.index') }}">Cancel</a></div></form></div></div>
@endsection
