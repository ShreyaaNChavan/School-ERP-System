@extends('layouts.schoolnexa')
@section('title','Assignment Details')
@section('content')
<div class="ac-page">
    <div class="ac-head"><div><h1>Assignment Details</h1><p>Review assignment information and evaluation criteria.</p></div><a class="ac-back" href="{{ route('assignments.index') }}">Back to Directory</a></div>
    <section class="ac-panel"><div class="ac-panel-head"><h2>{{ $assignment->title }}</h2><p>{{ $assignment->class_name }} - {{ $assignment->section_name }} · {{ $assignment->subject_name }}</p></div>
        <div class="ac-grid">
            <div><strong>Teacher</strong><p>{{ $assignment->teacher_first_name }} {{ $assignment->teacher_last_name }}</p></div>
            <div><strong>Type</strong><p>{{ ucfirst($assignment->assignment_type) }}</p></div>
            <div><strong>Maximum Marks</strong><p>{{ $assignment->maximum_marks }}</p></div>
            <div><strong>Passing Marks</strong><p>{{ $assignment->passing_marks }}</p></div>
            <div><strong>Start Date</strong><p>{{ $assignment->start_date }}</p></div>
            <div><strong>Due Date</strong><p>{{ $assignment->due_date ?? '—' }}</p></div>
            <div class="ac-span"><strong>Description</strong><p>{{ $assignment->description ?: 'No additional instructions.' }}</p></div>
            <div class="ac-span"><strong>Evaluation Criteria</strong>
                @forelse($rubrics as $r)<p>{{ $r->criterion }} — {{ $r->maximum_marks }} marks</p>@empty<p>No rubric criteria configured.</p>@endforelse
            </div>
            @if($assignment->attachment_path)<div class="ac-span"><a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank">Open attachment</a></div>@endif
        </div>
        <div class="ac-footer"><a class="ac-btn" href="{{ route('assignments.submissions',$assignment->id) }}">View Submissions</a><a class="ac-btn ac-btn-primary" href="{{ route('assignments.edit',$assignment->id) }}">Edit Assignment</a></div>
    </section>
</div>

<style>
.ac-page{font-family:Inter,sans-serif;color:#25313C;padding:22px 24px}.ac-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
.ac-head h1{font-size:22px;font-weight:700;color:#123B5D;margin:0}.ac-head p{font-size:13px;color:#64748B;margin:5px 0 0}
.ac-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;overflow:hidden;margin-bottom:16px}.ac-panel-head{padding:16px 18px;border-bottom:1px solid #E2E8F0}
.ac-panel-head h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}.ac-panel-head p{font-size:12px;color:#64748B;margin:4px 0 0}
.ac-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;padding:18px}.ac-field{min-width:0}.ac-span{grid-column:span 2}
.ac-field label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:7px}
.ac-field input:not([type=file]),.ac-field select,.ac-field textarea{width:100%;min-height:42px;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#25313C;font:400 13px Inter,sans-serif;box-sizing:border-box}
.ac-field textarea{min-height:110px;resize:vertical}.ac-field input:focus,.ac-field select:focus,.ac-field textarea:focus{outline:none;border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.1)}
.ac-btn,.ac-back{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px Inter,sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap}
.ac-btn-primary{background:#123B5D;border-color:#123B5D;color:#fff}.ac-btn-danger{color:#DC2626}.ac-actions{display:flex;align-items:center;gap:7px;white-space:nowrap}.ac-actions form{margin:0}
.ac-table-wrap{overflow-x:auto}.ac-table{width:100%;border-collapse:collapse;min-width:900px}.ac-table th{padding:12px 13px;text-align:left;background:#F8FAFC;color:#64748B;font-size:11px;border-bottom:1px solid #E2E8F0;white-space:nowrap}
.ac-table td{padding:12px 13px;border-bottom:1px solid #E2E8F0;font-size:12px;vertical-align:middle}.ac-table td strong{color:#123B5D}
.ac-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}.ac-stat{background:#fff;border:1px solid #CBD5E1;border-radius:12px;padding:14px 16px}
.ac-stat span{display:block;font-size:11px;font-weight:600;color:#64748B;text-transform:uppercase}.ac-stat strong{display:block;font-size:26px;color:#123B5D;margin-top:5px}
.ac-filters{display:flex;gap:10px;flex-wrap:wrap;padding:14px;background:#fff;border:1px solid #CBD5E1;border-radius:12px;margin-bottom:16px}
.ac-filters input,.ac-filters select{min-height:39px;padding:8px 10px;border:1px solid #CBD5E1;border-radius:8px;font:13px Inter,sans-serif}
.ac-footer{display:flex;justify-content:flex-end;gap:8px;padding:14px 18px;border-top:1px solid #E2E8F0}
.ac-badge{display:inline-flex;padding:5px 9px;border-radius:20px;background:#DCFCE7;color:#166534;font-size:10px;font-weight:650}
@media(max-width:750px){.ac-page{padding:14px}.ac-grid{grid-template-columns:1fr}.ac-span{grid-column:span 1}.ac-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

@endsection
