@extends('layouts.schoolnexa')

@section('title', 'Super Admin Dashboard')

@section('content')

@php
    use Illuminate\Support\Str;

    /* ---------- Fallback SVG icons (used only if a 3D image cannot load) ---------- */
    $paths = [
        'building'      => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h2m4 0h2M8 11h2m4 0h2"/>',
        'school'        => '<path d="M3 21h18"/><path d="M5 21V10l7-5 7 5v11"/><path d="M12 5V2l3 1"/><path d="M9.5 21v-5h5v5"/>',
        'students'      => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9v6"/>',
        'teachers'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c0-3.5 2.9-6 6.5-6s6.5 2.5 6.5 6"/><rect x="15.5" y="4" width="6" height="7" rx="1"/>',
        'staff'         => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'user'          => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        'admissions'    => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M19 8v6M16 11h6"/>',
        'attendance'    => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'wallet'        => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M16 15h2"/><path d="M6 6V5a2 2 0 0 1 2-2h8"/>',
        'map'           => '<path d="m3 6 6-3 6 3 6-3v15l-6 3-6-3-6 3zM9 3v15m6-12v15"/>',
        'transport'     => '<rect x="4" y="3" width="16" height="15" rx="2.5"/><path d="M4 11h16"/><circle cx="8" cy="15" r=".8"/><circle cx="16" cy="15" r=".8"/><path d="M6 18v2M18 18v2"/>',
        'hostel'        => '<path d="M3 20V6"/><path d="M3 16h18v4"/><path d="M21 16v-4a3 3 0 0 0-3-3h-7v7"/><circle cx="7" cy="12" r="2"/>',
        'library'       => '<rect x="4" y="4" width="5" height="16" rx=".5"/><rect x="10" y="7" width="4" height="13" rx=".5"/><path d="m15.5 6 4-1 2 14-4 1Z"/>',
        'events'        => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m12 13 1.2 2.4 2.6.4-1.9 1.8.5 2.6-2.4-1.3-2.4 1.3.5-2.6-1.9-1.8 2.6-.4Z"/>',
        'key'           => '<circle cx="8" cy="15" r="4"/><path d="m11 12 9-9M16 7l3 3M14 9l2 2"/>',
        'reports'       => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'emergency'     => '<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17v.01"/>',
        'safety'        => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'ai'            => '<path d="m12 3 1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8Z"/><path d="m19 15 .8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8Z"/>',
        'layers'        => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'curriculum'    => '<path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v17H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M4 21.5V4.5"/><path d="M9 7h7M9 11h5"/>',
        'exams'         => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
        'finance'       => '<path d="M7 5h10M7 9h10M7 5c5 0 7 1.5 7 4s-2 4-7 4l7 7"/>',
        'inventory'     => '<path d="M21 8 12 3 3 8v8l9 5 9-5Z"/><path d="m3 8 9 5 9-5M12 13v8"/>',
        'settings'      => '<path d="M4 6h9M19 6h1M4 12h3M13 12h7M4 18h11M21 18h-1"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/>',
        'notifications' => '<path d="M6 9a6 6 0 0 1 12 0c0 7 3 8 3 8H3s3-1 3-8"/><path d="M10 21h4"/>',
        'meetings'      => '<rect x="2" y="6" width="14" height="12" rx="2"/><path d="m16 10 6-3v10l-6-3"/>',
        'chevron-left'  => '<path d="m15 6-6 6 6 6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ----------
       If you want a different picture for any icon, change its name here.
       Browse names at https://icons8.com/icons/3d-fluency                         */
    $slugs = [
        'building'      => 'organization',
        'school'        => 'school',
        'students'      => 'graduation-cap',
        'staff'         => 'conference-call',
        'user'          => 'group',
        'admissions'    => 'books',
        'attendance'    => 'calendar',
        'wallet'        => 'money-bag',
        'finance'       => 'money-bag',
        'map'           => 'marker',
        'transport'     => 'bus',
        'hostel'        => 'bed',
        'library'       => 'book',
        'events'        => 'star',
        'key'           => 'key',
        'reports'       => 'bar-chart',
        'emergency'     => 'high-priority',
        'ai'            => 'bot',
        'settings'      => 'settings',
        'notifications' => 'bell',
        'safety'        => 'shield',
        'curriculum'    => 'open-book',
        'inventory'     => 'box',
    ];

    /* Fallback colours: [highlight, base, shadow] */
    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'danger'  => ['#FA8585', '#DC2626', '#991B1B'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    /* Interactive 3D icon: sizes xs | sm | md | lg */
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

    // Pick a distinct icon for each module by its name
    $moduleIcon = function (array $module) {
        if (!empty($module['icon'])) return $module['icon'];
        $n = Str::lower($module['name'] ?? '');
        $map = [
            'organization' => 'building', 'school' => 'school', 'campus' => 'map', 'student' => 'students',
            'teacher' => 'teachers', 'staff' => 'staff', 'employee' => 'staff', 'user' => 'user', 'role' => 'key',
            'permission' => 'key', 'academic' => 'curriculum', 'curricul' => 'curriculum', 'attend' => 'attendance',
            'exam' => 'exams', 'result' => 'exams', 'admission' => 'admissions', 'fee' => 'finance',
            'financ' => 'finance', 'hr' => 'staff', 'payroll' => 'wallet', 'transport' => 'transport',
            'hostel' => 'hostel', 'librar' => 'library', 'inventor' => 'inventory', 'event' => 'events',
            'meeting' => 'meetings', 'safety' => 'safety', 'complian' => 'safety', 'report' => 'reports',
            'analytic' => 'reports', 'notif' => 'notifications', 'setting' => 'settings', 'ai' => 'ai',
        ];
        foreach ($map as $kw => $ic) { if (Str::contains($n, $kw)) return $ic; }
        return 'layers';
    };

    $tones = ['sky', 'success', 'purple', 'warning', 'teal', 'navy'];

    /* ---------- Data ---------- */
    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $cards = [
        ['Organizations',      $stats['organizations'], 'building',   'sky',     'Registered organizations'],
        ['Total Schools',      $stats['schools'],       'school',     'success', 'Schools on the platform'],
        ['Total Students',     $stats['students'],      'students',   'purple',  'Across all schools'],
        ['Total Staff',        $stats['staff'],         'staff',      'warning', 'Staff profiles'],
        ['Platform Users',     $stats['users'],         'user',       'teal',    'Registered accounts'],
        ['Admissions',         $stats['admissions'],    'admissions', 'success', 'Admission enquiries'],
        ['Attendance Records', $stats['attendance'],    'attendance', 'purple',  'Recorded attendance'],
        ['Fee Transactions',   $stats['payments'],      'wallet',     'warning', 'Recorded payments'],
    ];

    $resources = [
        ['Registered Campuses', $stats['campuses'],     'map',       'sky'],
        ['Transport Vehicles',  $stats['vehicles'],     'transport', 'success'],
        ['Hostel Rooms',        $stats['hostel_rooms'], 'hostel',    'purple'],
        ['Library Books',       $stats['books'],        'library',   'warning'],
        ['Platform Events',     $stats['events'],       'events',    'teal'],
    ];

    $quick = [
        ['Organizations', 'building',   'sky'],
        ['Schools',       'school',     'success'],
        ['Campuses',      'map',        'teal'],
        ['Create User',   'admissions', 'purple'],
        ['Manage Roles',  'key',        'warning'],
        ['Reports',       'reports',    'navy'],
    ];

    $alerts = [
        ['Safeguarding Cases',   'Recorded platform cases',  $stats['safety_cases'], 'emergency', 'danger'],
        ['School Registrations', 'Registered schools',       $stats['schools'],      'school',    'success'],
        ['Platform Accounts',    'Registered user accounts', $stats['users'],        'user',      'teal'],
    ];

    $orgMax      = max(1, (int) $organizationSchoolData->max('total'));
    $maxUsers    = max(1, (int) collect($userGrowth)->max('total'));
    $firstDay    = now()->startOfMonth()->dayOfWeek;
    $daysInMonth = now()->daysInMonth;
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

    /* ================= INTERACTIVE 3D ICONS ================= */
    .sa-3d{
        position:relative; display:inline-block; flex-shrink:0; margin-bottom:8px;
        width:64px; height:64px;
        transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
        transition:transform .18s ease-out;
        will-change:transform; cursor:pointer;
    }
    .sa-3d.xs{ width:42px; height:42px; margin-bottom:6px; }
    .sa-3d.sm{ width:48px; height:48px; }
    .sa-3d.lg{ width:92px; height:92px; margin-bottom:10px; }

    .sa-3d-in{ display:grid; place-items:center; width:100%; height:100%; animation:saFloat 4.2s ease-in-out infinite; }
    .sa-3d-img{ width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none;
                filter:drop-shadow(0 10px 8px rgba(18,59,93,.28)); }

    /* soft ground shadow under the icon */
    .sa-3d::after{
        content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%;
        background:rgba(18,59,93,.22); filter:blur(5px); transition:all .18s ease-out;
    }
    .sa-3d.xs::after{ bottom:-5px; height:6px; }

    /* fallback (flat glossy tile) only appears if the picture fails to load */
    .sa-ico{ display:none; }
    .sa-3d.fb .sa-3d-img{ display:none; }
    .sa-3d.fb .sa-ico{
        --c1:#7DB5FF; --c2:#3B82F6; --c3:#1D4ED8;
        position:relative; display:grid; place-items:center; width:82%; height:82%; border-radius:30%; color:#fff;
        background:linear-gradient(150deg,var(--c1) 0%,var(--c2) 55%,var(--c3) 100%);
        box-shadow:0 5px 0 var(--c3), inset 0 2px 0 rgba(255,255,255,.55), inset 0 -3px 6px rgba(0,0,0,.16);
    }
    .sa-3d.fb .sa-ico svg{ filter:drop-shadow(0 2px 2px rgba(0,0,0,.28)); }

    /* click "pop" */
    .sa-3d.pop .sa-3d-in{ animation:saPop .55s cubic-bezier(.3,1.6,.5,1), saFloat 4.2s ease-in-out infinite .55s; }

    @keyframes saFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-5px); } }
    @keyframes saPop{ 0%{ transform:scale(1); } 35%{ transform:scale(1.28) rotate(-6deg); } 70%{ transform:scale(.94) rotate(3deg); } 100%{ transform:scale(1); } }
    @media (prefers-reduced-motion: reduce){ .sa-3d-in{ animation:none !important; } .sa-3d{ transition:none; } }

    /* ---------- Panels ---------- */
    .sa-panel{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; }
    .sa-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; }
    .sa-title{ font-size:15px; font-weight:700; color:var(--navy); }
    .sa-link{ display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:var(--teal); text-decoration:none; white-space:nowrap; }
    .sa-pill{ display:inline-flex; align-items:center; justify-content:center; min-width:26px; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; background:#FEF3C7; color:var(--warning); }
    .sa-pill.danger{ background:#FEE2E2; color:var(--danger); }
    .sa-pill.teal{ background:#D5EBF2; color:var(--teal); }
    .sa-pill.success{ background:#DCFCE7; color:var(--success); }
    .sa-muted{ font-size:12px; color:var(--muted); }

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

    /* ---------- KPIs ---------- */
    .sa-kpis{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
    .sa-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
    .sa-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
    .sa-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
    .sa-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
    .sa-kpi-note{ font-size:12px; color:var(--muted); }

    /* ---------- Main grid ---------- */
    .sa-main{ display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.25fr) minmax(0,1fr); gap:16px; align-items:start; }
    .sa-col{ display:flex; flex-direction:column; gap:16px; min-width:0; }

    /* Calendar */
    .sa-cal-top{ display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; color:var(--muted); }
    .sa-cal-month{ font-size:14px; font-weight:700; color:var(--navy); }
    .sa-cal-grid{ display:grid; grid-template-columns:repeat(7,1fr); gap:4px; text-align:center; }
    .sa-cal-grid .dow{ font-size:11px; font-weight:700; color:var(--muted); padding:6px 0; text-transform:uppercase; }
    .sa-cal-grid .d{ height:34px; display:grid; place-items:center; font-size:13px; border-radius:10px; color:var(--text); }
    .sa-cal-grid .d.today{ background:var(--teal); color:#fff; font-weight:700; box-shadow:0 4px 10px rgba(23,107,135,.35); }

    /* Rows */
    .sa-row{ display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #EEF2F6; }
    .sa-row:last-child{ border-bottom:none; padding-bottom:0; }
    .sa-row-body{ flex:1; min-width:0; }
    .sa-row-title{ font-size:13px; font-weight:600; color:var(--navy); }
    .sa-row-sub{ font-size:12px; color:var(--muted); }
    .sa-row strong{ font-size:15px; font-weight:700; color:var(--navy); }
    .sa-scroll{ max-height:340px; overflow-y:auto; overflow-x:hidden; padding-right:4px; }

    /* Quick actions */
    .sa-quick-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .sa-quick{ display:flex; flex-direction:column; align-items:center; gap:6px; padding:16px 8px 12px; border:1px solid var(--border); border-radius:14px; background:var(--surface); text-align:center; cursor:pointer; transition:.15s; }
    .sa-quick:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); }
    .sa-quick strong{ font-size:12px; font-weight:600; color:var(--navy); }
    .sa-note{ margin-top:14px; font-size:12px; color:var(--muted); }

    /* Schools by organization */
    .sa-growth{ display:grid; grid-template-columns:minmax(80px,130px) 1fr 32px; align-items:center; gap:12px; padding:8px 0; }
    .sa-growth-name{ font-size:13px; font-weight:600; color:var(--navy); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sa-track{ height:10px; background:#E2E8F0; border-radius:999px; overflow:hidden; }
    .sa-fill{ height:100%; border-radius:999px; background:linear-gradient(90deg,var(--sky),var(--teal)); }
    .sa-growth-val{ font-size:13px; font-weight:700; color:var(--navy); text-align:right; }

    /* Bar chart */
    .sa-bars{ display:flex; align-items:flex-end; justify-content:space-around; gap:10px; height:150px; padding-top:6px; }
    .sa-bar-wrap{ flex:1; display:flex; flex-direction:column; align-items:center; justify-content:flex-end; gap:6px; height:100%; }
    .sa-bar{ width:100%; max-width:38px; border-radius:8px 8px 0 0; background:linear-gradient(180deg,#3B82F6,var(--navy)); min-height:4px; transition:filter .15s; }
    .sa-bar:hover{ filter:brightness(1.15); }
    .sa-bar-label{ font-size:11px; color:var(--muted); font-weight:500; }

    /* Bottom */
    .sa-bottom{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .sa-metrics{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
    .sa-metric{ background:var(--surface); border:1px solid #E2E8F0; border-radius:12px; padding:14px; }
    .sa-metric small{ display:block; font-size:12px; color:var(--muted); margin-bottom:4px; }
    .sa-metric strong{ font-size:22px; font-weight:800; color:var(--navy); }

    /* AI panel */
    .sa-ai{ display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; padding:20px 26px; border-radius:18px; border:1px solid #DDD6FE; background:linear-gradient(100deg,#EDE9FE,#F5F3FF); }
    .sa-ai-lead{ display:flex; align-items:center; gap:18px; }
    .sa-ai h3{ font-size:17px; font-weight:700; color:var(--purple); }
    .sa-ai p{ margin-top:4px; font-size:13px; color:var(--muted); max-width:640px; }
    .sa-ai-btn{ display:inline-flex; align-items:center; gap:8px; background:var(--purple); color:#fff; border:none; border-radius:12px; padding:12px 20px; font-size:13px; font-weight:700; font-family:inherit; cursor:pointer; box-shadow:0 4px 0 #3E2A78; transition:.15s; }
    .sa-ai-btn:hover{ background:#4B3490; transform:translateY(-1px); }

    .sa-empty{ font-size:13px; color:var(--muted); padding:6px 0; }

    /* ---------- Responsive ---------- */
    @media (max-width:1280px){
        .sa-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-main{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-col:last-child{ grid-column:1 / -1; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); align-items:start; }
    }
    @media (max-width:800px){
        .sa-main,.sa-col:last-child,.sa-bottom{ grid-template-columns:1fr; }
        .sa-welcome{ padding:20px; }
        .sa-welcome h1{ font-size:22px; }
        .sa-welcome-right{ width:100%; justify-content:space-between; }
        .sa-metrics{ grid-template-columns:1fr; }
    }
    @media (max-width:520px){
        .sa-kpis{ grid-template-columns:1fr; }
        .sa-quick-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
</style>

<div class="sa">

    {{-- WELCOME --}}
    <div class="sa-welcome" data-tilt>
        <div>
            <div class="sa-eyebrow">{{ $greet }} <i></i> Platform Administrator</div>
            <h1>Welcome Back, Super Admin!</h1>
            <p>Here's an overview of your entire SchoolNexa platform.</p>
        </div>
        <div class="sa-welcome-right">
            {!! $tile('school', 'sky', 'lg') !!}
            <div class="sa-date">
                {{ now()->format('l, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </div>

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

    {{-- MAIN COLUMNS --}}
    <div class="sa-main">

        {{-- LEFT --}}
        <div class="sa-col">
            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Platform Calendar</h3>
                    <a href="#" class="sa-link">View Calendar {!! $icon('arrow-right', 14) !!}</a>
                </div>

                <div class="sa-cal-top">
                    {!! $icon('chevron-left', 18) !!}
                    <span class="sa-cal-month">{{ now()->format('F Y') }}</span>
                    {!! $icon('chevron-right', 18) !!}
                </div>

                <div class="sa-cal-grid">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $dow)
                        <span class="dow">{{ $dow }}</span>
                    @endforeach
                    @for($i = 0; $i < $firstDay; $i++)<span></span>@endfor
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        <span class="d {{ $d === now()->day ? 'today' : '' }}">{{ $d }}</span>
                    @endfor
                </div>
            </div>

            <div class="sa-panel">
                <div class="sa-head"><h3 class="sa-title">Platform Resources</h3></div>
                @foreach($resources as [$label, $count, $ic, $tone])
                    <div class="sa-row" data-tilt>
                        {!! $tile($ic, $tone, 'xs') !!}
                        <div class="sa-row-body">
                            <div class="sa-row-title">{{ $label }}</div>
                            <div class="sa-row-sub">Across the platform</div>
                        </div>
                        <strong>{{ number_format($count) }}</strong>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- MIDDLE --}}
        <div class="sa-col">
            <div class="sa-panel">
                <div class="sa-head"><h3 class="sa-title">Quick Actions</h3></div>
                <div class="sa-quick-grid">
                    @foreach($quick as [$label, $ic, $tone])
                        <div class="sa-quick" data-tilt>
                            {!! $tile($ic, $tone, 'sm') !!}
                            <strong>{{ $label }}</strong>
                        </div>
                    @endforeach
                </div>
                <p class="sa-note">Management screens will be connected as their modules are implemented.</p>
            </div>

            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Schools by Organization</h3>
                    <span class="sa-link">Platform overview</span>
                </div>

                @forelse($organizationSchoolData as $item)
                    <div class="sa-growth">
                        <span class="sa-growth-name" title="{{ $item->name }}">{{ $item->name }}</span>
                        <div class="sa-track">
                            <div class="sa-fill" style="width:{{ ($item->total / $orgMax) * 100 }}%"></div>
                        </div>
                        <span class="sa-growth-val">{{ $item->total }}</span>
                    </div>
                @empty
                    <p class="sa-empty">No organizations registered yet.</p>
                @endforelse
            </div>

            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">User Registration Trend</h3>
                    <span class="sa-muted">Last 6 months</span>
                </div>

                <div class="sa-bars">
                    @foreach($userGrowth as $month)
                        <div class="sa-bar-wrap">
                            <span class="sa-row-sub">{{ $month['total'] }}</span>
                            <div class="sa-bar" style="height:{{ max(4, ($month['total'] / $maxUsers) * 100) }}px"></div>
                            <span class="sa-bar-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="sa-col">
            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Critical Alerts</h3>
                    <span class="sa-pill teal">Platform</span>
                </div>

                @foreach($alerts as [$title, $sub, $count, $ic, $tone])
                    <div class="sa-row" data-tilt>
                        {!! $tile($ic, $tone, 'xs') !!}
                        <div class="sa-row-body">
                            <div class="sa-row-title">{{ $title }}</div>
                            <div class="sa-row-sub">{{ $sub }}</div>
                        </div>
                        <span class="sa-pill {{ $tone === 'danger' ? 'danger' : '' }}">{{ $count }}</span>
                    </div>
                @endforeach
            </div>

            <div class="sa-panel">
                <div class="sa-head">
                    <h3 class="sa-title">Platform Modules</h3>
                    <span class="sa-link">{{ count($modules) }} available</span>
                </div>

                <div class="sa-scroll">
                    @foreach($modules as $i => $module)
                        <div class="sa-row" data-tilt>
                            {!! $tile($moduleIcon($module), $tones[$i % count($tones)], 'xs') !!}
                            <div class="sa-row-body"><div class="sa-row-title">{{ $module['name'] }}</div></div>
                            <span class="sa-pill success">Authorized</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- LOWER --}}
    <div class="sa-bottom">
        <div class="sa-panel">
            <div class="sa-head"><h3 class="sa-title">Platform Overview</h3></div>
            <div class="sa-metrics">
                <div class="sa-metric"><small>Organizations</small><strong>{{ number_format($stats['organizations']) }}</strong></div>
                <div class="sa-metric"><small>Schools</small><strong>{{ number_format($stats['schools']) }}</strong></div>
                <div class="sa-metric"><small>Campuses</small><strong>{{ number_format($stats['campuses']) }}</strong></div>
            </div>
        </div>

        <div class="sa-panel">
            <div class="sa-head"><h3 class="sa-title">User Distribution</h3></div>
            <div class="sa-metrics">
                <div class="sa-metric"><small>Students</small><strong>{{ number_format($stats['students']) }}</strong></div>
                <div class="sa-metric"><small>Staff</small><strong>{{ number_format($stats['staff']) }}</strong></div>
                <div class="sa-metric"><small>Other Accounts</small><strong>{{ number_format(max(0, $stats['users'] - $stats['students'] - $stats['staff'])) }}</strong></div>
            </div>
        </div>
    </div>

    {{-- AI PANEL --}}
    <div class="sa-ai" data-tilt>
        <div class="sa-ai-lead">
            {!! $tile('ai', 'purple') !!}
            <div>
                <h3>SchoolNexa AI</h3>
                <p>Get a platform-wide summary of organizations, schools, user registrations and operational activity.</p>
            </div>
        </div>
        <button class="sa-ai-btn" type="button">Ask SchoolNexa AI {!! $icon('arrow-right', 16) !!}</button>
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