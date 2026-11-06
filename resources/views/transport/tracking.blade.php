@extends('layouts.schoolnexa')
@section('content') @include('transport._style')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

{{-- SVG icon sprite (no emojis). Use: <svg class="fl-i"><use href="#i-name"/></svg> --}}
<svg width="0" height="0" style="position:absolute" aria-hidden="true">
    <symbol id="i-bus" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="13" rx="2"/><path d="M3 11h18M12 4v7"/><circle cx="7.5" cy="19.5" r="1.5"/><circle cx="16.5" cy="19.5" r="1.5"/></symbol>
    <symbol id="i-van" viewBox="0 0 24 24"><path d="M2 17V7a1 1 0 0 1 1-1h10v11"/><path d="M13 10h4l4 4v3"/><path d="M9 17h5"/><circle cx="6.5" cy="17.5" r="1.8"/><circle cx="17.5" cy="17.5" r="1.8"/></symbol>
    <symbol id="i-car" viewBox="0 0 24 24"><path d="M5 15l1.6-4.6A2 2 0 0 1 8.5 9h7a2 2 0 0 1 1.9 1.4L19 15"/><rect x="3" y="15" width="18" height="4" rx="1.5"/><path d="M6 19v1.5M18 19v1.5"/></symbol>
    <symbol id="i-arrow-left" viewBox="0 0 24 24"><path d="M19 12H5M11 6l-6 6 6 6"/></symbol>
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></symbol>
    <symbol id="i-reset" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></symbol>
    <symbol id="i-chev-down" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></symbol>
    <symbol id="i-chev-up" viewBox="0 0 24 24"><path d="M18 15l-6-6-6 6"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M8 12l3 3 5-6"/></symbol>
    <symbol id="i-warn" viewBox="0 0 24 24"><path d="M12 3L2 20h20L12 3z"/><path d="M12 10v4M12 17.5v.01"/></symbol>
    <symbol id="i-x" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M9 9l6 6M15 9l-6 6"/></symbol>
    <symbol id="i-dash" viewBox="0 0 24 24"><path d="M6 12h12"/></symbol>
    <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></symbol>
    <symbol id="i-user-off" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" stroke-dasharray="3 3"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6" stroke-dasharray="3 3"/></symbol>
    <symbol id="i-route" viewBox="0 0 24 24"><circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h7a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h7"/></symbol>
    <symbol id="i-pin" viewBox="0 0 24 24"><path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/></symbol>
    <symbol id="i-map" viewBox="0 0 24 24"><path d="M9 4L3 6v14l6-2 6 2 6-2V4l-6 2-6-2z"/><path d="M9 4v14M15 6v14"/></symbol>
    <symbol id="i-nav" viewBox="0 0 24 24"><path d="M3 11l18-8-8 18-2-8-8-2z"/></symbol>
    <symbol id="i-gauge" viewBox="0 0 24 24"><path d="M3 18a9 9 0 1 1 18 0"/><path d="M12 18l4-6"/></symbol>
</svg>

<style>
/* Fonts and base colours are inherited from transport._style; accents below are icon/map/status touches only */
.fl-i{width:1.15em;height:1.15em;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;vertical-align:-.2em;flex:none}
.fl-dot{display:inline-block;width:9px;height:9px;border-radius:50%;margin-right:6px;background:currentColor;vertical-align:baseline}
.c-blue{color:#2563eb}.c-green{color:#16a34a}.c-orange{color:#f97316}.c-red{color:#e11d48}
.c-purple{color:#9333ea}.c-teal{color:#0891b2}.c-amber{color:#ca8a04}.c-muted{color:#94a3b8}

.fl-tools{display:flex;gap:10px;flex-wrap:wrap;margin:10px 0 12px;align-items:center}
.fl-tools input,.fl-tools select,.fl-tools button{font-family:inherit;font-size:inherit;color:inherit;background:transparent;border:1px solid #e2e8f0;border-radius:8px;padding:8px 10px}
.fl-tools input,.fl-tools select{min-width:180px}
.fl-tools button{cursor:pointer;padding:8px 12px;display:inline-flex;gap:6px;align-items:center}
.fl-tools button:hover,.fl-table tbody tr:hover{background:rgba(148,163,184,.12)}
.fl-s{position:relative;display:inline-block}
.fl-s .fl-i{position:absolute;left:10px;top:50%;transform:translateY(-50%);opacity:.55;pointer-events:none}
.fl-s input{padding-left:34px;min-width:240px}
.fl-count{margin-left:auto}
.fl-hide{display:none!important}

.fl-table th[data-sort]{cursor:pointer;user-select:none;white-space:nowrap}
.fl-table th[data-sort]::after{content:'';display:inline-block;width:8px;height:11px;margin-left:6px;vertical-align:-1px;background:center/contain no-repeat url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 14'%3E%3Cpath d='M5 0L9 5H1zM5 14L1 9h8z' fill='%2394a3b8'/%3E%3C/svg%3E")}
.fl-table th.asc::after{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 14'%3E%3Cpath d='M5 0L9 5H1z' fill='%23334155'/%3E%3Cpath d='M5 14L1 9h8z' fill='%23cbd5e1'/%3E%3C/svg%3E")}
.fl-table th.desc::after{background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 10 14'%3E%3Cpath d='M5 0L9 5H1z' fill='%23cbd5e1'/%3E%3Cpath d='M5 14L1 9h8z' fill='%23334155'/%3E%3C/svg%3E")}

/* stat cards */
.fl-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:14px}
.fl-stat{display:flex;gap:12px;align-items:center;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;cursor:default;transition:transform .15s,box-shadow .15s}
.fl-stat[data-filter]{cursor:pointer}
.fl-stat:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(15,23,42,.08)}
.fl-stat .ic{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex:none}
.fl-stat .ic .fl-i{width:22px;height:22px}
.fl-stat .num{font-size:1.5em;font-weight:700;line-height:1.1}
.fl-ic-blue{background:rgba(37,99,235,.14);color:#2563eb}.fl-ic-green{background:rgba(22,163,74,.14);color:#16a34a}
.fl-ic-orange{background:rgba(249,115,22,.16);color:#f97316}.fl-ic-red{background:rgba(225,29,72,.14);color:#e11d48}
.fl-ic-purple{background:rgba(147,51,234,.14);color:#9333ea}.fl-ic-teal{background:rgba(8,145,178,.14);color:#0891b2}

/* table cells */
.fl-cell{display:inline-flex;gap:8px;align-items:center}
.fl-exp{white-space:nowrap}
.fl-exp .tag{font-size:.85em;margin-left:4px}

/* map */
#flMap{height:400px;border:1px solid #e2e8f0;border-radius:12px;margin:10px 0;z-index:0}
.leaflet-container{font-family:inherit}
.fl-pin{width:28px;height:28px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center}
.fl-pin span{transform:rotate(45deg);color:#fff;font-weight:700;font-size:12px}
.fl-legend{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px}
.fl-chip{display:inline-flex;gap:6px;align-items:center;border:1px solid #e2e8f0;border-radius:999px;padding:5px 12px;cursor:pointer;font-family:inherit;font-size:inherit;color:inherit;background:transparent}
.fl-chip i{width:12px;height:12px;border-radius:50%;display:inline-block}
.fl-chip.off{opacity:.4;text-decoration:line-through}

/* routes */
.fl-route{border-bottom:1px solid #e2e8f0}
.fl-route summary{cursor:pointer;padding:12px 0;list-style:none;display:flex;gap:8px;align-items:center}
.fl-route summary::-webkit-details-marker{display:none}
.fl-route summary::before{content:'';width:0;height:0;border-left:6px solid #94a3b8;border-top:5px solid transparent;border-bottom:5px solid transparent;transition:transform .15s}
.fl-route[open] summary::before{transform:rotate(90deg)}
.fl-route summary .fl-dot{margin-right:0}
.fl-route ol{list-style:none;margin:0 0 12px;padding-left:6px}
.fl-route li{display:flex;gap:10px;align-items:flex-start;padding:7px 8px;border-radius:8px}
.fl-route li.has-geo{cursor:pointer}
.fl-route li.has-geo:hover{background:rgba(148,163,184,.14)}
.fl-n{flex:none;width:24px;height:24px;border-radius:50%;color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;margin-top:1px}
.fl-route a{color:inherit;text-decoration:underline}
.fl-go{margin-left:auto;white-space:nowrap;display:inline-flex;gap:4px;align-items:center}
.tr-btn .fl-i{margin-right:6px}
</style>

@php
    $palette = ['#2563eb','#16a34a','#f97316','#9333ea','#e11d48','#0891b2','#ca8a04','#db2777'];

    $typeIcon = function ($t) {
        $t = strtolower((string) $t);
        if (str_contains($t, 'van') || str_contains($t, 'mini')) return 'van';
        if (str_contains($t, 'car') || str_contains($t, 'jeep') || str_contains($t, 'suv')) return 'car';
        return 'bus';
    };
    // colour class for the status dot
    $statusColor = function ($s) {
        $s = strtolower((string) $s);
        if (str_contains($s, 'active') || str_contains($s, 'available') || str_contains($s, 'running')) return 'c-green';
        if (str_contains($s, 'maint') || str_contains($s, 'repair') || str_contains($s, 'service')) return 'c-orange';
        if (str_contains($s, 'inactive') || str_contains($s, 'retired') || str_contains($s, 'out')) return 'c-red';
        return 'c-amber';
    };
    // returns [icon, colour class, short label, days]
    $expiry = function ($d) {
        if (!$d) return ['dash', 'c-muted', '', null];
        $days = (int) now()->startOfDay()->diffInDays($d->copy()->startOfDay(), false);
        if ($days < 0)   return ['x', 'c-red', 'Expired', $days];
        if ($days <= 30) return ['warn', 'c-orange', $days.'d left', $days];
        return ['check', 'c-green', '', $days];
    };

    $totalV     = $vehicles->count();
    $activeV    = $vehicles->filter(fn ($v) => str_contains(strtolower((string) $v->status), 'active'))->count();
    $unassigned = $vehicles->filter(fn ($v) => !$v->driverAssignments->first())->count();
    $attention  = $vehicles->filter(function ($v) use ($expiry) {
        foreach ([$v->insurance_expiry, $v->permit_expiry] as $d) {
            $e = $expiry($d);
            if ($e[3] !== null && $e[3] <= 30) return true;
        }
        return false;
    })->count();
    $totalStops = $routes->sum(fn ($r) => $r->stops->count());

    $mapData = $routes->values()->map(function ($r, $ri) use ($palette) {
        return [
            'name'  => $r->name,
            'color' => $palette[$ri % count($palette)],
            'stops' => $r->stops->values()->map(fn ($s, $si) => [
                'n'       => $si + 1,
                'name'    => $s->name,
                'address' => $s->address,
                'lat'     => ($s->latitude && $s->longitude) ? (float) $s->latitude : null,
                'lng'     => ($s->latitude && $s->longitude) ? (float) $s->longitude : null,
            ])->all(),
        ];
    })->all();
    $mappedStops = collect($mapData)->flatMap(fn ($r) => $r['stops'])->filter(fn ($s) => $s['lat'] !== null)->count();
@endphp

<div class="tr-wrap"><div class="tr-head"><div><div class="tr-title">Live Fleet Overview</div><div class="tr-sub">Vehicle availability and configured route stop locations</div></div><a class="tr-btn" href="{{ route('transport.dashboard') }}"><svg class="fl-i"><use href="#i-arrow-left"/></svg>Back to transport</a></div>

<div class="fl-stats">
    <div class="fl-stat" data-filter=""><div class="ic fl-ic-blue"><svg class="fl-i"><use href="#i-bus"/></svg></div><div><div class="num">{{ $totalV }}</div><div class="tr-muted">Total vehicles</div></div></div>
    <div class="fl-stat" data-filter="active"><div class="ic fl-ic-green"><svg class="fl-i"><use href="#i-check"/></svg></div><div><div class="num">{{ $activeV }}</div><div class="tr-muted">Active</div></div></div>
    <div class="fl-stat" data-filter="__unassigned"><div class="ic fl-ic-orange"><svg class="fl-i"><use href="#i-user"/></svg></div><div><div class="num">{{ $unassigned }}</div><div class="tr-muted">No driver assigned</div></div></div>
    <div class="fl-stat" data-filter="__attention"><div class="ic fl-ic-red"><svg class="fl-i"><use href="#i-warn"/></svg></div><div><div class="num">{{ $attention }}</div><div class="tr-muted">Expiring / expired docs</div></div></div>
    <div class="fl-stat"><div class="ic fl-ic-purple"><svg class="fl-i"><use href="#i-route"/></svg></div><div><div class="num">{{ $routes->count() }}</div><div class="tr-muted">Routes</div></div></div>
    <div class="fl-stat"><div class="ic fl-ic-teal"><svg class="fl-i"><use href="#i-pin"/></svg></div><div><div class="num">{{ $mappedStops }}<span class="tr-muted" style="font-size:.6em;font-weight:400"> / {{ $totalStops }}</span></div><div class="tr-muted">Stops on map</div></div></div>
</div>

<div class="tr-card"><b>Vehicle status</b>
<div class="fl-tools">
    <label class="fl-s"><svg class="fl-i"><use href="#i-search"/></svg><input type="search" id="flSearch" placeholder="Search vehicle, type or driver"></label>
    <select id="flStatus">
        <option value="">All statuses</option>
        @foreach($vehicles->pluck('status')->filter()->unique() as $s)
            <option value="{{ strtolower($s) }}">{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <button type="button" id="flReset"><svg class="fl-i"><use href="#i-reset"/></svg>Reset</button>
    <span class="tr-muted fl-count" id="flCount"></span>
</div>
<table class="tr-table fl-table" id="flTable"><thead><tr>
    <th data-sort="text">Vehicle</th><th data-sort="text">Type</th><th data-sort="text">Current driver</th><th data-sort="text">Status</th><th data-sort="date">Insurance</th><th data-sort="date">Permit</th>
</tr></thead><tbody>
@forelse($vehicles as $v)
@php
    $drv = $v->driverAssignments->first()?->staff?->full_name;
    $ins = $expiry($v->insurance_expiry);
    $per = $expiry($v->permit_expiry);
    $flag = ($ins[3] !== null && $ins[3] <= 30) || ($per[3] !== null && $per[3] <= 30);
@endphp
<tr data-row data-status="{{ strtolower($v->status) }}" data-unassigned="{{ $drv ? 0 : 1 }}" data-attention="{{ $flag ? 1 : 0 }}">
    <td data-v="{{ strtolower($v->registration_number) }}"><span class="fl-cell"><svg class="fl-i c-blue" style="width:1.4em;height:1.4em"><use href="#i-{{ $typeIcon($v->vehicle_type) }}"/></svg><b>{{ $v->registration_number }}</b></span></td>
    <td data-v="{{ strtolower($v->vehicle_type) }}">{{ $v->vehicle_type }}</td>
    <td data-v="{{ strtolower($drv ?? 'Unassigned') }}"><span class="fl-cell">@if($drv)<svg class="fl-i c-teal"><use href="#i-user"/></svg>{{ $drv }}@else<svg class="fl-i c-muted"><use href="#i-user-off"/></svg><span class="tr-muted">Unassigned</span>@endif</span></td>
    <td data-v="{{ strtolower($v->status) }}"><span class="tr-pill"><span class="fl-dot {{ $statusColor($v->status) }}"></span>{{ ucfirst($v->status) }}</span></td>
    <td class="fl-exp" data-v="{{ $v->insurance_expiry?->format('Y-m-d') ?? '' }}"><span class="fl-cell"><svg class="fl-i {{ $ins[1] }}"><use href="#i-{{ $ins[0] }}"/></svg>{{ $v->insurance_expiry?->format('d M Y') ?? '—' }}@if($ins[2])<span class="tag {{ $ins[1] }}">({{ $ins[2] }})</span>@endif</span></td>
    <td class="fl-exp" data-v="{{ $v->permit_expiry?->format('Y-m-d') ?? '' }}"><span class="fl-cell"><svg class="fl-i {{ $per[1] }}"><use href="#i-{{ $per[0] }}"/></svg>{{ $v->permit_expiry?->format('d M Y') ?? '—' }}@if($per[2])<span class="tag {{ $per[1] }}">({{ $per[2] }})</span>@endif</span></td>
</tr>
@empty
<tr><td colspan="6" class="tr-muted">No vehicles registered.</td></tr>
@endforelse
<tr id="flNoMatch" class="fl-hide"><td colspan="6" class="tr-muted">No vehicles match your filters.</td></tr>
</tbody></table></div>

<div class="tr-card"><b>Route stop map</b>
<p class="tr-muted">Click a route chip to show or hide it. Click a stop below to zoom to it. Coordinates are shown only when recorded. This page does not claim real-time GPS tracking.</p>
<div class="fl-legend" id="flLegend"></div>
<div id="flMap"></div>
<p class="tr-muted fl-hide" id="flMapEmpty">No stop coordinates are recorded yet, so there is nothing to plot.</p>
</div>

<div class="tr-card"><b>Route stop locations</b>
<div class="fl-tools">
    <label class="fl-s"><svg class="fl-i"><use href="#i-search"/></svg><input type="search" id="rtSearch" placeholder="Search route, stop or address"></label>
    <button type="button" id="rtExpand"><svg class="fl-i"><use href="#i-chev-down"/></svg>Expand all</button>
    <button type="button" id="rtCollapse"><svg class="fl-i"><use href="#i-chev-up"/></svg>Collapse all</button>
</div>
@forelse($routes as $ri => $route)
@php $color = $palette[$ri % count($palette)]; @endphp
<details class="fl-route" data-route>
    <summary><span class="fl-dot" style="background:{{ $color }}"></span><b>{{ $route->name }}</b><span class="tr-muted">({{ $route->stops->count() }} {{ \Illuminate\Support\Str::plural('stop', $route->stops->count()) }})</span></summary>
    <ol>
    @forelse($route->stops as $si => $stop)
        @php $geo = $stop->latitude && $stop->longitude; @endphp
        <li data-stop @if($geo) class="has-geo" data-ri="{{ $ri }}" data-si="{{ $si }}" title="Show on map" @endif>
            <span class="fl-n" style="background:{{ $color }}">{{ $si + 1 }}</span>
            <span>{{ $stop->name }} — {{ $stop->address ?? 'Address not set' }}
                @if($geo)<span class="tr-muted">({{ $stop->latitude }}, {{ $stop->longitude }})</span>@endif
            </span>
            @if($geo)<a class="fl-go tr-muted" href="https://www.google.com/maps/dir/?api=1&destination={{ $stop->latitude }},{{ $stop->longitude }}" target="_blank" rel="noopener" onclick="event.stopPropagation()"><svg class="fl-i"><use href="#i-nav"/></svg>Directions</a>@endif
        </li>
    @empty
        <li class="tr-muted">No stops configured.</li>
    @endforelse
    </ol>
</details>
@empty
<p class="tr-muted">No routes configured.</p>
@endforelse
<p class="tr-muted fl-hide" id="rtNoMatch">No routes or stops match your search.</p>
</div></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function(){
    // ---------- Vehicle table: search, status filter, stat-card filters, sort ----------
    var table=document.getElementById('flTable'), body=table.tBodies[0];
    var rows=[].slice.call(body.querySelectorAll('tr[data-row]')), original=rows.slice();
    var q=document.getElementById('flSearch'), st=document.getElementById('flStatus');
    var count=document.getElementById('flCount'), none=document.getElementById('flNoMatch');
    var special=''; // '__unassigned' | '__attention' from stat cards

    function filter(){
        var t=q.value.trim().toLowerCase(), s=st.value, n=0;
        rows.forEach(function(r){
            var ok=(!s||r.dataset.status===s)
                &&(special!=='__unassigned'||r.dataset.unassigned==='1')
                &&(special!=='__attention'||r.dataset.attention==='1')
                &&(!t||r.textContent.toLowerCase().indexOf(t)>-1);
            r.classList.toggle('fl-hide',!ok); if(ok)n++;
        });
        if(rows.length){none.classList.toggle('fl-hide',n>0); count.textContent=n+' of '+rows.length+' vehicles';}
    }
    q.addEventListener('input',filter); st.addEventListener('change',filter);

    [].forEach.call(document.querySelectorAll('.fl-stat[data-filter]'),function(c){
        c.addEventListener('click',function(){
            var f=c.dataset.filter;
            special=''; st.value='';
            if(f==='__unassigned'||f==='__attention') special=f; else st.value=f;
            filter(); table.scrollIntoView({behavior:'smooth',block:'start'});
        });
    });

    document.getElementById('flReset').addEventListener('click',function(){
        q.value='';st.value='';special='';
        [].forEach.call(table.querySelectorAll('th'),function(h){h.classList.remove('asc','desc');});
        rows=original.slice(); rows.forEach(function(r){body.insertBefore(r,none);}); filter();
    });

    [].forEach.call(table.querySelectorAll('th[data-sort]'),function(th,idx){
        th.addEventListener('click',function(){
            var dir=th.classList.contains('asc')?'desc':'asc', isDate=th.dataset.sort==='date';
            [].forEach.call(table.querySelectorAll('th'),function(h){h.classList.remove('asc','desc');});
            th.classList.add(dir);
            rows.sort(function(a,b){
                var x=a.cells[idx].dataset.v||'', y=b.cells[idx].dataset.v||'';
                if(isDate){ if(!x&&!y)return 0; if(!x)return 1; if(!y)return -1; }
                var c=x.localeCompare(y,undefined,{numeric:true});
                return dir==='asc'?c:-c;
            });
            rows.forEach(function(r){body.insertBefore(r,none);});
        });
    });
    filter();

    // ---------- Routes list: search + expand/collapse ----------
    var routes=[].slice.call(document.querySelectorAll('[data-route]'));
    var rq=document.getElementById('rtSearch'), rNone=document.getElementById('rtNoMatch');
    rq.addEventListener('input',function(){
        var t=rq.value.trim().toLowerCase(), shown=0;
        routes.forEach(function(r){
            var stops=[].slice.call(r.querySelectorAll('[data-stop]'));
            var nameHit=!t||r.querySelector('summary').textContent.toLowerCase().indexOf(t)>-1, any=false;
            stops.forEach(function(s){
                var hit=nameHit||s.textContent.toLowerCase().indexOf(t)>-1;
                s.classList.toggle('fl-hide',!hit); if(hit)any=true;
            });
            var vis=nameHit||any;
            r.classList.toggle('fl-hide',!vis); if(vis)shown++;
            if(t&&vis)r.open=true;
        });
        rNone.classList.toggle('fl-hide',shown>0||!routes.length);
    });
    document.getElementById('rtExpand').addEventListener('click',function(){routes.forEach(function(r){r.open=true;});});
    document.getElementById('rtCollapse').addEventListener('click',function(){routes.forEach(function(r){r.open=false;});});

    // ---------- Map ----------
    var data=@json($mapData);
    var mapEl=document.getElementById('flMap'), emptyEl=document.getElementById('flMapEmpty'), legend=document.getElementById('flLegend');
    var hasGeo=data.some(function(r){return r.stops.some(function(s){return s.lat!==null;});});
    if(!hasGeo||!window.L){
        mapEl.classList.add('fl-hide'); legend.classList.add('fl-hide');
        emptyEl.classList.remove('fl-hide');
        if(hasGeo&&!window.L) emptyEl.textContent='The map library could not be loaded. Check your internet connection.';
        return;
    }
    function esc(s){var d=document.createElement('div');d.textContent=s==null?'':s;return d.innerHTML;}
    var map=L.map(mapEl,{scrollWheelZoom:false});
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:19,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    map.on('click',function(){map.scrollWheelZoom.enable();});
    map.on('mouseout',function(){map.scrollWheelZoom.disable();});

    var groups=[], markers={}, all=[];
    data.forEach(function(r,ri){
        var g=L.layerGroup(), pts=[];
        r.stops.forEach(function(s,si){
            if(s.lat===null)return;
            var ll=[s.lat,s.lng]; pts.push(ll); all.push(ll);
            var icon=L.divIcon({className:'',iconSize:[28,28],iconAnchor:[14,28],popupAnchor:[0,-26],
                html:'<div class="fl-pin" style="background:'+r.color+'"><span>'+s.n+'</span></div>'});
            var m=L.marker(ll,{icon:icon}).bindPopup(
                '<b>'+esc(s.name)+'</b><br>'+esc(r.name)+' &middot; stop '+s.n+'<br>'+esc(s.address||'Address not set')+
                '<br><a target="_blank" rel="noopener" href="https://www.google.com/maps/dir/?api=1&destination='+s.lat+','+s.lng+'">Directions</a>');
            g.addLayer(m); markers[ri+'-'+si]=m;
        });
        if(pts.length>1) g.addLayer(L.polyline(pts,{color:r.color,weight:4,opacity:.75,dashArray:'8 6'}));
        g.addTo(map); groups[ri]=g;

        var chip=document.createElement('button');
        chip.type='button'; chip.className='fl-chip';
        chip.innerHTML='<i style="background:'+r.color+'"></i>'+esc(r.name);
        chip.addEventListener('click',function(){
            var off=chip.classList.toggle('off');
            if(off)map.removeLayer(g); else g.addTo(map);
        });
        legend.appendChild(chip);
    });
    map.fitBounds(L.latLngBounds(all),{padding:[40,40],maxZoom:16});

    // click a stop in the list -> fly to marker
    [].forEach.call(document.querySelectorAll('li.has-geo'),function(li){
        li.addEventListener('click',function(){
            var ri=li.dataset.ri, si=li.dataset.si, m=markers[ri+'-'+si];
            if(!m)return;
            if(!map.hasLayer(groups[ri])) groups[ri].addTo(map);
            mapEl.scrollIntoView({behavior:'smooth',block:'center'});
            map.flyTo(m.getLatLng(),16,{duration:.8});
            setTimeout(function(){m.openPopup();},850);
        });
    });
    setTimeout(function(){map.invalidateSize();},200);
})();
</script>
@endsection