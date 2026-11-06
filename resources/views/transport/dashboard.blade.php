@extends('layouts.schoolnexa')
@section('title', 'Transport Dashboard')
@section('content')
@include('transport._style')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'vehicles' => '<rect x="4" y="3" width="16" height="15" rx="2.5"/><path d="M4 11h16"/><circle cx="8" cy="15" r=".8"/><circle cx="16" cy="15" r=".8"/><path d="M6 18v2M18 18v2"/>',
        'active'   => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'routes'   => '<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3zM9 3v15m6-12v15"/>',
        'drivers'  => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        'tracking' => '<path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/>',
        'fleet'    => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M8 15h3"/>',
        'builder'  => '<circle cx="6" cy="18" r="2.5"/><circle cx="18" cy="6" r="2.5"/><path d="M8.5 18H15a3 3 0 0 0 0-6H9a3 3 0 0 1 0-6h6.5"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['vehicles']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'vehicles' => 'bus',
        'active'   => 'checkmark',
        'routes'   => 'map',
        'drivers'  => 'driver',
        'tracking' => 'marker',
        'fleet'    => 'bus',
        'builder'  => 'route',
        'hero'     => 'bus',
    ];
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    /* Interactive 3D icon. sizes: xs | sm | md | lg */
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        $img = $slug
            ? '<img class="tri-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.tri-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="tri-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="tri-3d '.$size.($slug ? '' : ' fb').'"><span class="tri-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['Bus Profiles',   'vehicles', 'sky',     route('transport.vehicles')],
        ['Route Builder',  'builder',  'teal',    route('transport.routes')],
        ['Fleet Tracking', 'tracking', 'success', route('transport.tracking')],
        ['Fleet Overview', 'fleet',    'warning', '#fleetSection'],
    ];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .tri-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .tri-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .tri-3d.sm{width:48px;height:48px}
    .tri-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .tri-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:triFloat 4.2s ease-in-out infinite}
    .tri-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .tri-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .tri-ico{display:none}
    .tri-3d.fb .tri-3d-img{display:none}
    .tri-3d.fb .tri-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .tri-3d.pop .tri-3d-in{animation:triPop .55s cubic-bezier(.3,1.6,.5,1),triFloat 4.2s ease-in-out infinite .55s}
    @keyframes triFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes triPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.tri-3d-in{animation:none!important}.tri-3d{transition:none}}

    /* ================= BANNER ================= */
    .tri-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .tri-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .tri-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .tri-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .tri-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .tri-welcome-right{display:flex;align-items:center;gap:26px}
    .tri-date{text-align:right;font-size:13px;color:#64748B}
    .tri-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .tri-card{display:flex;align-items:center;gap:16px;color:inherit;cursor:default;transition:transform .15s,box-shadow .15s}
    .tri-card:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .tri-card .tr-kpi{line-height:1.15;margin:2px 0}
    .tri-card-body{min-width:0}

    /* ================= QUICK ACTIONS ================= */
    .tri-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
    .tri-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .tri-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .tri-quick strong{font-size:12px;font-weight:600;color:#123B5D}
    .tri-quick small{font-size:11px;color:#64748B;line-height:1.35}
    @@media (max-width:900px){.tri-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media (max-width:520px){.tri-welcome h1{font-size:22px}}

    /* ================= FLEET TABLE ================= */
    .tri-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:12px 0}
    .tri-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:14px;min-width:220px;outline:none;background:#fff}
    .tri-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .tri-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .tri-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .tri-sort.asc:after{content:' \2191';opacity:1}
    .tri-sort.desc:after{content:' \2193';opacity:1}
    .tri-row{transition:background .2s}
    .tri-row:hover{background:rgba(23,107,135,.05)}
    .tri-empty{display:none}
</style>

<div class="tr-wrap">

 {{-- BANNER --}}
 <div class="tri-welcome" data-tilt>
  <div>
   <div class="tri-eyebrow">{{ $greet }} <i></i> Transport Department</div>
   <h1>Welcome Back, Transport!</h1>
   <p>Here's an overview of your fleet operations, routes and driver assignments.</p>
  </div>
  <div class="tri-welcome-right">
   {!! $tile('hero', 'sky', 'lg') !!}
   <div class="tri-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
  </div>
 </div>

 <div class="tr-head">
  <div><div class="tr-title">Transport Dashboard</div><div class="tr-sub">Fleet operations, routes and driver assignments</div></div>
  <a class="tr-btn tr-primary" href="{{ route('transport.vehicles') }}">Manage vehicles</a>
 </div>

 {{-- KPI CARDS --}}
 <div class="tr-grid">
  <div class="tr-card tri-card" data-tilt>
   {!! $tile('vehicles', 'sky') !!}
   <div class="tri-card-body"><div class="tr-label">TOTAL VEHICLES</div><div class="tr-kpi" data-count="{{ (int) $vehicles }}">{{ $vehicles }}</div></div>
  </div>
  <div class="tr-card tri-card" data-tilt>
   {!! $tile('active', 'success') !!}
   <div class="tri-card-body"><div class="tr-label">ACTIVE VEHICLES</div><div class="tr-kpi" data-count="{{ (int) $active }}">{{ $active }}</div>
    <div class="tr-muted" id="activeRate" style="font-size:12px"></div></div>
  </div>
  <div class="tr-card tri-card" data-tilt>
   {!! $tile('routes', 'teal') !!}
   <div class="tri-card-body"><div class="tr-label">ACTIVE ROUTES</div><div class="tr-kpi" data-count="{{ (int) $routes }}">{{ $routes }}</div></div>
  </div>
  <div class="tr-card tri-card" data-tilt>
   {!! $tile('drivers', 'purple') !!}
   <div class="tri-card-body"><div class="tr-label">CURRENT DRIVER ASSIGNMENTS</div><div class="tr-kpi" data-count="{{ (int) $drivers }}">{{ $drivers }}</div></div>
  </div>
 </div>

 {{-- QUICK ACTIONS --}}
 <div class="tr-card" style="display:block">
  <b>Quick Actions</b>
  <div class="tri-quick-grid">
   @foreach($quick as [$label, $ic, $tone, $url])
    <a class="tri-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
     {!! $tile($ic, $tone, 'sm') !!}
     <strong>{{ $label }}</strong>
    </a>
   @endforeach
  </div>
 </div>

 {{-- FLEET OVERVIEW --}}
 <div class="tr-card" id="fleetSection" style="display:block;margin-top:16px">
  <b>Fleet overview</b>

  <div class="tri-tools">
   <input type="search" id="fleetSearch" class="tri-input" placeholder="Search vehicle, model or driver…" autocomplete="off">
   <select id="fleetStatus" class="tri-input" style="min-width:150px"><option value="">All statuses</option></select>
   <span class="tr-muted" id="fleetCount"></span>
  </div>

  <table class="tr-table" id="fleetTable">
   <thead><tr>
    <th class="tri-sort" data-key="0">Vehicle</th>
    <th class="tri-sort" data-key="1">Type / Model</th>
    <th class="tri-sort" data-key="2" data-num="1">Capacity</th>
    <th class="tri-sort" data-key="3">Driver</th>
    <th class="tri-sort" data-key="4">Status</th>
    <th></th>
   </tr></thead>
   <tbody>
   @forelse($fleet as $v)
    <tr class="tri-row">
     <td><b>{{ $v->registration_number }}</b></td>
     <td>{{ $v->vehicle_type }} {{ $v->make_model }}</td>
     <td>{{ $v->capacity ?? '—' }}</td>
     <td>{{ $v->driverAssignments->first()?->staff?->full_name ?? 'Unassigned' }}</td>
     <td><span class="tr-pill">{{ ucfirst($v->status) }}</span></td>
     <td><a class="tr-btn" href="{{ route('transport.vehicles.show', $v) }}">View</a></td>
    </tr>
   @empty
    <tr><td colspan="6" class="tr-muted">No vehicles registered yet.</td></tr>
   @endforelse
   </tbody>
  </table>
  <p class="tr-muted tri-empty" id="noMatch">No vehicles match your search.</p>
 </div>
</div>

<script>
(function(){
 var $ = function(s,c){return (c||document).querySelector(s)};
 var $$ = function(s,c){return Array.prototype.slice.call((c||document).querySelectorAll(s))};

 /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
 if(!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)){
  $$('[data-tilt]').forEach(function(card){
   var art=$('.tri-3d',card);if(!art)return;
   card.addEventListener('mousemove',function(e){
    var r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
    var cl=function(v){return Math.max(-22,Math.min(22,v))};
    art.style.setProperty('--ry',cl(x*44)+'deg');art.style.setProperty('--rx',cl(-y*44)+'deg');art.style.setProperty('--s','1.14');
   });
   card.addEventListener('mouseleave',function(){
    art.style.setProperty('--ry','0deg');art.style.setProperty('--rx','0deg');art.style.setProperty('--s','1');
   });
   card.addEventListener('click',function(){art.classList.remove('pop');void art.offsetWidth;art.classList.add('pop')});
  });
 }

 /* ---- Quick action that scrolls to a section on this page ---- */
 $$('[data-scroll]').forEach(function(a){
  a.addEventListener('click',function(e){
   e.preventDefault();
   var s=$(a.getAttribute('data-scroll'));if(!s)return;
   s.scrollIntoView({behavior:'smooth',block:'start'});
   s.style.transition='box-shadow .3s';s.style.boxShadow='0 0 0 3px rgba(23,107,135,.35)';
   setTimeout(function(){s.style.boxShadow=''},1200);
  });
 });

 /* ---- KPI count-up ---- */
 $$('[data-count]').forEach(function(el){
  var target=parseInt(el.getAttribute('data-count'),10)||0,start=null,dur=700;
  if(target===0)return;
  function step(ts){
   if(!start)start=ts;var p=Math.min((ts-start)/dur,1);
   el.textContent=Math.round(target*(1-Math.pow(1-p,3))).toLocaleString();
   if(p<1)requestAnimationFrame(step);
  }
  requestAnimationFrame(step);
 });

 var total={{ (int) $vehicles }},act={{ (int) $active }};
 if(total>0){$('#activeRate').textContent=Math.round(act/total*100)+'% of fleet';}

 /* ---- Fleet table: search, status filter, sort ---- */
 var table=$('#fleetTable'),tbody=$('tbody',table);
 function rows(){return $$('.tri-row',tbody)}
 if(rows().length===0){$('.tri-tools').style.display='none';return;}

 var statuses={};rows().forEach(function(r){statuses[r.children[4].textContent.trim()]=1});
 Object.keys(statuses).sort().forEach(function(s){
  var o=document.createElement('option');o.value=s;o.textContent=s;$('#fleetStatus').appendChild(o);
 });

 function applyFilter(){
  var q=$('#fleetSearch').value.trim().toLowerCase(),st=$('#fleetStatus').value,shown=0;
  rows().forEach(function(r){
   var ok=(!q||r.textContent.toLowerCase().indexOf(q)>-1)&&(!st||r.children[4].textContent.trim()===st);
   r.style.display=ok?'':'none';if(ok)shown++;
  });
  $('#fleetCount').textContent=shown+' of '+rows().length+' shown';
  $('#noMatch').style.display=shown===0?'block':'none';
 }
 $('#fleetSearch').addEventListener('input',applyFilter);
 $('#fleetStatus').addEventListener('change',applyFilter);
 applyFilter();

 var sortState={key:null,dir:1};
 $$('.tri-sort',table).forEach(function(th){
  th.addEventListener('click',function(){
   var key=parseInt(th.getAttribute('data-key'),10),num=th.hasAttribute('data-num');
   sortState.dir=(sortState.key===key)?-sortState.dir:1;sortState.key=key;
   $$('.tri-sort',table).forEach(function(x){x.classList.remove('asc','desc')});
   th.classList.add(sortState.dir===1?'asc':'desc');
   var list=rows();
   list.sort(function(a,b){
    var av=a.children[key].textContent.trim(),bv=b.children[key].textContent.trim();
    if(num){av=parseFloat(av);bv=parseFloat(bv);if(isNaN(av))av=-1;if(isNaN(bv))bv=-1;return (av-bv)*sortState.dir}
    return av.localeCompare(bv,undefined,{numeric:true})*sortState.dir;
   });
   list.forEach(function(r){tbody.appendChild(r)});
  });
 });
})();
</script>
@endsection