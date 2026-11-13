@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Edit Alumni Record</h1><div class="sn-sub">Update the selected record</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('alumni.show',$item->id) }}">← Back to Details</a></div></div>
@if($errors->any())<div class="sn-alert" style="background:#FEF2F2;border-color:#FECACA;color:#991B1B">Please correct the highlighted form errors: @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<div class="sn-panel"><form method="POST" action="{{ route('alumni.update',$item->id) }}">@csrf @method('PUT')<div class="sn-form"><div class="sn-field"><label>Full Name</label><input type="text" name="full_name" value="{{ old('full_name', $item->full_name ?? '') }}"></div>
<div class="sn-field"><label>Email</label><input type="email" name="email" value="{{ old('email', $item->email ?? '') }}"></div>
<div class="sn-field"><label>Phone</label><input type="text" name="phone" value="{{ old('phone', $item->phone ?? '') }}"></div>
<div class="sn-field"><label>Admission Number</label><input type="text" name="admission_number" value="{{ old('admission_number', $item->admission_number ?? '') }}"></div>
<div class="sn-field"><label>Graduation Year</label><input type="text" name="graduation_year" value="{{ old('graduation_year', $item->graduation_year ?? '') }}"></div>
<div class="sn-field"><label>Course Or Class</label><input type="text" name="course_or_class" value="{{ old('course_or_class', $item->course_or_class ?? '') }}"></div>
<div class="sn-field"><label>Current Organization</label><input type="text" name="current_organization" value="{{ old('current_organization', $item->current_organization ?? '') }}"></div>
<div class="sn-field"><label>Designation</label><input type="text" name="designation" value="{{ old('designation', $item->designation ?? '') }}"></div>
<div class="sn-field"><label>City</label><input type="text" name="city" value="{{ old('city', $item->city ?? '') }}"></div>
<div class="sn-field"><label>Linkedin Url</label><input type="url" name="linkedin_url" value="{{ old('linkedin_url', $item->linkedin_url ?? '') }}"></div>
<div class="sn-field"><label>Status</label><select name="status">@foreach(['active', 'inactive'] as $opt)<option value="{{ $opt }}" @selected(old('status', $item->status ?? '')===$opt)>{{ ucfirst($opt) }}</option>@endforeach</select></div>
<div class="sn-field sn-span"><label>Notes</label><textarea name="notes">{{ old('notes', $item->notes ?? '') }}</textarea></div></div><div class="sn-actions" style="margin-top:20px"><button class="sn-btn sn-primary" type="submit">Save Changes</button><a class="sn-btn" href="{{ route('alumni.show',$item->id) }}">Cancel</a></div></form></div></div>
@endsection
