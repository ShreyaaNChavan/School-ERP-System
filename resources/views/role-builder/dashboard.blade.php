@extends('layouts.schoolnexa')

@section('title', 'Role Builder')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'  => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/>',
        'active' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'system' => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'custom' => '<path d="M4 6h9M19 6h1M4 12h3M13 12h7M4 18h11M21 18h-1"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'create' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'all'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'users'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'recent' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'back'   => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'  => 'key',
        'active' => 'checkmark',
        'system' => 'shield',
        'custom' => 'settings',
        'create' => 'plus-math',
        'all'    => 'clipboard',
        'users'  => 'conference-call',
        'recent' => 'hourglass',
        'back'   => 'home',
        'hero'   => 'key',
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
            ? '<img class="rbi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.rbi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="rbi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="rbi-3d '.$size.($slug ? '' : ' fb').'"><span class="rbi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpis = [
        ['Total Roles',  $totalRoles,  'All roles in SchoolNexa',   'total',  'sky'],
        ['Active Roles', $activeRoles, 'Currently in use',          'active', 'success'],
        ['System Roles', $systemRoles, 'Built-in, protected roles', 'system', 'purple'],
        ['Custom Roles', $customRoles, 'Created by your team',      'custom', 'warning'],
    ];

    $quick = [
        ['Create Role',     'Build a new role',            'create', 'success', route('role-builder.create')],
        ['All Roles',       'Browse and manage roles',     'all',    'sky',     route('role-builder.index')],
        ['Recent Roles',    'Recently updated roles',      'recent', 'warning', '#recentSection'],
        ['Role Usage',      'Most assigned roles',         'users',  'purple',  '#usageSection'],
        ['Dashboard',       'Back to the main dashboard',  'back',   'teal',    route('dashboard')],
    ];

    $maxUsage = max(1, (int) collect($roleUsage)->max('assigned_count'));
@endphp

<div class="rb-page">
@include('role-builder._style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .rbi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .rbi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .rbi-3d.sm{width:48px;height:48px}
    .rbi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .rbi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:rbiFloat 4.2s ease-in-out infinite}
    .rbi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .rbi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .rbi-ico{display:none}
    .rbi-3d.fb .rbi-3d-img{display:none}
    .rbi-3d.fb .rbi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .rbi-3d.pop .rbi-3d-in{animation:rbiPop .55s cubic-bezier(.3,1.6,.5,1),rbiFloat 4.2s ease-in-out infinite .55s}
    @keyframes rbiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes rbiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.rbi-3d-in{animation:none!important}.rbi-3d{transition:none}}

    /* ================= BANNER ================= */
    .rbi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .rbi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .rbi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .rbi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .rbi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .rbi-welcome-right{display:flex;align-items:center;gap:26px}
    .rbi-date{text-align:right;font-size:13px;color:#64748B}
    .rbi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .rbi-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .rbi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .rbi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .rbi-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .rbi-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin:2px 0}
    .rbi-kpi span.foot{font-size:11px;color:#94A3B8}

    /* ================= QUICK ACTIONS ================= */
    .rbi-quick-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-top:12px}
    .rbi-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .rbi-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .rbi-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .rbi-quick small{font-size:10px;color:#64748B;line-height:1.4}
    .rbi-box{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px;margin-bottom:16px}
    .rbi-box h3{margin:0;font-size:15px;color:#123B5D}

    /* ================= PILLS / TABLE TOOLS ================= */
    .rbi-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700}
    .rbi-pill.good{background:#DCFCE7;color:#166534}
    .rbi-pill.bad{background:#FEE2E2;color:#DC2626}
    .rbi-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .rbi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:200px;outline:none;background:#fff}
    .rbi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .rbi-muted{font-size:12px;color:#64748B}
    .rbi-sort{cursor:pointer;user-select:none;white-space:nowrap}
    .rbi-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .rbi-sort.asc:after{content:' \2191';opacity:1}
    .rbi-sort.desc:after{content:' \2193';opacity:1}
    .rbi-row{transition:background .2s}
    .rbi-row:hover{background:rgba(23,107,135,.05)}
    .rbi-hide{display:none}

    /* usage bars */
    .rbi-usage{display:flex;align-items:center;gap:10px;min-width:150px}
    .rbi-track{flex:1;height:8px;border-radius:999px;background:#E2E8F0;overflow:hidden}
    .rbi-track i{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,#3B82F6,#176B87)}
    .rbi-usage b{min-width:24px;text-align:right;color:#123B5D}

    @@media(max-width:1200px){.rbi-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:900px){.rbi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.rbi-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.rbi-welcome h1{font-size:22px}.rbi-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="rbi-welcome" data-tilt>
    <div>
        <div class="rbi-eyebrow">{{ $greet }} <i></i> Access Control</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Create, organize and manage role-based access across SchoolNexa.</p>
    </div>
    <div class="rbi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="rbi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="rb-head">
    <div><h1 class="rb-title">Role Builder</h1><p class="rb-sub">Create, organize and manage role-based access across SchoolNexa.</p></div>
    <div class="rb-actions"><a class="rb-btn primary" href="{{ route('role-builder.create') }}">+ Create Role</a></div>
</div>

@include('role-builder._flash')

{{-- KPI CARDS --}}
<div class="rbi-kpis">
    @foreach($kpis as [$label, $value, $foot, $ic, $tone])
        <div class="rbi-kpi" data-tilt>
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
<div class="rbi-box">
    <h3>Quick Actions</h3>
    <div class="rbi-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="rbi-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

<div class="rb-two">
    {{-- RECENTLY UPDATED ROLES --}}
    <section class="rb-panel" id="recentSection"><h3>Recently Updated Roles</h3>

        <div class="rbi-tools">
            <input type="search" id="rowSearch" class="rbi-input" placeholder="Search role, code or scope…" autocomplete="off">
            <select id="rowStatus" class="rbi-input" style="min-width:130px"><option value="">All statuses</option></select>
            <span class="rbi-muted" id="rowCount"></span>
        </div>

        <div class="rb-table-wrap">
            <table class="rb-table" id="roleTable">
                <thead><tr>
                    <th class="rbi-sort" data-key="0">ROLE</th>
                    <th class="rbi-sort" data-key="1">SCOPE</th>
                    <th class="rbi-sort" data-key="2">STATUS</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($recentRoles as $role)
                    <tr class="rbi-row">
                        <td><strong>{{ $role->name }}</strong><div class="rb-muted">{{ $role->code }}</div></td>
                        <td>{{ ucfirst($role->scope_type) }}</td>
                        <td><span class="rbi-pill {{ $role->is_active ? 'good' : 'bad' }}">{{ $role->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td><a class="rb-btn" href="{{ route('role-builder.show', $role->id) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="rb-empty">No roles found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <p class="rbi-muted rbi-hide" id="noMatch" style="margin-top:10px">No roles match your search.</p>
    </section>

    {{-- MOST ASSIGNED ROLES --}}
    <section class="rb-panel" id="usageSection"><h3>Most Assigned Roles</h3>
        <table class="rb-table">
            <thead><tr><th>ROLE</th><th>USERS</th></tr></thead>
            <tbody>
            @forelse($roleUsage as $usage)
                <tr class="rbi-row">
                    <td><strong>{{ $usage->name }}</strong><div class="rb-muted">{{ $usage->code }}</div></td>
                    <td>
                        <div class="rbi-usage">
                            <div class="rbi-track"><i style="width:{{ max(4, round(((int) $usage->assigned_count / $maxUsage) * 100)) }}%"></i></div>
                            <b>{{ $usage->assigned_count }}</b>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="2" class="rb-empty">No role assignments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px"><strong class="rb-muted">Active assigned users:</strong> <span data-count="{{ (int) $assignedUsers }}">{{ $assignedUsers }}</span></div>
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
            const art = $('.rbi-3d', card);
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

    /* ---- Recent roles table: search, status filter, sort ---- */
    const table = $('#roleTable'), tbody = $('tbody', table);
    const rows = () => $$('.rbi-row', tbody);
    if (rows().length === 0) { $('.rbi-tools').style.display = 'none'; return; }

    const seen = {};
    rows().forEach(r => seen[r.children[2].textContent.trim()] = 1);
    Object.keys(seen).sort().forEach(function (v) {
        const o = document.createElement('option'); o.value = v; o.textContent = v; $('#rowStatus').appendChild(o);
    });

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q)) && (!st || r.children[2].textContent.trim() === st);
            r.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        $('#rowCount').textContent = shown + ' of ' + rows().length + ' shown';
        $('#noMatch').style.display = shown === 0 ? 'block' : 'none';
    }
    $('#rowSearch').addEventListener('input', applyFilter);
    $('#rowStatus').addEventListener('change', applyFilter);
    applyFilter();

    let sortKey = null, sortDir = 1;
    $$('.rbi-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10);
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.rbi-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
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