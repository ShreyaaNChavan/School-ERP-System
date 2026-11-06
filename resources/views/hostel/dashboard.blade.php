@extends('layouts.schoolnexa')

@section('title', 'Hostel Dashboard')

@section('content')

@php
    use Illuminate\Support\Str;

    /* ---------- Fallback SVG icons (used only if a 3D image cannot load) ---------- */
    $paths = [
        'hostel'     => '<path d="M3 20V6"/><path d="M3 16h18v4"/><path d="M21 16v-4a3 3 0 0 0-3-3h-7v7"/><circle cx="7" cy="12" r="2"/>',
        'rooms'      => '<rect x="5" y="3" width="14" height="18" rx="1.5"/><circle cx="15" cy="12" r=".9"/>',
        'layers'     => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'students'   => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9v6"/>',
        'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        'staff'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'key'        => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/>',
        'wallet'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M16 15h2"/><path d="M6 6V5a2 2 0 0 1 2-2h8"/>',
        'reports'    => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'attendance' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'arrow-right'=> '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ---------- */
    $slugs = [
        'hostel'     => 'bed',
        'rooms'      => 'door',
        'layers'     => 'box',
        'students'   => 'graduation-cap',
        'user'       => 'group',
        'staff'      => 'conference-call',
        'key'        => 'key',
        'wallet'     => 'money-bag',
        'reports'    => 'bar-chart',
        'attendance' => 'calendar',
    ];

    /* Fallback colours: [highlight, base, shadow] */
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'danger'  => ['#FA8585', '#DC2626', '#991B1B'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    /* Interactive 3D icon: sizes xs | sm | md | lg */
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';

        $img = $slug
            ? '<img class="sa-3d-img" src="https://img.icons8.com/3d-fluency/188/' . $slug . '.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.sa-3d\').classList.add(\'fb\')">'
            : '';

        $fb = '<span class="sa-ico" style="--c1:' . $a . ';--c2:' . $b . ';--c3:' . $c . '">' . $icon($name, $px) . '</span>';

        return '<span class="sa-3d ' . $size . ($slug ? '' : ' fb') . '"><span class="sa-3d-in" style="animation-delay:' . $delay . '">' . $img . $fb . '</span></span>';
    };

    /* ---------- Data ---------- */
    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $vacantBeds = max(0, (int) $bedCount - (int) $occupiedBeds);
    $occupancy  = $bedCount > 0 ? round(($occupiedBeds / $bedCount) * 100) : 0;

    $cards = [
        ['Hostels',        $hostelCount,  'hostel',   'sky',     'Registered hostels'],
        ['Rooms',          $roomCount,    'rooms',    'success', 'Total hostel rooms'],
        ['Beds',           $bedCount,     'layers',   'purple',  'Total bed capacity'],
        ['Occupied',       $occupiedBeds, 'students', 'warning', 'Beds currently allocated'],
        ['Visitors Today', $visitorCount, 'user',     'teal',    'Logged today'],
    ];

    $quick = [
        ['Hostel List',  'hostel.index',       'hostel',     'sky'],
        ['Hostel Rooms', 'hostel.rooms',       'rooms',      'success'],
        ['Room Types',   'hostel.room-types',  'layers',     'purple'],
        ['Allocation',   'hostel.allocations', 'key',        'warning'],
        ['Fees',         'hostel.fees',        'wallet',     'teal'],
        ['Wardens',      'hostel.wardens',     'staff',      'navy'],
        ['Visitor Log',  'hostel.visitors',    'reports',    'sky'],
        ['Attendance',   'hostel.attendance',  'attendance', 'success'],
    ];
@endphp

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .sa{
        --navy:#123B5D; --teal:#176B87; --purple:#5B3FA3; --sky:#3B82F6;
        --success:#16A34A; --warning:#D97706; --danger:#DC2626;
        --surface:#F8FAFC; --border:#CBD5E1; --text:#25313C; --muted:#64748B;
        font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;
        color:var(--text); display:flex; flex-direction:column; gap:20px;
        width:100%; max-width:1500px; margin:0 auto;
    }
    .sa *{ box-sizing:border-box; }
    .sa h1,.sa h2,.sa h3,.sa p{ margin:0; }
    .sa svg{ display:block; flex-shrink:0; }
    .sa a{ text-decoration:none; color:inherit; }

    /* ================= INTERACTIVE 3D ICONS ================= */
    .sa-3d{
        position:relative; display:inline-block; flex-shrink:0; margin-bottom:8px;
        width:64px; height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out; will-change:transform; cursor:pointer;
    }
    .sa-3d.xs{ width:42px; height:42px; margin-bottom:6px; }
    .sa-3d.sm{ width:48px; height:48px; }
    .sa-3d.lg{ width:92px; height:92px; margin-bottom:10px; }
    .sa-3d-in{ display:grid; place-items:center; width:100%; height:100%; animation:saFloat 4.2s ease-in-out infinite; }
    .sa-3d-img{ width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none; filter:drop-shadow(0 10px 8px rgba(18,59,93,.28)); }
    .sa-3d::after{ content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%; background:rgba(18,59,93,.22); filter:blur(5px); }
    .sa-3d.xs::after{ bottom:-5px; height:6px; }
    .sa-ico{ display:none; }
    .sa-3d.fb .sa-3d-img{ display:none; }
    .sa-3d.fb .sa-ico{
        --c1:#7DB5FF; --c2:#3B82F6; --c3:#1D4ED8;
        position:relative; display:grid; place-items:center; width:82%; height:82%; border-radius:30%; color:#fff;
        background:linear-gradient(150deg,var(--c1) 0%,var(--c2) 55%,var(--c3) 100%);
        box-shadow:0 5px 0 var(--c3), inset 0 2px 0 rgba(255,255,255,.55), inset 0 -3px 6px rgba(0,0,0,.16);
    }
    .sa-3d.pop .sa-3d-in{ animation:saPop .55s cubic-bezier(.3,1.6,.5,1), saFloat 4.2s ease-in-out infinite .55s; }
    @keyframes saFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-5px); } }
    @keyframes saPop{ 0%{ transform:scale(1); } 35%{ transform:scale(1.28) rotate(-6deg); } 70%{ transform:scale(.94) rotate(3deg); } 100%{ transform:scale(1); } }
    @media (prefers-reduced-motion: reduce){ .sa-3d-in{ animation:none !important; } .sa-3d{ transition:none; } }

    /* ---------- Panels ---------- */
    .sa-panel{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; }
    .sa-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; }
    .sa-title{ font-size:15px; font-weight:700; color:var(--navy); }
    .sa-link{ display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:var(--teal); white-space:nowrap; }
    .sa-pill{ display:inline-flex; align-items:center; justify-content:center; min-width:26px; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; background:#FEF3C7; color:var(--warning); }
    .sa-pill.teal{ background:#D5EBF2; color:var(--teal); }
    .sa-pill.success{ background:#DCFCE7; color:var(--success); }
    .sa-muted{ font-size:12px; color:var(--muted); }
    .sa-empty{ font-size:13px; color:var(--muted); padding:6px 0; }

    /* ---------- Welcome ---------- */
    .sa-welcome{
        display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
        padding:26px 30px; border-radius:18px; border:1px solid #DCE8F7;
        background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);
    }
    .sa-eyebrow{ display:flex; align-items:center; gap:8px; font-size:11px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:var(--teal); margin-bottom:6px; }
    .sa-eyebrow i{ width:4px; height:4px; border-radius:50%; background:var(--teal); display:inline-block; }
    .sa-welcome h1{ font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-.02em; }
    .sa-welcome p{ margin-top:6px; font-size:14px; color:var(--muted); }
    .sa-welcome-right{ display:flex; align-items:center; gap:26px; }
    .sa-date{ text-align:right; font-size:13px; color:var(--muted); }
    .sa-date strong{ display:block; font-size:24px; font-weight:800; color:var(--navy); margin-top:2px; }

    /* ---------- Alerts ---------- */
    .sa-alert{ padding:12px 16px; border-radius:12px; font-size:13px; font-weight:600; background:#DCFCE7; color:#0F7A36; border:1px solid #BBF7D0; }
    .sa-alert.error{ background:#FEE2E2; color:#991B1B; border-color:#FECACA; font-weight:500; }

    /* ---------- KPIs ---------- */
    .sa-kpis{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:16px; }
    .sa-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
    .sa-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
    .sa-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .sa-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
    .sa-kpi-note{ font-size:12px; color:var(--muted); }

    /* ---------- Main grid ---------- */
    .sa-main{ display:grid; grid-template-columns:minmax(0,1.6fr) minmax(0,1fr); gap:16px; align-items:start; }
    .sa-col{ display:flex; flex-direction:column; gap:16px; min-width:0; }

    /* Quick actions */
    .sa-quick-grid{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .sa-quick{ display:flex; flex-direction:column; align-items:center; gap:6px; padding:16px 8px 12px; border:1px solid var(--border); border-radius:14px; background:var(--surface); text-align:center; cursor:pointer; transition:.15s; }
    .sa-quick:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); }
    .sa-quick strong{ font-size:12px; font-weight:600; color:var(--navy); }

    /* Table */
    .sa-table{ width:100%; border-collapse:collapse; }
    .sa-table th{ text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); padding:10px 12px; background:var(--surface); border-bottom:1px solid var(--border); }
    .sa-table th:first-child{ border-radius:10px 0 0 10px; }
    .sa-table th:last-child{ border-radius:0 10px 10px 0; }
    .sa-table td{ font-size:13px; color:var(--text); padding:12px; border-bottom:1px solid #EEF2F6; }
    .sa-table tr:last-child td{ border-bottom:none; }
    .sa-table td:first-child{ font-weight:600; color:var(--navy); }

    /* Occupancy */
    .sa-occ{ display:flex; align-items:baseline; gap:8px; margin-bottom:12px; }
    .sa-occ strong{ font-size:34px; font-weight:800; color:var(--navy); }
    .sa-track{ height:10px; background:#E2E8F0; border-radius:999px; overflow:hidden; }
    .sa-fill{ height:100%; border-radius:999px; background:linear-gradient(90deg,var(--sky),var(--teal)); }
    .sa-metrics{ display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-top:16px; }
    .sa-metric{ background:var(--surface); border:1px solid #E2E8F0; border-radius:12px; padding:14px; }
    .sa-metric small{ display:block; font-size:12px; color:var(--muted); margin-bottom:4px; }
    .sa-metric strong{ font-size:22px; font-weight:800; color:var(--navy); }

    /* ---------- Responsive ---------- */
    @media (max-width:1280px){
        .sa-kpis{ grid-template-columns:repeat(3,minmax(0,1fr)); }
        .sa-quick-grid{ grid-template-columns:repeat(3,minmax(0,1fr)); }
    }
    @media (max-width:900px){
        .sa-main{ grid-template-columns:1fr; }
        .sa-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width:800px){
        .sa-welcome{ padding:20px; }
        .sa-welcome h1{ font-size:22px; }
        .sa-welcome-right{ width:100%; justify-content:space-between; }
    }
    @media (max-width:520px){
        .sa-kpis{ grid-template-columns:1fr; }
        .sa-quick-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
</style>

<div class="sa">

    {{-- WELCOME --}}
    <div class="sa-welcome" data-tilt>
        <div>
            <div class="sa-eyebrow">{{ $greet }} <i></i> Hostel Management</div>
            <h1>Hostel Dashboard</h1>
            <p>Occupancy, rooms, allocations and visitors at a glance.</p>
        </div>
        <div class="sa-welcome-right">
            {!! $tile('hostel', 'sky', 'lg') !!}
            <div class="sa-date">
                {{ now()->format('l, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="sa-alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="sa-alert error">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    {{-- KPI CARDS --}}
    <div class="sa-kpis">
        @foreach($cards as [$label, $value, $ic, $tone, $note])
            <div class="sa-kpi" data-tilt>
                {!! $tile($ic, $tone) !!}
                <div>
                    <div class="sa-kpi-label">{{ $label }}</div>
                    <div class="sa-kpi-value">{{ number_format($value) }}</div>
                    <div class="sa-kpi-note">{{ $note }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MAIN COLUMNS --}}
    <div class="sa-main">

        {{-- LEFT --}}
        <div class="sa-col">
            <div class="sa-panel">
                <div class="sa-head"><h3 class="sa-title">Quick Actions</h3></div>
                <div class="sa-quick-grid">
                    @foreach($quick as [$label, $route, $ic, $tone])
                        <a href="{{ route($route) }}" class="sa-quick" data-tilt>
                            {!! $tile($ic, $tone, 'sm') !!}
                            <strong>{{ $label }}</strong>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Hostels</h3>
                    <a href="{{ route('hostel.index') }}" class="sa-link">View all {!! $icon('arrow-right', 14) !!}</a>
                </div>
                <table class="sa-table">
                    <thead>
                        <tr><th>Name</th><th>Type</th><th>Warden</th></tr>
                    </thead>
                    <tbody>
                        @forelse($hostels as $h)
                            <tr>
                                <td>{{ $h->name }}</td>
                                <td>{{ $h->hostel_type }}</td>
                                <td>{{ $h->warden_name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="sa-empty">No hostels registered.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="sa-col">
            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Bed Occupancy</h3>
                    <span class="sa-pill teal">Live</span>
                </div>
                <div class="sa-occ">
                    <strong>{{ $occupancy }}%</strong>
                    <span class="sa-muted">of beds occupied</span>
                </div>
                <div class="sa-track"><div class="sa-fill" style="width:{{ $occupancy }}%"></div></div>
                <div class="sa-metrics">
                    <div class="sa-metric"><small>Occupied</small><strong>{{ number_format($occupiedBeds) }}</strong></div>
                    <div class="sa-metric"><small>Vacant</small><strong>{{ number_format($vacantBeds) }}</strong></div>
                </div>
            </div>

            <div class="sa-panel">
                <div class="sa-head"><h3 class="sa-title">Today</h3></div>
                <div class="sa-metrics" style="margin-top:0">
                    <div class="sa-metric"><small>Visitors</small><strong>{{ number_format($visitorCount) }}</strong></div>
                    <div class="sa-metric"><small>Rooms</small><strong>{{ number_format($roomCount) }}</strong></div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    /* Interactive 3D icons: tilt toward the mouse, grow on hover, pop on click */
    (function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        document.querySelectorAll('.sa [data-tilt]').forEach(function (card) {
            var art = card.querySelector('.sa-3d');
            if (!art) return;

            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect();
                var x = (e.clientX - r.left) / r.width  - 0.5;
                var y = (e.clientY - r.top)  / r.height - 0.5;
                var clamp = function (v) { return Math.max(-22, Math.min(22, v)); };
                art.style.setProperty('--ry', clamp(x * 44) + 'deg');
                art.style.setProperty('--rx', clamp(-y * 44) + 'deg');
                art.style.setProperty('--s', '1.14');
            });

            card.addEventListener('mouseleave', function () {
                art.style.setProperty('--ry', '0deg');
                art.style.setProperty('--rx', '0deg');
                art.style.setProperty('--s', '1');
            });

            card.addEventListener('click', function () {
                art.classList.remove('pop');
                void art.offsetWidth;
                art.classList.add('pop');
            });
        });
    })();
</script>

@endsection