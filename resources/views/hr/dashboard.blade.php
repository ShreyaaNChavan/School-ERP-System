@extends('layouts.schoolnexa')
@section('title', 'HR & Payroll')
@section('content')
@include('hr._style')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'employees'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'present'     => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'pending'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'recruitment' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
        'attendance'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'leave'       => '<path d="M4 20h16M7 20V9l5-5 5 5v11"/><path d="M10 20v-5h4v5"/>',
        'payroll'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/>',
        'reports'     => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'approve'     => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['employees']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'employees'   => 'conference-call',
        'present'     => 'calendar',
        'pending'     => 'hourglass',
        'recruitment' => 'briefcase',
        'attendance'  => 'checked-checkbox',
        'leave'       => 'beach',
        'payroll'     => 'money-bag',
        'reports'     => 'bar-chart',
        'approve'     => 'checkmark',
        'hr'          => 'group',
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
            ? '<img class="hri-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.hri-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="hri-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="hri-3d '.$size.($slug ? '' : ' fb').'"><span class="hri-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['Recruitment', 'recruitment', 'sky',     route('hr.recruitment')],
        ['Attendance',  'attendance',  'success', route('hr.attendance')],
        ['Leave',       'leave',       'teal',    route('hr.leave')],
        ['Payroll',     'payroll',     'warning', route('hr.payroll')],
        ['Approvals',   'approve',     'purple',  '#pendingSection'],
        ['Run Payroll', 'reports',     'navy',    route('hr.payroll')],
    ];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .hri-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .hri-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .hri-3d.sm{width:48px;height:48px}
    .hri-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .hri-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:hriFloat 4.2s ease-in-out infinite}
    .hri-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .hri-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .hri-ico{display:none}
    .hri-3d.fb .hri-3d-img{display:none}
    .hri-3d.fb .hri-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .hri-3d.pop .hri-3d-in{animation:hriPop .55s cubic-bezier(.3,1.6,.5,1),hriFloat 4.2s ease-in-out infinite .55s}
    @keyframes hriFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes hriPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.hri-3d-in{animation:none!important}.hri-3d{transition:none}}

    /* ================= BANNER ================= */
    .hri-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .hri-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .hri-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .hri-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .hri-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .hri-welcome-right{display:flex;align-items:center;gap:26px}
    .hri-date{text-align:right;font-size:13px;color:#64748B}
    .hri-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI + NAV CARDS ================= */
    .hri-card{display:flex;align-items:center;gap:16px;color:inherit;cursor:default;transition:transform .15s,box-shadow .15s}
    .hri-card:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .hri-card .hr-kpi{line-height:1.15;margin:2px 0}
    .hri-card-body{min-width:0}

    /* ================= QUICK ACTIONS ================= */
    .hri-quick-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-top:12px}
    .hri-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .hri-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .hri-quick strong{font-size:12px;font-weight:600;color:#123B5D}
    @@media (max-width:1100px){.hri-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media (max-width:520px){.hri-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hri-welcome h1{font-size:22px}}

    /* ================= TABLE / MODAL / TOAST ================= */
    .hri-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:12px 0}
    .hri-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:14px;min-width:220px;outline:none}
    .hri-input:focus{border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.12)}
    .hri-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .hri-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .hri-sort.asc:after{content:' \2191';opacity:1}
    .hri-sort.desc:after{content:' \2193';opacity:1}
    .hri-act{display:flex;gap:6px}
    .hri-ok,.hri-no{border:0;border-radius:6px;padding:5px 10px;font-size:12px;cursor:pointer;font-weight:600}
    .hri-ok{background:#dcfce7;color:#166534}.hri-ok:hover{background:#bbf7d0}
    .hri-no{background:#fee2e2;color:#991b1b}.hri-no:hover{background:#fecaca}
    .hri-ok[disabled],.hri-no[disabled]{opacity:.5;cursor:wait}
    .hri-row{transition:opacity .35s,transform .35s,background .2s}
    .hri-row:hover{background:rgba(79,70,229,.04)}
    .hri-row.gone{opacity:0;transform:translateX(24px)}
    .hri-toast-wrap{position:fixed;right:18px;bottom:18px;display:flex;flex-direction:column;gap:8px;z-index:9999}
    .hri-toast{background:#111827;color:#fff;padding:10px 14px;border-radius:8px;font-size:13px;box-shadow:0 6px 18px rgba(0,0,0,.2);animation:hriIn .2s}
    .hri-toast.err{background:#b91c1c}.hri-toast.ok{background:#15803d}
    @keyframes hriIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
    .hri-modal{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;z-index:9998}
    .hri-modal.open{display:flex}
    .hri-box{background:#fff;border-radius:12px;padding:20px;width:min(420px,92vw)}
    .hri-box h4{margin:0 0 6px;font-size:16px}
    .hri-box textarea{width:100%;min-height:70px;margin:10px 0;padding:8px;border:1px solid #d9dee7;border-radius:8px;font:inherit}
    .hri-box .hri-btns{display:flex;justify-content:flex-end;gap:8px}
    .hri-empty{display:none}
</style>

<div class="hr-wrap">

 {{-- BANNER --}}
 <div class="hri-welcome" data-tilt>
  <div>
   <div class="hri-eyebrow">{{ $greet }} <i></i> HR Department</div>
   <h1>Welcome Back, HR!</h1>
   <p>Here's an overview of your workforce and pending HR &amp; Payroll activities.</p>
  </div>
  <div class="hri-welcome-right">
   {!! $tile('hr', 'sky', 'lg') !!}
   <div class="hri-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
  </div>
 </div>

 <div class="hr-head">
  <div><div class="hr-title">HR & Payroll</div><div class="hr-sub">Workforce overview and pending HR activities</div></div>
  <a class="hr-btn hr-primary" href="{{ route('hr.payroll') }}">Manage payroll</a>
 </div>

 {{-- KPI CARDS --}}
 <div class="hr-grid">
  <div class="hr-card hri-card" data-tilt>
   {!! $tile('employees', 'sky') !!}
   <div class="hri-card-body"><div class="hr-label">ACTIVE EMPLOYEES</div><div class="hr-kpi" data-count="{{ (int) $employees }}">{{ number_format($employees) }}</div></div>
  </div>
  <div class="hr-card hri-card" data-tilt>
   {!! $tile('present', 'success') !!}
   <div class="hri-card-body"><div class="hr-label">PRESENT TODAY</div><div class="hr-kpi" data-count="{{ (int) $present }}">{{ number_format($present) }}</div>
    <div class="hr-muted" id="presentRate" style="font-size:12px"></div></div>
  </div>
  <div class="hr-card hri-card" data-tilt>
   {!! $tile('pending', 'warning') !!}
   <div class="hri-card-body"><div class="hr-label">PENDING LEAVE REQUESTS</div><div class="hr-kpi" id="pendingKpi" data-count="{{ (int) $pending }}">{{ number_format($pending) }}</div></div>
  </div>
  <div class="hr-card hri-card" data-tilt>
   {!! $tile('recruitment', 'purple') !!}
   <div class="hri-card-body"><div class="hr-label">OPEN RECRUITMENT</div><div class="hr-kpi" data-count="{{ (int) $jobs }}">{{ number_format($jobs) }}</div></div>
  </div>
 </div>

 {{-- QUICK ACTIONS --}}
 <div class="hr-card">
  <div class="hr-head"><b>Quick Actions</b></div>
  <div class="hri-quick-grid">
   @foreach($quick as [$label, $ic, $tone, $url])
    <a class="hri-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
     {!! $tile($ic, $tone, 'sm') !!}
     <strong>{{ $label }}</strong>
    </a>
   @endforeach
  </div>
 </div>

 {{-- PENDING LEAVE APPROVALS --}}
 <div class="hr-card" id="pendingSection" style="display:block;margin-top:16px">
  <div class="hr-head"><b>Pending leave approvals</b><a class="hr-btn" href="{{ route('hr.leave') }}">View all</a></div>

  @if($leaves->isEmpty())
   <p class="hr-muted">There are no pending leave requests.</p>
  @else
   <div class="hri-tools">
    <input type="search" id="leaveSearch" class="hri-input" placeholder="Search employee or leave type…" autocomplete="off">
    <select id="leaveTypeFilter" class="hri-input" style="min-width:160px"><option value="">All leave types</option></select>
    <span class="hr-muted" id="leaveCount"></span>
   </div>

   <table class="hr-table" id="leaveTable">
    <thead><tr>
     <th class="hri-sort" data-key="0">Employee</th>
     <th class="hri-sort" data-key="1">Leave type</th>
     <th class="hri-sort" data-key="2">Dates</th>
     <th class="hri-sort" data-key="3" data-num="1">Days</th>
     <th>Status</th>
     <th>Action</th>
    </tr></thead>
    <tbody>
    @foreach($leaves as $leave)
     <tr class="hri-row" data-id="{{ $leave->id }}"
         data-approve="{{ Route::has('hr.leave.approve') ? route('hr.leave.approve', $leave->id) : '' }}"
         data-reject="{{ Route::has('hr.leave.reject') ? route('hr.leave.reject', $leave->id) : '' }}"
         data-sort-2="{{ $leave->start_date->format('Y-m-d') }}">
      <td>{{ $leave->staff->full_name ?? 'Employee' }}</td>
      <td>{{ $leave->leaveType->name ?? '—' }}</td>
      <td>{{ $leave->start_date->format('d M Y') }} – {{ $leave->end_date->format('d M Y') }}</td>
      <td>{{ $leave->total_days }}</td>
      <td><span class="hr-pill">Pending</span></td>
      <td><div class="hri-act">
       <button type="button" class="hri-ok" data-action="approve">Approve</button>
       <button type="button" class="hri-no" data-action="reject">Reject</button>
      </div></td>
     </tr>
    @endforeach
    </tbody>
   </table>
   <p class="hr-muted hri-empty" id="noMatch">No requests match your search.</p>
   <p class="hr-muted hri-empty" id="allDone">All caught up — no pending leave requests.</p>
  @endif

  @if($latestPayroll)
   <p class="hr-muted" style="margin-top:14px">Latest payroll: {{ $latestPayroll->name }} · {{ ucfirst($latestPayroll->status) }}</p>
  @endif
 </div>
</div>

<div class="hri-modal" id="confirmModal">
 <div class="hri-box">
  <h4 id="mTitle">Confirm</h4>
  <div class="hr-muted" id="mText"></div>
  <textarea id="mNote" placeholder="Add a note (optional)"></textarea>
  <div class="hri-btns">
   <button type="button" class="hr-btn" id="mCancel">Cancel</button>
   <button type="button" class="hr-btn hr-primary" id="mOk">Confirm</button>
  </div>
 </div>
</div>
<div class="hri-toast-wrap" id="toasts"></div>

<script>
(function(){
 var $ = function(s,c){return (c||document).querySelector(s)};
 var $$ = function(s,c){return Array.prototype.slice.call((c||document).querySelectorAll(s))};

 function toast(msg,type){
  var t=document.createElement('div');t.className='hri-toast '+(type||'');t.textContent=msg;
  $('#toasts').appendChild(t);setTimeout(function(){t.style.opacity=0;setTimeout(function(){t.remove()},300)},3200);
 }

 /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
 if(!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)){
  $$('[data-tilt]').forEach(function(card){
   var art=$('.hri-3d',card);if(!art)return;
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
  a.addEventListener('click',function(e){e.preventDefault();flash($(a.getAttribute('data-scroll')))});
 });
 function flash(s){
  if(!s)return;
  s.scrollIntoView({behavior:'smooth',block:'start'});
  s.style.transition='box-shadow .3s';s.style.boxShadow='0 0 0 3px rgba(79,70,229,.35)';
  setTimeout(function(){s.style.boxShadow=''},1200);
 }

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

 var emp={{ (int) $employees }},pre={{ (int) $present }};
 if(emp>0){$('#presentRate').textContent=Math.round(pre/emp*100)+'% of workforce';}

 var table=$('#leaveTable');
 if(!table)return;
 var tbody=$('tbody',table),pendingKpi=$('#pendingKpi');
 function rows(){return $$('.hri-row',tbody)}
 function visibleRows(){return rows().filter(function(r){return r.style.display!=='none'})}

 var types={};rows().forEach(function(r){types[r.children[1].textContent.trim()]=1});
 Object.keys(types).sort().forEach(function(t){
  var o=document.createElement('option');o.value=t;o.textContent=t;$('#leaveTypeFilter').appendChild(o);
 });

 function applyFilter(){
  var q=$('#leaveSearch').value.trim().toLowerCase(),ty=$('#leaveTypeFilter').value;
  rows().forEach(function(r){
   var txt=r.textContent.toLowerCase(),rt=r.children[1].textContent.trim();
   r.style.display=(!q||txt.indexOf(q)>-1)&&(!ty||rt===ty)?'':'none';
  });
  var v=visibleRows().length,total=rows().length;
  $('#leaveCount').textContent=v+' of '+total+' shown';
  $('#noMatch').style.display=(total>0&&v===0)?'block':'none';
 }
 $('#leaveSearch').addEventListener('input',applyFilter);
 $('#leaveTypeFilter').addEventListener('change',applyFilter);
 applyFilter();

 var sortState={key:null,dir:1};
 $$('.hri-sort',table).forEach(function(th){
  th.addEventListener('click',function(){
   var key=parseInt(th.getAttribute('data-key'),10),num=th.hasAttribute('data-num');
   sortState.dir=(sortState.key===key)?-sortState.dir:1;sortState.key=key;
   $$('.hri-sort',table).forEach(function(x){x.classList.remove('asc','desc')});
   th.classList.add(sortState.dir===1?'asc':'desc');
   var list=rows();
   list.sort(function(a,b){
    var av=a.getAttribute('data-sort-'+key)||a.children[key].textContent.trim();
    var bv=b.getAttribute('data-sort-'+key)||b.children[key].textContent.trim();
    if(num){av=parseFloat(av)||0;bv=parseFloat(bv)||0;return (av-bv)*sortState.dir}
    return av.localeCompare(bv,undefined,{numeric:true})*sortState.dir;
   });
   list.forEach(function(r){tbody.appendChild(r)});
  });
 });

 var modal=$('#confirmModal'),pending=null;
 function openModal(row,action){
  pending={row:row,action:action};
  $('#mTitle').textContent=(action==='approve'?'Approve':'Reject')+' leave request?';
  $('#mText').textContent=row.children[0].textContent.trim()+' · '+row.children[1].textContent.trim()+' · '+row.children[3].textContent.trim()+' day(s)';
  $('#mNote').value='';$('#mOk').textContent=action==='approve'?'Approve':'Reject';
  modal.classList.add('open');
 }
 function closeModal(){modal.classList.remove('open');pending=null}
 $('#mCancel').addEventListener('click',closeModal);
 modal.addEventListener('click',function(e){if(e.target===modal)closeModal()});
 document.addEventListener('keydown',function(e){if(e.key==='Escape')closeModal()});

 tbody.addEventListener('click',function(e){
  var b=e.target.closest('button[data-action]');if(!b)return;
  var row=b.closest('tr'),action=b.getAttribute('data-action');
  if(!row.getAttribute('data-'+action)){
   toast('Approval endpoint is not configured — opening Leave Management.','err');
   setTimeout(function(){window.location.href="{{ route('hr.leave') }}"},900);
   return;
  }
  openModal(row,action);
 });

 function updatePendingCount(delta){
  var n=Math.max(0,(parseInt(pendingKpi.textContent.replace(/,/g,''),10)||0)+delta);
  pendingKpi.textContent=n.toLocaleString();
 }

 $('#mOk').addEventListener('click',function(){
  if(!pending)return;
  var row=pending.row,action=pending.action,url=row.getAttribute('data-'+action),note=$('#mNote').value;
  var btns=$$('button',row);btns.forEach(function(x){x.disabled=true});
  closeModal();
  fetch(url,{method:'POST',
   headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':$('meta[name="csrf-token"]').getAttribute('content')},
   body:JSON.stringify({note:note})
  }).then(function(r){
   if(!r.ok)throw new Error('Request failed ('+r.status+')');
   row.classList.add('gone');
   setTimeout(function(){
    row.remove();updatePendingCount(-1);applyFilter();
    if(rows().length===0){table.style.display='none';$('.hri-tools').style.display='none';$('#noMatch').style.display='none';$('#allDone').style.display='block'}
   },350);
   toast('Leave request '+(action==='approve'?'approved':'rejected')+'.','ok');
  }).catch(function(err){
   btns.forEach(function(x){x.disabled=false});
   toast(err.message||'Something went wrong.','err');
  });
 });
})();
</script>
@endsection