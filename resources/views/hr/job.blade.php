@extends('layouts.schoolnexa')
@section('content')
@include('hr._style')
<div class="hr-wrap"><div class="hr-head"><div><div class="hr-title">{{ $job->title }}</div><div class="hr-sub">{{ $job->vacancies }} vacancy/vacancies · {{ ucfirst($job->status) }}</div></div><a class="hr-btn" href="{{ route('hr.recruitment') }}">Back to recruitment</a></div>
<div class="hr-card"><b>Add applicant</b><form method="POST" action="{{ route('hr.applications.store',$job) }}">@csrf<div class="hr-formgrid">
@foreach(['applicant_name'=>'Applicant name','email'=>'Email','phone'=>'Phone'] as $name=>$label)<div class="hr-field"><label>{{ $label }}</label><input class="hr-input" name="{{ $name }}" {{ $name==='applicant_name'?'required':'' }}></div>@endforeach
<div class="hr-field" style="grid-column:1/-1"><label>Cover letter / notes</label><textarea class="hr-input" name="cover_letter"></textarea></div></div><button class="hr-btn hr-primary" style="margin-top:12px">Add applicant</button></form></div>
<div class="hr-card"><b>Applicant pipeline</b><table class="hr-table"><thead><tr><th>Name</th><th>Contact</th><th>Status</th><th>Update stage</th></tr></thead><tbody>
@forelse($job->applications as $app)<tr><td>{{ $app->applicant_name }}</td><td>{{ $app->email }}<br>{{ $app->phone }}</td><td><span class="hr-pill">{{ ucfirst($app->status) }}</span></td><td><form method="POST" action="{{ route('hr.applications.update',$app) }}">@csrf @method('PATCH')<select class="hr-input" name="status" onchange="this.form.submit()">@foreach(['received','screening','shortlisted','interview','offered','hired','rejected'] as $st)<option value="{{ $st }}" @selected($app->status===$st)>{{ ucfirst($st) }}</option>@endforeach</select></form></td></tr>@empty<tr><td colspan="4" class="hr-muted">No applicants recorded.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
