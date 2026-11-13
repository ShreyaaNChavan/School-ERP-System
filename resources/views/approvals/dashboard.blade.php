@extends('layouts.schoolnexa')

@section('title', 'Approval Center')

@section('content')

@php
    /* green = approved/completed, red = rejected/cancelled/overdue, amber = pending/review, blue = submitted/draft */
    $statusClass = function ($s) {
        $s = strtolower(trim((string) $s));
        if (in_array($s, ['approved','completed','closed','active'])) return 'good';
        if (in_array($s, ['rejected','cancelled','overdue','declined','inactive','deactive'])) return 'bad';
        if (in_array($s, ['pending','in_review','review','under_review','escalated'])) return 'warn';
        if (in_array($s, ['submitted','draft','new'])) return 'info';
        return '';
    };
    $priorityClass = function ($p) {
        $p = strtolower(trim((string) $p));
        if (in_array($p, ['urgent','high','critical'])) return 'bad';
        if (in_array($p, ['medium','normal'])) return 'warn';
        if ($p === 'low') return 'good';
        return '';
    };

    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'pending'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'approved' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'rejected' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        'overdue'  => '<circle cx="12" cy="13" r="8"/><path d="M12 9v4l2 2M9 2h6"/>',
        'create'   => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'all'      => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'flow'     => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="12" r="2.5"/><circle cx="6" cy="18" r="2.5"/><path d="M8.5 6H13a3 3 0 0 1 0 6h-2.5M8.5 18H13a3 3 0 0 0 0-6"/>',
        'back'     => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'    => 'clipboard',
        'pending'  => 'hourglass',
        'approved' => 'checkmark',
        'rejected' => 'cancel',
        'overdue'  => 'alarm-clock',
        'create'   => 'plus-math',
        'all'      => 'task',
        'flow'     => 'route',
        'back'     => 'home',
        'hero'     => 'checked-checkbox',
    ];
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'danger'  => ['#FA8585', '#DC2626', '#991B1B'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    /* Interactive 3D icon. sizes: xs | sm | md | lg */
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        $img = $slug
            ? '<img class="ari-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.ari-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="ari-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="ari-3d '.$size.($slug ? '' : ' fb').'"><span class="ari-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpis = [
        ['Total Requests', $stats['total'],          'total',    'sky'],
        ['Pending',        $stats['pending'],        'pending',  'warning'],
        ['Approved Today', $stats['approved_today'], 'approved', 'success'],
        ['Rejected',       $stats['rejected'],       'rejected', 'danger'],
        ['Overdue',        $stats['overdue'],        'overdue',  'purple'],
    ];

    $quick = [
        ['Create Request',   'Submit a new approval request', 'create', 'success', route('approvals.create')],
        ['All Requests',     'Browse every request',          'all',    'sky',     route('approvals.requests')],
        ['Pipeline',         'See the approval workflow',     'flow',   'purple',  '#pipelineSection'],
        ['Recent Requests',  'Jump to the latest requests',   'pending','warning', '#recentSection'],
        ['Dashboard',        'Back to the main dashboard',    'back',   'teal',    route('dashboard')],
    ];
@endphp

<div class="sn-page ac-page">
@include('approvals._style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .ari-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .ari-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .ari-3d.sm{width:48px;height:48px}
    .ari-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .ari-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:ariFloat 4.2s ease-in-out infinite}
    .ari-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .ari-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .ari-ico{display:none}
    .ari-3d.fb .ari-3d-img{display:none}
    .ari-3d.fb .ari-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .ari-3d.pop .ari-3d-in{animation:ariPop .55s cubic-bezier(.3,1.6,.5,1),ariFloat 4.2s ease-in-out infinite .55s}
    @keyframes ariFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes ariPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.ari-3d-in{animation:none!important}.ari-3d{transition:none}}

    /* ================= BANNER ================= */
    .ari-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .ari-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .ari-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .ari-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .ari-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .ari-welcome-right{display:flex;align-items:center;gap:26px}
    .ari-date{text-align:right;font-size:13px;color:#64748B}
    .ari-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .ari-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .ari-kpi{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:16px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .ari-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .ari-kpi span.lbl{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .ari-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin-top:3px}

    /* ================= QUICK ACTIONS ================= */
    .ari-quick-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .ari-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .ari-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .ari-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .ari-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= PILLS ================= */
    .ari-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B;white-space:nowrap}
    .ari-pill.good{background:#DCFCE7;color:#166534}
    .ari-pill.bad{background:#FEE2E2;color:#DC2626}
    .ari-pill.warn{background:#FEF3C7;color:#B45309}
    .ari-pill.info{background:#EAF2FF;color:#2563EB}

    /* ================= TABLE TOOLS ================= */
    .ari-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .ari-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:220px;outline:none;background:#fff}
    .ari-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .ari-muted{font-size:12px;color:#64748B}
    .ari-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .ari-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .ari-sort.asc:after{content:' \2191';opacity:1}
    .ari-sort.desc:after{content:' \2193';opacity:1}
    .ari-row{transition:background .2s}
    .ari-row:hover{background:rgba(23,107,135,.05)}
    .ari-hide{display:none}

    @@media(max-width:1200px){.ari-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.ari-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:800px){.ari-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ari-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.ari-welcome h1{font-size:22px}.ari-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="ari-welcome" data-tilt>
    <div>
        <div class="ari-eyebrow">{{ $greet }} <i></i> Approval Desk</div>
        <h1>Welcome Back, Approvers!</h1>
        <p>Review requests, make decisions and monitor workflows from one place.</p>
    </div>
    <div class="ari-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="ari-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="ac-head">
    <div><h1>Approval Center</h1><div class="ac-sub">Centralized review, decisions and workflow monitoring</div></div>
    <div class="ac-actions"><a class="ac-btn primary" href="{{ route('approvals.create') }}">+ Create Request</a></div>
</div>

@if(session('success'))<div class="ac-panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="ac-panel" style="color:#DC2626">{{ $errors->first() }}</div>@endif
@include('approvals._nav')

{{-- KPI CARDS --}}
<div class="ari-kpis">
    @foreach($kpis as [$label, $value, $ic, $tone])
        <div class="ari-kpi" data-tilt>
            {!! $tile($ic, $tone) !!}
            <div>
                <span class="lbl">{{ $label }}</span>
                <strong data-count="{{ (int) $value }}">{{ $value }}</strong>
            </div>
        </div>
    @endforeach
</div>

{{-- QUICK ACTIONS --}}
<div class="ac-panel">
    <h3>Quick Actions</h3>
    <div class="ari-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="ari-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- PIPELINE --}}
<div class="ac-panel" id="pipelineSection"><h3>Approval Pipeline</h3>
    <div class="ac-flow">
        <div class="ac-node done"><strong>Submitted</strong><small>Request received</small></div><div class="ac-arrow done"></div>
        <div class="ac-node active"><strong>Review</strong><small>Awaiting decision</small></div><div class="ac-arrow"></div>
        <div class="ac-node wait"><strong>Final Approval</strong><small>Next stage</small></div><div class="ac-arrow"></div>
        <div class="ac-node wait"><strong>Completed</strong><small>Closed request</small></div>
    </div>
    <div class="ac-muted">Illustrative workflow path. Live request progress is shown on each request detail page.</div>
</div>

{{-- RECENT REQUESTS --}}
<div class="ac-panel" id="recentSection"><h3>Recent Requests</h3>

    <div class="ari-tools">
        <input type="search" id="rowSearch" class="ari-input" placeholder="Search ID, title, type or submitter…" autocomplete="off">
        <select id="rowStatus" class="ari-input" style="min-width:150px"><option value="">All statuses</option></select>
        <select id="rowPriority" class="ari-input" style="min-width:150px"><option value="">All priorities</option></select>
        <span class="ari-muted" id="rowCount"></span>
    </div>

    <div style="overflow:auto">
        <table class="ac-table" id="reqTable">
            <thead><tr>
                <th class="ari-sort" data-key="0">Request ID</th>
                <th class="ari-sort" data-key="1">Title</th>
                <th class="ari-sort" data-key="2">Type</th>
                <th class="ari-sort" data-key="3">Submitted By</th>
                <th class="ari-sort" data-key="4">Priority</th>
                <th class="ari-sort" data-key="5">Status</th>
                <th></th>
            </tr></thead>
            <tbody>
            @forelse($recent as $r)
                <tr class="ari-row">
                    <td>{{ $r->request_number }}</td>
                    <td>{{ $r->title }}</td>
                    <td>{{ $r->request_type }}</td>
                    <td>{{ $r->submitted_name }}</td>
                    <td><span class="ari-pill {{ $priorityClass($r->priority) }}">{{ ucfirst($r->priority) }}</span></td>
                    <td><span class="ari-pill {{ $statusClass($r->status) }}">{{ ucfirst($r->status) }}</span></td>
                    <td><a class="ac-btn" href="{{ route('approvals.show', $r->id) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7">No approval requests yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="ari-muted ari-hide" id="noMatch" style="margin-top:10px">No requests match your search.</p>

    <div style="margin-top:14px"><a class="ac-btn primary" href="{{ route('approvals.create') }}">Create Request</a> <a class="ac-btn" href="{{ route('approvals.requests') }}">View All Requests</a></div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.ari-3d', card);
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

    /* ---- Table: search, status + priority filters, sort ---- */
    const table = $('#reqTable'), tbody = $('tbody', table);
    const rows = () => $$('.ari-row', tbody);
    if (rows().length === 0) { $('.ari-tools').style.display = 'none'; return; }

    function fillOptions(selectEl, col) {
        const seen = {};
        rows().forEach(r => { const v = r.children[col].textContent.trim(); if (v) seen[v] = 1; });
        Object.keys(seen).sort().forEach(function (v) {
            const o = document.createElement('option'); o.value = v; o.textContent = v; selectEl.appendChild(o);
        });
    }
    fillOptions($('#rowStatus'), 5);
    fillOptions($('#rowPriority'), 4);

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value, pr = $('#rowPriority').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q))
                    && (!st || r.children[5].textContent.trim() === st)
                    && (!pr || r.children[4].textContent.trim() === pr);
            r.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        $('#rowCount').textContent = shown + ' of ' + rows().length + ' shown';
        $('#noMatch').style.display = shown === 0 ? 'block' : 'none';
    }
    $('#rowSearch').addEventListener('input', applyFilter);
    $('#rowStatus').addEventListener('change', applyFilter);
    $('#rowPriority').addEventListener('change', applyFilter);
    applyFilter();

    let sortKey = null, sortDir = 1;
    $$('.ari-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10);
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.ari-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
            th.classList.add(sortDir === 1 ? 'asc' : 'desc');
            const list = rows();
            list.sort(function (a, b) {
                const av = a.children[key].textContent.trim(), bv = b.children[key].textContent.trim();
                return av.localeCompare(bv, undefined, { numeric: true }) * sortDir;
            });
            list.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>
@endsection