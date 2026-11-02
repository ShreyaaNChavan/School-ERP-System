@extends('layouts.schoolnexa')
@section('title','Homework Details')
@section('content')
<div class="hw-show">
    <div class="hw-show-head"><div><a href="{{ route('homework.index') }}" class="hw-back">← Homework Directory</a><h1>{{ $homework->title }}</h1><p>{{ $homework->section->schoolClass->name ?? '-' }} · Section {{ $homework->section->name ?? '-' }} · {{ $homework->subject->name ?? '-' }}</p></div>
        <div class="hw-actions"><span class="hw-badge {{ $homework->status }}">{{ ucfirst($homework->status) }}</span>@if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))<a class="hw-action" href="{{ route('homework.edit',$homework) }}">Edit</a>@endif</div>
    </div>
    @if(session('success'))<div class="hw-alert-success">{{ session('success') }}</div>@endif
    <div class="hw-detail-grid">
        <div><span>Academic Year</span><strong>{{ $homework->academicYear->name ?? '-' }}</strong></div>
        <div><span>Teacher</span><strong>{{ trim(($homework->teacher->first_name ?? '').' '.($homework->teacher->last_name ?? '')) }}</strong></div>
        <div><span>Assigned Date</span><strong>{{ $homework->assigned_date?->format('d M Y') }}</strong></div>
        <div><span>Due Date</span><strong>{{ $homework->due_date?->format('d M Y') ?? 'No due date' }}</strong></div>
        <div><span>Enrolled Students</span><strong>{{ $enrolledIds->count() }}</strong></div>
        <div><span>Submitted</span><strong>{{ $submittedCount }} / {{ $enrolledIds->count() }}</strong></div>
        <div><span>Reviewed</span><strong>{{ $reviewedCount }}</strong></div>
    </div>
    <section class="hw-detail-panel"><h2>Instructions</h2><div class="hw-instructions">{!! nl2br(e($homework->instructions ?: 'No additional instructions provided.')) !!}</div>
        @if($homework->attachment_path)<a class="hw-action" href="{{ asset('storage/'.$homework->attachment_path) }}" target="_blank">View Attachment</a>@endif
    </section>
    <section class="hw-detail-panel"><div class="hw-panel-head"><div><h2>Student Submissions</h2><p>Review submitted work, assign marks and provide feedback.</p></div><span>{{ $homework->submissions_count }} submissions</span></div>
        <div class="hw-table-wrap"><table class="hw-table"><thead><tr><th>Student</th><th>Admission No.</th><th>Submitted At</th><th>Status</th><th>Marks</th><th>Teacher Feedback</th><th>Review</th></tr></thead><tbody>
        @forelse($submissions as $submission)
            <tr><td><strong>{{ $submission->student->full_name ?? '-' }}</strong></td><td>{{ $submission->student->admission_number ?? '-' }}</td><td>{{ $submission->submitted_at?->format('d M Y, h:i A') ?? 'Not submitted' }}</td><td>{{ ucfirst($submission->status) }}</td><td>{{ $submission->marks_obtained ?? '—' }}</td><td>{{ \Illuminate\Support\Str::limit($submission->teacher_feedback,45) ?: '—' }}</td><td>
                @if($submission->submission_path)<a class="hw-action" href="{{ asset('storage/'.$submission->submission_path) }}" target="_blank">File</a>@endif
                <form class="hw-review-form" method="POST" action="{{ route('homework.submissions.review',$submission) }}">@csrf @method('PATCH')
                    <input type="number" name="marks_obtained" min="0" step="0.5" value="{{ $submission->marks_obtained }}" placeholder="Marks">
                    <select name="status"><option value="reviewed" @selected($submission->status==='reviewed')>Reviewed</option><option value="graded" @selected($submission->status==='graded')>Graded</option><option value="needs_correction" @selected($submission->status==='needs_correction')>Needs correction</option></select>
                    <input type="text" name="teacher_feedback" value="{{ $submission->teacher_feedback }}" placeholder="Feedback">
                    <button class="hw-primary" type="submit">Save</button>
                </form>
            </td></tr>
        @empty<tr><td colspan="7" class="hw-empty">No student submissions have been received yet.</td></tr>@endforelse
        </tbody></table></div>
        <div class="hw-pagination"><div>Showing {{ $submissions->firstItem() ?? 0 }}–{{ $submissions->lastItem() ?? 0 }} of {{ $submissions->total() }} submissions</div>{{ $submissions->links('pagination::bootstrap-5') }}</div>
    </section>
</div>
<style>
.hw-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 0 19px;
    border: 1px solid #CBD5E1;
    border-radius: 11px;
    background: #FFFFFF;
    color: #123B5D;
    font: 600 14px 'Inter', sans-serif;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s ease;
}

.hw-back-button:hover {
    background: #F8FAFC;
    color: #123B5D;
}
.hw-show{font-family:Inter,sans-serif;color:#25313C;padding:22px 24px}.hw-show-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}.hw-back{font-size:12px;color:#64748B;text-decoration:none}.hw-show-head h1{font-size:22px;font-weight:700;color:#123B5D;margin:7px 0 4px}.hw-show-head p{font-size:13px;color:#64748B;margin:0}.hw-actions{display:flex;align-items:center;gap:8px}.hw-badge{padding:5px 9px;border-radius:20px;font-size:10px;font-weight:650}.hw-badge.published{background:#DCFCE7;color:#166534}.hw-badge.draft{background:#FEF3C7;color:#92400E}.hw-badge.archived{background:#F1F5F9;color:#475569}
.hw-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px;margin-bottom:16px}.hw-detail-grid>div,.hw-detail-panel{background:#fff;border:1px solid #CBD5E1;border-radius:12px;padding:15px}.hw-detail-grid span{display:block;font-size:11px;font-weight:600;color:#64748B;margin-bottom:6px}.hw-detail-grid strong{font-size:13px;color:#123B5D}.hw-detail-panel{padding:0;margin-bottom:16px;overflow:hidden}.hw-detail-panel>h2{font-size:16px;color:#123B5D;margin:0;padding:16px 18px;border-bottom:1px solid #E2E8F0}.hw-instructions{padding:16px 18px;font-size:13px;line-height:1.7;white-space:normal}.hw-detail-panel>.hw-action{margin:0 18px 16px}.hw-panel-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #E2E8F0}.hw-panel-head h2{font-size:16px;color:#123B5D;margin:0}.hw-panel-head p{font-size:12px;color:#64748B;margin:4px 0 0}.hw-panel-head>span{font-size:12px;color:#64748B}
.hw-table-wrap{overflow-x:auto}.hw-table{width:100%;min-width:1100px;border-collapse:collapse}.hw-table th{background:#F8FAFC;text-align:left;padding:11px 12px;font-size:11px;color:#64748B;border-bottom:1px solid #E2E8F0}.hw-table td{padding:11px 12px;font-size:12px;border-bottom:1px solid #E2E8F0;vertical-align:top}.hw-table td strong{color:#123B5D}.hw-review-form{display:flex;flex-direction:column;gap:6px;min-width:160px;margin-top:7px}.hw-review-form input,.hw-review-form select{height:32px;border:1px solid #CBD5E1;border-radius:7px;padding:0 7px;font:12px Inter,sans-serif}.hw-primary,.hw-action{display:inline-flex;align-items:center;justify-content:center;min-height:34px;padding:0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#123B5D;font:600 12px Inter,sans-serif;text-decoration:none;cursor:pointer}.hw-primary{background:#123B5D;color:#fff;border-color:#123B5D}.hw-empty{text-align:center;padding:28px!important;color:#64748B!important}.hw-pagination{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;padding:13px 16px;font-size:12px;color:#64748B}.hw-alert-success{padding:12px 15px;margin-bottom:15px;background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;border-radius:9px;font-size:13px}
@media(max-width:900px){.hw-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:600px){.hw-show{padding:14px}.hw-detail-grid{grid-template-columns:1fr}}
</style>
@endsection
