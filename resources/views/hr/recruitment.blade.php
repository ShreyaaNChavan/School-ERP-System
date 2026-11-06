@extends('layouts.schoolnexa')
@section('content')
@include('hr._style')
<div class="hr-wrap"><div class="hr-head"><div><div class="hr-title">Recruitment</div><div class="hr-sub">Manage vacancies and applicant progress</div></div><a class="hr-btn" href="{{ route('hr.dashboard') }}">Back to HR</a></div>
<div class="hr-card"><h3>Create vacancy</h3><form method="POST" action="{{ route('hr.jobs.store') }}">@csrf<div class="hr-formgrid">
<div class="hr-field"><label>Job title</label><input class="hr-input" name="title" required></div><div class="hr-field"><label>Vacancies</label><input class="hr-input" type="number" name="vacancies" min="1" value="1" required></div><div class="hr-field"><label>Application deadline</label><input class="hr-input" type="date" name="application_deadline"></div><div class="hr-field"><label>Status</label><select class="hr-input" name="status"><option value="draft">Draft</option><option value="open">Open</option><option value="published">Published</option><option value="closed">Closed</option></select></div>
<div class="hr-field" style="grid-column:1/-1"><label>Description</label><textarea class="hr-input" name="description" rows="2"></textarea></div></div><button class="hr-btn hr-primary" style="margin-top:12px">Create vacancy</button></form></div>
<div class="hr-card"><b>Job openings</b><table class="hr-table"><thead><tr><th>Position</th><th>Vacancies</th><th>Applicants</th><th>Deadline</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($jobs as $job)<tr><td><b>{{ $job->title }}</b></td><td>{{ $job->vacancies }}</td><td>{{ $job->applications_count }}</td><td>{{ $job->application_deadline?->format('d M Y') ?? '—' }}</td><td><span class="hr-pill">{{ ucfirst($job->status) }}</span></td><td><a class="hr-btn" href="{{ route('hr.jobs.show',$job) }}">Manage</a></td></tr>@empty<tr><td colspan="6" class="hr-muted">No vacancies created yet.</td></tr>@endforelse
</tbody></table><div style="margin-top:14px">{{ $jobs->links() }}</div></div></div>
@endsection
