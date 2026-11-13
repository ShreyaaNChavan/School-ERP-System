@extends('layouts.schoolnexa')

@section('title', $meta[0] ?? 'Reports')

@section('content')

@php
    $fmtDate = function ($v) {
        if (!$v) return '—';
        try { return \Carbon\Carbon::parse($v)->format('d M Y'); } catch (\Throwable $e) { return (string) $v; }
    };
    $rawDate = function ($v) {
        if (!$v) return '';
        try { return \Carbon\Carbon::parse($v)->format('Y-m-d'); } catch (\Throwable $e) { return (string) $v; }
    };

    /* green = active/current, blue = upcoming/planned, red = inactive/closed/archived */
    $statusClass = function ($s) {
        $s = strtolower(trim((string) $s));
        if (in_array($s, ['active','current','open','ongoing','1'])) return 'good';
        if (in_array($s, ['upcoming','planned','draft','scheduled'])) return 'info';
        if (in_array($s, ['inactive','deactive','deactivated','closed','archived','completed','ended','expired','0'])) return 'bad';
        return '';
    };

    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'kpi'         => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'attendance'  => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'academic'    => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M9 7h7M9 11h5"/>',
        'performance' => '<path d="M3 17l6-6 4 4 8-8"/><path d="M15 7h6v6"/>',
        'classes'     => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>',
        'grades'      => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'fees'        => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/>',
        'teachers'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'custom'      => '<path d="M4 6h9M19 6h1M4 12h3M13 12h7M4 18h11M21 18h-1"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'print'       => '<path d="M6 9V3h12v6"/><rect x="3" y="9" width="18" height="9" rx="2"/><path d="M7 14h10v7H7z"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['kpi']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'kpi'         => 'bar-chart',
        'attendance'  => 'calendar',
        'academic'    => 'open-book',
        'performance' => 'rocket',
        'classes'     => 'school',
        'grades'      => 'medal',
        'fees'        => 'money-bag',
        'teachers'    => 'conference-call',
        'custom'      => 'settings',
        'print'       => 'print',
        'hero'        => 'bar-chart',
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
            ? '<img class="rpi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.rpi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="rpi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="rpi-3d '.$size.($slug ? '' : ' fb').'"><span class="rpi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    /* KPI cards: pick an icon from the label, otherwise cycle through the set */
    $kpiLook = function (string $label, int $i) {
        $l = strtolower($label);
        $map = [
            ['attend', 'attendance', 'success'], ['year', 'academic', 'sky'], ['exam', 'academic', 'purple'],
            ['student', 'classes', 'teal'], ['class', 'classes', 'teal'], ['fee', 'fees', 'warning'],
            ['invoice', 'fees', 'warning'], ['teacher', 'teachers', 'purple'], ['staff', 'teachers', 'purple'],
            ['grade', 'grades', 'success'], ['perform', 'performance', 'sky'],
        ];
        foreach ($map as [$kw, $ic, $tone]) if (str_contains($l, $kw)) return [$ic, $tone];
        $cycle = [['kpi','sky'], ['performance','success'], ['academic','purple'], ['classes','warning']];
        return $cycle[$i % count($cycle)];
    };

    $reportLinks = [
        ['Attendance Reports',  'attendance',  'Daily student attendance',        'attendance',  'success'],
        ['Academic Reports',    'academic',    'Examination summaries',           'academic',    'sky'],
        ['Student Performance', 'performance', 'Student-wise results',            'performance', 'purple'],
        ['Class Analytics',     'classes',     'Class and section enrollment',    'classes',     'teal'],
        ['Grade Reports',       'grades',      'Grade distribution',              'grades',       'success'],
        ['Fee Collection',      'fees',        'Invoices and outstanding fees',   'fees',        'warning'],
        ['Teacher Performance', 'teachers',    'Staff attendance',                'teachers',    'purple'],
        ['Custom Reports',      'custom',      'Build a custom report',           'custom',      'navy'],
    ];
@endphp

<div class="sn-page rpt">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .rpt{font-family:Inter,system-ui,sans-serif;color:#25313c}
    .rpt *{box-sizing:border-box}

    /* ================= INTERACTIVE 3D ICONS ================= */
    .rpi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .rpi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .rpi-3d.sm{width:48px;height:48px}
    .rpi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .rpi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:rpiFloat 4.2s ease-in-out infinite}
    .rpi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .rpi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .rpi-ico{display:none}
    .rpi-3d.fb .rpi-3d-img{display:none}
    .rpi-3d.fb .rpi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .rpi-3d.pop .rpi-3d-in{animation:rpiPop .55s cubic-bezier(.3,1.6,.5,1),rpiFloat 4.2s ease-in-out infinite .55s}
    @keyframes rpiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes rpiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.rpi-3d-in{animation:none!important}.rpi-3d{transition:none}}

    /* ================= BANNER ================= */
    .rpi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px}
    .rpi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .rpi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .rpi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .rpi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .rpi-welcome-right{display:flex;align-items:center;gap:26px}
    .rpi-date{text-align:right;font-size:13px;color:#64748B}
    .rpi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= HEADER / PANELS ================= */
    .rh{display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:18px}
    .rh h1{font-size:22px;color:#123b5d;margin:0 0 5px}
    .muted{color:#64748b;font-size:13px}
    .btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#123b5d;text-decoration:none;font-size:13px;font-weight:650;cursor:pointer}
    .btn:hover{border-color:#94A3B8;background:#F8FAFC}
    .primary{background:#123b5d;color:#fff;border-color:#123b5d}
    .primary:hover{background:#1B527A;color:#fff}
    .panel{background:#fff;border:1px solid #dbe3ec;border-radius:14px;padding:18px;margin-bottom:16px}
    .panel h3{font-size:15px;color:#123b5d;margin:0 0 15px}
    .note{margin:10px 0;font-size:12px;color:#64748b}

    /* ================= KPI CARDS (no links) ================= */
    .rpi-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .rpi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #dbe3ec;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .rpi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .rpi-kpi span.lbl{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748b}
    .rpi-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123b5d;margin-top:3px}

    /* ================= REPORT TILES ================= */
    .rpi-tiles{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
    .rpi-tile{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 10px 12px;border:1px solid #CBD5E1;border-radius:14px;background:#F8FAFC;
        text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
    .rpi-tile:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
    .rpi-tile strong{font-size:13px;font-weight:650;color:#123B5D}
    .rpi-tile small{font-size:11px;color:#64748B;line-height:1.4}

    /* ================= TABLE ================= */
    .rpi-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 0 14px}
    .rpi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:220px;outline:none;background:#fff;font-family:inherit}
    .rpi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .tblwrap{overflow:auto}
    .tbl{width:100%;border-collapse:collapse;font-size:13px}
    .tbl th{background:#f8fafc;color:#64748b;font-size:11px;text-align:left;padding:12px;border-bottom:1px solid #e2e8f0;white-space:nowrap}
    .tbl td{padding:13px 12px;border-bottom:1px solid #e2e8f0;white-space:nowrap}
    .empty{text-align:center!important;padding:30px!important;color:#64748b}
    .rpi-row{transition:background .2s}
    .rpi-row:hover{background:rgba(23,107,135,.05)}
    .rpi-sort{cursor:pointer;user-select:none}
    .rpi-sort:after{content:' \2195';opacity:.35;font-size:11px}
    .rpi-sort.asc:after{content:' \2191';opacity:1}
    .rpi-sort.desc:after{content:' \2193';opacity:1}
    .rpi-pill{display:inline-flex;align-items:center;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:700;background:#F1F5F9;color:#64748B}
    .rpi-pill.good{background:#DCFCE7;color:#166534}
    .rpi-pill.bad{background:#FEE2E2;color:#DC2626}
    .rpi-pill.info{background:#EAF2FF;color:#2563EB}
    .pager{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-top:15px}
    .rpi-hide{display:none}

    @@media(max-width:1050px){.rpi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.rpi-tiles{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:650px){.rh{flex-direction:column;align-items:flex-start}.rpi-kpis,.rpi-tiles{grid-template-columns:1fr}.rpi-welcome h1{font-size:22px}}
    @@media print{.actions,.rpi-tools,.pager,.rpi-tiles-panel,.rpi-welcome{display:none!important}}
</style>

{{-- BANNER --}}
<div class="rpi-welcome" data-tilt>
    <div>
        <div class="rpi-eyebrow">{{ $greet }} <i></i> Reports &amp; Analytics</div>
        <h1>Welcome Back, Reports Team!</h1>
        <p>Track attendance, academics, fees and performance from one place.</p>
    </div>
    <div class="rpi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="rpi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="rh">
    <div><h1>{{ $meta[0] }}</h1><div class="muted">{{ $meta[1] }}</div></div>
    <!-- <div class="actions">
        <button class="btn" onclick="window.print()" type="button">Print</button>
        <a class="btn" href="{{ url('/caspa/reports') }}">Back to Reports</a>
    </div> -->
</div>

@if(session('success'))<div class="panel" style="color:#166534">{{ session('success') }}</div>@endif
@if($errors->any())<div class="panel" style="color:#DC2626">{{ $errors->first() }}</div>@endif

{{-- KPI CARDS --}}
<div class="rpi-kpis">
    @foreach($data['cards'] as $i => $c)
        @php
            [$kic, $ktone] = $kpiLook((string) $c['label'], $i);
            $isNum = is_numeric(str_replace(',', '', (string) $c['value']));
        @endphp
        <div class="rpi-kpi" data-tilt>
            {!! $tile($kic, $ktone) !!}
            <div>
                <span class="lbl">{{ $c['label'] }}</span>
                <strong @if($isNum) data-count="{{ (int) str_replace(',', '', (string) $c['value']) }}" @endif>{{ $c['value'] }}</strong>
            </div>
        </div>
    @endforeach
</div>

{{-- REPORT TILES --}}
<div class="panel rpi-tiles-panel">
    <h3>Reports</h3>
    <div class="rpi-tiles">
        @foreach($reportLinks as [$title, $slug, $desc, $ic, $tone])
            <a class="rpi-tile" data-tilt href="{{ url('/caspa/reports/' . $slug) }}">
                {!! $tile($ic, $tone, 'sm') !!}
                <strong>{{ $title }}</strong>
                <small>{{ $desc }}</small>
            </a>
        @endforeach
    </div>
</div>

{{-- ACADEMIC YEAR OVERVIEW --}}
<div class="panel">
    <h3>Academic Year Overview</h3>

    <div class="rpi-tools">
        <input type="search" id="rowSearch" class="rpi-input" placeholder="Search academic year or status…" autocomplete="off">
        <select id="rowStatus" class="rpi-input" style="min-width:150px"><option value="">All statuses</option></select>
        <span class="muted" id="rowCount"></span>
    </div>

    <div class="tblwrap">
        <table class="tbl" id="yearTable">
            <thead><tr>
                <th class="rpi-sort" data-key="0">Academic Year</th>
                <th class="rpi-sort" data-key="1">Start Date</th>
                <th class="rpi-sort" data-key="2">End Date</th>
                <th class="rpi-sort" data-key="3">Status</th>
            </tr></thead>
            <tbody>
            @forelse($data['rows'] as $row)
                <tr class="rpi-row" data-sort-1="{{ $rawDate($row->start_date ?? null) }}" data-sort-2="{{ $rawDate($row->end_date ?? null) }}">
                    <td><strong>{{ $row->name ?? '—' }}</strong></td>
                    <td>{{ $fmtDate($row->start_date ?? null) }}</td>
                    <td>{{ $fmtDate($row->end_date ?? null) }}</td>
                    <td><span class="rpi-pill {{ $statusClass($row->status ?? '') }}">{{ ucfirst($row->status ?? '—') }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="empty">No academic years found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <p class="muted rpi-hide" id="noMatch" style="margin-top:10px">No academic years match your search.</p>

    <div class="pager">
        <span class="btn">Showing {{ $data['rows']->firstItem() ?? 0 }}–{{ $data['rows']->lastItem() ?? 0 }} of {{ $data['rows']->total() }}</span>
        <div>{{ $data['rows']->links() }}</div>
    </div>
</div>

@foreach(($data['notes'] ?? []) as $note)<div class="note">{{ $note }}</div>@endforeach

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.rpi-3d', card);
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

    /* ---- Table: search, status filter, sort (applies to the rows on this page) ---- */
    const table = $('#yearTable'), tbody = $('tbody', table);
    const rows = () => $$('.rpi-row', tbody);
    if (rows().length === 0) { $('.rpi-tools').style.display = 'none'; return; }

    const seen = {};
    rows().forEach(r => seen[r.children[3].textContent.trim()] = 1);
    Object.keys(seen).sort().forEach(function (s) {
        const o = document.createElement('option'); o.value = s; o.textContent = s; $('#rowStatus').appendChild(o);
    });

    function applyFilter() {
        const q = $('#rowSearch').value.trim().toLowerCase(), st = $('#rowStatus').value; let shown = 0;
        rows().forEach(function (r) {
            const ok = (!q || r.textContent.toLowerCase().includes(q)) && (!st || r.children[3].textContent.trim() === st);
            r.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        $('#rowCount').textContent = shown + ' of ' + rows().length + ' shown';
        $('#noMatch').style.display = shown === 0 ? 'block' : 'none';
    }
    $('#rowSearch').addEventListener('input', applyFilter);
    $('#rowStatus').addEventListener('change', applyFilter);
    applyFilter();

    let sortKey = null, sortDir = 1;
    $$('.rpi-sort', table).forEach(function (th) {
        th.addEventListener('click', function () {
            const key = parseInt(th.getAttribute('data-key'), 10);
            sortDir = (sortKey === key) ? -sortDir : 1; sortKey = key;
            $$('.rpi-sort', table).forEach(x => x.classList.remove('asc', 'desc'));
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