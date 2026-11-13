@extends('layouts.schoolnexa')

@section('title', 'Integration Hub')

@section('content')

@php
    $disconnected = max(0, (int) $total - (int) $connected - (int) $attention);

    /* green = connected, amber = needs attention/pending, red = disconnected/error */
    $statusClass = function ($s) {
        $s = strtolower(trim((string) $s));
        if (in_array($s, ['connected','active','healthy','success'])) return 'good';
        if (in_array($s, ['attention','needs_attention','warning','pending','degraded'])) return 'warn';
        if (in_array($s, ['disconnected','error','failed','inactive','disabled','deactive'])) return 'bad';
        return '';
    };

    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'        => '<path d="M10 13a5 5 0 0 0 7 0l3-3a5 5 0 0 0-7-7l-1 1"/><path d="M14 11a5 5 0 0 0-7 0l-3 3a5 5 0 0 0 7 7l1-1"/>',
        'connected'    => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'attention'    => '<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17v.01"/>',
        'disconnected' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        'add'          => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'all'          => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'configured'   => '<path d="M4 6h9M19 6h1M4 12h3M13 12h7M4 18h11M21 18h-1"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'activity'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'back'         => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'        => 'link',
        'connected'    => 'checkmark',
        'attention'    => 'high-priority',
        'disconnected' => 'cancel',
        'add'          => 'plus-math',
        'all'          => 'clipboard',
        'configured'   => 'settings',
        'activity'     => 'hourglass',
        'back'         => 'home',
        'hero'         => 'link',
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
            ? '<img class="ihi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.ihi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="ihi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="ihi-3d '.$size.($slug ? '' : ' fb').'"><span class="ihi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpis = [
        ['Total Integrations', $total,        'Configured services',    'total',        'sky'],
        ['Connected',          $connected,    'Healthy and running',    'connected',    'success'],
        ['Needs Attention',    $attention,    'Review configuration',   'attention',    'warning'],
        ['Disconnected',       $disconnected, 'Not currently linked',   'disconnected', 'danger'],
    ];

    $quick = [
        ['Add Integration',   'Connect a new service',        'add',        'success', route('integration-hub.create')],
        ['All Integrations',  'Browse every integration',     'all',        'sky',     route('integration-hub.index')],
        ['Configured',        'Jump to the integration list', 'configured', 'purple',  '#itemsSection'],
        ['Recent Activity',   'See the latest events',        'activity',   'warning', '#logsSection'],
        ['Dashboard',         'Back to the main dashboard',   'back',       'teal',    route('dashboard')],
    ];
@endphp

@include('integration-hub._style')

<div class="sx-page">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .ihi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .ihi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .ihi-3d.sm{width:48px;height:48px}
    .ihi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .ihi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:ihiFloat 4.2s ease-in-out infinite}
    .ihi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .ihi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .ihi-ico{display:none}
    .ihi-3d.fb .ihi-3d-img{display:none}
    .ihi-3d.fb .ihi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .ihi-3d.pop .ihi-3d-in{animation:ihiPop .55s cubic-bezier(.3,1.6,.5,1),ihiFloat 4.2s ease-in-out infinite .55s}
    @keyframes ihiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes ihiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.ihi-3d-in{animation:none!important}.ihi-3d{transition:none}}

    /* ================= BANNER ================= */
    .ihi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .ihi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .ihi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .ihi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .ihi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .ihi-welcome-right{display:flex;align-items:center;gap:26px}
    .ihi-date{text-align:right;font-size:13px;color:#64748B}
    .ihi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .ihi-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .ihi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .ihi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .ihi-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .ihi-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin:2px 0}
    .ihi-kpi span.foot{font-size:11px;color:#94A3B8}

    /* ================= QUICK ACTIONS ================= */
    .ihi-box{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;margin-bottom:16px}
    .ihi-box h3{margin:0;font-size:15px;color:#123B5D}
    .ihi-quick-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-top:12px}
    .ihi-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .ihi-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .ihi-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .ihi-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= PILLS / TABLE TOOLS ================= */
    .ihi-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B;white-space:nowrap;text-transform:capitalize}
    .ihi-pill.good{background:#DCFCE7;color:#166534}
    .ihi-pill.bad{background:#FEE2E2;color:#DC2626}
    .ihi-pill.warn{background:#FEF3C7;color:#B45309}
    .ihi-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .ihi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:190px;outline:none;background:#fff;font-family:inherit}
    .ihi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .ihi-muted{font-size:12px;color:#64748B}
    .ihi-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .ihi-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .ihi-sort.asc:after{content:' \2191';opacity:1}
    .ihi-sort.desc:after{content:' \2193';opacity:1}
    .ihi-row{transition:background .2s}
    .ihi-row:hover{background:rgba(23,107,135,.05)}
    .ihi-hide{display:none}

    @@media(max-width:1200px){.ihi-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:900px){.ihi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.ihi-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.ihi-welcome h1{font-size:22px}.ihi-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="ihi-welcome" data-tilt>
    <div>
        <div class="ihi-eyebrow">{{ $greet }} <i></i> System Administration</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Manage external services, provider configurations and integration health.</p>
    </div>
    <div class="ihi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="ihi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="sx-head">
    <div><h1 class="sx-title">Integration Hub</h1><p class="sx-sub">Manage external services, provider configurations and integration health.</p></div>
    <div class="sx-actions"><a class="sx-btn primary" href="{{ route('integration-hub.create') }}">+ Add Integration</a></div>
</div>

@if(session('success'))<div class="sx-alert">{{ session('success') }}</div>@endif

{{-- KPI CARDS --}}
<div class="ihi-kpis">
    @foreach($kpis as [$label, $value, $foot, $ic, $tone])
        <div class="ihi-kpi" data-tilt>
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
<div class="ihi-box">
    <h3>Quick Actions</h3>
    <div class="ihi-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="ihi-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

<div class="sx-grid" style="grid-template-columns:1fr 1fr">

    {{-- CONFIGURED INTEGRATIONS --}}
    <section class="sx-panel" id="itemsSection"><h3>Configured Integrations</h3>
        <div class="ihi-tools">
            <input type="search" id="itemSearch" class="ihi-input" placeholder="Search integration or provider…" autocomplete="off">
            <select id="itemStatus" class="ihi-input" style="min-width:140px"><option value="">All statuses</option></select>
            <span class="ihi-muted" id="itemCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="itemTable">
                <thead><tr>
                    <th class="ihi-sort" data-key="0">INTEGRATION</th>
                    <th class="ihi-sort" data-key="1">PROVIDER</th>
                    <th class="ihi-sort" data-key="2">STATUS</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($items as $i)
                    <tr class="ihi-row">
                        <td><strong>{{ $i->name }}</strong><div class="sx-muted">{{ $i->category }}</div></td>
                        <td>{{ $i->provider }}</td>
                        <td><span class="ihi-pill {{ $statusClass($i->status) }}">{{ str_replace('_', ' ', $i->status) }}</span></td>
                        <td><a class="sx-btn" href="{{ route('integration-hub.show', $i->id) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="sx-empty">No integrations configured.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="ihi-muted ihi-hide" id="itemNoMatch" style="margin-top:10px">No integrations match your search.</p>
    </section>

    {{-- RECENT ACTIVITY --}}
    <section class="sx-panel" id="logsSection"><h3>Recent Integration Activity</h3>
        <div class="ihi-tools">
            <input type="search" id="logSearch" class="ihi-input" placeholder="Search event or message…" autocomplete="off">
            <span class="ihi-muted" id="logCount"></span>
        </div>
        <div style="overflow:auto">
            <table class="sx-table" id="logTable">
                <thead><tr>
                    <th class="ihi-sort" data-key="0">EVENT</th>
                    <th class="ihi-sort" data-key="1">MESSAGE</th>
                    <th class="ihi-sort" data-key="2">DATE</th>
                </tr></thead>
                <tbody>
                @forelse($logs as $l)
                    <tr class="ihi-row">
                        <td>{{ $l->event }}</td>
                        <td>{{ $l->message }}</td>
                        <td>{{ $l->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="sx-empty">No integration activity.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="ihi-muted ihi-hide" id="logNoMatch" style="margin-top:10px">No activity matches your search.</p>
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
            const art = $('.ihi-3d', card);
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

    /* ---- Reusable table helper: search, optional status filter, sort ---- */
    function setupTable(o) {
        const table = $(o.table); if (!table) return;
        const tbody = $('tbody', table);
        const rows = () => $$('.ihi-row', tbody);
        const tools = $(o.search).closest('.ihi-tools');
        if (rows().length === 0) { tools.style.display = 'none'; return; }

        const statusEl = o.status ? $(o.status) : null;
        if (statusEl) {
            const seen = {};
            rows().forEach(r => seen[r.children[o.statusCol].textContent.trim()] = 1);
            Object.keys(seen).sort().forEach(function (v) {
                const opt = document.createElement('option'); opt.value = v; opt.textContent = v; statusEl.appendChild(opt);
            });
            statusEl.addEventListener('change', apply);
        }

        function apply() {
            const q = $(o.search).value.trim().toLowerCase(), st = statusEl ? statusEl.value : ''; let shown = 0;
            rows().forEach(function (r) {
                const ok = (!q || r.textContent.toLowerCase().includes(q)) && (!st || r.children[o.statusCol].textContent.trim() === st);
                r.style.display = ok ? '' : 'none'; if (ok) shown++;
            });
            $(o.count).textContent = shown + ' of ' + rows().length + ' shown';
            $(o.noMatch).style.display = shown === 0 ? 'block' : 'none';
        }
        $(o.search).addEventListener('input', apply);
        apply();

        let key = null, dir = 1;
        $$('.ihi-sort', table).forEach(function (th) {
            th.addEventListener('click', function () {
                const k = parseInt(th.getAttribute('data-key'), 10);
                dir = (key === k) ? -dir : 1; key = k;
                $$('.ihi-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
                th.classList.add(dir === 1 ? 'asc' : 'desc');
                const list = rows();
                list.sort((a, b) => a.children[k].textContent.trim().localeCompare(b.children[k].textContent.trim(), undefined, { numeric: true }) * dir);
                list.forEach(r => tbody.appendChild(r));
            });
        });
    }

    setupTable({ table: '#itemTable', search: '#itemSearch', status: '#itemStatus', statusCol: 2, count: '#itemCount', noMatch: '#itemNoMatch' });
    setupTable({ table: '#logTable',  search: '#logSearch',  status: null,          statusCol: 0, count: '#logCount',  noMatch: '#logNoMatch' });
});
</script>
@endsection