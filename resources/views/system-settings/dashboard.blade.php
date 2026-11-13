@extends('layouts.schoolnexa')

@section('title', 'System Settings')

@section('content')

@php
    /* ---------- Fallback SVG icons (used only if a 3D picture cannot load) ---------- */
    $paths = [
        'system'        => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1"/>',
        'school'        => '<path d="M3 21h18"/><path d="M5 21V10l7-5 7 5v11"/><path d="M12 5V2l3 1"/><path d="M9.5 21v-5h5v5"/>',
        'campus'        => '<path d="M12 22s7-6.2 7-12a7 7 0 0 0-14 0c0 5.8 7 12 7 12Z"/><circle cx="12" cy="10" r="2.5"/>',
        'updated'       => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/>',
        'general'       => '<path d="M4 6h9M19 6h1M4 12h3M13 12h7M4 18h11M21 18h-1"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'academic'      => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M9 7h7M9 11h5"/>',
        'attendance'    => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'notifications' => '<path d="M6 9a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 21h4"/>',
        'security'      => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'branding'      => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
    ];
    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name] ?? $paths['system']).'</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       Change a name here to use a different picture: https://icons8.com/icons/3d-fluency */
    $slugs = [
        'system'        => 'settings',
        'school'        => 'school',
        'campus'        => 'marker',
        'updated'       => 'calendar',
        'general'       => 'settings',
        'academic'      => 'open-book',
        'attendance'    => 'calendar',
        'notifications' => 'bell',
        'security'      => 'shield',
        'branding'      => 'paint-palette',
        'hero'          => 'settings',
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
            ? '<img class="sxi-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.sxi-3d\').classList.add(\'fb\')">'
            : '';
        $fb = '<span class="sxi-ico" style="--c1:'.$a.';--c2:'.$b.';--c3:'.$c.'">'.$icon($name, $px).'</span>';
        return '<span class="sxi-3d '.$size.($slug ? '' : ' fb').'"><span class="sxi-3d-in" style="animation-delay:'.$delay.'">'.$img.$fb.'</span></span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $updatedText = $updated ? \Illuminate\Support\Carbon::parse($updated)->format('d M Y') : '—';

    $sections = [
        ['general',       'General',                 'School profile, defaults and general preferences', 'general',       'sky'],
        ['academic',      'Academic',                'Academic years, terms and grading defaults',        'academic',      'purple'],
        ['attendance',    'Attendance',              'Attendance rules, shifts and cut-off times',        'attendance',    'success'],
        ['notifications', 'Notifications',           'Email, SMS and in-app alert preferences',           'notifications', 'warning'],
        ['security',      'Security',                'Passwords, sessions and access policies',           'security',      'teal'],
        ['branding',      'Branding & Localization', 'Logo, colours, language and regional formats',      'branding',      'navy'],
    ];
@endphp

@include('system-settings._style')

<div class="sx-page">

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    /* ================= INTERACTIVE 3D ICONS ================= */
    .sxi-3d{position:relative;display:inline-block;flex-shrink:0;margin-bottom:8px;width:64px;height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;will-change:transform;cursor:pointer}
    .sxi-3d.xs{width:42px;height:42px;margin-bottom:6px}
    .sxi-3d.sm{width:48px;height:48px}
    .sxi-3d.lg{width:92px;height:92px;margin-bottom:10px}
    .sxi-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:sxiFloat 4.2s ease-in-out infinite}
    .sxi-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;filter:drop-shadow(0 10px 8px rgba(18,59,93,.28))}
    .sxi-3d::after{content:'';position:absolute;left:20%;right:20%;bottom:-7px;height:9px;border-radius:50%;background:rgba(18,59,93,.22);filter:blur(5px)}
    .sxi-ico{display:none}
    .sxi-3d.fb .sxi-3d-img{display:none}
    .sxi-3d.fb .sxi-ico{--c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
        background:linear-gradient(150deg,var(--c1),var(--c2) 55%,var(--c3));
        box-shadow:0 5px 0 var(--c3),inset 0 2px 0 rgba(255,255,255,.55),inset 0 -3px 6px rgba(0,0,0,.16)}
    .sxi-3d.pop .sxi-3d-in{animation:sxiPop .55s cubic-bezier(.3,1.6,.5,1),sxiFloat 4.2s ease-in-out infinite .55s}
    @keyframes sxiFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}
    @keyframes sxiPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
    @@media (prefers-reduced-motion:reduce){.sxi-3d-in{animation:none!important}.sxi-3d{transition:none}}

    /* ================= BANNER ================= */
    .sxi-welcome{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;padding:26px 30px;border-radius:18px;
        border:1px solid #DCE8F7;background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);margin-bottom:16px;font-family:'Inter',system-ui,sans-serif}
    .sxi-eyebrow{display:flex;align-items:center;gap:8px;font-size:11px;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#176B87;margin-bottom:6px}
    .sxi-eyebrow i{width:4px;height:4px;border-radius:50%;background:#176B87;display:inline-block}
    .sxi-welcome h1{margin:0;font-size:28px;font-weight:800;color:#123B5D;letter-spacing:-.02em}
    .sxi-welcome p{margin:6px 0 0;font-size:14px;color:#64748B}
    .sxi-welcome-right{display:flex;align-items:center;gap:26px}
    .sxi-date{text-align:right;font-size:13px;color:#64748B}
    .sxi-date strong{display:block;font-size:24px;font-weight:800;color:#123B5D;margin-top:2px}

    /* ================= KPI CARDS (no links) ================= */
    .sxi-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
    .sxi-kpi{display:flex;align-items:center;gap:16px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:18px;min-width:0;cursor:default;transition:transform .15s,box-shadow .15s}
    .sxi-kpi:hover{transform:translateY(-2px);box-shadow:0 10px 26px rgba(18,59,93,.12)}
    .sxi-kpi small{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#64748B}
    .sxi-kpi strong{display:block;font-size:28px;line-height:1.15;color:#123B5D;margin-top:3px}
    .sxi-kpi strong.date{font-size:18px;margin-top:8px}

    /* ================= SECTIONS ================= */
    .sxi-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin:6px 0 14px}
    .sxi-head h3{margin:0;font-size:16px;font-weight:750;color:#123B5D}
    .sxi-head p{margin:4px 0 0;font-size:12px;color:#64748B}
    .sxi-input{padding:8px 12px;border:1px solid #d9dee7;border-radius:8px;font-size:13px;min-width:240px;outline:none;background:#fff;font-family:inherit}
    .sxi-input:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.14)}
    .sxi-sections{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .sxi-sec{display:flex;align-items:center;gap:16px;padding:20px;border:1px solid #CBD5E1;border-radius:16px;background:#fff;text-decoration:none;color:inherit;transition:transform .15s,box-shadow .15s,border-color .15s}
    .sxi-sec:hover{transform:translateY(-3px);border-color:#176B87;box-shadow:0 12px 28px rgba(23,107,135,.14)}
    .sxi-sec h3{margin:0;font-size:16px;font-weight:750;color:#123B5D}
    .sxi-sec p{margin:4px 0 0;font-size:12px;line-height:1.5;color:#64748B}
    .sxi-sec .go{display:block;margin-top:10px;color:#176B87;font-size:13px;font-weight:700}
    .sxi-empty{display:none;grid-column:1/-1;padding:30px 15px;text-align:center;color:#64748B;border:1px dashed #CBD5E1;border-radius:12px}

    @@media(max-width:1100px){.sxi-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.sxi-sections{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @@media(max-width:640px){.sxi-sections{grid-template-columns:1fr}.sxi-welcome h1{font-size:22px}.sxi-kpis{grid-template-columns:1fr}}
</style>

{{-- BANNER --}}
<div class="sxi-welcome" data-tilt>
    <div>
        <div class="sxi-eyebrow">{{ $greet }} <i></i> System Administration</div>
        <h1>Welcome Back, Admin!</h1>
        <p>Configure school-wide preferences and operational defaults.</p>
    </div>
    <div class="sxi-welcome-right">
        {!! $tile('hero', 'sky', 'lg') !!}
        <div class="sxi-date">{{ now()->format('l, d M Y') }}<strong>{{ now()->format('h:i A') }}</strong></div>
    </div>
</div>

{{-- HEADER --}}
<div class="sx-head">
    <div><h1 class="sx-title">System Settings</h1><p class="sx-sub">Configure school-wide preferences and operational defaults.</p></div>
    <div class="sx-actions"><a class="sx-btn" href="{{ route('dashboard') }}">Back to Dashboard</a></div>
</div>

{{-- KPI CARDS --}}
<div class="sxi-kpis">
    <div class="sxi-kpi" data-tilt>
        {!! $tile('system', 'sky') !!}
        <div><small>System Settings</small><strong data-count="{{ (int) $count }}">{{ $count }}</strong></div>
    </div>
    <div class="sxi-kpi" data-tilt>
        {!! $tile('school', 'success') !!}
        <div><small>School Settings</small><strong data-count="{{ (int) $schoolCount }}">{{ $schoolCount }}</strong></div>
    </div>
    <div class="sxi-kpi" data-tilt>
        {!! $tile('campus', 'purple') !!}
        <div><small>Campus Settings</small><strong data-count="{{ (int) $campusCount }}">{{ $campusCount }}</strong></div>
    </div>
    <div class="sxi-kpi" data-tilt>
        {!! $tile('updated', 'warning') !!}
        <div><small>Last Updated</small><strong class="date">{{ $updatedText }}</strong></div>
    </div>
</div>

{{-- SETTINGS SECTIONS --}}
<div class="sxi-head">
    <div><h3>Settings Sections</h3><p>Choose a section to configure</p></div>
    <input type="search" id="secSearch" class="sxi-input" placeholder="Search settings sections…" autocomplete="off">
</div>

<div class="sxi-sections" id="secGrid">
    @foreach($sections as [$key, $label, $desc, $ic, $tone])
        <a class="sxi-sec" data-tilt data-search="{{ strtolower($label . ' ' . $desc) }}" href="{{ route('system-settings.section', $key) }}">
            {!! $tile($ic, $tone) !!}
            <div>
                <h3>{{ $label }}</h3>
                <p>{{ $desc }}</p>
                <span class="go">Configure →</span>
            </div>
        </a>
    @endforeach
    <div class="sxi-empty" id="secNoMatch">No settings sections match your search.</div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = (s, c) => (c || document).querySelector(s);
    const $$ = (s, c) => Array.from((c || document).querySelectorAll(s));

    /* ---- 3D icon tilt: follows the mouse, grows on hover, pops on click ---- */
    if (!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches)) {
        $$('[data-tilt]').forEach(function (card) {
            const art = $('.sxi-3d', card);
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

    /* ---- Section search ---- */
    const search = $('#secSearch'), cards = $$('.sxi-sec'), none = $('#secNoMatch');
    search.addEventListener('input', function () {
        const q = search.value.trim().toLowerCase(); let shown = 0;
        cards.forEach(function (c) {
            const ok = !q || c.dataset.search.includes(q);
            c.style.display = ok ? '' : 'none'; if (ok) shown++;
        });
        none.style.display = shown === 0 ? 'block' : 'none';
    });
});
</script>
@endsection