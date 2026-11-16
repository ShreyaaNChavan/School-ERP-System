@extends('layouts.schoolnexa')

@section('content')
<div class="tm-wrap">
    <div class="tm-head"><div><span>ACADEMICS</span><h1>Marks Entry</h1><p>Examination subjects assigned to you for marks recording.</p></div></div>
    @if(session('success'))<div class="tm-alert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="tm-alert tm-error">{{ session('error') }}</div>@endif
    <div class="tm-table-wrap"><table class="tm-table"><thead><tr><th>Examination</th><th>Class / Section</th><th>Subject</th><th>Exam date</th><th>Academic year</th><th>Entries</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($examSubjects as $row)
        <tr>
            <td><strong>{{ $row->examination_name }}</strong></td>
            <td>{{ $row->class_name }} / {{ $row->section_name }}</td>
            <td>{{ $row->subject_name }}</td>
            <td>{{ $row->exam_date ? \Carbon\Carbon::parse($row->exam_date)->format('d M Y') : '—' }}</td>
            <td>{{ $row->academic_year_name ?: '—' }}</td>
            <td>{{ $row->entered_count }}</td>
            <td><span class="tm-pill {{ (int)$row->published_count > 0 ? 'published' : '' }}">{{ (int)$row->published_count > 0 ? 'Published' : 'Draft' }}</span></td>
            <td><a class="tm-link" href="{{ route('teacher.marks.show', $row->id) }}">Open sheet</a></td>
        </tr>
    @empty
        <tr><td colspan="8" class="tm-empty">No examination subjects are currently assigned to you.</td></tr>
    @endforelse
    </tbody></table></div>
    <div class="tm-pagination">{{ $examSubjects->links() }}</div>
</div>
<style>
.tm-wrap{max-width:1250px;margin:0 auto;padding:24px;color:#172b4d}.tm-head{margin-bottom:20px}.tm-head span{font-size:11px;font-weight:800;letter-spacing:1.2px;color:#176b87}.tm-head h1{font-size:28px;margin:4px 0}.tm-head p{color:#64748b;margin:0}.tm-table-wrap{overflow:auto;background:#fff;border:1px solid #e5edf4;border-radius:15px}.tm-table{width:100%;min-width:950px;border-collapse:collapse}.tm-table th,.tm-table td{padding:14px 15px;border-bottom:1px solid #edf1f5;text-align:left;font-size:13px}.tm-table th{background:#f7fafc;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.5px}.tm-pill{display:inline-block;border-radius:20px;background:#fff7ed;color:#b45309;padding:5px 10px;font-size:11px;font-weight:700}.tm-pill.published{background:#ecfdf3;color:#15803d}.tm-link{color:#176b87;font-weight:700;text-decoration:none}.tm-empty{text-align:center!important;padding:40px!important;color:#64748b}.tm-pagination{margin-top:15px}.tm-pagination svg{width:16px}.tm-alert{padding:12px 15px;border-radius:10px;background:#ecfdf3;color:#166534;margin-bottom:15px}.tm-error{background:#fff1f2;color:#be123c}
</style>
@endsection
