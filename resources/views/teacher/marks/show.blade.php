@extends('layouts.schoolnexa')

@section('content')
<div class="tm-wrap">
    <div class="tm-head tm-head-row">
        <div><span>MARKS ENTRY</span><h1>{{ $examSubject->examination?->name ?: 'Examination' }}</h1><p>{{ $examSubject->schoolClass?->name }} · {{ $examSubject->section?->name }} · {{ $examSubject->subject?->name }}</p></div>
        <a class="tm-btn tm-light" href="{{ route('teacher.marks.index') }}">Back to marks entry</a>
    </div>
    @if(session('success'))<div class="tm-alert">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="tm-alert tm-error">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="tm-alert tm-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    <div class="tm-summary">
        <div><small>Maximum marks</small><strong>{{ $examSubject->maximum_marks }}</strong></div>
        <div><small>Passing marks</small><strong>{{ $examSubject->passing_marks }}</strong></div>
        <div><small>Students</small><strong>{{ $students->count() }}</strong></div>
        <div><small>Sheet status</small><strong>{{ $isPublished ? 'Published' : 'Draft' }}</strong></div>
    </div>
    <form method="POST" action="{{ route('teacher.marks.save', $examSubject->id) }}" class="tm-card">
        @csrf
        <div class="tm-table-wrap"><table class="tm-table"><thead><tr><th>Admission no.</th><th>Student</th><th>Attendance</th><th>Marks</th><th>Grade</th><th>Remarks</th></tr></thead><tbody>
        @forelse($students as $index => $student)
            <tr>
                <td>{{ $student->admission_number }}</td>
                <td><strong>{{ $student->full_name }}</strong></td>
                <td>
                    <select name="entries[{{ $index }}][status]" class="tm-input" @disabled($isPublished)>
                        <option value="present" @selected($student->entry_status !== 'absent')>Present</option>
                        <option value="absent" @selected($student->entry_status === 'absent')>Absent</option>
                    </select>
                    <input type="hidden" name="entries[{{ $index }}][student_id]" value="{{ $student->student_id }}">
                </td>
                <td><input class="tm-input tm-marks" type="number" name="entries[{{ $index }}][marks]" min="0" max="{{ $examSubject->maximum_marks }}" step="0.01" value="{{ $student->marks_obtained }}" @disabled($isPublished)></td>
                <td>{{ $student->grade ?: '—' }}</td>
                <td><input class="tm-input" type="text" name="entries[{{ $index }}][remarks]" maxlength="2000" value="{{ $student->remarks }}" @disabled($isPublished)></td>
            </tr>
        @empty
            <tr><td colspan="6" class="tm-empty">No active students are enrolled in this section.</td></tr>
        @endforelse
        </tbody></table></div>
        @if(!$isPublished && $students->isNotEmpty())
            <div class="tm-actions"><button type="submit" class="tm-btn tm-primary">Save draft</button></div>
        @endif
    </form>
    @if(!$isPublished && $students->isNotEmpty())
        <form method="POST" action="{{ route('teacher.marks.publish', $examSubject->id) }}" class="tm-publish" onsubmit="return confirm('Publish this marks sheet? Published marks will be locked from further editing.');">
            @csrf
            <p>Before publishing, make sure every student has marks entered or is marked absent.</p>
            <button class="tm-btn tm-publish-btn" type="submit">Publish marks</button>
        </form>
    @else
        <div class="tm-published-note">This marks sheet is published and locked.</div>
    @endif
</div>
<style>
.tm-wrap{max-width:1250px;margin:0 auto;padding:24px;color:#172b4d}.tm-head{margin-bottom:20px}.tm-head-row{display:flex;justify-content:space-between;align-items:center;gap:16px}.tm-head span{font-size:11px;font-weight:800;letter-spacing:1.2px;color:#176b87}.tm-head h1{font-size:27px;margin:4px 0}.tm-head p{color:#64748b;margin:0}.tm-summary{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px}.tm-summary div{background:#fff;border:1px solid #e5edf4;border-radius:13px;padding:15px}.tm-summary small{display:block;color:#64748b;font-size:11px}.tm-summary strong{display:block;font-size:20px;margin-top:5px}.tm-card{background:#fff;border:1px solid #e5edf4;border-radius:15px;padding:15px}.tm-table-wrap{overflow:auto}.tm-table{width:100%;min-width:850px;border-collapse:collapse}.tm-table th,.tm-table td{padding:12px;border-bottom:1px solid #edf1f5;text-align:left;font-size:13px}.tm-table th{background:#f7fafc;color:#64748b;font-size:11px;text-transform:uppercase}.tm-input{border:1px solid #dbe5ee;border-radius:8px;padding:9px;max-width:160px;width:100%;font:inherit}.tm-marks{max-width:100px}.tm-actions,.tm-publish{text-align:right;margin-top:15px}.tm-publish{background:#f7fafc;border:1px solid #e5edf4;border-radius:13px;padding:14px 16px;display:flex;justify-content:space-between;align-items:center;gap:15px}.tm-publish p{margin:0;color:#64748b;font-size:13px}.tm-btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:9px;padding:10px 15px;text-decoration:none;font-weight:700;cursor:pointer}.tm-primary{background:#123b5d;color:#fff}.tm-light{background:#edf3f7;color:#123b5d}.tm-publish-btn{background:#176b87;color:#fff}.tm-alert{padding:12px 15px;border-radius:10px;background:#ecfdf3;color:#166534;margin-bottom:15px}.tm-error{background:#fff1f2;color:#be123c}.tm-empty{text-align:center!important;padding:35px!important;color:#64748b}.tm-published-note{margin-top:16px;padding:13px;border-radius:10px;background:#ecfdf3;color:#166534;font-weight:700}.tm-head-row .tm-btn{white-space:nowrap}@media(max-width:700px){.tm-summary{grid-template-columns:repeat(2,1fr)}.tm-head-row,.tm-publish{align-items:flex-start;flex-direction:column}}
</style>
@endsection
