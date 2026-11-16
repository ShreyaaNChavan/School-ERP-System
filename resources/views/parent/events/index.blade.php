@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')

<style>
.px-wrap{max-width:1200px;margin:0 auto;padding:8px 0 28px;color:#172b45}
.px-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.px-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#16809a}
.px-title{font-size:27px;font-weight:800;margin:4px 0;color:#123b5d}
.px-sub{color:#718096;font-size:13px}
.px-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin:18px 0}
.px-card{background:#fff;border:1px solid #e5edf3;border-radius:16px;padding:18px;box-shadow:0 5px 18px rgba(18,59,93,.045)}
.px-stat{font-size:24px;font-weight:800;color:#123b5d;margin-top:6px}
.px-label{font-size:12px;color:#718096;font-weight:700}
.px-table{width:100%;border-collapse:collapse}
.px-table th,.px-table td{padding:13px 12px;text-align:left;border-bottom:1px solid #edf1f5;font-size:13px;vertical-align:middle}
.px-table th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#718096;background:#f8fafc}
.px-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 14px;border-radius:10px;background:#176b87;color:white!important;text-decoration:none;font-size:13px;font-weight:700;border:0;cursor:pointer}
.px-btn.secondary{background:#edf5f8;color:#176b87!important}
.px-badge{display:inline-block;padding:5px 9px;border-radius:30px;background:#edf5f8;color:#176b87;font-size:11px;font-weight:800;text-transform:capitalize}
.px-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.px-field{display:flex;flex-direction:column;gap:6px}
.px-field.full{grid-column:1/-1}
.px-field label{font-size:12px;font-weight:700;color:#53677d}
.px-field input,.px-field select,.px-field textarea{width:100%;border:1px solid #dce5ec;border-radius:10px;padding:10px 12px;background:#fff;color:#243b53;font:inherit;font-size:13px}
.px-empty{padding:32px;text-align:center;color:#718096}
.px-alert{padding:12px 15px;border-radius:10px;background:#eff8f3;color:#23734a;margin-bottom:15px;font-size:13px}
@media(max-width:650px){.px-form{grid-template-columns:1fr}.px-table{min-width:650px}.px-table-wrap{overflow-x:auto}.px-title{font-size:23px}}
</style>

<div class="px-wrap">

<div class="px-head"><div><div class="px-kicker">School life</div><h1 class="px-title">Events Calendar</h1><div class="px-sub">School-wide and relevant campus events for your children.</div></div></div>
<div class="px-card" style="margin-bottom:15px"><form method="GET" class="px-form"><div class="px-field"><label>Event type</label><select name="type"><option value="">All event types</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ $type }}</option>@endforeach</select></div><div class="px-field" style="justify-content:end"><button class="px-btn">Filter events</button></div></form></div>
<div class="px-grid">
@forelse($events as $event)
<div class="px-card"><div style="display:flex;justify-content:space-between;gap:10px"><span class="px-badge">{{ $event->event_type ?? 'Event' }}</span><span class="px-sub">{{ ucfirst($event->status) }}</span></div><h3 style="font-size:17px;color:#123b5d;margin:13px 0 7px">{{ $event->title }}</h3><div class="px-sub">{{ \Carbon\Carbon::parse($event->starts_at)->format('D, d M Y · h:i A') }}</div>@if($event->ends_at)<div class="px-sub">Until {{ \Carbon\Carbon::parse($event->ends_at)->format('d M Y · h:i A') }}</div>@endif<p style="font-size:13px;color:#53677d;margin:12px 0">{{ \Illuminate\Support\Str::limit(strip_tags($event->description ?? ''),150) }}</p><div class="px-sub">Venue: {{ $event->venue ?: 'To be announced' }}</div><div class="px-sub">Campus: {{ $event->campus_name ?: 'School-wide' }}</div></div>
@empty<div class="px-card px-empty">No upcoming or published school events are currently listed.</div>@endforelse
</div><div>{{ $events->links() }}</div>

</div>
@endsection
