@extends('layouts.schoolnexa')

@section('title', 'PTM Workspace')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'scheduled'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'completed'  => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'pending'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'upcoming'   => '<path d="M6 9a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 21h4"/>',
        'duration'   => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M9 2h6"/>',
        'add'        => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'history'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'teachers'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['scheduled']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'scheduled' => 'calendar',
        'completed' => 'checkmark',
        'pending'   => 'hourglass',
        'upcoming'  => 'alarm-clock',
        'duration'  => 'stopwatch',
        'add'       => 'plus-math',
        'history'   => 'clipboard',
        'teachers'  => 'conference-call',
        'hero'      => 'conference-call',
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
            ? '<img class="pti-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.pti-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="pti-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="pti-3d '.$size.($slug ? '' : ' fb').'"><span class="pti-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $weekLabel = \Carbon\Carbon::parse($weekStart)->format('d M') . ' – ' . \Carbon\Carbon::parse($weekEnd)->format('d M');

    $kpis = [
        ['Meetings Scheduled',  $scheduled,                'Total PTM records',            'scheduled', 'sky',     true],
        ['Completed Today',     $completedToday,           'Meetings marked completed',    'completed', 'success', true],
        ['Pending Requests',    $pendingCount,             'Require review',               'pending',   'warning', true],
        ['Upcoming This Week',  $upcomingWeek,             $weekLabel,                     'upcoming',  'purple',  true],
        ['Average Duration',    $avgDuration . ' min',     'Based on scheduled meetings',  'duration',  'teal',    false],
    ];

    $quick = [
        ['Schedule Meeting',    'Book a new PTM slot',          'add',       'success', route('ptm.create')],
        ['View History',        'Browse all past meetings',     'history',   'sky',     route('ptm.index')],
        ['Pending Requests',    'Accept or decline requests',   'pending',   'warning', '#pendingSection'],
        ['Today\'s Meetings',   'See today\'s schedule',        'scheduled', 'purple',  '#todaySection'],
        ['Teacher Availability','Check available slots',        'teachers',  'teal',    '#availabilitySection'],
    ];

    /* green = confirmed/completed, red = cancelled/declined, blue = scheduled, amber = pending */
    $badgeClass = function ($s) {
        $s = strtolower(trim((string) $s));
        if (in_array($s, ['confirmed','completed','approved','available'])) return 'good';
        if (in_array($s, ['cancelled','declined','rejected','no_show','inactive'])) return 'bad';
        if (in_array($s, ['scheduled'])) return 'info';
        if (in_array($s, ['pending','requested','rescheduled'])) return 'warn';
        return '';
    };
@endphp

<div class="sn-page ptm-page">@include('ptm._style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .pti-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .pti-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .pti-3d.sm{width:48px;height:48px}
    .pti-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .pti-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:ptiFloat 4.2s ease-in-out infinite}
    .pti-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .pti-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .pti-ico{display:none}
    .pti-3d.fb .pti-3d-img{display:none}
    .pti-3d.fb .pti-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .pti-3d.pop .pti-3d-in{animation:ptiPop .55s cubic-bezier(.3,1.6,.5,1),ptiFloat 4.2s ease-in-out infinite .55s}
    @keyframes ptiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes ptiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.pti-3d-in{animation:none!important}.pti-3d{transition:none}}

    /* ================= BANNER ================= */
    .pti-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .pti-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .pti-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .pti-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .pti-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .pti-welcome-right{display:flex;align-items:center;gap:26px}
    .pti-date{text-align:right;font-size:13px;color:#64748B}
    .pti-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .pti-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-top:6px}
    .pti-kpi{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:16px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .pti-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .pti-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .pti-kpi strong{display:block;font-size:26px;line-height:1.15;color:#123B5D;margin:2px 0}
    .pti-kpi span.foot{font-size:11px;color:#94A3B8}

    /* ================= QUICK ACTIONS ================= */
    .pti-quick-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .pti-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .pti-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .pti-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .pti-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= BADGES (green / red / blue / amber) ================= */
    .ptm-page .pti-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B;white-space:nowrap;text-transform:capitalize}
    .ptm-page .pti-pill.good{background:#DCFCE7;color:#166534}
    .ptm-page .pti-pill.bad{background:#FEE2E2;color:#DC2626}
    .ptm-page .pti-pill.info{background:#EAF2FF;color:#2563EB}
    .ptm-page .pti-pill.warn{background:#FEF3C7;color:#B45309}
    .ptm-page .ptm-btn.danger{color:#DC2626}

    /* ================= TABLE TOOLS ================= */
    .pti-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .pti-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:220px;outline:none;background:#fff}
    .pti-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .pti-muted{font-size:12px;color:#64748B}
    .pti-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .pti-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .pti-sort.asc:after{content:' \2191';opacity:1}
    .pti-sort.desc:after{content:' \2193';opacity:1}
    .pti-row{transition:background .2s}
    .pti-row:hover{background:rgba(23,107,135,.05)}
    .pti-hide{display:none}

    @@media(max-width:1200px){.pti-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.pti-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:800px){.pti-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.pti-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.pti-welcome h1{font-size:22px}.pti-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="pti-welcome" data-tilt>
    <div>
        <div class="pti-eyebrow">{{ $greet }} <i></i> PTM Team</div>
        <h1>Welcome Back, PTM Team!</h1>
        <p>Plan meetings, manage parent requests and track student discussions.</p>
    </div>
    <div class="pti-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="pti-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="ptm-head">
    <div><h1>Parent–Teacher Meeting (PTM) Workspace</h1><div class="ptm-sub">Plan meetings, manage parent requests and track student discussions</div></div>
    <div class="ptm-actions"><a class="ptm-btn primary" href="{{ route('ptm.create') }}">+ Schedule Meeting</a></div>
</div>

@if(session('success'))
    <div class="ptm-panel" style="margin-top:0;color:#15803d">{{ session('success') }}</div>
@endif

{{-- KPI CARDS --}}
<div class="pti-kpis">
    @foreach($kpis as [$label, $value, $foot, $ic, $tone, $countable])
        <div class="pti-kpi" data-tilt>
            {!! $tile($ic, $tone) !!}
            <div>
                <small>{{ $label }}</small>
                <strong @if($countable) data-count="{{ (int) $value }}" @endif>{{ $value }}</strong>
                <span class="foot">{{ $foot }}</span>
            </div>
        </div>
    @endforeach
</div>

{{-- QUICK ACTIONS --}}
<div class="ptm-panel">
    <h3>Quick Actions</h3>
    <div class="pti-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="pti-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- WEEKLY SCHEDULE --}}
<div class="ptm-panel"><h3>PTM Schedule <span class="ptm-muted" style="font-weight:400">{{ \Carbon\Carbon::parse($weekStart)->format('d M') }} – {{ \Carbon\Carbon::parse($weekEnd)->format('d M Y') }}</span></h3>
    <div class="ptm-calendar">
        <div></div>@for($d=0;$d<7;$d++)@php($day=\Carbon\Carbon::parse($weekStart)->addDays($d))<div class="ptm-day {{ $day->isToday()?'today':'' }}">{{ $day->format('D d') }}</div>@endfor
        @foreach(['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00'] as $time)
            <div class="ptm-time">{{ \Carbon\Carbon::createFromFormat('H:i',$time)->format('h:i A') }}</div>
            @for($d=0;$d<7;$d++)
                @php($date=\Carbon\Carbon::parse($weekStart)->addDays($d)->toDateString())
                @php($slot=$calendar->first(fn($m)=>$m->meeting_date===$date && substr((string)$m->start_time,0,5)===$time))
                @if($slot)<a class="ptm-slot filled {{ $slot->status }}" href="{{ route('ptm.show',$slot->id) }}">{{ $slot->parent_name }}<br>{{ $slot->status }}</a>@else<div class="ptm-slot">Empty</div>@endif
            @endfor
        @endforeach
    </div>
    <div class="ptm-muted" style="margin-top:10px">Select a scheduled meeting to view its details. Use Schedule Meeting to add a new slot.</div>
</div>

<div class="ptm-columns">
    {{-- TODAY'S MEETINGS --}}
    <div class="ptm-panel" id="todaySection"><h3>Today's Meetings ({{ now()->format('d M Y') }})</h3>
        @forelse($meetings as $m)
            <div class="ptm-meeting">
                <div>
                    <strong>{{ \Carbon\Carbon::parse($m->start_time)->format('h:i A') }} · {{ $m->parent_name }}</strong>
                    <div class="ptm-muted">Student: {{ trim(($m->student_first_name??'').' '.($m->student_last_name??'')) }} · Teacher: {{ trim(($m->teacher_first_name??'').' '.($m->teacher_last_name??'')) }}</div>
                </div>
                <span class="pti-pill {{ $badgeClass($m->status) }}">{{ $m->status }}</span>
            </div>
        @empty
            <div class="ptm-empty">No meetings scheduled for today.</div>
        @endforelse
    </div>

    {{-- PENDING REQUESTS --}}
    <div class="ptm-panel" id="pendingSection"><h3>Pending Requests <span class="ptm-muted">{{ $pendingCount }} total</span></h3>
        @forelse($pending as $m)
            <div class="ptm-meeting">
                <div>
                    <strong>{{ $m->parent_name }}</strong>
                    <div class="ptm-muted">{{ trim(($m->student_first_name??'').' '.($m->student_last_name??'')) }} · {{ \Carbon\Carbon::parse($m->meeting_date)->format('d M') }}</div>
                </div>
                <div class="ptm-inline">
                    <form method="POST" action="{{ route('ptm.status',$m->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="ptm-btn primary" type="submit">Accept</button></form>
                    <form method="POST" action="{{ route('ptm.status',$m->id) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="ptm-btn danger" type="submit">Decline</button></form>
                </div>
            </div>
        @empty
            <div class="ptm-empty">No pending requests.</div>
        @endforelse
        <a class="ptm-muted" href="{{ route('ptm.index',['status'=>'pending']) }}">View all requests →</a>
    </div>
</div>

{{-- TEACHER AVAILABILITY --}}
<div class="ptm-panel" id="availabilitySection"><h3>Teacher Availability</h3>
    <div style="overflow-x:auto">
        <table class="ptm-table">
            <thead><tr><th>Teacher Name</th><th>Designation</th><th>Available Day</th><th>Available Hours</th><th>Slot Duration</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($availability as $a)
                <tr>
                    <td>{{ $a->first_name }} {{ $a->last_name }}</td>
                    <td>{{ $a->designation ?: '—' }}</td>
                    <td>{{ ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$a->weekday] ?? '—' }}</td>
                    <td>{{ substr($a->start_time,0,5) }} – {{ substr($a->end_time,0,5) }}</td>
                    <td>{{ $a->slot_minutes }} min</td>
                    <td><span class="pti-pill good">Available</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="ptm-empty">No teacher availability configured yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- MEETING HISTORY --}}
<div class="ptm-panel" id="historySection">
    <div class="ptm-head" style="margin-bottom:12px"><h3 style="margin:0">Meeting History</h3><a class="ptm-btn" href="{{ route('ptm.index') }}">View All Meetings</a></div>

    <div class="pti-tools">
        <input type="search" id="rowSearch" class="pti-input" placeholder="Search parent, student, teacher or ID…" autocomplete="off">
        <select id="rowStatus" class="pti-input" style="min-width:150px"><option value="">All statuses</option></select>
        <select id="rowType" class="pti-input" style="min-width:150px"><option value="">All types</option></select>
        <span class="pti-muted" id="rowCount"></span>
    </div>

    <div style="overflow-x:auto">
        <table class="ptm-table" id="historyTable">
            <thead><tr>
                <th class="pti-sort" data-key="0">Meeting ID</th>
                <th class="pti-sort" data-key="1">Date &amp; Time</th>
                <th class="pti-sort" data-key="2">Parent</th>
                <th class="pti-sort" data-key="3">Student</th>
                <th class="pti-sort" data-key="4">Teacher</th>
                <th class="pti-sort" data-key="5">Type</th>
                <th class="pti-sort" data-key="6" data-num="1">Duration</th>
                <th class="pti-sort" data-key="7">Status</th>
                <th>Actions</th>
            </tr></thead>
            <tbody>
            @forelse($history as $m)
                <tr class="pti-row" data-sort-1="{{ \Carbon\Carbon::parse($m->meeting_date)->format('Y-m-d') }} {{ substr((string) $m->start_time, 0, 5) }}">
                    <td>#PTM-{{ str_pad($m->id,4,'0',STR_PAD_LEFT) }}</td>
                    <td>{{ \Carbon\Carbon::parse($m->meeting_date)->format('d M Y') }}<br><span class="ptm-muted">{{ \Carbon\Carbon::parse($m->start_time)->format('h:i A') }}</span></td>
                    <td>{{ $m->parent_name }}</td>
                    <td>{{ trim(($m->student_first_name??'').' '.($m->student_last_name??'')) }}</td>
                    <td>{{ trim(($m->teacher_first_name??'').' '.($m->teacher_last_name??'')) }}</td>
                    <td>{{ $m->meeting_type }}</td>
                    <td>{{ $m->duration_minutes }} min</td>
                    <td><span class="pti-pill {{ $badgeClass($m->status) }}">{{ $m->status }}</span></td>
                    <td><a class="ptm-btn" href="{{ route('ptm.show',$m->id) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="ptm-empty">No meeting history yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="pti-muted pti-hide" id="noMatch" style="margin-top:10px">No meetings match your search.</p>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.pti-3d', card);
            if (!art) return;
            card.addEventListener('mousemove', function (e) {
                const r = card.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
                const cl = v => Math.max(-22, Math.min(22, v));
                art.style.setProperty('--ry', cl(x * 44) + 'deg');
                art.style.setProperty('--rx', cl(-y * 44) + 'deg');
                art.style.setProperty('--s', '1.14');
            });
            card.addEventListener('mouseleave', function () {
                art.style.setProperty('--ry', '0deg'); art.style.setProperty('--rx', '0deg'); art.style.setProperty('--s', '1');
            });
            card.addEventListener('click', function () { art.classList.remove('pop'); void art.offsetWidth; art.classList.add('pop'); });
        });
    }

    /* ---- Quick action that scrolls to a section on this page ---- */
    $$('[data-scroll]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            const s = $(a.getAttribute('data-scroll')); if (!s) return;
            s.scrollIntoView({ behavior: 'smooth', block: 'start' });
            s.style.transition = 'box-shadow .3s'; s.style.boxShadow = '0 0 0 3px rgba(23,107,135,.35)';
            setTimeout(function () { s.style.boxShadow = ''; }, 1200);
        });
    });

    /* ---- KPI count-up ---- */
    $$('[data-count]').forEach(function (el) {
        const target = parseInt(el.getAttribute('data-count'), 10) || 0; let start = null;
        if (target === 0) return;
        function step(ts) {
            if (!start) start = ts; const p = Math.min((ts - start) / 700, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    });

    /* ---- Meeting history: search, status + type filters, sort ---- */
    const table = $('#historyTable'), tbody = $('tbody', table);
    const rows = () => $$('.pti-row', tbody);
    if (rows().length === 0) { $('.pti-tools').style.display = 'none'; return; }

    function fillOptions(selectEl, col) {
        const seen = {};
        rows().forEach(r => { const v = r.children[col].textContent.trim(); if (v) seen[v] = 1; });
        Object.keys(seen).sort().forEach(function (v) {
            const o = document.createElement('option'); o.value = v; o.textContent = v; selectEl.appendChild(o);
        });
    }
    fillOptions($('#rowStatus'), 7);
    fillOptions($('#rowType'), 5);

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value, ty = $('#rowType').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q))
                    && (!st || r.children[7].textContent.trim() === st)
                    && (!ty || r.children[5].textContent.trim() === ty);
            r.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        $('#rowCount').textContent = shown + ' of ' + rows().length + ' shown';
        $('#noMatch').style.display = shown === 0 ? 'block' : 'none';
    }
    $('#rowSearch').addEventListener('input', applyFilter);
    $('#rowStatus').addEventListener('change', applyFilter);
    $('#rowType').addEventListener('change', applyFilter);
    applyFilter();

    let sortKey = null, sortDir = 1;
    $$('.pti-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10), num = th.hasAttribute('data-num');
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.pti-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
            th.classList.add(sortDir === 1 ? 'asc' : 'desc');
            const list = rows();
            list.sort(function (a, b) {
                let av = a.getAttribute('data-sort-' + key) || a.children[key].textContent.trim();
                let bv = b.getAttribute('data-sort-' + key) || b.children[key].textContent.trim();
                if (num) { av = parseFloat(av) || 0; bv = parseFloat(bv) || 0; return (av - bv) * sortDir; }
                return av.localeCompare(bv, undefined, { numeric: true }) * sortDir;
            });
            list.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>
@endsection