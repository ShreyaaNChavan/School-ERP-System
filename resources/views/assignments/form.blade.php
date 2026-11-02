@extends('layouts.schoolnexa')
@section('title',$pageTitle)
@section('content')
<div class="ac-page">
    <div class="ac-head"><div><h1>{{ $pageTitle }}</h1><p>Create a structured academic task with marks and evaluation criteria.</p></div><a class="ac-back" href="{{ route('assignments.index') }}">Back to Directory</a></div>
    @if($errors->any())<div class="ac-panel" style="padding:14px;color:#B91C1C">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf @if($formMethod!=='POST') @method($formMethod) @endif
        <section class="ac-panel"><div class="ac-panel-head"><h2>Assignment Details</h2><p>Define the task, allocation and evaluation schedule.</p></div>
            <div class="ac-grid">
                <div class="ac-field ac-span"><label>Assignment Title *</label><input name="title" required value="{{ old('title',$assignment->title ?? '') }}" placeholder="e.g. Renewable Energy Project"></div>
                <div class="ac-field"><label>School *</label><select name="school_id" required>@foreach($schools as $s)<option value="{{ $s->id }}" @selected(old('school_id',$assignment->school_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Academic Year *</label><select name="academic_year_id" required>@foreach($academicYears as $y)<option value="{{ $y->id }}" @selected(old('academic_year_id',$assignment->academic_year_id ?? '')==$y->id)>{{ $y->name }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Class & Section *</label><select name="section_id" required>@foreach($sections as $s)<option value="{{ $s->id }}" @selected(old('section_id',$assignment->section_id ?? '')==$s->id)>{{ $s->class_name }} - {{ $s->name }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Subject *</label><select name="subject_id" required>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id',$assignment->subject_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Assigned Teacher *</label><select name="staff_profile_id" required>@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('staff_profile_id',$assignment->staff_profile_id ?? '')==$t->id)>{{ $t->first_name }} {{ $t->last_name }} — {{ $t->employee_number }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Assignment Type *</label><select name="assignment_type" required>@foreach(['individual','group','project','practical'] as $v)<option value="{{ $v }}" @selected(old('assignment_type',$assignment->assignment_type ?? 'individual')==$v)>{{ ucfirst($v) }}</option>@endforeach</select></div>
                <div class="ac-field"><label>Maximum Marks *</label><input type="number" name="maximum_marks" min="1" step="0.5" required value="{{ old('maximum_marks',$assignment->maximum_marks ?? 20) }}"></div>
                <div class="ac-field"><label>Passing Marks *</label><input type="number" name="passing_marks" min="0" step="0.5" required value="{{ old('passing_marks',$assignment->passing_marks ?? 7) }}"></div>
                <div class="ac-field"><label>Start Date *</label><input type="date" name="start_date" required value="{{ old('start_date',$assignment->start_date ?? now()->format('Y-m-d')) }}"></div>
                <div class="ac-field"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date',$assignment->due_date ?? '') }}"></div>
                <div class="ac-field"><label>Status *</label><select name="status"><option value="draft">Draft</option><option value="published" @selected(old('status',$assignment->status ?? 'draft')=='published')>Published</option><option value="archived">Archived</option></select></div>
                <div class="ac-field ac-span"><label>Description / Instructions</label><textarea name="description" placeholder="Describe the task, expected outcome and submission instructions...">{{ old('description',$assignment->description ?? '') }}</textarea></div>
                <div class="ac-field ac-span"><label>Reference Attachment</label><input type="file" name="attachment" accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.png"></div>
            </div>
        </section>
        <section class="ac-panel"><div class="ac-panel-head"><h2>Evaluation Rubric</h2><p>Optional criteria used to assess student submissions. The sum should not exceed maximum marks.</p></div>
            <div class="ac-grid" id="rubricRows">
                @forelse(old('rubrics',$rubrics ?? []) as $i=>$r)
                    <div class="ac-field"><label>Criterion</label><input name="rubrics[{{ $i }}][criterion]" value="{{ is_array($r) ? $r['criterion'] : $r->criterion }}" placeholder="e.g. Research quality"></div>
                    <div class="ac-field"><label>Maximum Marks</label><input type="number" step="0.5" min="0" name="rubrics[{{ $i }}][maximum_marks]" value="{{ is_array($r) ? $r['maximum_marks'] : $r->maximum_marks }}"></div>
                @empty
                    <div class="ac-field"><label>Criterion</label><input name="rubrics[0][criterion]" placeholder="e.g. Research quality"></div>
                    <div class="ac-field"><label>Maximum Marks</label><input type="number" step="0.5" min="0" name="rubrics[0][maximum_marks]" placeholder="5"></div>
                @endforelse
            </div>
            <div style="padding:0 18px 18px"><button type="button" class="ac-btn" id="addRubric">+ Add Criterion</button></div>
        </section>
        <div class="ac-footer"><a class="ac-btn" href="{{ route('assignments.index') }}">Cancel</a><button class="ac-btn ac-btn-primary">{{ $submitLabel }}</button></div>
    </form>
</div>
<script>
document.getElementById('addRubric')?.addEventListener('click',function(){
 const wrap=document.getElementById('rubricRows'), index=wrap.querySelectorAll('input[name*="[criterion]"]').length;
 const a=document.createElement('div');a.className='ac-field';a.innerHTML=`<label>Criterion</label><input name="rubrics[${index}][criterion]" placeholder="e.g. Presentation">`;
 const b=document.createElement('div');b.className='ac-field';b.innerHTML=`<label>Maximum Marks</label><input type="number" step="0.5" min="0" name="rubrics[${index}][maximum_marks]" placeholder="5">`;
 wrap.append(a,b);
});
</script>

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
