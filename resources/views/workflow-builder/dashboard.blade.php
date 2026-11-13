@extends('layouts.schoolnexa')

@section('title', 'Workflow Builder')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'    => '<circle cx="6" cy="6" r="2.5"/><circle cx="18" cy="12" r="2.5"/><circle cx="6" cy="18" r="2.5"/><path d="M8.5 6H13a3 3 0 0 1 3 3v.5M8.5 18H13a3 3 0 0 0 3-3v-.5"/>',
        'active'   => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 3 3 5-6"/>',
        'inactive' => '<circle cx="12" cy="12" r="9"/><path d="M10 9v6M14 9v6"/>',
        'runs'     => '<circle cx="12" cy="12" r="9"/><path d="m10 8.5 6 3.5-6 3.5Z"/>',
        'create'   => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'list'     => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'recent'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'back'     => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'    => 'flow-chart',
        'active'   => 'checkmark',
        'inactive' => 'pause',
        'runs'     => 'play',
        'create'   => 'plus',
        'list'     => 'clipboard',
        'recent'   => 'hourglass',
        'back'     => 'home',
        'hero'     => 'puzzle',
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
            ? '<img class="aui-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.aui-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="aui-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="aui-3d '.$size.($slug ? '' : ' fb').'"><span class="aui-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpis = [
        ['Total Workflows',   $total,    'All designed workflows',     'total',    'sky'],
        ['Active',            $active,   'Currently running',          'active',   'success'],
        ['Inactive',          $inactive, 'Paused or drafted',          'inactive', 'warning'],
        ['Test Runs Recorded',$runs,     'Executions logged so far',   'runs',     'purple'],
    ];

    $quick = [
        ['Create Workflow',  'Design a new approval path',  'create', 'success', route('workflow-builder.create')],
        ['All Workflows',    'Browse every workflow',       'list',   'sky',     route('workflow-builder.index')],
        ['Recent Workflows', 'Jump to latest updates',      'total',  'warning', '#recentWorkflowsSection'],
        ['Recent Runs',      'See the latest executions',   'runs',   'purple',  '#recentRunsSection'],
        ['Dashboard',        'Back to the main dashboard',  'back',   'teal',    route('dashboard')],
    ];

    /* Workflow path steps shown in the interactive preview */
    $steps = [
        ['Trigger',   'The event that starts the workflow, such as a form submission, a status change or a schedule.'],
        ['Condition', 'Rules that decide which path is taken, for example amount, department or role checks.'],
        ['Approval',  'One or more approvers review the request and approve or reject it.'],
        ['Action',    'Automated work performed after approval: notifications, record updates, assignments.'],
        ['Complete',  'The workflow finishes and the outcome is recorded in the run history.'],
    ];

    $runClass = function ($status) {
        $s = strtolower((string) $status);
        if (in_array($s, ['completed', 'complete', 'success', 'succeeded', 'passed'])) return 'ok';
        if (in_array($s, ['failed', 'error', 'rejected', 'cancelled', 'canceled'])) return 'off';
        return 'run';
    };
@endphp

@include('workflow-builder._style')

<div class="sx-page">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .aui-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .aui-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .aui-3d.sm{width:48px;height:48px}
    .aui-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .aui-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:auiFloat 4.2s ease-in-out infinite}
    .aui-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .aui-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .aui-ico{display:none}
    .aui-3d.fb .aui-3d-img{display:none}
    .aui-3d.fb .aui-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .aui-3d.pop .aui-3d-in{animation:auiPop .55s cubic-bezier(.3,1.6,.5,1),auiFloat 4.2s ease-in-out infinite .55s}
    @keyframes auiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes auiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.aui-3d-in{animation:none!important}.aui-3d{transition:none}}

    /* ================= BANNER ================= */
    .aui-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .aui-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .aui-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .aui-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .aui-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .aui-welcome-right{display:flex;align-items:center;gap:26px}
    .aui-date{text-align:right;font-size:13px;color:#64748B}
    .aui-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= ALERT ================= */
    .aui-alert{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 16px;margin-bottom:16px;border-radius:12px;
        background:#ECFDF3;border:1px solid #A7F0C4;color:#0F7A36;font-size:13px;font-weight:600;transition:opacity .3s,transform .3s}
    .aui-alert.gone{opacity:0;transform:translateY(-6px)}
    .aui-alert button{border:0;background:transparent;color:inherit;font-size:18px;line-height:1;cursor:pointer}

    /* ================= KPI CARDS ================= */
    .aui-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .aui-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .aui-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .aui-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .aui-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin:2px 0}
    .aui-kpi span.foot{font-size:11px;color:#94A3B8}

    /* ================= QUICK ACTIONS ================= */
    .aui-box{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;margin-bottom:16px}
    .aui-box h3{margin:0;font-size:15px;color:#123B5D}
    .aui-quick-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-top:12px}
    .aui-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .aui-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .aui-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .aui-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= INTERACTIVE WORKFLOW PATH ================= */
    .aui-flow-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
    .aui-flow-btns{display:flex;gap:8px}
    .aui-btn{padding:7px 14px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#123B5D;font-size:12px;font-weight:600;cursor:pointer;font-family:inherit;transition:.15s}
    .aui-btn:hover{border-color:#176B87;background:#F1F7FA}
    .aui-btn.primary{background:#176B87;border-color:#176B87;color:#fff}
    .aui-btn.primary:hover{background:#0E4A5E}
    .aui-btn:disabled{opacity:.55;cursor:not-allowed}
    .aui-flow{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:18px 0 12px}
    .aui-step{position:relative;display:flex;align-items:center;gap:8px;padding:11px 18px;border:2px solid #CBD5E1;border-radius:12px;background:#F8FAFC;
        font-size:13px;font-weight:700;color:#123B5D;cursor:pointer;font-family:inherit;transition:.2s}
    .aui-step .n{display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#E2E8F0;color:#475569;font-size:11px;font-weight:800;transition:.2s}
    .aui-step:hover{border-color:#176B87;transform:translateY(-2px);box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .aui-step.active{border-color:#176B87;background:#E3F1F6;box-shadow:0 0 0 3px rgba(23,107,135,.16)}
    .aui-step.active .n{background:#176B87;color:#fff}
    .aui-step.done{border-color:#16A34A;background:#ECFDF3}
    .aui-step.done .n{background:#16A34A;color:#fff}
    .aui-link{color:#94A3B8;font-size:18px;transition:color .2s}
    .aui-link.done{color:#16A34A}
    .aui-detail{padding:14px 16px;border-radius:12px;background:#F1F7FA;border:1px solid #D5E6EE;font-size:13px;color:#334155;min-height:48px}
    .aui-detail b{color:#123B5D}
    .aui-bar{height:6px;background:#E2E8F0;border-radius:999px;overflow:hidden;margin-top:12px}
    .aui-bar i{display:block;height:100%;width:0;border-radius:999px;background:linear-gradient(90deg,#3B82F6,#16A34A);transition:width .4s ease}

    /* ================= TABLE TOOLS ================= */
    .aui-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .aui-input,.aui-select{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:170px;outline:none;background:#fff;font-family:inherit}
    .aui-select{min-width:120px;cursor:pointer}
    .aui-input:focus,.aui-select:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .aui-muted{font-size:12px;color:#64748B}
    .aui-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .aui-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .aui-sort.asc:after{content:' \2191';opacity:1}
    .aui-sort.desc:after{content:' \2193';opacity:1}
    .aui-row{transition:background .2s}
    .aui-row:hover{background:rgba(23,107,135,.05)}
    .aui-hide{display:none}
    .aui-pill{display:inline-block;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;background:#E2E8F0;color:#475569}
    .aui-pill.ok{background:#DCFCE7;color:#0F7A36}
    .aui-pill.off{background:#FEE2E2;color:#B91C1C}
    .aui-pill.run{background:#E0ECFF;color:#1D4ED8}

    @@media(max-width:1200px){.aui-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:900px){.aui-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.aui-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.aui-welcome h1{font-size:22px}.aui-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="aui-welcome" data-tilt>
    <div>
        <div class="aui-eyebrow">{{ $greet }} <i></i> Workflow Automation</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Design and manage automated business workflows and approval paths.</p>
    </div>
    <div class="aui-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="aui-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="sx-head">
    <div><h1 class="sx-title">Workflow Builder</h1><p class="sx-sub">Design and manage automated business workflows and approval paths.</p></div>
    <div class="sx-actions">
        <a class="sx-btn" href="{{ route('dashboard') }}">Back to Dashboard</a>
        <a class="sx-btn" href="{{ route('workflow-builder.index') }}">All Workflows</a>
        <a class="sx-btn primary" href="{{ route('workflow-builder.create') }}">+ Create Workflow</a>
    </div>
</div>

@if(session('success'))
    <div class="aui-alert" id="successAlert">
        <span>{{ session('success') }}</span>
        <button type="button" id="alertClose" aria-label="Dismiss">&times;</button>
    </div>
@endif

{{-- KPI CARDS --}}
<div class="aui-kpis">
    @foreach($kpis as [$label, $value, $foot, $ic, $tone])
        <div class="aui-kpi" data-tilt>
            {!! $tile($ic, $tone) !!}
            <div>
                <small>{{ $label }}</small>
                <strong data-count="{{ (int) $value }}">{{ $value }}</strong>
                <span class="foot">{{ $foot }}</span>
            </div>
        </div>
    @endforeach
</div>

{{-- QUICK ACTIONS --}}
<div class="aui-box">
    <h3>Quick Actions</h3>
    <div class="aui-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="aui-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- WORKFLOW PATH PREVIEW (interactive) --}}
<section class="aui-box" id="flowBox">
    <div class="aui-flow-head">
        <h3>Workflow Path Preview</h3>
        <div class="aui-flow-btns">
            <button type="button" class="aui-btn primary" id="flowPlay">&#9654; Simulate</button>
            <button type="button" class="aui-btn" id="flowReset">Reset</button>
        </div>
    </div>

    <div class="aui-flow" id="flowPath">
        @foreach($steps as $i => [$name, $desc])
            <button type="button" class="aui-step" data-i="{{ $i }}" data-desc="{{ $desc }}">
                <span class="n">{{ $i + 1 }}</span>{{ $name }}
            </button>
            @if(!$loop->last)<span class="aui-link">&rarr;</span>@endif
        @endforeach
    </div>

    <div class="aui-detail" id="flowDetail">Click a step to see what it does, or press <b>Simulate</b> to watch a workflow run end to end.</div>
    <div class="aui-bar"><i id="flowBar"></i></div>
</section>

<div class="sx-grid" style="grid-template-columns:1fr 1fr">

    {{-- RECENTLY UPDATED WORKFLOWS --}}
    <section class="sx-panel" id="recentWorkflowsSection"><h3>Recently Updated Workflows</h3>
        <div class="aui-tools">
            <input type="search" id="wfSearch" class="aui-input" placeholder="Search workflow…" autocomplete="off">
            <select id="wfStatus" class="aui-select" aria-label="Filter by status">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <span class="aui-muted" id="wfCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="wfTable">
                <thead><tr>
                    <th class="aui-sort" data-key="0">WORKFLOW</th>
                    <th class="aui-sort" data-key="1">STATUS</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($recent as $w)
                    <tr class="aui-row" data-status="{{ $w->is_active ? 'active' : 'inactive' }}">
                        <td><strong>{{ $w->name }}</strong></td>
                        <td><span class="sx-pill {{ $w->is_active ? 'ok' : 'off' }}">{{ $w->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td><a class="sx-btn" href="{{ route('workflow-builder.show', $w->id) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="sx-empty">No workflows created.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="aui-muted aui-hide" id="wfNoMatch" style="margin-top:10px">No workflows match your filters.</p>
    </section>

    {{-- RECENT WORKFLOW RUNS --}}
    <section class="sx-panel" id="recentRunsSection"><h3>Recent Workflow Runs</h3>
        <div class="aui-tools">
            <input type="search" id="runSearch" class="aui-input" placeholder="Search workflow or status…" autocomplete="off">
            <span class="aui-muted" id="runCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="runTable">
                <thead><tr>
                    <th class="aui-sort" data-key="0">WORKFLOW</th>
                    <th class="aui-sort" data-key="1">STATUS</th>
                    <th class="aui-sort" data-key="2">STARTED</th>
                </tr></thead>
                <tbody>
                @forelse($recentRuns as $r)
                    <tr class="aui-row">
                        <td>{{ $r->workflow_name }}</td>
                        <td><span class="aui-pill {{ $runClass($r->status) }}">{{ $r->status }}</span></td>
                        <td>{{ $r->started_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="sx-empty">No runs recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="aui-muted aui-hide" id="runNoMatch" style="margin-top:10px">No runs match your search.</p>
    </section>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.aui-3d', card);
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

    /* ---- Quick actions that scroll to a section on this page ---- */
    $$('[data-scroll]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            const s = $(a.getAttribute('data-scroll')); if (!s) return;
            s.scrollIntoView({ behavior: 'smooth', block: 'start' });
            s.style.transition = 'box-shadow .3s'; s.style.boxShadow = '0 0 0 3px rgba(23,107,135,.35)';
            setTimeout(function () { s.style.boxShadow = ''; }, 1200);
        });
    });

    /* ---- Dismissable success alert (auto-hides after 6s) ---- */
    const alertBox = $('#successAlert');
    if (alertBox) {
        const close = function () { alertBox.classList.add('gone'); setTimeout(function () { alertBox.remove(); }, 320); };
        $('#alertClose').addEventListener('click', close);
        setTimeout(close, 6000);
    }

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

    /* ---- Interactive workflow path: select a step / simulate a run ---- */
    (function () {
        const steps = $$('.aui-step'), links = $$('.aui-link'), detail = $('#flowDetail'), bar = $('#flowBar');
        const playBtn = $('#flowPlay'), resetBtn = $('#flowReset');
        const idle = detail.innerHTML;
        let timer = null;

        function show(i) {
            const s = steps[i];
            detail.innerHTML = '<b>Step ' + (i + 1) + ' &middot; ' + s.textContent.replace(/^\d+/, '').trim() + '</b><br>' + s.getAttribute('data-desc');
        }
        function paint(current, finished) {
            steps.forEach(function (s, i) {
                s.classList.toggle('active', i === current && !finished);
                s.classList.toggle('done', i < current || finished);
            });
            links.forEach(function (l, i) { l.classList.toggle('done', i < current || finished); });
            bar.style.width = (finished ? 100 : current < 0 ? 0 : (current / steps.length) * 100) + '%';
        }
        function reset() {
            clearInterval(timer); timer = null;
            paint(-1, false); detail.innerHTML = idle;
            playBtn.disabled = false; playBtn.innerHTML = '&#9654; Simulate';
        }

        steps.forEach(function (s, i) {
            s.addEventListener('click', function () {
                if (timer) return;
                paint(i, false); show(i);
                bar.style.width = (((i + 1) / steps.length) * 100) + '%';
            });
        });

        playBtn.addEventListener('click', function () {
            if (timer) return;
            let i = 0;
            playBtn.disabled = true; playBtn.textContent = 'Running…';
            paint(0, false); show(0);
            timer = setInterval(function () {
                i++;
                if (i >= steps.length) {
                    clearInterval(timer); timer = null;
                    paint(steps.length, true);
                    detail.innerHTML = '<b>Run finished</b><br>All steps completed successfully. This is only a preview and nothing was saved.';
                    playBtn.disabled = false; playBtn.innerHTML = '&#9654; Simulate again';
                    return;
                }
                paint(i, false); show(i);
            }, 900);
        });
        resetBtn.addEventListener('click', reset);
    })();

    /* ---- Reusable table helper: search + optional status filter + sort ---- */
    function setupTable(o) {
        const table = $(o.table); if (!table) return;
        const tbody = $('tbody', table);
        const rows = () => $$('.aui-row', tbody);
        const tools = $(o.search).closest('.aui-tools');
        if (rows().length === 0) { tools.style.display = 'none'; return; }
        const filter = o.filter ? $(o.filter) : null;

        function apply() {
            const q = $(o.search).value.trim().toLowerCase();
            const f = filter ? filter.value : '';
            let shown = 0;
            rows().forEach(function (r) {
                const ok = (!q || r.textContent.toLowerCase().includes(q)) && (!f || r.getAttribute('data-status') === f);
                r.style.display = ok ? '' : 'none'; if (ok) shown++;
            });
            $(o.count).textContent = shown + ' of ' + rows().length + ' shown';
            $(o.noMatch).style.display = shown === 0 ? 'block' : 'none';
        }
        $(o.search).addEventListener('input', apply);
        if (filter) filter.addEventListener('change', apply);
        apply();

        let key = null, dir = 1;
        $$('.aui-sort', table).forEach(function (th) {
            th.addEventListener('click', function () {
                const k = parseInt(th.getAttribute('data-key'), 10);
                dir = (key === k) ? -dir : 1; key = k;
                $$('.aui-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
                th.classList.add(dir === 1 ? 'asc' : 'desc');
                const list = rows();
                list.sort((a, b) => a.children[k].textContent.trim().localeCompare(b.children[k].textContent.trim(), undefined, { numeric: true }) * dir);
                list.forEach(r => tbody.appendChild(r));
            });
        });
    }

    setupTable({ table: '#wfTable',  search: '#wfSearch',  filter: '#wfStatus', count: '#wfCount',  noMatch: '#wfNoMatch' });
    setupTable({ table: '#runTable', search: '#runSearch', count: '#runCount', noMatch: '#runNoMatch' });
});
</script>
@endsection