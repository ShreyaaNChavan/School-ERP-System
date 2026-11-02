@extends('layouts.schoolnexa')
@section('title','Timetable Details')
@section('content')
@php
$days=['monday'=>'Monday','tuesday'=>'Tuesday','wednesday'=>'Wednesday','thursday'=>'Thursday','friday'=>'Friday','saturday'=>'Saturday'];
$styles=['draft'=>['#FEF3C7','#92400E'],'published'=>['#DCFCE7','#166534'],'archived'=>['#F1F5F9','#475569']];
$sc=$styles[$timetable->status]??$styles['draft'];
@endphp
<div class="tt-show" style="font-family:Inter,sans-serif;color:#25313C;padding:22px 24px;">
 <div class="tt-head"><div><a href="{{ route('timetables.index') }}" class="tt-back">← Timetable Directory</a><h1>{{ $timetable->name }}</h1><p>{{ $timetable->schoolClass->name??'-' }} · Section {{ $timetable->section->name??'-' }} · {{ $timetable->academicYear->name??'-' }}</p></div>
 <div class="tt-actions"><span class="tt-status" style="background:{{ $sc[0] }};color:{{ $sc[1] }}">{{ ucfirst($timetable->status) }}</span>
 @if($timetable->status==='draft' && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
 <a class="tt-btn" href="{{ route('timetables.edit',$timetable) }}">Edit Draft</a>
 <form method="POST" action="{{ route('timetables.publish',$timetable) }}" onsubmit="return confirm('Publish this timetable?')">@csrf<button class="tt-btn tt-primary" type="submit">Publish</button></form>
 @endif
 @if($timetable->status==='published' && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
 <form method="POST" action="{{ route('timetables.archive',$timetable) }}" onsubmit="return confirm('Archive this timetable?')">@csrf<button class="tt-btn" type="submit">Archive</button></form>
 @endif
 <button class="tt-btn" type="button" onclick="window.print()">Print</button></div></div>
 @if(session('success'))<div class="tt-success">{{ session('success') }}</div>@endif
 @if($errors->any())<div class="tt-error">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
 <div class="tt-meta">
  <div><span>School</span><strong>{{ $timetable->school->name??'-' }}</strong></div>
  <div><span>Campus</span><strong>{{ $timetable->campus->name??'-' }}</strong></div>
  <div><span>Version</span><strong>v{{ $timetable->version }}</strong></div>
  <div><span>Effective From</span><strong>{{ $timetable->effective_from?->format('d M Y')??'Not set' }}</strong></div>
  <div><span>Effective Until</span><strong>{{ $timetable->effective_until?->format('d M Y')??'Not set' }}</strong></div>
  <div><span>Published At</span><strong>{{ $timetable->published_at?->format('d M Y, h:i A')??'Not published' }}</strong></div>
 </div>
 <section class="tt-panel"><div class="tt-panel-head"><div><h2>Weekly Timetable</h2><p>Monday to Saturday schedule</p></div><span>{{ $timetable->periods->count() }} periods</span></div>
 <div class="tt-scroll"><table class="tt-week"><thead><tr><th>Period / Time</th>@foreach($days as $day)<th>{{ $day }}</th>@endforeach</tr></thead><tbody>
 @forelse($timetable->periods->sortBy('period_number') as $period)
 @if($period->period_type==='break')
 <tr class="tt-break"><td><strong>{{ $period->name }}</strong><small>{{ substr((string)$period->start_time,0,5) }} – {{ substr((string)$period->end_time,0,5) }}</small></td><td colspan="6">BREAK</td></tr>
 @else
 <tr><td class="tt-time"><strong>{{ $period->name }}</strong><small>{{ substr((string)$period->start_time,0,5) }} – {{ substr((string)$period->end_time,0,5) }}</small></td>
 @foreach($days as $key=>$label) @php($entry=$period->entries->firstWhere('day_of_week',$key))
 <td>@if($entry)<div class="tt-subject"><strong>{{ $entry->classSubject->subject->name??'Subject' }}</strong><span>{{ trim(($entry->teacher->first_name??'').' '.($entry->teacher->last_name??''))?:'Teacher not available' }}</span>@if($entry->room_number)<small>Room {{ $entry->room_number }}</small>@endif</div>@else<span class="tt-empty">—</span>@endif</td>
 @endforeach</tr>
 @endif
 @empty<tr><td colspan="7" class="tt-no-data">No periods have been added.</td></tr>@endforelse
 </tbody></table></div></section>
 <p class="tt-foot">Created {{ $timetable->created_at?->format('d M Y, h:i A')??'-' }} · Updated {{ $timetable->updated_at?->format('d M Y, h:i A')??'-' }}</p>
</div>
<style>
.tt-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;flex-wrap:wrap;margin-bottom:20px}.tt-head h1{font-size:22px;font-weight:700;color:#123B5D;margin:8px 0 4px}.tt-head p{font-size:13px;color:#64748B;margin:0}.tt-back{font-size:12px;color:#64748B;text-decoration:none}
.tt-actions{display:flex;align-items:center;gap:8px;flex-wrap:wrap}.tt-actions form{display:inline-flex;margin:0}.tt-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px Inter,sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap}.tt-primary{background:#123B5D;color:#fff;border-color:#123B5D}.tt-status{padding:6px 11px;border-radius:20px;font:650 11px Inter,sans-serif}
.tt-meta{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}.tt-meta>div{background:#fff;border:1px solid #CBD5E1;border-radius:11px;padding:14px 16px}.tt-meta span{display:block;font-size:11px;font-weight:600;color:#64748B;margin-bottom:6px}.tt-meta strong{font-size:13px;font-weight:600}
.tt-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;overflow:hidden}.tt-panel-head{display:flex;justify-content:space-between;align-items:center;padding:17px 19px;border-bottom:1px solid #E2E8F0}.tt-panel-head h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}.tt-panel-head p{font-size:12px;color:#64748B;margin:4px 0 0}.tt-panel-head>span{font-size:12px;color:#64748B}
.tt-scroll{overflow-x:auto}.tt-week{width:100%;min-width:1050px;border-collapse:collapse}.tt-week th{background:#F8FAFC;padding:13px 10px;text-align:left;font-size:12px;color:#64748B;border-bottom:1px solid #E2E8F0}.tt-week td{padding:10px;border-bottom:1px solid #E2E8F0;vertical-align:top;min-width:130px}.tt-time{min-width:145px!important;background:#FCFDFE}.tt-time strong{display:block;font-size:12px;color:#123B5D}.tt-time small,.tt-break small{display:block;font-size:11px;color:#64748B;margin-top:5px}
.tt-subject{background:#EFF6FF;border-left:3px solid #3B82F6;border-radius:7px;padding:9px;min-height:54px}.tt-subject strong{display:block;font-size:12px;color:#123B5D}.tt-subject span{display:block;font-size:11px;color:#475569;margin-top:5px}.tt-subject small{display:block;font-size:10px;color:#64748B;margin-top:4px}.tt-empty{font-size:12px;color:#94A3B8}.tt-break td{background:#F8FAFC;color:#64748B;text-align:center;font-size:11px;font-weight:650}.tt-break td:first-child{text-align:left}.tt-no-data{text-align:center;padding:35px!important;color:#64748B;font-size:13px}.tt-foot{font-size:11px;color:#94A3B8;margin-top:12px}.tt-success,.tt-error{padding:12px 15px;border-radius:9px;margin-bottom:15px;font-size:13px}.tt-success{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534}.tt-error{background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C}
@media(max-width:700px){.tt-meta{grid-template-columns:repeat(2,minmax(0,1fr))}}@media print{.tt-actions,.tt-back{display:none!important}.tt-scroll{overflow:visible}.tt-week{min-width:0}.tt-week td{padding:5px;min-width:0}.tt-subject{padding:5px}}
</style>
@endsection
