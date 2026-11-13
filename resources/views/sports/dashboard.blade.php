@extends('layouts.schoolnexa')
@section('title', 'Sports Management')
@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'   => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0Z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
        'active'  => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'all'     => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'add'     => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'recent'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'back'    => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'  => 'trophy',
        'active' => 'checkmark',
        'all'    => 'clipboard',
        'add'    => 'plus-math',
        'recent' => 'hourglass',
        'back'   => 'home',
        'hero'   => 'football',
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
            ? '<img class="sni-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.sni-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="sni-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="sni-3d '.$size.($slug ? '' : ' fb').'"><span class="sni-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['View All',   'all',    'sky',     route('sports.index')],
        ['Add New',    'add',    'success', route('sports.create')],
        ['Recent',     'recent', 'warning', '#recentSection'],
        ['Dashboard',  'back',   'teal',    url('/dashboard')],
    ];
@endphp

<div class="sn-page">@include('shared.module-style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .sni-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .sni-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .sni-3d.sm{width:48px;height:48px}
    .sni-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .sni-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:sniFloat 4.2s ease-in-out infinite}
    .sni-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .sni-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .sni-ico{display:none}
    .sni-3d.fb .sni-3d-img{display:none}
    .sni-3d.fb .sni-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .sni-3d.pop .sni-3d-in{animation:sniPop .55s cubic-bezier(.3,1.6,.5,1),sniFloat 4.2s ease-in-out infinite .55s}
    @keyframes sniFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes sniPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.sni-3d-in{animation:none!important}.sni-3d{transition:none}}

    /* ================= BANNER ================= */
    .sni-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .sni-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .sni-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .sni-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .sni-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .sni-welcome-right{display:flex;align-items:center;gap:26px}
    .sni-date{text-align:right;font-size:13px;color:#64748B}
    .sni-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .sni-kpi{display:flex;align-items:center;gap:16px;cursor:default;transition:transform .15s,box-shadow .15s}
    .sni-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .sni-kpi-body{min-width:0;display:flex;flex-direction:column;gap:2px}
    .sni-kpi-body b{line-height:1.15}

    /* ================= QUICK ACTIONS ================= */
    .sni-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
    .sni-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .sni-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .sni-quick strong{font-size:12px;font-weight:600;color:#123B5D}
    @@media (max-width:900px){.sni-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media (max-width:520px){.sni-welcome h1{font-size:22px}}

    /* ================= TABLE ================= */
    .sni-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 12px}
    .sni-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:14px;min-width:220px;outline:none;background:#fff}
    .sni-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .sni-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .sni-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .sni-sort.asc:after{content:' \2191';opacity:1}
    .sni-sort.desc:after{content:' \2193';opacity:1}
    .sni-row{transition:background .2s}
    .sni-row:hover{background:rgba(23,107,135,.05)}
    .sni-empty{display:none}
    .sni-muted{font-size:12px;color:#64748B}
    .sni-status{display:inline-flex;align-items:center;padding:5px 11px;border-radius:999px;font-size:11px;font-weight:700;line-height:1.3}
    .sni-status.is-active{background:#DCFCE7;color:#166534}
    .sni-status.is-inactive{background:#F1F5F9;color:#475569}
</style>

{{-- BANNER --}}
<div class="sni-welcome" data-tilt>
 <div>
  <div class="sni-eyebrow">{{ $greet }} <i></i> Sports Department</div>
  <h1>Welcome Back, Sports!</h1>
  <p>Here's an overview of your sports management records and activities.</p>
 </div>
 <div class="sni-welcome-right">
  {!! $tile('hero', 'sky', 'lg') !!}
  <div class="sni-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
 </div>
</div>

<div class="sn-head"><div><h1>Sports Management</h1><div class="sn-sub">Manage sports management records and activities</div></div><div class="sn-actions"><a class="sn-btn sn-primary" href="{{ route('sports.create') }}">+ Add New</a></div></div>

{{-- KPI CARDS --}}
<div class="sn-grid">
 <div class="sn-kpi sni-kpi" data-tilt>
  {!! $tile('total', 'warning') !!}
  <div class="sni-kpi-body"><b data-count="{{ (int) $total }}">{{ $total }}</b><span>Total records</span></div>
 </div>
 <div class="sn-kpi sni-kpi" data-tilt>
  {!! $tile('active', 'success') !!}
  <div class="sni-kpi-body"><b data-count="{{ (int) $active }}">{{ $active }}</b><span>Active / Open</span><span class="sni-muted" id="activeRate"></span></div>
 </div>
</div>

{{-- QUICK ACTIONS --}}
<div class="sn-panel" style="margin-top:18px">
 <h3 style="margin:0;color:#123B5D">Quick Actions</h3>
 <div class="sni-quick-grid">
  @foreach($quick as [$label, $ic, $tone, $url])
   <a class="sni-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
    {!! $tile($ic, $tone, 'sm') !!}
    <strong>{{ $label }}</strong>
   </a>
  @endforeach
 </div>
</div>

{{-- RECENTLY ADDED --}}
<div class="sn-panel" id="recentSection" style="margin-top:18px">
 <h3 style="margin:0 0 14px;color:#123B5D">Recently added</h3>

 <div class="sni-tools">
  <input type="search" id="rowSearch" class="sni-input" placeholder="Search name or status…" autocomplete="off">
  <select id="rowStatus" class="sni-input" style="min-width:150px"><option value="">All statuses</option></select>
  <span class="sni-muted" id="rowCount"></span>
 </div>

 <table class="sn-table" id="recentTable">
  <thead><tr>
   <th class="sni-sort" data-key="0">NAME / TITLE</th>
   <th class="sni-sort" data-key="1">STATUS</th>
   <th class="sni-sort" data-key="2">CREATED</th>
   <th>ACTIONS</th>
  </tr></thead>
  <tbody>
  @forelse($recent as $row)
   <tr class="sni-row" data-sort-2="{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') : '' }}">
    <td>{{ $row->name }}</td>
    <td><span class="sni-status {{ (int) $row->is_active === 1 ? 'is-active' : 'is-inactive' }}">{{ (int) $row->is_active === 1 ? 'Active' : 'Inactive' }}</span></td>
    <td>{{ optional($row->created_at ? \Carbon\Carbon::parse($row->created_at) : null)->format('d M Y') ?? '—' }}</td>
    <td><a class="sn-btn" href="{{ route('sports.show', $row->id) }}">View</a></td>
   </tr>
  @empty
   <tr><td colspan="4">No records yet.</td></tr>
  @endforelse
  </tbody>
 </table>
 <p class="sni-muted sni-empty" id="noMatch" style="margin-top:10px">No records match your search.</p>
</div>
</div>

<script>
(function(){
 var $ = function(s,c){return (c||document).querySelector(s)};
 var $$ = function(s,c){return Array.prototype.slice.call((c||document).querySelectorAll(s))};

 /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
 if(!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)){
  $$('[data-tilt]').forEach(function(card){
   var art=$('.sni-3d',card);if(!art)return;
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

 var total={{ (int) $total }},act={{ (int) $active }};
 if(total>0){$('#activeRate').textContent=Math.round(act/total*100)+'% of all records';}

 /* ---- Table: search, status filter, sort ---- */
 var table=$('#recentTable'),tbody=$('tbody',table);
 function rows(){return $$('.sni-row',tbody)}
 if(rows().length===0){$('.sni-tools').style.display='none';return;}

 var statuses={};rows().forEach(function(r){statuses[r.children[1].textContent.trim()]=1});
 Object.keys(statuses).sort().forEach(function(s){
  var o=document.createElement('option');o.value=s;o.textContent=s;$('#rowStatus').appendChild(o);
 });

 function applyFilter(){
  var q=$('#rowSearch').value.trim().toLowerCase(),st=$('#rowStatus').value,shown=0;
  rows().forEach(function(r){
   var ok=(!q||r.textContent.toLowerCase().indexOf(q)>-1)&&(!st||r.children[1].textContent.trim()===st);
   r.style.display=ok?'':'none';if(ok)shown++;
  });
  $('#rowCount').textContent=shown+' of '+rows().length+' shown';
  $('#noMatch').style.display=shown===0?'block':'none';
 }
 $('#rowSearch').addEventListener('input',applyFilter);
 $('#rowStatus').addEventListener('change',applyFilter);
 applyFilter();

 var sortState={key:null,dir:1};
 $$('.sni-sort',table).forEach(function(th){
  th.addEventListener('click',function(){
   var key=parseInt(th.getAttribute('data-key'),10);
   sortState.dir=(sortState.key===key)?-sortState.dir:1;sortState.key=key;
   $$('.sni-sort',table).forEach(function(x){x.classList.remove('asc','desc')});
   th.classList.add(sortState.dir===1?'asc':'desc');
   var list=rows();
   list.sort(function(a,b){
    var av=a.getAttribute('data-sort-'+key)||a.children[key].textContent.trim();
    var bv=b.getAttribute('data-sort-'+key)||b.children[key].textContent.trim();
    return av.localeCompare(bv,undefined,{numeric:true})*sortState.dir;
   });
   list.forEach(function(r){tbody.appendChild(r)});
  });
 });
})();
</script>
@endsection