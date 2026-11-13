@extends('layouts.schoolnexa')

@section('title', 'Audit Logs')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'today'    => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'actors'   => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'entities' => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'export'   => '<path d="M12 3v12M7 10l5 5 5-5"/><path d="M4 20h16"/>',
        'recent'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'frequent' => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'back'     => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'    => 'clipboard',
        'today'    => 'calendar',
        'actors'   => 'conference-call',
        'entities' => 'box',
        'export'   => 'download',
        'recent'   => 'hourglass',
        'frequent' => 'bar-chart',
        'back'     => 'home',
        'hero'     => 'shield',
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
        ['Total Events',  $total,    'All recorded activity',   'total',    'sky'],
        ['Events Today',  $today,    'Logged since midnight',   'today',    'success'],
        ['Active Actors', $actors,   'Distinct users and systems', 'actors', 'purple'],
        ['Entity Types',  $entities, 'Record types tracked',    'entities', 'warning'],
    ];

    $quick = [
        ['All Audit Events', 'Browse the full log',          'total',    'sky',     route('audit-logs.index')],
        ['Export CSV',       'Download the audit trail',     'export',   'success', route('audit-logs.export')],
        ['Recent Activity',  'Jump to the latest events',    'recent',   'warning', '#recentSection'],
        ['Frequent Actions', 'See the most common actions',  'frequent', 'purple',  '#actionsSection'],
        ['Dashboard',        'Back to the main dashboard',   'back',     'teal',    route('dashboard')],
    ];

    $actionMax = max(1, (int) collect($actions)->max('total'));
@endphp

@include('audit-logs._style')

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

    /* ================= KPI CARDS (no links) ================= */
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

    /* ================= TABLE TOOLS ================= */
    .aui-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .aui-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:190px;outline:none;background:#fff;font-family:inherit}
    .aui-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .aui-muted{font-size:12px;color:#64748B}
    .aui-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .aui-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .aui-sort.asc:after{content:' \2191';opacity:1}
    .aui-sort.desc:after{content:' \2193';opacity:1}
    .aui-row{transition:background .2s}
    .aui-row:hover{background:rgba(23,107,135,.05)}
    .aui-hide{display:none}
    .aui-count{display:flex;align-items:center;gap:10px;min-width:150px}
    .aui-track{flex:1;height:8px;background:#E2E8F0;border-radius:999px;overflow:hidden;min-width:60px}
    .aui-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,#3B82F6,#176B87)}

    @@media(max-width:1200px){.aui-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:900px){.aui-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.aui-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.aui-welcome h1{font-size:22px}.aui-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="aui-welcome" data-tilt>
    <div>
        <div class="aui-eyebrow">{{ $greet }} <i></i> System Administration</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Monitor important activity and changes across your SchoolNexa environment.</p>
    </div>
    <div class="aui-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="aui-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="sx-head">
    <div><h1 class="sx-title">Audit Logs</h1><p class="sx-sub">Monitor important activity and changes across your SchoolNexa environment.</p></div>
    <div class="sx-actions">
        <a class="sx-btn" href="{{ route('dashboard') }}">Back to Dashboard</a>
        <a class="sx-btn" href="{{ route('audit-logs.index') }}">All Audit Events</a>
        <a class="sx-btn primary" href="{{ route('audit-logs.export') }}">Export CSV</a>
    </div>
</div>

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

<div class="sx-grid" style="grid-template-columns:1fr 1fr">

    {{-- RECENT ACTIVITY --}}
    <section class="sx-panel" id="recentSection"><h3>Recent Activity</h3>
        <div class="aui-tools">
            <input type="search" id="recentSearch" class="aui-input" placeholder="Search user or action…" autocomplete="off">
            <span class="aui-muted" id="recentCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="recentTable">
                <thead><tr>
                    <th class="aui-sort" data-key="0">USER</th>
                    <th class="aui-sort" data-key="1">ACTION</th>
                    <th class="aui-sort" data-key="2">DATE</th>
                </tr></thead>
                <tbody>
                @forelse($recent as $r)
                    <tr class="aui-row">
                        <td>{{ $r->actor_name ?? 'System' }}</td>
                        <td><a href="{{ route('audit-logs.show', $r->id) }}">{{ $r->action }}</a></td>
                        <td>{{ $r->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="sx-empty">No audit events recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="aui-muted aui-hide" id="recentNoMatch" style="margin-top:10px">No events match your search.</p>
    </section>

    {{-- MOST FREQUENT ACTIONS --}}
    <section class="sx-panel" id="actionsSection"><h3>Most Frequent Actions</h3>
        <div class="aui-tools">
            <input type="search" id="actionSearch" class="aui-input" placeholder="Search action…" autocomplete="off">
            <span class="aui-muted" id="actionCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="actionTable">
                <thead><tr>
                    <th class="aui-sort" data-key="0">ACTION</th>
                    <th class="aui-sort" data-key="1">EVENTS</th>
                </tr></thead>
                <tbody>
                @forelse($actions as $a)
                    <tr class="aui-row">
                        <td>{{ $a->action }}</td>
                        <td>
                            <div class="aui-count">
                                <strong>{{ $a->total }}</strong>
                                <div class="aui-track"><div class="aui-fill" style="width:{{ ($a->total / $actionMax) * 100 }}%"></div></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="sx-empty">No actions recorded.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="aui-muted aui-hide" id="actionNoMatch" style="margin-top:10px">No actions match your search.</p>
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

    /* ---- Reusable table helper: search + sort ---- */
    function setupTable(o) {
        const table = $(o.table); if (!table) return;
        const tbody = $('tbody', table);
        const rows = () => $$('.aui-row', tbody);
        const tools = $(o.search).closest('.aui-tools');
        if (rows().length === 0) { tools.style.display = 'none'; return; }

        function apply() {
            const q = $(o.search).value.trim().toLowerCase(); let shown = 0;
            rows().forEach(function (r) {
                const ok = !q || r.textContent.toLowerCase().includes(q);
                r.style.display = ok ? '' : 'none'; if (ok) shown++;
            });
            $(o.count).textContent = shown + ' of ' + rows().length + ' shown';
            $(o.noMatch).style.display = shown === 0 ? 'block' : 'none';
        }
        $(o.search).addEventListener('input', apply);
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

    setupTable({ table: '#recentTable', search: '#recentSearch', count: '#recentCount', noMatch: '#recentNoMatch' });
    setupTable({ table: '#actionTable', search: '#actionSearch', count: '#actionCount', noMatch: '#actionNoMatch' });
});
</script>
@endsection