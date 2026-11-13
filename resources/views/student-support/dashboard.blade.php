@extends('layouts.schoolnexa')

@section('title', 'Safety & Student Support Center')

@section('content')

@php
    use Illuminate\Support\Str;

    /* ---------- Fallback SVG icons (used only if a 3D image cannot load) ---------- */
    $paths = [
        'grievance'  => '<path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z"/><path d="M12 7v4M12 14v.01"/>',
        'safety'     => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'counselling'=> '<path d="M20.8 5.6a5 5 0 0 0-7.1 0L12 7.3l-1.7-1.7a5 5 0 0 0-7.1 7.1L12 21.5l8.8-8.8a5 5 0 0 0 0-7.1Z"/>',
        'emergency'  => '<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17v.01"/>',
        'records'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        'open'       => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'layers'     => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'arrow-right'=> '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'back'       => '<path d="M19 12H5M11 6l-6 6 6 6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ---------- */
    $slugs = [
        'grievance'   => 'speech-bubble',
        'safety'      => 'shield',
        'counselling' => 'like',
        'emergency'   => 'high-priority',
        'records'     => 'open-book',
        'open'        => 'clock',
        'layers'      => 'box',
        'school'      => 'school',
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

    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone] ?? $ramp['sky'];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';

        $img = $slug
            ? '<img class="sa-3d-img" src="https://img.icons8.com/3d-fluency/188/' . $slug . '.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.sa-3d\').classList.add(\'fb\')">'
            : '';

        $fb = '<span class="sa-ico" style="--c1:' . $a . ';--c2:' . $b . ';--c3:' . $c . '">' . $icon($name, $px) . '</span>';

        return '<span class="sa-3d ' . $size . ($slug ? '' : ' fb') . '"><span class="sa-3d-in" style="animation-delay:' . $delay . '">' . $img . $fb . '</span></span>';
    };

    /* Pick icon + tone for each support workspace from its slug/title */
    $cardStyle = function (array $card) {
        $n = Str::lower(($card['slug'] ?? '') . ' ' . ($card['title'] ?? ''));
        $map = [
            'emergency'  => ['emergency',   'danger'],
            'safeguard'  => ['safety',      'purple'],
            'safety'     => ['safety',      'purple'],
            'counsel'    => ['counselling', 'success'],
            'grievance'  => ['grievance',   'warning'],
            'complaint'  => ['grievance',   'warning'],
        ];
        foreach ($map as $kw => $style) {
            if (Str::contains($n, $kw)) return $style;
        }
        return ['layers', 'sky'];
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $cardsCol    = collect($cards);
    $totalAll    = (int) $cardsCol->sum('total');
    $openAll     = (int) $cardsCol->sum('open');
    $workspaces  = $cardsCol->count();
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

    .sa-3d::after{
        content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%;
        background:rgba(18,59,93,.22); filter:blur(5px); transition:all .18s ease-out;
    }
    .sa-3d.xs::after{ bottom:-5px; height:6px; }

    .sa-ico{ display:none; }
    .sa-3d.fb .sa-3d-img{ display:none; }
    .sa-3d.fb .sa-ico{
        --c1:#7DB5FF; --c2:#3B82F6; --c3:#1D4ED8;
        position:relative; display:grid; place-items:center; width:82%; height:82%; border-radius:30%; color:#fff;
        background:linear-gradient(150deg,var(--c1) 0%,var(--c2) 55%,var(--c3) 100%);
        box-shadow:0 5px 0 var(--c3), inset 0 2px 0 rgba(255,255,255,.55), inset 0 -3px 6px rgba(0,0,0,.16);
    }
    .sa-3d.fb .sa-ico svg{ filter:drop-shadow(0 2px 2px rgba(0,0,0,.28)); }

    .sa-3d.pop .sa-3d-in{ animation:saPop .55s cubic-bezier(.3,1.6,.5,1), saFloat 4.2s ease-in-out infinite .55s; }

    @keyframes saFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-5px); } }
    @keyframes saPop{ 0%{ transform:scale(1); } 35%{ transform:scale(1.28) rotate(-6deg); } 70%{ transform:scale(.94) rotate(3deg); } 100%{ transform:scale(1); } }
    @media (prefers-reduced-motion: reduce){ .sa-3d-in{ animation:none !important; } .sa-3d{ transition:none; } }

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
    .sa-welcome-right{ display:flex; align-items:center; gap:26px; flex-wrap:wrap; }
    .sa-date{ text-align:right; font-size:13px; color:var(--muted); }
    .sa-date strong{ display:block; font-size:24px; font-weight:800; color:var(--navy); margin-top:2px; }

    .sa-back{
        min-height:44px; display:inline-flex; align-items:center; justify-content:center; gap:8px;
        padding:11px 22px; background:#fff; border:1px solid var(--border); border-radius:12px;
        font-size:14px; font-weight:700; color:var(--text); white-space:nowrap;
        transition:background-color .15s, border-color .15s;
    }
    .sa-back:hover{ background:rgba(23,107,135,.06); border-color:var(--teal); }
    .sa-back:focus-visible{ outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(23,107,135,.10); }

    /* ---------- Summary KPIs ---------- */
    .sa-kpis{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .sa-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
    .sa-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
    .sa-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .sa-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
    .sa-kpi-note{ font-size:12px; color:var(--muted); }

    /* ---------- Panel / head ---------- */
    .sa-panel{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; }
    .sa-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:16px; }
    .sa-title{ font-size:15px; font-weight:700; color:var(--navy); }
    .sa-muted{ font-size:12px; color:var(--muted); }
    .sa-empty{ font-size:13px; color:var(--muted); padding:6px 0; }

    /* ---------- Workspace cards ---------- */
    .sa-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; }
    .sa-card{
        display:flex; flex-direction:column; gap:14px; background:#fff; border:1px solid var(--border);
        border-radius:16px; padding:20px; transition:box-shadow .15s, transform .15s, border-color .15s;
    }
    .sa-card:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); border-color:var(--teal); }
    .sa-card-top{ display:flex; align-items:center; gap:16px; }
    .sa-card h3{ font-size:16px; font-weight:700; color:var(--navy); }
    .sa-card p{ font-size:13px; color:var(--muted); line-height:1.5; }

    .sa-metrics{ display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
    .sa-metric{ background:var(--surface); border:1px solid #E2E8F0; border-radius:12px; padding:14px; }
    .sa-metric small{ display:block; font-size:12px; color:var(--muted); margin-bottom:4px; }
    .sa-metric strong{ font-size:22px; font-weight:800; color:var(--navy); }

    .sa-open{ display:inline-flex; align-items:center; gap:6px; margin-top:auto; font-size:13px; font-weight:700; color:var(--teal); }

    /* ---------- Responsive ---------- */
    @media (max-width:800px){
        .sa-kpis{ grid-template-columns:1fr; }
        .sa-welcome{ padding:20px; }
        .sa-welcome h1{ font-size:22px; }
        .sa-welcome-right{ width:100%; justify-content:space-between; }
    }
</style>

<div class="sa">

    {{-- WELCOME --}}
    <div class="sa-welcome" data-tilt>
        <div>
            <div class="sa-eyebrow">{{ $greet }} <i></i> Student Support</div>
            <h1>Safety &amp; Student Support Center</h1>
            <p>One workspace for grievances, safeguarding, counselling and emergency coordination.</p>
        </div>
        <div class="sa-welcome-right">
            {!! $tile('safety', 'sky', 'lg') !!}
            <div class="sa-date">
                {{ now()->format('l, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
            <a class="sa-back" href="{{ url('/dashboard') }}">{!! $icon('back', 16) !!} Back to Dashboard</a>
        </div>
    </div>

    {{-- SUMMARY KPIs --}}
    <div class="sa-kpis">
        <div class="sa-kpi" data-tilt>
            {!! $tile('layers', 'teal') !!}
            <div>
                <div class="sa-kpi-label">Workspaces</div>
                <div class="sa-kpi-value">{{ number_format($workspaces) }}</div>
                <div class="sa-kpi-note">Support areas available</div>
            </div>
        </div>
        <div class="sa-kpi" data-tilt>
            {!! $tile('records', 'purple') !!}
            <div>
                <div class="sa-kpi-label">Total Records</div>
                <div class="sa-kpi-value">{{ number_format($totalAll) }}</div>
                <div class="sa-kpi-note">Across all workspaces</div>
            </div>
        </div>
        <div class="sa-kpi" data-tilt>
            {!! $tile('open', 'warning') !!}
            <div>
                <div class="sa-kpi-label">Open / Active</div>
                <div class="sa-kpi-value">{{ number_format($openAll) }}</div>
                <div class="sa-kpi-note">Need attention</div>
            </div>
        </div>
    </div>

    {{-- WORKSPACES --}}
    <div class="sa-panel">
        <div class="sa-head">
            <h3 class="sa-title">Support Workspaces</h3>
            <span class="sa-muted">{{ $workspaces }} available</span>
        </div>

        @if($workspaces)
            <div class="sa-grid">
                @foreach($cards as $card)
                    @php [$ic, $tone] = $cardStyle($card); @endphp
                    <a class="sa-card" data-tilt href="{{ route('student-support.index', $card['slug']) }}">
                        <div class="sa-card-top">
                            {!! $tile($ic, $tone) !!}
                            <h3>{{ $card['title'] }}</h3>
                        </div>

                        <p>{{ $card['description'] }}</p>

                        <div class="sa-metrics">
                            <div class="sa-metric">
                                <small>Total records</small>
                                <strong>{{ number_format($card['total']) }}</strong>
                            </div>
                            <div class="sa-metric">
                                <small>Open / active</small>
                                <strong>{{ number_format($card['open']) }}</strong>
                            </div>
                        </div>

                        <span class="sa-open">Open workspace {!! $icon('arrow-right', 14) !!}</span>
                    </a>
                @endforeach
            </div>
        @else
            <p class="sa-empty">No support workspaces available yet.</p>
        @endif
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