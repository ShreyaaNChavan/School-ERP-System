@extends('layouts.schoolnexa')

@section('title', 'Career Management')

@section('content')

@php
    $recent = $recent ?? collect();
    $total  = $total ?? 0;
    $active = $active ?? 0;

    /* green = open/active/completed/selected, red = closed/inactive/rejected, blue = in progress, purple = shortlisted */
    $statusClass = function ($status) {
        $s = strtolower(str_replace(' ', '_', $status ?? ''));
        if (in_array($s, ['open','active','completed','selected'])) return 'good';
        if (in_array($s, ['closed','cancelled','rejected','inactive','deactive','deactivated','disabled'])) return 'bad';
        if (in_array($s, ['scheduled','applied','under_review'])) return 'info';
        if ($s === 'shortlisted') return 'violet';
        return '';
    };

    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'records'  => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2M3 13h18"/>',
        'active'   => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'recent'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'growth'   => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
        'add'      => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'manage'   => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'sessions' => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'back'     => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['records']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'records'  => 'briefcase',
        'active'   => 'checkmark',
        'recent'   => 'hourglass',
        'growth'   => 'rocket',
        'add'      => 'plus-math',
        'manage'   => 'clipboard',
        'sessions' => 'conference-call',
        'back'     => 'home',
        'hero'     => 'graduation-cap',
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
            ? '<img class="cri-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.cri-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="cri-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="cri-3d '.$size.($slug ? '' : ' fb').'"><span class="cri-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['Add Career Record', 'Create a new career management record', 'add',    'success', route('career.create')],
        ['Manage Records',    'View and manage existing records',       'manage', 'sky',     route('career.index')],
        ['Recent Activities', 'Review recently created records',        'recent', 'warning', '#recentSection'],
        ['Dashboard',         'Go back to the main dashboard',          'back',   'teal',    url('/dashboard')],
    ];
@endphp

<div class="sn-page career-dashboard">

    @include('shared.module-style')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        .career-dashboard{--navy:#123B5D;--teal:#176B87;--muted:#64748B;--border:#E2E8F0}
        .career-dashboard *{box-sizing:border-box}

        /* ================= INTERACTIVE 3D ICONS ================= */
        .cri-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
            transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
            transition:transform .18s ease-out;will-change:transform;cursor:pointer}
        .cri-3d.xs{width:42px;height:42px;margin-bottom:6px}
        .cri-3d.sm{width:48px;height:48px}
        .cri-3d.lg{width:92px;height:92px;margin-bottom:10px}
        .cri-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:criFloat 4.2s ease-in-out infinite}
        .cri-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
        .cri-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
        .cri-ico{display:none}
        .cri-3d.fb .cri-3d-img{display:none}
        .cri-3d.fb .cri-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
            background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
            box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
        .cri-3d.pop .cri-3d-in{animation:criPop .55s cubic-bezier(.3,1.6,.5,1),criFloat 4.2s ease-in-out infinite .55s}
        @keyframes criFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
        @keyframes criPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
        @@media (prefers-reduced-motion:reduce){.cri-3d-in{animation:none!important}.cri-3d{transition:none}}

        /* ================= BANNER ================= */
        .cri-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
            border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
        .cri-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
        .cri-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
        .cri-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
        .cri-welcome p{margin:6px 0 0;font-size:14px;color:#64748B;max-width:600px}
        .cri-welcome-right{display:flex;align-items:center;gap:26px}
        .cri-date{text-align:right;font-size:13px;color:#64748B}
        .cri-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

        /* ================= HEADER ================= */
        .career-header{display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;margin-bottom:6px}
        .career-header h1{color:var(--navy);font-size:22px;font-weight:750;margin:0 0 4px;letter-spacing:-.4px}
        .career-header .sn-sub{color:var(--muted);font-size:13px}
        .career-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:10px 15px;border:1px solid var(--border);border-radius:9px;background:#fff;
            color:#334155;font-size:12px;font-weight:650;text-decoration:none;transition:.2s ease;cursor:pointer;white-space:nowrap}
        .career-btn:hover{border-color:#94A3B8;background:#F8FAFC;color:var(--navy)}
        .career-btn-primary{background:var(--navy);border-color:var(--navy);color:#fff}
        .career-btn-primary:hover{background:#1B527A;color:#fff}

        /* ================= SECTIONS ================= */
        .career-section{margin-top:22px}
        .career-section-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:13px}
        .career-section-heading h3{margin:0;color:var(--navy);font-size:15px;font-weight:750}
        .career-section-heading p{color:var(--muted);font-size:11px;margin:4px 0 0}

        /* ================= KPI CARDS (no links) ================= */
        .cri-kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
        .cri-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid var(--border);border-radius:14px;padding:16px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
        .cri-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
        .cri-kpi-label{font-size:11px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .cri-kpi-value{font-size:28px;font-weight:800;color:var(--navy);line-height:1.15;margin:2px 0}
        .cri-kpi-foot{color:#94A3B8;font-size:11px}

        /* ================= QUICK ACTIONS ================= */
        .cri-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
        .cri-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 10px 12px;border:1px solid #CBD5E1;border-radius:14px;
            background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
        .cri-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
        .cri-quick strong{font-size:12px;font-weight:650;color:var(--navy)}
        .cri-quick span.sub{font-size:10px;color:var(--muted);line-height:1.4}

        /* ================= PANELS / TABLE ================= */
        .career-panel{background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden}
        .career-panel-header{padding:16px 19px;border-bottom:1px solid #EDF1F5;display:flex;justify-content:space-between;align-items:center;gap:10px}
        .career-panel-header h3{color:var(--navy);font-size:13px;font-weight:750;margin:0}
        .career-panel-body{padding:17px 19px}
        .cri-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;padding:14px 19px 0}
        .cri-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:220px;outline:none;background:#fff}
        .cri-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
        .cri-muted{font-size:12px;color:#64748B}
        .career-table-wrap{width:100%;overflow-x:auto}
        .career-table{width:100%;border-collapse:collapse;min-width:620px}
        .career-table th{text-align:left;padding:12px 15px;background:#F8FAFC;color:#64748B;font-size:10px;font-weight:750;letter-spacing:.35px;white-space:nowrap}
        .career-table td{padding:13px 15px;border-top:1px solid #EDF1F5;color:#475569;font-size:12px;vertical-align:middle}
        .cri-row{transition:background .2s}
        .cri-row:hover{background:rgba(23,107,135,.05)}
        .cri-sort{cursor:pointer;user-select:none}
        .cri-sort:after{content:' \2195';opacity:.35;font-size:11px}
        .cri-sort.asc:after{content:' \2191';opacity:1}
        .cri-sort.desc:after{content:' \2193';opacity:1}
        .career-record-title{color:var(--navy);font-weight:700;font-size:13px}
        .career-record-sub{font-size:10px;color:#94A3B8;margin-top:4px}
        .career-status{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B;white-space:nowrap}
        .career-status.good{background:#DCFCE7;color:#166534}
        .career-status.bad{background:#FEE2E2;color:#DC2626}
        .career-status.info{background:#EAF2FF;color:#2563EB}
        .career-status.violet{background:#F1ECFA;color:#6941A5}
        .career-table-action{color:var(--teal);font-weight:700;text-decoration:none;font-size:12px}
        .career-empty{padding:32px 15px!important;text-align:center;color:#94A3B8!important}
        .cri-hide{display:none}

        /* ================= AREAS ================= */
        .career-two-column{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(260px,1fr);gap:16px}
        .career-type-row{display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid #EDF1F5}
        .career-type-row:last-child{border-bottom:0}
        .career-type-label{display:flex;align-items:center;gap:14px;font-size:13px;color:#475569;font-weight:600}
        .career-type-label .cri-3d{margin-bottom:0}
        .career-type-count{font-size:13px;font-weight:750;color:var(--navy)}
        .career-info-note{padding:13px 15px;background:#F0F7FA;border:1px solid #DCEBF2;border-radius:10px;color:#36576C;font-size:12px;line-height:1.6;margin-top:12px}

        @@media(max-width:1050px){
            .cri-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .cri-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .career-two-column{grid-template-columns:1fr}
        }
        @@media(max-width:650px){
            .cri-welcome{padding:20px}.cri-welcome h1{font-size:22px}
            .cri-kpi-grid{gap:9px}
        }
    </style>

    {{-- BANNER --}}
    <div class="cri-welcome" data-tilt>
        <div>
            <div class="cri-eyebrow">{{ $greet }} <i></i> Career Department</div>
            <h1>Welcome Back, Career Team!</h1>
            <p>Manage career opportunities, monitor student applications and organize guidance sessions from one workspace.</p>
        </div>
        <div class="cri-welcome-right">
            {!! $tile('hero', 'sky', 'lg') !!}
            <div class="cri-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
        </div>
    </div>

    {{-- PAGE HEADER --}}
    <div class="career-header">
        <div>
            <h1>Career Management</h1>
            <div class="sn-sub">Manage student career opportunities, applications and professional guidance.</div>
        </div>
        <a href="{{ route('career.create') }}" class="career-btn career-btn-primary">+ Add New</a>
    </div>

    {{-- KPI CARDS --}}
    <div class="career-section">
        <div class="career-section-heading">
            <div><h3>Career Overview</h3><p>Summary of career management activities</p></div>
        </div>

        <div class="cri-kpi-grid">
            <div class="cri-kpi" data-tilt>
                {!! $tile('records', 'sky') !!}
                <div>
                    <div class="cri-kpi-label">Total Career Records</div>
                    <div class="cri-kpi-value" data-count="{{ (int) $total }}">{{ number_format($total) }}</div>
                    <div class="cri-kpi-foot">All recorded career activities</div>
                </div>
            </div>
            <div class="cri-kpi" data-tilt>
                {!! $tile('active', 'success') !!}
                <div>
                    <div class="cri-kpi-label">Active / Open Records</div>
                    <div class="cri-kpi-value" data-count="{{ (int) $active }}">{{ number_format($active) }}</div>
                    <div class="cri-kpi-foot" id="activeRate">Currently active records</div>
                </div>
            </div>
            <div class="cri-kpi" data-tilt>
                {!! $tile('recent', 'warning') !!}
                <div>
                    <div class="cri-kpi-label">Recent Activities</div>
                    <div class="cri-kpi-value" data-count="{{ $recent->count() }}">{{ $recent->count() }}</div>
                    <div class="cri-kpi-foot">Latest records displayed below</div>
                </div>
            </div>
            <div class="cri-kpi" data-tilt>
                {!! $tile('growth', 'purple') !!}
                <div>
                    <div class="cri-kpi-label">Career Development</div>
                    <div class="cri-kpi-value" data-count="3">3</div>
                    <div class="cri-kpi-foot">Core management areas</div>
                </div>
            </div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="career-section">
        <div class="career-section-heading">
            <div><h3>Quick Actions</h3><p>Access frequently used career management activities</p></div>
        </div>

        <div class="cri-quick-grid">
            @foreach($quick as [$label, $sub, $ic, $tone, $url])
                <a class="cri-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                    {!! $tile($ic, $tone, 'sm') !!}
                    <strong>{{ $label }}</strong>
                    <span class="sub">{{ $sub }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- RECENT RECORDS --}}
    <div class="career-section" id="recentSection">
        <div class="career-section-heading">
            <div><h3>Recently Added Career Records</h3><p>Latest career management entries</p></div>
            <a href="{{ route('career.index') }}" class="career-table-action">View All Records →</a>
        </div>

        <div class="career-panel">
            <div class="cri-tools">
                <input type="search" id="rowSearch" class="cri-input" placeholder="Search record or status…" autocomplete="off">
                <select id="rowStatus" class="cri-input" style="min-width:150px"><option value="">All statuses</option></select>
                <span class="cri-muted" id="rowCount"></span>
            </div>

            <div class="career-table-wrap">
                <table class="career-table" id="careerTable">
                    <thead>
                        <tr>
                            <th class="cri-sort" data-key="0">CAREER RECORD</th>
                            <th class="cri-sort" data-key="1">STATUS</th>
                            <th class="cri-sort" data-key="2">CREATED DATE</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recent as $row)
                            <tr class="cri-row" data-sort-2="{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') : '' }}">
                                <td>
                                    <div class="career-record-title">{{ $row->title ?? 'Untitled Record' }}</div>
                                    <div class="career-record-sub">Record #{{ $row->id }}</div>
                                </td>
                                <td>
                                    <span class="career-status {{ $statusClass($row->status ?? '') }}">{{ ucfirst(str_replace('_', ' ', $row->status ?? 'Not Set')) }}</span>
                                </td>
                                <td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '—' }}</td>
                                <td><a href="{{ route('career.show', $row->id) }}" class="career-table-action">View Details →</a></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="career-empty">
                                    <strong>No career records found</strong>
                                    <div style="margin:6px 0 13px">Start by adding your first career management record.</div>
                                    <a href="{{ route('career.create') }}" class="career-btn career-btn-primary">+ Add Career Record</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="cri-muted cri-hide" id="noMatch" style="padding:0 19px 16px">No records match your search.</p>
        </div>
    </div>

    {{-- CAREER MANAGEMENT AREAS --}}
    <div class="career-section">
        <div class="career-section-heading">
            <div><h3>Career Management Areas</h3><p>Three connected areas supporting student career development</p></div>
        </div>

        <div class="career-two-column">
            <div class="career-panel">
                <div class="career-panel-header"><h3>Module Capabilities</h3></div>
                <div class="career-panel-body">
                    <div class="career-type-row" data-tilt>
                        <div class="career-type-label">{!! $tile('records', 'sky', 'xs') !!} Career Opportunities</div>
                        <span class="career-type-count">01</span>
                    </div>
                    <div class="career-type-row" data-tilt>
                        <div class="career-type-label">{!! $tile('active', 'success', 'xs') !!} Student Applications</div>
                        <span class="career-type-count">02</span>
                    </div>
                    <div class="career-type-row" data-tilt>
                        <div class="career-type-label">{!! $tile('sessions', 'purple', 'xs') !!} Career Guidance Sessions</div>
                        <span class="career-type-count">03</span>
                    </div>
                </div>
            </div>

            <div class="career-panel">
                <div class="career-panel-header"><h3>Management Guidelines</h3></div>
                <div class="career-panel-body">
                    <div class="career-info-note" style="margin-top:0">
                        <strong>Career Opportunities</strong><br>
                        Publish internships, job openings and other career opportunities for eligible students.
                    </div>
                    <div class="career-info-note">
                        <strong>Student Applications</strong><br>
                        Monitor applications, review resumes and update selection status.
                    </div>
                    <div class="career-info-note">
                        <strong>Guidance Sessions</strong><br>
                        Schedule counselling appointments and maintain guidance records.
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.cri-3d', card);
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

    const total = {{ (int) $total }}, act = {{ (int) $active }};
    if (total > 0) $('#activeRate').textContent = Math.round(act / total * 100) + '% of all records';

    /* ---- Table: search, status filter, sort ---- */
    const table = $('#careerTable'), tbody = $('tbody', table);
    const rows = () => $$('.cri-row', tbody);
    if (rows().length === 0) { $('.cri-tools').style.display = 'none'; return; }

    const statuses = {};
    rows().forEach(r => statuses[r.children[1].textContent.trim()] = 1);
    Object.keys(statuses).sort().forEach(function (s) {
        const o = document.createElement('option'); o.value = s; o.textContent = s; $('#rowStatus').appendChild(o);
    });

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q)) && (!st || r.children[1].textContent.trim() === st);
            r.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        $('#rowCount').textContent = shown + ' of ' + rows().length + ' shown';
        $('#noMatch').style.display = shown === 0 ? 'block' : 'none';
    }
    $('#rowSearch').addEventListener('input', applyFilter);
    $('#rowStatus').addEventListener('change', applyFilter);
    applyFilter();

    let sortKey = null, sortDir = 1;
    $$('.cri-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10);
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.cri-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
            th.classList.add(sortDir === 1 ? 'asc' : 'desc');
            const list = rows();
            list.sort(function (a, b) {
                const av = a.getAttribute('data-sort-' + key) || a.children[key].textContent.trim();
                const bv = b.getAttribute('data-sort-' + key) || b.children[key].textContent.trim();
                return av.localeCompare(bv, undefined, { numeric: true }) * sortDir;
            });
            list.forEach(r => tbody.appendChild(r));
        });
    });
});
</script>
@endsection