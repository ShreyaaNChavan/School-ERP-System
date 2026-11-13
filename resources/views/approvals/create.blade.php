@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ac-page">
@include('approvals._style')
<div class="ac-head"><div><h1>Create Approval Request</h1><div class="ac-sub">Submit a request into the school's approval process</div></div><div class="ac-actions"><a class="ac-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div></div>
@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#b42318">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

<div class="ac-panel"><form method="POST" action="{{ route('approvals.store') }}">@csrf
<div class="ac-form">
<div class="ac-field"><label>Request Title *</label><input name="title" value="{{ old('title') }}" required maxlength="190"></div>
<div class="ac-field"><label>Request Type *</label><select name="request_type" required><option value="">Select type</option>@foreach(['Purchase','Leave','Fee Adjustment','Admission Exception','Academic Change','Maintenance','Other'] as $t)<option @selected(old('request_type')===$t)>{{ $t }}</option>@endforeach</select></div>
<div class="ac-field"><label>Department</label><select name="department"><option value="">Select department</option>@foreach(['Administration','Academics','Finance','Human Resources','Transport','IT','Operations'] as $d)<option @selected(old('department')===$d)>{{ $d }}</option>@endforeach</select></div>
<div class="ac-field"><label>Priority *</label><select name="priority" required>@foreach(['low','medium','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority','medium')===$p)>{{ ucfirst($p) }}</option>@endforeach</select></div>
<div class="ac-field"><label>Due Date</label><input type="datetime-local" name="due_at" value="{{ old('due_at') }}"></div>
<div class="ac-field"><label>Approver User ID (optional)</label><input type="number" min="1" name="assigned_to" value="{{ old('assigned_to') }}"></div>
<div class="ac-field" style="grid-column:1/-1"><label>Description *</label><textarea name="description" required>{{ old('description') }}</textarea></div>
</div><div style="margin-top:15px"><button class="ac-btn primary">Submit Request</button> <a class="ac-btn" href="{{ route('approvals.requests') }}">Cancel</a></div>
</form></div>

</div>
@endsection
