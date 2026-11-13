@extends('layouts.schoolnexa')

@section('title', 'Communication Center')

@section('content')

@php
    /* green = published/sent, blue = scheduled, grey = draft, red = archived/cancelled/failed/inactive */
    $statusClass = function ($s) {
        $s = strtolower(trim((string) $s));
        if (in_array($s, ['published','sent','active','delivered'])) return 'good';
        if (in_array($s, ['scheduled','pending','queued'])) return 'info';
        if (in_array($s, ['archived','cancelled','failed','inactive','deactive','deactivated','expired'])) return 'bad';
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
        'total'     => '<path d="M3 11v2a1 1 0 0 0 1 1h3l6 4V6L7 10H4a1 1 0 0 0-1 1Z"/><path d="M16 9a4 4 0 0 1 0 6"/>',
        'published' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'scheduled' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'drafts'    => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        'unread'    => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'chats'     => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.4A8 8 0 1 1 21 12Z"/>',
        'create'    => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'send'      => '<path d="M22 2 11 13"/><path d="m22 2-7 20-4-9-9-4Z"/>',
        'templates' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
        'all'       => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'center'    => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.4A8 8 0 1 1 21 12Z"/><path d="M8 11h8M8 14h5"/>',
        'reports'   => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'back'      => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'     => 'megaphone',
        'published' => 'checkmark',
        'scheduled' => 'alarm-clock',
        'drafts'    => 'document',
        'unread'    => 'email',
        'chats'     => 'chat',
        'create'    => 'plus-math',
        'send'      => 'paper-plane',
        'templates' => 'template',
        'all'       => 'clipboard',
        'center'    => 'speech-bubble',
        'reports'   => 'bar-chart',
        'back'      => 'home',
        'hero'      => 'megaphone',
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
            ? '<img class="cmi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.cmi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="cmi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="cmi-3d '.$size.($slug ? '' : ' fb').'"><span class="cmi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpis = [
        ['Total announcements',        $stats['total'],         'total',     'sky'],
        ['Published notices',          $stats['published'],     'published', 'success'],
        ['Scheduled messages',         $stats['scheduled'],     'scheduled', 'warning'],
        ['Drafts',                     $stats['drafts'],        'drafts',    'teal'],
        ['Unread read-receipt rows',   $stats['unread'],        'unread',    'purple'],
        ['Conversations',              $stats['conversations'], 'chats',     'navy'],
    ];

    $quick = [
        ['Create Announcement', 'Publish a school notice',                'create',    'success', route('communication.create')],
        ['Send Message',        'Start a direct or group conversation',   'send',      'sky',     route('communication.compose')],
        ['Manage Templates',    'Reusable communication formats',         'templates', 'purple',  route('communication.templates')],
        ['All Announcements',   'Search, edit and archive notices',       'all',       'teal',    route('communication.index')],
        ['Message Center',      'Open conversation threads',              'center',    'warning', route('communication.messages')],
        ['Delivery Reports',    'View in-app delivery records',           'reports',   'navy',    route('communication.deliveries')],
    ];
@endphp

<div class="sn-page">@include('shared.module-style')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .cmi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .cmi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .cmi-3d.sm{width:48px;height:48px}
    .cmi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .cmi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:cmiFloat 4.2s ease-in-out infinite}
    .cmi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .cmi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .cmi-ico{display:none}
    .cmi-3d.fb .cmi-3d-img{display:none}
    .cmi-3d.fb .cmi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .cmi-3d.pop .cmi-3d-in{animation:cmiPop .55s cubic-bezier(.3,1.6,.5,1),cmiFloat 4.2s ease-in-out infinite .55s}
    @keyframes cmiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes cmiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.cmi-3d-in{animation:none!important}.cmi-3d{transition:none}}

    /* ================= BANNER ================= */
    .cmi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .cmi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .cmi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .cmi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .cmi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .cmi-welcome-right{display:flex;align-items:center;gap:26px}
    .cmi-date{text-align:right;font-size:13px;color:#64748B}
    .cmi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= HEADER / PANELS ================= */
    .cc-head{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:18px}
    .cc-head h1{font-size:22px;color:#123B5D;margin:0 0 5px}
    .cc-head p{font-size:13px;color:#64748B;margin:0}
    .cc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font-size:13px;font-weight:650;cursor:pointer}
    .cc-btn:hover{border-color:#94A3B8;background:#F8FAFC}
    .cc-primary{background:#123B5D;color:#fff;border-color:#123B5D}
    .cc-primary:hover{background:#1B527A;color:#fff}
    .cc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:20px}
    .cc-panel h3{font-size:15px;color:#123B5D;margin:0 0 15px}

    /* ================= KPI CARDS (no links) ================= */
    .cmi-kpis{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
    .cmi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;cursor:default;transition:transform .15s,box-shadow .15s}
    .cmi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .cmi-kpi b{display:block;font-size:28px;line-height:1.15;color:#123B5D}
    .cmi-kpi span.lbl{font-size:12px;color:#64748B}

    /* ================= QUICK ACTIONS ================= */
    .cmi-quick-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
    .cmi-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
        background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .cmi-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .cmi-quick strong{font-size:12px;font-weight:650;color:#123B5D}
    .cmi-quick small{font-size:10px;color:#64748B;line-height:1.4}

    /* ================= TABLE ================= */
    .cmi-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .cmi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:220px;outline:none;background:#fff}
    .cmi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .cmi-muted{font-size:12px;color:#64748B}
    .cc-table{width:100%;border-collapse:collapse}
    .cc-table th{background:#F8FAFC;color:#64748B;font-size:11px;text-align:left;padding:13px;border-bottom:1px solid #CBD5E1;white-space:nowrap}
    .cc-table td{font-size:13px;padding:14px 13px;border-bottom:1px solid #E2E8F0}
    .cmi-row{transition:background .2s}
    .cmi-row:hover{background:rgba(23,107,135,.05)}
    .cmi-sort{cursor:pointer;user-select:none}
    .cmi-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .cmi-sort.asc:after{content:' \2191';opacity:1}
    .cmi-sort.desc:after{content:' \2193';opacity:1}
    .cmi-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B;white-space:nowrap}
    .cmi-pill.good{background:#DCFCE7;color:#166534}
    .cmi-pill.bad{background:#FEE2E2;color:#DC2626}
    .cmi-pill.info{background:#EAF2FF;color:#2563EB}
    .cmi-pill.warn{background:#FEF3C7;color:#B45309}
    .cmi-hide{display:none}

    @@media(max-width:1200px){.cmi-quick-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
    @@media(max-width:800px){.cmi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.cmi-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:520px){.cmi-welcome h1{font-size:22px}.cmi-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="cmi-welcome" data-tilt>
    <div>
        <div class="cmi-eyebrow">{{ $greet }} <i></i> Communication Team</div>
        <h1>Welcome Back, Communication!</h1>
        <p>Here's an overview of your announcements, messages and school communication.</p>
    </div>
    <div class="cmi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="cmi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="cc-head">
    <div><h1>Communication Center</h1><p>Centralized announcements, messaging and school communication</p></div>
    <a class="cc-btn cc-primary" href="{{ route('communication.create') }}">+ Create Announcement</a>
</div>

@if(session('success'))
    <div class="cc-panel" style="color:#166534;margin-bottom:16px">{{ session('success') }}</div>
@endif

{{-- KPI CARDS --}}
<div class="cmi-kpis">
    @foreach($kpis as [$label, $value, $ic, $tone])
        <div class="cmi-kpi" data-tilt>
            {!! $tile($ic, $tone) !!}
            <div><b data-count="{{ (int) $value }}">{{ $value }}</b><span class="lbl">{{ $label }}</span></div>
        </div>
    @endforeach
</div>

{{-- QUICK ACTIONS --}}
<div class="cc-panel" style="margin-top:18px">
    <h3>Quick Actions</h3>
    <div class="cmi-quick-grid">
        @foreach($quick as [$label, $sub, $ic, $tone, $url])
            <a class="cmi-quick" data-tilt href="{{ $url }}">
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $label }}</strong>
                <small>{{ $sub }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- RECENT ANNOUNCEMENTS --}}
<div class="cc-panel" style="margin-top:18px" id="recentSection">
    <h3>Recently created announcements</h3>

    <div class="cmi-tools">
        <input type="search" id="rowSearch" class="cmi-input" placeholder="Search title, priority or status…" autocomplete="off">
        <select id="rowStatus" class="cmi-input" style="min-width:150px"><option value="">All statuses</option></select>
        <select id="rowPriority" class="cmi-input" style="min-width:150px"><option value="">All priorities</option></select>
        <span class="cmi-muted" id="rowCount"></span>
    </div>

    <div style="overflow-x:auto">
        <table class="cc-table" id="annTable">
            <thead><tr>
                <th class="cmi-sort" data-key="0">TITLE</th>
                <th class="cmi-sort" data-key="1">PRIORITY</th>
                <th class="cmi-sort" data-key="2">STATUS</th>
                <th class="cmi-sort" data-key="3">CREATED</th>
                <th>ACTIONS</th>
            </tr></thead>
            <tbody>
            @forelse($recent as $row)
                <tr class="cmi-row" data-sort-3="{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('Y-m-d') : '' }}">
                    <td><strong>{{ $row->title }}</strong></td>
                    <td><span class="cmi-pill {{ $priorityClass($row->priority) }}">{{ ucfirst($row->priority) }}</span></td>
                    <td><span class="cmi-pill {{ $statusClass($row->status) }}">{{ ucfirst($row->status) }}</span></td>
                    <td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '—' }}</td>
                    <td><a class="cc-btn" href="{{ route('communication.show', $row->id) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="5">No announcements created yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="cmi-muted cmi-hide" id="noMatch" style="margin-top:10px">No announcements match your search.</p>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.cmi-3d', card);
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
    const table = $('#annTable'), tbody = $('tbody', table);
    const rows = () => $$('.cmi-row', tbody);
    if (rows().length === 0) { $('.cmi-tools').style.display = 'none'; return; }

    function fillOptions(selectEl, colIndex) {
        const seen = {};
        rows().forEach(r => seen[r.children[colIndex].textContent.trim()] = 1);
        Object.keys(seen).sort().forEach(function (v) {
            const o = document.createElement('option'); o.value = v; o.textContent = v; selectEl.appendChild(o);
        });
    }
    fillOptions($('#rowPriority'), 1);
    fillOptions($('#rowStatus'), 2);

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value, pr = $('#rowPriority').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q))
                    && (!st || r.children[2].textContent.trim() === st)
                    && (!pr || r.children[1].textContent.trim() === pr);
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
    $$('.cmi-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10);
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.cmi-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
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