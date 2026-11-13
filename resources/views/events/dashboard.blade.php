@extends('layouts.schoolnexa')

@section('title', 'Events Management')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D image cannot load) ---------- */
    $paths = [
        'events'    => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m12 13 1.2 2.4 2.6.4-1.9 1.8.5 2.6-2.4-1.3-2.4 1.3.5-2.6-1.9-1.8 2.6-.4Z"/>',
        'calendar'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'check'     => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'list'      => '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['events']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ---------- */
    $slugs = [
        'events'   => 'star',
        'calendar' => 'calendar',
        'clock'    => 'clock',
        'check'    => 'checkmark',
        'plus'     => 'plus-math',
        'list'     => 'books',
    ];

    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

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

    $cards = [
        ['Total Events',     $total,     'events',   'sky',     'All recorded events'],
        ['Upcoming Events',  $upcoming,  'calendar', 'purple',  'Scheduled for the future'],
        ['Ongoing Events',   $ongoing,   'clock',    'warning', 'Currently in progress'],
        ['Completed Events', $completed, 'check',    'success', 'Successfully completed'],
    ];

    $quick = [
        ['Create Event',    'Schedule a new event',       route('events.create'),                          'plus',     'success'],
        ['Manage Events',   'View and manage all events', route('events.index'),                           'list',     'purple'],
        ['Planned Events',  'View scheduled events',      route('events.index', ['status' => 'planned']), 'calendar', 'teal'],
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
    .sa-3d.sm{ width:48px; height:48px; }
    .sa-3d.lg{ width:92px; height:92px; margin-bottom:10px; }
    .sa-3d-in{ display:grid; place-items:center; width:100%; height:100%; animation:saFloat 4.2s ease-in-out infinite; }
    .sa-3d-img{ width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none; filter:drop-shadow(0 10px 8px rgba(18,59,93,.28)); }
    .sa-3d::after{ content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%; background:rgba(18,59,93,.22); filter:blur(5px); }
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
    .sa-sub{ font-size:12px; color:var(--muted); margin-top:3px; }
    .sa-link{ display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:var(--teal); white-space:nowrap; }
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

    .sa-btn{ display:inline-flex; align-items:center; gap:6px; padding:7px 14px; border-radius:10px; font-size:12px; font-weight:700; background:#fff; color:var(--navy) !important; border:1px solid var(--border); transition:.15s; }
    .sa-btn:hover{ border-color:var(--teal); }

    /* ---------- Alerts ---------- */
    .sa-alert{ padding:12px 16px; border-radius:12px; font-size:13px; font-weight:600; background:#DCFCE7; color:#0F7A36; border:1px solid #BBF7D0; }

    /* ---------- KPIs ---------- */
    .sa-kpis{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
    .sa-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
    .sa-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
    .sa-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .sa-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
    .sa-kpi-note{ font-size:12px; color:var(--muted); }

    /* Quick actions */
    .sa-quick-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .sa-quick{ display:flex; align-items:center; gap:14px; padding:14px 16px; border:1px solid var(--border); border-radius:14px; background:var(--surface); cursor:pointer; transition:.15s; }
    .sa-quick:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); }
    .sa-quick .sa-3d{ margin-bottom:0; }
    .sa-quick strong{ display:block; font-size:13px; font-weight:700; color:var(--navy); }
    .sa-quick span.sub{ display:block; font-size:12px; color:var(--muted); margin-top:2px; }

    /* Table */
    .sa-table-wrap{ overflow-x:auto; }
    .sa-table{ width:100%; border-collapse:collapse; min-width:680px; }
    .sa-table th{ text-align:left; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--muted); padding:10px 12px; background:var(--surface); border-bottom:1px solid var(--border); }
    .sa-table th:first-child{ border-radius:10px 0 0 10px; }
    .sa-table th:last-child{ border-radius:0 10px 10px 0; }
    .sa-table td{ font-size:13px; color:var(--text); padding:12px; border-bottom:1px solid #EEF2F6; vertical-align:middle; }
    .sa-table tr:last-child td{ border-bottom:none; }
    .sa-table .name{ font-weight:600; color:var(--navy); }

    .sa-pill{ display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-size:11px; font-weight:700; background:#EDE9FE; color:var(--purple); }
    .sa-pill.planned{ background:#FEF3C7; color:#92400E; }
    .sa-pill.ongoing{ background:#DBEAFE; color:#1D4ED8; }
    .sa-pill.completed{ background:#DCFCE7; color:#166534; }
    .sa-pill.cancelled{ background:#FEE2E2; color:#991B1B; }

    /* ---------- Responsive ---------- */
    @media (max-width:1100px){ .sa-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:800px){
        .sa-welcome{ padding:20px; }
        .sa-welcome h1{ font-size:22px; }
        .sa-welcome-right{ width:100%; justify-content:space-between; }
        .sa-quick-grid{ grid-template-columns:1fr; }
    }
    @media (max-width:520px){ .sa-kpis{ grid-template-columns:1fr; } }
</style>

<div class="sa">

    {{-- WELCOME --}}
    <div class="sa-welcome" data-tilt>
        <div>
            <div class="sa-eyebrow">{{ $greet }} <i></i> Events</div>
            <h1>Events Management</h1>
            <p>Plan, organize and manage school events and activities.</p>
        </div>
        <div class="sa-welcome-right">
            {!! $tile('events', 'sky', 'lg') !!}
            <div class="sa-date">
                {{ now()->format('l, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="sa-alert">{{ session('success') }}</div>
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

    {{-- QUICK ACTIONS --}}
    <div class="sa-panel">
        <div class="sa-head">
            <div>
                <h3 class="sa-title">Quick Actions</h3>
                <div class="sa-sub">Frequently used event management operations</div>
            </div>
        </div>
        <div class="sa-quick-grid">
            @foreach($quick as [$label, $sub, $url, $ic, $tone])
                <a href="{{ $url }}" class="sa-quick" data-tilt>
                    {!! $tile($ic, $tone, 'sm') !!}
                    <div>
                        <strong>{{ $label }}</strong>
                        <span class="sub">{{ $sub }}</span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- RECENT EVENTS --}}
    <div class="sa-panel">
        <div class="sa-head">
            <div>
                <h3 class="sa-title">Recent Events</h3>
                <div class="sa-sub">Latest scheduled and recorded events</div>
            </div>
            <a href="{{ route('events.index') }}" class="sa-link">View all events {!! $icon('arrow-right', 14) !!}</a>
        </div>

        <div class="sa-table-wrap">
            <table class="sa-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Category</th>
                        <th>Date &amp; Time</th>
                        <th>Venue</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent as $event)
                        <tr>
                            <td>
                                <div class="name">{{ $event->title }}</div>
                                <div class="sa-sub">Event ID: #{{ $event->id }}</div>
                            </td>
                            <td><span class="sa-pill">{{ $event->event_type ?: 'General' }}</span></td>
                            <td>
                                <div class="name">{{ \Carbon\Carbon::parse($event->starts_at)->format('d M Y') }}</div>
                                <div class="sa-sub">{{ \Carbon\Carbon::parse($event->starts_at)->format('h:i A') }}</div>
                            </td>
                            <td>{{ $event->venue ?: 'Not specified' }}</td>
                            <td><span class="sa-pill {{ strtolower($event->status) }}">{{ ucfirst($event->status) }}</span></td>
                            <td><a href="{{ route('events.show', $event->id) }}" class="sa-btn">View</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="sa-empty">
                                No events have been created yet. Create your first school event to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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