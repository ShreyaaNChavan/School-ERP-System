@extends('layouts.schoolnexa')
@section('title',$pageTitle)
@section('content')
<div class="ac-page"><div class="ac-head"><div><h1>{{ $pageTitle }}</h1><p>Create a reusable question and define its answer and difficulty.</p></div><a class="ac-back" href="{{ route('question-bank.index') }}">Back to Question Bank</a></div>
@if($errors->any())<div class="ac-panel" style="padding:14px;color:#B91C1C">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $formAction }}">@csrf @if($formMethod!=='POST') @method($formMethod) @endif
<section class="ac-panel"><div class="ac-panel-head"><h2>Question Details</h2><p>Classify the question for reuse in assessments and examinations.</p></div><div class="ac-grid">
<div class="ac-field"><label>Question Bank *</label><select name="question_bank_id" required>@foreach($banks as $b)<option value="{{ $b->id }}" @selected(old('question_bank_id',$question->question_bank_id ?? '')==$b->id)>{{ $b->name }}</option>@endforeach</select></div>
<div class="ac-field"><label>Subject *</label><select name="subject_id" required>@foreach($subjects as $s)<option value="{{ $s->id }}" @selected(old('subject_id',$question->subject_id ?? '')==$s->id)>{{ $s->name }}</option>@endforeach</select></div>
<div class="ac-field"><label>Chapter</label><select name="chapter_id"><option value="">Select Chapter</option>@foreach($chapters as $c)<option value="{{ $c->id }}" @selected(old('chapter_id',$question->chapter_id ?? '')==$c->id)>{{ $c->title }}</option>@endforeach</select></div>
<div class="ac-field"><label>Topic</label><select name="topic_id"><option value="">Select Topic</option>@foreach($topics as $t)<option value="{{ $t->id }}" @selected(old('topic_id',$question->topic_id ?? '')==$t->id)>{{ $t->title }}</option>@endforeach</select></div>
<div class="ac-field"><label>Question Type *</label><select name="question_type" id="questionType">@foreach(['mcq'=>'Multiple Choice','true_false'=>'True / False','descriptive'=>'Descriptive'] as $v=>$l)<option value="{{ $v }}" @selected(old('question_type',$question->question_type ?? 'mcq')==$v)>{{ $l }}</option>@endforeach</select></div>
<div class="ac-field"><label>Difficulty *</label><select name="difficulty">@foreach(['easy','medium','hard'] as $v)<option value="{{ $v }}" @selected(old('difficulty',$question->difficulty ?? 'medium')==$v)>{{ ucfirst($v) }}</option>@endforeach</select></div>
<div class="ac-field"><label>Marks *</label><input type="number" name="marks" min="0.5" step="0.5" required value="{{ old('marks',$question->marks ?? 1) }}"></div>
<div class="ac-field"><label>Status *</label><select name="is_active"><option value="1" @selected(old('is_active',$question->is_active ?? 1)==1)>Active</option><option value="0" @selected(old('is_active',$question->is_active ?? 1)==0)>Inactive</option></select></div>
<div class="ac-field ac-span"><label>Question Text *</label><textarea name="question_text" required placeholder="Enter the complete question...">{{ old('question_text',$question->question_text ?? '') }}</textarea></div>
<div class="ac-field ac-span" id="optionFields"><label>Answer Options (for MCQ / True-False)</label><div id="optionsWrap">
@forelse(old('options',$options) as $i=>$o)<div class="ac-grid" style="padding:8px 0"><div class="ac-field"><label>Option Label</label><input name="options[{{ $i }}][option_label]" value="{{ is_array($o)?$o['option_label']:$o->option_label }}"></div><div class="ac-field"><label>Option Text</label><input name="options[{{ $i }}][option_text]" value="{{ is_array($o)?$o['option_text']:$o->option_text }}"></div><div class="ac-field"><label><input type="checkbox" name="options[{{ $i }}][is_correct]" value="1" @checked(is_array($o)?!empty($o['is_correct']):$o->is_correct)> Correct answer</label></div></div>
@empty
@foreach(['A','B','C','D'] as $i=>$label)<div class="ac-grid" style="padding:8px 0"><div class="ac-field"><label>Option {{ $label }}</label><input name="options[{{ $i }}][option_label]" value="{{ $label }}"><input name="options[{{ $i }}][option_text]" placeholder="Enter option text" style="margin-top:7px"></div><div class="ac-field"><label>Correct answer</label><select name="options[{{ $i }}][is_correct]"><option value="0">No</option><option value="1">Yes</option></select></div></div>@endforeach
@endforelse
</div></div>
<div class="ac-field ac-span"><label>Correct Answer / Model Answer</label><textarea name="answer_text" placeholder="Enter the correct answer or expected response...">{{ old('answer_text',$question->answer_text ?? '') }}</textarea></div>
<div class="ac-field ac-span"><label>Explanation</label><textarea name="explanation" placeholder="Explain why this answer is correct...">{{ old('explanation',$question->explanation ?? '') }}</textarea></div>
</div></section><div class="ac-footer"><a class="ac-btn" href="{{ route('question-bank.index') }}">Cancel</a><button class="ac-btn ac-btn-primary">Save Question</button></div></form></div>
<script>
document.getElementById('questionType')?.addEventListener('change',function(){document.getElementById('optionFields').style.display=this.value==='descriptive'?'none':'';});
document.getElementById('questionType')?.dispatchEvent(new Event('change'));
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
