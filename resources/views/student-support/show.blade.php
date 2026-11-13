@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ss-page">@include('student-support._style')
    <div class="ss-head"><div><h1>{{ $config['title'] }} Details</h1><div class="ss-sub">Reference: {{ $item->reference_number ?? $item->case_reference ?? ('#'.$item->id) }}</div></div><div class="ss-actions"><a class="ss-btn" href="{{ route('student-support.index',$module) }}">← Back to List</a></div></div>
    <div class="ss-panel"><div class="ss-grid" style="grid-template-columns:repeat(2,minmax(0,1fr))">
        @foreach((array)$item as $key=>$value)
            @if(!in_array($key,['id','school_id','created_at','updated_at','reported_by','assigned_to','case_owner','counsellor_id','student_id','admission_application_id'],true) && !is_null($value))
                <div><div class="ss-muted">{{ ucwords(str_replace('_',' ',$key)) }}</div><div style="font-size:14px;font-weight:600;margin-top:5px;white-space:pre-wrap">{{ $value }}</div></div>
            @endif
        @endforeach
        @if($student)<div><div class="ss-muted">Student</div><div style="font-weight:600;margin-top:5px">{{ $student->first_name }} {{ $student->last_name }} ({{ $student->admission_number }})</div></div>@endif
    </div></div>
    <div class="ss-panel"><h3 style="margin:0 0 14px;color:#123B5D">Update case status</h3><form method="POST" action="{{ route('student-support.status',[$module,$item->id]) }}" class="ss-actions" style="justify-content:flex-start">@csrf @method('PATCH')<select name="status" style="min-width:220px;border:1px solid #CBD5E1;border-radius:9px;padding:10px">@foreach(['submitted','open','under_review','investigating','in_progress','scheduled','completed','resolved','closed','cancelled','active','responding'] as $v)<option value="{{ $v }}" @selected(($item->status ?? '')===$v)>{{ ucwords(str_replace('_',' ',$v)) }}</option>@endforeach</select><button class="ss-btn primary">Update Status</button></form></div>
    @if(in_array($module,['anti-ragging','child-protection'],true))<div class="ss-panel"><h3 style="margin:0 0 14px;color:#123B5D">Restricted case notes</h3><form method="POST" action="{{ route('student-support.notes',[$module,$item->id]) }}">@csrf<div class="ss-field"><label>Case note</label><textarea name="note" required></textarea></div><label style="display:block;margin:12px 0;font-size:12px"><input type="checkbox" name="is_restricted" value="1" checked> Mark as restricted</label><button class="ss-btn primary">Add Note</button></form>@foreach($notes as $note)<div style="border-top:1px solid #E2E8F0;padding:12px 0;margin-top:12px"><div style="font-size:13px;white-space:pre-wrap">{{ $note->note }}</div><div class="ss-muted">{{ $note->created_at }} · {{ $note->is_restricted ? 'Restricted' : 'Standard note' }}</div></div>@endforeach</div>@endif
</div>
@endsection
