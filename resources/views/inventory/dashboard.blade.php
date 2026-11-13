@extends('layouts.schoolnexa')

@section('title', 'Inventory & Asset Dashboard')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D image cannot load) ---------- */
    $paths = [
        'inventory'  => '<path d="M21 8 12 3 3 8v8l9 5 9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
        'wallet'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M16 15h2"/><path d="M6 6V5a2 2 0 0 1 2-2h8"/>',
        'alert'      => '<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17v.01"/>',
        'vendors'    => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'assets'     => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h2m4 0h2M8 11h2m4 0h2"/>',
        'orders'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        'layers'     => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'plus'       => '<path d="M12 5v14M5 12h14"/>',
        'arrow-right'=> '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ---------- */
    $slugs = [
        'inventory' => 'box',
        'wallet'    => 'money-bag',
        'alert'     => 'high-priority',
        'vendors'   => 'conference-call',
        'assets'    => 'organization',
        'orders'    => 'document',
        'layers'    => 'folder',
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
        ['Inventory Items',       number_format($itemCount),                 'inventory', 'sky',     'Items in stock register'],
        ['Stock Value',           '₹' . number_format($stockValue, 2),       'wallet',    'success', 'Total value on hand'],
        ['Low Stock Items',       number_format($lowStock),                  'alert',     'danger',  'At or below reorder level'],
        ['Vendors',               number_format($vendorCount),               'vendors',   'purple',  'Registered suppliers'],
        ['Registered Assets',     number_format($assetCount),                'assets',    'teal',    'Tracked in asset register'],
        ['Open Purchase Orders',  number_format($pendingOrders),             'orders',    'warning', 'Awaiting completion'],
    ];

    $quick = [
        ['Inventory Items', 'inventory.items.index',          'inventory', 'sky'],
        ['Add Item',        'inventory.items.create',         'inventory', 'success'],
        ['Categories',      'inventory.categories.index',     'layers',    'purple'],
        ['Vendors',         'inventory.vendors.index',        'vendors',   'warning'],
        ['Asset Register',  'inventory.assets.index',         'assets',    'teal'],
        ['Purchase Orders', 'inventory.purchase-orders.index','orders',    'navy'],
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
    .sa-pill.danger{ background:#FEE2E2; color:var(--danger); }
    .sa-pill.success{ background:#DCFCE7; color:var(--success); }
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
    .sa-btn{ display:inline-flex; align-items:center; gap:6px; padding:11px 18px; border-radius:12px; font-size:13px; font-weight:700; background:var(--teal); color:#fff !important; box-shadow:0 4px 0 #0E4A5E; transition:.15s; }
    .sa-btn:hover{ transform:translateY(-1px); background:#145a73; }

    /* ---------- Alerts ---------- */
    .sa-alert{ padding:12px 16px; border-radius:12px; font-size:13px; font-weight:600; background:#DCFCE7; color:#0F7A36; border:1px solid #BBF7D0; }
    .sa-alert.error{ background:#FEE2E2; color:#991B1B; border-color:#FECACA; font-weight:500; }
    .sa-alert ul{ margin:0; padding-left:18px; }

    /* ---------- KPIs ---------- */
    .sa-kpis{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .sa-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
    .sa-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
    .sa-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .sa-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
    .sa-kpi-note{ font-size:12px; color:var(--muted); }

    /* ---------- Main grid ---------- */
    .sa-main{ display:grid; grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); gap:16px; align-items:start; }

    /* Quick actions */
    .sa-quick-grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
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

    /* ---------- Responsive ---------- */
    @media (max-width:1100px){ .sa-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:900px){ .sa-main{ grid-template-columns:1fr; } }
    @media (max-width:800px){
        .sa-welcome{ padding:20px; }
        .sa-welcome h1{ font-size:22px; }
        .sa-welcome-right{ width:100%; justify-content:space-between; }
    }
    @media (max-width:520px){ .sa-kpis{ grid-template-columns:1fr; } }
</style>

<div class="sa">

    {{-- WELCOME --}}
    <div class="sa-welcome" data-tilt>
        <div>
            <div class="sa-eyebrow">{{ $greet }} <i></i> Inventory &amp; Assets</div>
            <h1>Inventory &amp; Asset Dashboard</h1>
            <p>Stock, procurement and asset overview.</p>
        </div>
        <div class="sa-welcome-right">
           <!-- // //<a class="sa-btn" href="{{ route('inventory.items.create') }}">{!! $icon('plus', 16) !!} Add Item</a> -->
            {!! $tile('inventory', 'sky', 'lg') !!}
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
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- KPI CARDS --}}
    <div class="sa-kpis">
        @foreach($cards as [$label, $value, $ic, $tone, $note])
            <div class="sa-kpi" data-tilt>
                {!! $tile($ic, $tone) !!}
                <div>
                    <div class="sa-kpi-label">{{ $label }}</div>
                    <div class="sa-kpi-value">{{ $value }}</div>
                    <div class="sa-kpi-note">{{ $note }}</div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MAIN COLUMNS --}}
    <div class="sa-main">

        <div class="sa-panel">
            <div class="sa-head">
                <h3 class="sa-title">Low Stock Alerts</h3>
                <a href="{{ route('inventory.items.index', ['stock' => 'low']) }}" class="sa-link">View all {!! $icon('arrow-right', 14) !!}</a>
            </div>
            <table class="sa-table">
                <thead>
                    <tr><th>Item</th><th>Available</th><th>Reorder Level</th></tr>
                </thead>
                <tbody>
                    @forelse($lowStockItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td><span class="sa-pill danger">{{ $item->quantity_on_hand }} {{ $item->unit }}</span></td>
                            <td>{{ $item->reorder_level }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="sa-empty">No low-stock items.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

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