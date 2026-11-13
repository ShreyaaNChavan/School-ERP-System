@extends('layouts.schoolnexa')

@section('title', 'Alumni Management')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'total'  => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9v6"/>',
        'active' => '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>',
        'all'    => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'add'    => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
        'find'   => '<circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>',
        'back'   => '<path d="M3 11 12 3l9 8"/><path d="M5 10v10h14V10"/><path d="M10 20v-6h4v6"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['total']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'total'  => 'graduation-cap',
        'active' => 'checkmark',
        'all'    => 'clipboard',
        'add'    => 'plus-math',
        'find'   => 'search',
        'back'   => 'home',
        'hero'   => 'conference-call',
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
            ? '<img class="ali-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.ali-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="ali-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="ali-3d '.$size.($slug ? '' : ' fb').'"><span class="ali-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $quick = [
        ['View All',     'all',    'sky',     route('alumni.index')],
        ['Add Alumni',   'add',    'success', route('alumni.create')],
        ['Find Alumni',  'find',   'purple',  '#alumniSearchPanel'],
        ['Dashboard',    'back',   'teal',    url('/dashboard')],
    ];
@endphp

<div class="sn-page">
    @include('shared.module-style')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        /* ================= INTERACTIVE 3D ICONS ================= */
        .ali-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
            transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
            transition:transform .18s ease-out;will-change:transform;cursor:pointer}
        .ali-3d.xs{width:42px;height:42px;margin-bottom:6px}
        .ali-3d.sm{width:48px;height:48px}
        .ali-3d.lg{width:92px;height:92px;margin-bottom:10px}
        .ali-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:aliFloat 4.2s ease-in-out infinite}
        .ali-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
        .ali-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
        .ali-ico{display:none}
        .ali-3d.fb .ali-3d-img{display:none}
        .ali-3d.fb .ali-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
            background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
            box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
        .ali-3d.pop .ali-3d-in{animation:aliPop .55s cubic-bezier(.3,1.6,.5,1),aliFloat 4.2s ease-in-out infinite .55s}
        @keyframes aliFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
        @keyframes aliPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
        @@media (prefers-reduced-motion:reduce){.ali-3d-in{animation:none!important}.ali-3d{transition:none}}

        /* ================= BANNER ================= */
        .ali-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
            border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
        .ali-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
        .ali-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
        .ali-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
        .ali-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
        .ali-welcome-right{display:flex;align-items:center;gap:26px}
        .ali-date{text-align:right;font-size:13px;color:#64748B}
        .ali-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

        /* ================= KPI CARDS (no links) ================= */
        .ali-kpi{display:flex;align-items:center;gap:16px;cursor:default;transition:transform .15s,box-shadow .15s}
        .ali-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
        .ali-kpi-body{min-width:0;display:flex;flex-direction:column;gap:2px}
        .ali-kpi-body b{line-height:1.15}
        .ali-muted{font-size:12px;color:#64748B}

        /* ================= QUICK ACTIONS ================= */
        .ali-quick-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-top:12px}
        .ali-quick{display:flex;flex-direction:column;align-items:center;gap:6px;padding:16px 8px 12px;border:1px solid #CBD5E1;border-radius:14px;
            background:#F8FAFC;text-align:center;text-decoration:none;cursor:pointer;transition:.15s}
        .ali-quick:hover{background:#fff;border-color:#176B87;box-shadow:0 8px 18px rgba(23,107,135,.14)}
        .ali-quick strong{font-size:12px;font-weight:600;color:#123B5D}
        @@media (max-width:900px){.ali-quick-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @@media (max-width:520px){.ali-welcome h1{font-size:22px}}

        /* ================= SEARCH + DIRECTORY ================= */
        .alumni-search-panel{display:flex;gap:10px;align-items:center;padding:12px;border:1px solid #CBD5E1;background:#FFFFFF;border-radius:13px;margin:18px 0 20px}
        .alumni-search-panel input,.alumni-search-panel select{height:42px;border:1px solid #CBD5E1;border-radius:8px;padding:0 12px;outline:none;color:#25313C;background:#FFFFFF;font-size:13px;min-width:0}
        .alumni-search-panel input{flex:1}
        .alumni-search-panel input:focus,.alumni-search-panel select:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.10)}
        .alumni-directory-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:14px}
        .alumni-directory-heading h3{margin:0;color:#123B5D;font-size:16px;font-weight:700}
        .alumni-count{font-size:12px;color:#64748B}
        .alumni-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
        .alumni-card{min-width:0;padding:20px 14px 15px;border:1px solid #DCE4EC;border-radius:13px;background:#FFFFFF;text-align:center;
            transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
        .alumni-card:hover{transform:translateY(-3px);border-color:#7DB5D1;box-shadow:0 9px 22px rgba(18,59,93,.09)}
        .alumni-avatar{width:76px;height:76px;margin:0 auto 12px;display:flex;justify-content:center;align-items:center;border-radius:50%;border:4px solid #FFFFFF;
            outline:1px solid #DCE5ED;background:linear-gradient(145deg,#DDF1F7,#C6E1ED);color:#123B5D;font-size:23px;font-weight:750;letter-spacing:.5px}
        .alumni-card:nth-child(4n+2) .alumni-avatar{background:linear-gradient(145deg,#EAE4FA,#DCD2F3);color:#5B3FA3}
        .alumni-card:nth-child(4n+3) .alumni-avatar{background:linear-gradient(145deg,#DDF5E9,#C5EAD7);color:#166534}
        .alumni-card:nth-child(4n+4) .alumni-avatar{background:linear-gradient(145deg,#FFF0D8,#F8DEB0);color:#92400E}
        .alumni-card h4{margin:0;color:#123B5D;font-size:14px;font-weight:700;overflow-wrap:anywhere}
        .alumni-designation{min-height:18px;margin-top:5px;color:#64748B;font-size:11px}
        .alumni-company{margin-top:7px;color:#176B87;font-size:12px;font-weight:650;overflow-wrap:anywhere}
        .alumni-location{margin-top:10px;padding-top:9px;border-top:1px solid #EDF1F5;color:#64748B;font-size:11px}
        .alumni-meta{display:flex;justify-content:center;align-items:center;gap:6px;flex-wrap:wrap;margin-top:9px}
        .alumni-status{display:inline-flex;padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;background:#DCFCE7;color:#166534}
        .alumni-status.inactive{background:#FEE2E2;color:#DC2626}
        .alumni-actions{display:flex;justify-content:center;gap:7px;margin-top:13px}
        .alumni-actions .sn-btn{min-height:34px;padding:0 12px;font-size:11px}
        .alumni-empty{grid-column:1/-1;padding:35px 15px;text-align:center;color:#64748B;border:1px dashed #CBD5E1;border-radius:12px}
        @@media(max-width:1100px){.alumni-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
        @@media(max-width:760px){.alumni-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @@media(max-width:480px){
            .alumni-grid{grid-template-columns:1fr}
            .alumni-search-panel{flex-direction:column;align-items:stretch}
            .alumni-search-panel input,.alumni-search-panel select{width:100%}
        }
    </style>

    {{-- BANNER --}}
    <div class="ali-welcome" data-tilt>
        <div>
            <div class="ali-eyebrow">{{ $greet }} <i></i> Alumni Network</div>
            <h1>Welcome Back, Alumni Team!</h1>
            <p>Celebrating achievements and staying connected beyond graduation.</p>
        </div>
        <div class="ali-welcome-right">
            {!! $tile('hero', 'sky', 'lg') !!}
            <div class="ali-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
        </div>
    </div>

    {{-- Page heading --}}
    <div class="sn-head">
        <div>
            <h1>Alumni Management</h1>
            <div class="sn-sub">Connect, discover and manage your alumni network</div>
        </div>
        <div class="sn-actions">
            <a class="sn-btn sn-primary" href="{{ route('alumni.create') }}">+ Add Alumni</a>
        </div>
    </div>

    {{-- KPI CARDS --}}
    <div class="sn-grid">
        <div class="sn-kpi ali-kpi" data-tilt>
            {!! $tile('total', 'sky') !!}
            <div class="ali-kpi-body"><b data-count="{{ (int) $total }}">{{ number_format($total) }}</b><span>Total Alumni Profiles</span></div>
        </div>
        <div class="sn-kpi ali-kpi" data-tilt>
            {!! $tile('active', 'success') !!}
            <div class="ali-kpi-body"><b data-count="{{ (int) $active }}">{{ number_format($active) }}</b><span>Active Alumni</span><span class="ali-muted" id="activeRate"></span></div>
        </div>
    </div>

    {{-- QUICK ACTIONS --}}
    <div class="sn-panel" style="margin-top:18px">
        <h3 style="margin:0;color:#123B5D">Quick Actions</h3>
        <div class="ali-quick-grid">
            @foreach($quick as [$label, $ic, $tone, $url])
                <a class="ali-quick" data-tilt href="{{ $url }}" @if(\Illuminate\Support\Str::startsWith($url, '#')) data-scroll="{{ $url }}" @endif>
                    {!! $tile($ic, $tone, 'sm') !!}
                    <strong>{{ $label }}</strong>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Search and filters --}}
    <div class="alumni-search-panel" id="alumniSearchPanel">
        <input type="search" id="alumniSearch" placeholder="Search alumni by name, company or city..." autocomplete="off">

        <select id="alumniYear">
            <option value="">All graduation years</option>
            @foreach($recent->pluck('graduation_year')->filter()->unique()->sortDesc() as $year)
                <option value="{{ $year }}">{{ $year }}</option>
            @endforeach
        </select>

        <select id="alumniStatus">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>

        <button type="button" class="sn-btn" id="resetAlumniFilters">Reset</button>
    </div>

    {{-- Alumni directory --}}
    <div class="sn-panel" id="directorySection">
        <div class="alumni-directory-heading">
            <h3>Discover Our Alumni</h3>
            <span class="alumni-count" id="alumniCount">{{ $recent->count() }} profiles displayed</span>
        </div>

        <div class="alumni-grid" id="alumniGrid">
            @forelse($recent as $row)
                @php
                    $name = trim($row->full_name ?? 'Alumni');
                    $parts = preg_split('/\s+/', $name);
                    $initials = collect($parts)->filter()->take(2)
                        ->map(fn($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');

                    $status = strtolower($row->status ?? 'active');
                    $company = $row->current_organization ?? 'Organization not specified';
                    $designation = $row->designation ?? 'Alumni Member';
                    $city = $row->city ?? 'Location not specified';
                @endphp

                <div class="alumni-card"
                     data-name="{{ strtolower($name) }}"
                     data-company="{{ strtolower($company) }}"
                     data-city="{{ strtolower($city) }}"
                     data-year="{{ $row->graduation_year ?? '' }}"
                     data-status="{{ $status }}">

                    <div class="alumni-avatar">{{ $initials ?: 'AL' }}</div>
                    <h4>{{ $name }}</h4>
                    <div class="alumni-designation">{{ $designation }}</div>
                    <div class="alumni-company">{{ $company }}</div>
                    <div class="alumni-location"><span aria-hidden="true">⌖</span> {{ $city }}</div>

                    <div class="alumni-meta">
                        @if($row->graduation_year)
                            <span style="font-size:10px;color:#64748B">Class of {{ $row->graduation_year }}</span>
                        @endif
                        <span class="alumni-status {{ $status === 'active' ? '' : 'inactive' }}">{{ ucfirst($status) }}</span>
                    </div>

                    <div class="alumni-actions">
                        <a href="{{ route('alumni.show', $row->id) }}" class="sn-btn">View</a>
                        <a href="{{ route('alumni.edit', $row->id) }}" class="sn-btn">Edit</a>
                    </div>
                </div>
            @empty
                <div class="alumni-empty">
                    <h3 style="color:#123B5D;margin:0 0 7px">No alumni profiles yet</h3>
                    <p style="font-size:13px;margin:0 0 14px">Start building your alumni community by adding the first profile.</p>
                    <a href="{{ route('alumni.create') }}" class="sn-btn sn-primary">+ Add First Alumni</a>
                </div>
            @endforelse
        </div>

        <div id="alumniNoResults" class="alumni-empty" style="display:none;margin-top:14px">
            No alumni match your search or selected filters.
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = card.querySelector('.ali-3d');
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
            const s = document.querySelector(a.getAttribute('data-scroll')); if (!s) return;
            s.scrollIntoView({ behavior: 'smooth', block: 'center' });
            s.style.transition = 'box-shadow .3s'; s.style.boxShadow = '0 0 0 3px rgba(23,107,135,.35)';
            setTimeout(function () { s.style.boxShadow = ''; }, 1200);
            const input = document.getElementById('alumniSearch'); if (input) input.focus({ preventScroll: true });
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

    const totalAlumni = {{ (int) $total }}, activeAlumni = {{ (int) $active }};
    if (totalAlumni > 0) document.getElementById('activeRate').textContent = Math.round(activeAlumni / totalAlumni * 100) + '% of all profiles';

    /* ---- Search + filters ---- */
    const search = document.getElementById('alumniSearch');
    const year = document.getElementById('alumniYear');
    const status = document.getElementById('alumniStatus');
    const reset = document.getElementById('resetAlumniFilters');
    const cards = $$('.alumni-card');
    const count = document.getElementById('alumniCount');
    const noResults = document.getElementById('alumniNoResults');

    function filterAlumni() {
        const query = search.value.trim().toLowerCase();
        const selectedYear = year.value;
        const selectedStatus = status.value;
        let visible = 0;

        cards.forEach(card => {
            const matchesQuery = !query || card.dataset.name.includes(query) || card.dataset.company.includes(query) || card.dataset.city.includes(query);
            const matchesYear = !selectedYear || card.dataset.year === selectedYear;
            const matchesStatus = !selectedStatus || card.dataset.status === selectedStatus;
            const show = matchesQuery && matchesYear && matchesStatus;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        count.textContent = visible + ' of ' + cards.length + ' profiles displayed';
        noResults.style.display = cards.length && visible === 0 ? 'block' : 'none';
    }

    search.addEventListener('input', filterAlumni);
    year.addEventListener('change', filterAlumni);
    status.addEventListener('change', filterAlumni);
    reset.addEventListener('click', function () { search.value = ''; year.value = ''; status.value = ''; filterAlumni(); });
    filterAlumni();
});
</script>
@endsection