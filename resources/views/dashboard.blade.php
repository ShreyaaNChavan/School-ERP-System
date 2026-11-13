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
        'pie'           => '<path d="M12 3v9h9"/><path d="M20.5 15A9 9 0 1 1 9 3.5"/>',
        'trend'         => '<path d="M3 3v18h18"/><path d="m7 15 4-4 3 3 5-6"/>',
        'records'       => '<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'chevron-left'  => '<path d="m15 6-6 6 6 6"/>',
        'chevron-right' => '<path d="m9 6 6 6-6 6"/>',
        'arrow-right'   => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron-down'  => '<path d="m6 9 6 6 6-6"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    /* ---------- 3D picture icons (icons8 "3d-fluency" set) ---------- */
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
        'pie'           => 'pie-chart',
        'trend'         => 'combo-chart',
        'records'       => 'clipboard',
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

    /*
     * Resolve links against routes already registered in this installation.
     * URL fallbacks keep the controls clickable if a route uses another name.
     */
    $resolveDashboardUrl = function (array $routeNames, string $fallback) {
        foreach ($routeNames as $routeName) {
            if (\Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName);
            }
        }

        return url($fallback);
    };

    $calendarUrl = $resolveDashboardUrl(
        ['events.dashboard', 'events.index'],
        '/events'
    );

    $aiChatUrl = $resolveDashboardUrl(
        ['caspa-chat.index'],
        '/caspa-chat'
    );

    $hostelUrl = $resolveDashboardUrl(
        ['hostel.dashboard'],
        '/hostel'
    );

    $hostelAllocationUrl = $resolveDashboardUrl(
        ['hostel.allocations'],
        '/hostel/allocations'
    );

    $quick = [
        ['Organizations', 'building',   'sky',     $resolveDashboardUrl(['organizations.index'], '/organizations')],
        ['Schools',       'school',     'success', $resolveDashboardUrl(['schools.index'], '/schools')],
        ['Campuses',      'map',        'teal',    $resolveDashboardUrl(['campuses.index'], '/campuses')],
        ['Create User',   'admissions', 'purple',  $resolveDashboardUrl(['staff.create'], '/staff/create')],
        ['Manage Roles',  'key',        'warning', $resolveDashboardUrl(['role-builder.dashboard'], '/role-builder')],
        ['Reports',       'reports',    'navy',    $resolveDashboardUrl(['caspa.reports.home', 'caspa.reports.index'], '/caspa/reports')],
    ];

    $alerts = [
        [
            'Safeguarding Cases', 'Recorded platform cases', $stats['safety_cases'], 'emergency', 'danger',
            $resolveDashboardUrl(['student-support.dashboard'], '/student-support')
        ],
        [
            'School Registrations', 'Registered schools', $stats['schools'], 'school', 'success',
            $resolveDashboardUrl(['schools.index'], '/schools')
        ],
        [
            'Platform Accounts', 'Registered user accounts', $stats['users'], 'user', 'teal',
            $resolveDashboardUrl(['staff.index'], '/staff')
        ],
    ];

    $orgMax      = max(1, (int) $organizationSchoolData->max('total'));
    $maxUsers    = max(1, (int) collect($userGrowth)->max('total'));
    $firstDay    = now()->startOfMonth()->dayOfWeek;
    $daysInMonth = now()->daysInMonth;

    /* ---------- Chart data ---------- */
    $orgRows  = collect($organizationSchoolData)
        ->map(fn ($i) => ['name' => (string) data_get($i, 'name', '—'), 'total' => (int) data_get($i, 'total', 0)])
        ->sortByDesc('total')->values();
    $orgTop   = $orgRows->take(5);
    $orgOther = (int) $orgRows->slice(5)->sum('total');
    $orgChartLabels = $orgTop->pluck('name')->when($orgOther > 0, fn ($c) => $c->push('Others'))->values()->all();
    $orgChartData   = $orgTop->pluck('total')->when($orgOther > 0, fn ($c) => $c->push($orgOther))->values()->all();

    $otherAccounts = max(0, (int) $stats['users'] - (int) $stats['students'] - (int) $stats['staff']);
    $userDistData  = [(int) $stats['students'], (int) $stats['staff'], $otherAccounts];

    $resourceLabels = array_column($resources, 0);
    $resourceData   = array_map(fn ($r) => (int) $r[1], $resources);

    $recordLabels = ['Admissions', 'Attendance Records', 'Fee Transactions', 'Safeguarding Cases'];
    $recordData   = [(int) $stats['admissions'], (int) $stats['attendance'], (int) $stats['payments'], (int) $stats['safety_cases']];

    /* ---------- Operations data ----------
       Optional variables from the controller (all have safe fallbacks, nothing is invented):
       $attendanceStats ['students'|'teachers'|'staff' => ['present','absent','late','emergency']]
       $libraryStats    ['total_titles','available','issued','reserved','low_stock']   $topBooks [['title','author','borrows'], ...]
       $fleetStats      ['total','active','idle','service','offline','trips','distance','occupancy','on_time']
       $routeStatus     [['name','status'=>'live|delayed|idle','onboard','eta','speed'], ...]
       $mapPoints       [['name','lat','lng','type'=>'vehicle','status'=>'live|delayed|idle','route'], ...]
       $hostelStats     ['occupied','available','reserved','maintenance']
       $hostelBlocks    [['name'=>'Boys Hostel','pct'=>88], ...]                                                      */
    $routeHas = fn ($n) => \Illuminate\Support\Facades\Route::has($n);

    $attKeys   = ['present', 'absent', 'late', 'emergency'];
    $attRaw    = $attendanceStats ?? [];
    $attGroups = [];
    foreach (['students', 'teachers', 'staff'] as $g) {
        $row = $attRaw[$g] ?? ($g === 'students' ? ['present' => (int) $stats['attendance']] : []);
        foreach ($attKeys as $k) { $attGroups[$g][$k] = (int) ($row[$k] ?? 0); }
    }

    $libS        = $libraryStats ?? [];
    $topBooks    = collect($topBooks ?? []);
    $topMax      = max(1, (int) $topBooks->max('borrows'));
    $libTotal    = (int) ($libS['total_titles'] ?? $stats['books']);
    $libIssued   = (int) ($libS['issued'] ?? 0);
    $libReserved = (int) ($libS['reserved'] ?? 0);
    $libLow      = (int) ($libS['low_stock'] ?? 0);
    $libAvail    = (int) ($libS['available'] ?? max(0, $libTotal - $libIssued));
    $libPct      = $libTotal > 0 ? (int) round($libAvail / $libTotal * 100) : 0;

    $fl        = $fleetStats ?? [];
    $fTotal    = (int) ($fl['total'] ?? $stats['vehicles']);
    $fActive   = (int) ($fl['active'] ?? 0);
    $fIdle     = (int) ($fl['idle'] ?? 0);
    $fService  = (int) ($fl['service'] ?? 0);
    $fOffline  = (int) ($fl['offline'] ?? 0);
    $fSeg      = max(1, $fActive + $fIdle + $fService + $fOffline);
    $fActivePct = $fTotal > 0 ? (int) round($fActive / $fTotal * 100) : 0;
    $fOcc      = (int) ($fl['occupancy'] ?? 0);
    $fOnTime   = (int) ($fl['on_time'] ?? 0);
    $routes    = collect($routeStatus ?? []);
    $vehiclePts = collect($mapPoints ?? [])->filter(fn ($p) => data_get($p, 'type', 'vehicle') === 'vehicle')->values();
    $statusTone = fn ($s) => match (strtolower((string) $s)) { 'delayed' => 'red', 'idle' => 'amber', default => 'green' };

    /* ---------- Hostel data ---------- */
    $hs      = $hostelStats ?? [];
    $hOcc    = (int) ($hs['occupied'] ?? 0);
    $hAvail  = (int) ($hs['available'] ?? 0);
    $hRes    = (int) ($hs['reserved'] ?? 0);
    $hMaint  = (int) ($hs['maintenance'] ?? 0);
    $hTotal  = max(1, $hOcc + $hAvail + $hRes + $hMaint);
    $hPct    = round($hOcc / $hTotal * 100, 1);
    $hBlocks = collect($hostelBlocks ?? [])->values();
    $hColors = ['#176B87', '#3B82F6', '#D97706', '#5B3FA3', '#16A34A'];
    $hBeds   = [
        ['Occupied',    $hOcc,   '#176B87'],
        ['Available',   $hAvail, '#16A34A'],
        ['Reserved',    $hRes,   '#D97706'],
        ['Maintenance', $hMaint, '#DC2626'],
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

    /* ---------- Panels ---------- */
    .sa-panel{ background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; }
    .sa-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; }
    .sa-head-actions{ display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end; }
    .sa-title{ font-size:15px; font-weight:700; color:var(--navy); }
    .sa-link{ display:inline-flex; align-items:center; gap:4px; font-size:12px; font-weight:600; color:var(--teal); text-decoration:none; white-space:nowrap; }
    .sa-link:hover{ text-decoration:underline; }
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
    .sa-cal-nav{ background:none; border:0; padding:6px; border-radius:8px; color:var(--muted); cursor:pointer; display:grid; place-items:center; }
    .sa-cal-nav:hover{ background:#EEF2F6; color:var(--navy); }
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

    /* ---------- Analytics / charts ---------- */
    .sa-sh{ display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .sa-sh h2{ font-size:15px; font-weight:800; color:var(--navy); }
    .sa-sh span{ font-size:11px; color:var(--muted); }
    .sa-charts-3{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; }
    .sa-charts-2{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .sa-chead{ display:flex; align-items:center; gap:12px; margin-bottom:12px; }
    .sa-chead .sa-3d{ margin-bottom:6px; }
    .sa-chead h3{ font-size:14px; font-weight:800; color:var(--navy); }
    .sa-chead small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; }
    .sa-chart{ position:relative; height:230px; }
    .sa-chart.donut{ height:200px; }
    .sa-chart canvas{ max-width:100%; }
    .sa-donut-c{ position:absolute; inset:0; display:grid; place-content:center; text-align:center; pointer-events:none; }
    .sa-donut-c strong{ display:block; font-size:26px; font-weight:800; color:var(--navy); line-height:1.1; }
    .sa-donut-c span{ font-size:10px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }
    .sa-legend{ display:flex; flex-wrap:wrap; justify-content:center; gap:6px 14px; margin-top:12px; font-size:11px; color:var(--muted); }
    .sa-legend span{ display:inline-flex; align-items:center; gap:6px; }
    .sa-legend i{ width:9px; height:9px; border-radius:50%; display:inline-block; }
    .sa-legend b{ color:var(--navy); }

    /* ---------- Operations: attendance / library / transport ---------- */
    .sa-ops-top{ display:grid; grid-template-columns:minmax(0,.8fr) minmax(0,2fr); gap:16px; align-items:stretch; }
    .sa-btn{ display:inline-flex; align-items:center; gap:6px; padding:8px 14px; border:1px solid var(--border); border-radius:10px; background:#fff; color:var(--navy); font-size:12px; font-weight:700; text-decoration:none; cursor:pointer; font-family:inherit; white-space:nowrap; }
    .sa-btn:hover{ border-color:var(--teal); background:#F0F9FF; }
    .sa-chead .push{ margin-left:auto; }

    .sa-tabs{ display:flex; gap:18px; border-bottom:1px solid #E2E8F0; margin-bottom:14px; }
    .sa-tab{ background:none; border:0; padding:8px 0; font-size:13px; font-weight:700; color:var(--muted); cursor:pointer; font-family:inherit; border-bottom:2px solid transparent; margin-bottom:-1px; }
    .sa-tab.on{ color:var(--navy); border-bottom-color:var(--teal); }
    .sa-att-stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:14px; }
    .sa-att-stat{ background:#F1F5F9; border-radius:10px; padding:10px 6px; text-align:center; }
    .sa-att-stat strong{ display:block; font-size:18px; font-weight:800; color:var(--navy); }
    .sa-att-stat span{ font-size:11px; color:var(--muted); }
    .sa-att-chart{ position:relative; height:240px; }
    .sa-bubble{ position:absolute; background:#fff; border-radius:50%; width:62px; height:62px; display:grid; place-content:center; text-align:center; box-shadow:0 6px 16px rgba(18,59,93,.2); pointer-events:none; }
    .sa-bubble strong{ font-size:14px; font-weight:800; color:var(--navy); line-height:1.1; }
    .sa-bubble span{ font-size:10px; color:var(--muted); }
    .sa-bubble.p{ left:2%; top:14%; }
    .sa-bubble.a{ right:2%; top:52%; }
    .sa-att-empty{ position:absolute; inset:0; display:none; place-items:center; font-size:13px; color:var(--muted); text-align:center; }

    .sa-lib{ display:grid; grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); gap:24px; }
    .sa-lib h4,.sa-tp h4{ margin:0 0 12px; font-size:15px; font-weight:800; color:var(--navy); }
    .sa-lib-list{ border:1px solid #E2E8F0; border-radius:12px; }
    .sa-bk{ display:grid; grid-template-columns:26px 40px minmax(0,1fr) auto; align-items:center; gap:10px; padding:10px 12px; border-bottom:1px solid #EEF2F6; }
    .sa-bk:last-child{ border-bottom:0; }
    .sa-bk .rk{ font-size:11px; color:#94A3B8; font-weight:700; }
    .sa-bk b{ display:block; font-size:13px; color:var(--navy); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .sa-bk .bar{ height:5px; background:#E2E8F0; border-radius:99px; margin-top:6px; overflow:hidden; }
    .sa-bk .bar i{ display:block; height:100%; background:var(--navy); border-radius:99px; }
    .sa-bk .cnt{ font-size:11px; color:var(--muted); white-space:nowrap; }
    .sa-ring{ --p:0; --c:#123B5D; position:relative; width:150px; height:150px; border-radius:50%; background:conic-gradient(var(--c) calc(var(--p) * 1%), #E2E8F0 0); display:grid; place-items:center; margin:0 auto; }
    .sa-ring::before{ content:''; position:absolute; inset:13px; background:#fff; border-radius:50%; }
    .sa-ring div{ position:relative; text-align:center; }
    .sa-ring strong{ display:block; font-size:28px; font-weight:800; color:var(--navy); line-height:1.1; }
    .sa-ring span{ font-size:11px; font-weight:700; color:var(--success); }
    .sa-ring.sm{ width:68px; height:68px; }
    .sa-ring.sm::before{ inset:7px; }
    .sa-ring.sm strong{ font-size:14px; }
    .sa-lib-mid{ text-align:center; margin:14px 0 12px; }
    .sa-lib-mid strong{ display:block; font-size:14px; color:var(--navy); }
    .sa-lib-mid span{ font-size:11px; color:var(--muted); }
    .sa-chips{ display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
    .sa-chip{ background:#F1F5F9; border-radius:10px; text-align:center; padding:9px 4px; }
    .sa-chip strong{ display:block; font-size:14px; color:var(--navy); }
    .sa-chip span{ font-size:10px; color:var(--muted); }
    .sa-chip i{ display:inline-block; width:6px; height:6px; border-radius:50%; margin-right:4px; }

    .sa-tp{ display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.1fr) minmax(0,1fr); gap:0; }
    .sa-tp > div{ padding:0 20px; }
    .sa-tp > div:first-child{ padding-left:0; }
    .sa-tp > div:last-child{ padding-right:0; }
    .sa-tp > div + div{ border-left:1px solid #E2E8F0; }
    .sa-live{ display:inline-flex; align-items:center; gap:6px; font-size:11px; color:var(--success); font-weight:700; }
    .sa-live i{ width:7px; height:7px; border-radius:50%; background:var(--success); display:inline-block; animation:saPulse 1.6s ease-in-out infinite; }
    @keyframes saPulse{ 0%,100%{ opacity:1; } 50%{ opacity:.3; } }
    .sa-route{ border-radius:10px; padding:10px 12px; margin-bottom:10px; border:1px solid transparent; }
    .sa-route.green{ background:#E7F5EC; border-color:#CDE9D7; }
    .sa-route.red{ background:#FDECEC; border-color:#F6CFCF; }
    .sa-route.amber{ background:#FEF3DC; border-color:#F8E1AE; }
    .sa-route .t{ display:flex; justify-content:space-between; align-items:center; font-size:13px; font-weight:700; color:var(--navy); }
    .sa-route .t em{ font-style:normal; font-size:10px; font-weight:800; letter-spacing:.04em; }
    .sa-route.green em{ color:var(--success); } .sa-route.red em{ color:var(--danger); } .sa-route.amber em{ color:var(--warning); }
    .sa-route .m{ display:flex; justify-content:space-between; gap:8px; margin-top:6px; font-size:11px; color:var(--muted); }
    .sa-fstats{ display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:14px; }
    .sa-fstat{ background:#EAF2FF; border-radius:10px; padding:10px; }
    .sa-fstat strong{ display:block; font-size:17px; font-weight:800; color:var(--navy); }
    .sa-fstat span{ font-size:11px; color:var(--muted); }
    .sa-seg{ display:flex; height:8px; border-radius:99px; overflow:hidden; background:#E2E8F0; margin:8px 0; }
    .sa-seg i{ display:block; height:100%; }
    .sa-seg-legend{ display:flex; justify-content:space-between; font-size:10px; color:var(--muted); }
    .sa-seg-legend span::before{ content:''; display:inline-block; width:6px; height:6px; border-radius:50%; margin-right:4px; background:var(--c); }
    .sa-gauges{ display:flex; justify-content:space-around; margin-top:16px; text-align:center; }
    .sa-gauges small{ display:block; margin-top:6px; font-size:11px; color:var(--muted); }
    .sa-fmap-wrap{ position:relative; }
    #saFleetMap{ height:150px; border-radius:10px; border:1px solid #E2E8F0; z-index:1; }
    .sa-fmap-empty{ position:absolute; inset:0; display:none; place-items:center; font-size:12px; color:var(--muted); text-align:center; pointer-events:none; z-index:500; padding:10px; }
    .sa-veh{ display:flex; justify-content:space-between; align-items:center; font-size:12px; color:var(--navy); padding:5px 0; }
    .sa-veh span{ display:inline-flex; align-items:center; gap:7px; }
    .sa-veh i{ width:7px; height:7px; border-radius:50%; display:inline-block; }
    .sa-veh em{ font-style:normal; font-size:11px; font-weight:600; }
    .sa-veh em.green{ color:var(--success); } .sa-veh em.red{ color:var(--danger); } .sa-veh em.amber{ color:var(--warning); }
    .sa-sub{ font-size:11px; color:var(--muted); margin:14px 0 4px; }

    /* ---------- Hostel ---------- */
    .sa-hostel{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .sa-selwrap{ position:relative; display:inline-flex; align-items:center; }
    .sa-select{ appearance:none; -webkit-appearance:none; border:1px solid var(--border); border-radius:8px; background:#fff; color:var(--muted); font:600 12px 'Inter',sans-serif; padding:7px 30px 7px 12px; cursor:pointer; }
    .sa-select:hover{ border-color:var(--teal); }
    .sa-selwrap svg{ position:absolute; right:9px; pointer-events:none; color:var(--muted); }
    .sa-hocc{ display:flex; align-items:center; gap:24px; }
    .sa-hocc .sa-ring{ width:130px; height:130px; flex-shrink:0; margin:0; }
    .sa-hocc .sa-ring::before{ inset:11px; }
    .sa-hocc .sa-ring strong{ font-size:20px; }
    .sa-hocc .sa-ring span{ color:var(--muted); font-weight:500; font-size:11px; }
    .sa-hrows{ flex:1; min-width:0; }
    .sa-hrow{ display:grid; grid-template-columns:1fr auto; gap:3px 12px; margin-bottom:12px; font-size:13px; color:var(--navy); font-weight:500; }
    .sa-hrow:last-child{ margin-bottom:0; }
    .sa-hrow strong{ font-weight:700; }
    .sa-hrow .sa-track{ grid-column:1 / -1; height:6px; }
    .sa-hrow .sa-fill{ background:var(--c); }
    .sa-bed{ display:grid; grid-template-columns:14px 1fr auto; column-gap:10px; align-items:center; margin-bottom:12px; font-size:13px; color:var(--navy); font-weight:500; }
    .sa-bed i{ width:7px; height:7px; border-radius:50%; background:var(--c); display:inline-block; }
    .sa-bed strong{ font-weight:700; }
    .sa-bed .sa-track{ grid-column:2 / -1; height:5px; margin-top:4px; }
    .sa-bed .sa-fill{ background:var(--c); }
    .sa-hfoot{ display:flex; justify-content:flex-end; margin-top:14px; }

    @media (max-width:1280px){ .sa-ops-top{ grid-template-columns:1fr; } .sa-tp{ grid-template-columns:1fr; gap:22px; } .sa-tp > div{ padding:0; } .sa-tp > div + div{ border-left:0; border-top:1px solid #E2E8F0; padding-top:20px; } }
    @media (max-width:800px){ .sa-lib{ grid-template-columns:1fr; } .sa-hostel{ grid-template-columns:1fr; } .sa-hocc{ flex-direction:column; align-items:flex-start; } }

    /* ---------- Responsive ---------- */
    @media (max-width:1280px){
        .sa-charts-3{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-main{ grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sa-col:last-child{ grid-column:1 / -1; display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); align-items:start; }
    }
    @media (max-width:800px){
        .sa-main,.sa-col:last-child,.sa-bottom,.sa-charts-3,.sa-charts-2{ grid-template-columns:1fr; }
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
                    <a href="{{ $calendarUrl }}" class="sa-link">View Calendar {!! $icon('arrow-right', 14) !!}</a>
                </div>

                <div class="sa-cal-top">
                    <button type="button" class="sa-cal-nav" id="saCalPrev" aria-label="Previous month">{!! $icon('chevron-left', 18) !!}</button>
                    <span class="sa-cal-month" id="saCalLabel">{{ now()->format('F Y') }}</span>
                    <button type="button" class="sa-cal-nav" id="saCalNext" aria-label="Next month">{!! $icon('chevron-right', 18) !!}</button>
                </div>

                <div class="sa-cal-grid" id="saCalGrid">
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
                    @foreach($quick as [$label, $ic, $tone, $url])
                        <a class="sa-quick" href="{{ $url }}" data-tilt style="text-decoration:none">
                            {!! $tile($ic, $tone, 'sm') !!}
                            <strong>{{ $label }}</strong>
                        </a>
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
                    <div class="sa-head-actions">
                        <span class="sa-pill teal">Platform</span>
                        <a href="{{ $alerts[0][5] }}" class="sa-link">Manage</a>
                        <a href="{{ $alerts[0][5] }}" class="sa-link">View All {!! $icon('arrow-right', 14) !!}</a>
                    </div>
                </div>

                @foreach($alerts as [$title, $sub, $count, $ic, $tone, $url])
                    <a class="sa-row" href="{{ $url }}" data-tilt style="text-decoration:none;color:inherit">
                        {!! $tile($ic, $tone, 'xs') !!}
                        <div class="sa-row-body">
                            <div class="sa-row-title">{{ $title }}</div>
                            <div class="sa-row-sub">{{ $sub }}</div>
                        </div>
                        <span class="sa-pill {{ $tone === 'danger' ? 'danger' : '' }}">{{ $count }}</span>
                    </a>
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

    {{-- ANALYTICS --}}
    <div class="sa-sh"><h2>Platform Analytics</h2><span>Charts built from the platform records above</span></div>

    <div class="sa-charts-3">
        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('school', 'success', 'xs') !!}
                <div><h3>Schools by Organization</h3><small>Share of registered schools</small></div>
            </div>
            <div class="sa-chart donut">
                <canvas id="saOrgChart"></canvas>
                <div class="sa-donut-c"><strong>{{ number_format(array_sum($orgChartData)) }}</strong><span>Schools</span></div>
            </div>
            <div class="sa-legend" id="saOrgLegend"></div>
        </div>

        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('pie', 'purple', 'xs') !!}
                <div><h3>User Distribution</h3><small>Students, staff and other accounts</small></div>
            </div>
            <div class="sa-chart donut">
                <canvas id="saUserChart"></canvas>
                <div class="sa-donut-c"><strong>{{ number_format(array_sum($userDistData)) }}</strong><span>Accounts</span></div>
            </div>
            <div class="sa-legend" id="saUserLegend"></div>
        </div>

        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('map', 'sky', 'xs') !!}
                <div><h3>Platform Resources</h3><small>Campuses, fleet, hostel, library, events</small></div>
            </div>
            <div class="sa-chart"><canvas id="saResChart"></canvas></div>
        </div>
    </div>

    <div class="sa-charts-2">
        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('trend', 'teal', 'xs') !!}
                <div><h3>User Registration Trend</h3><small>New accounts over the last 6 months</small></div>
            </div>
            <div class="sa-chart"><canvas id="saTrendChart"></canvas></div>
        </div>

        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('records', 'warning', 'xs') !!}
                <div><h3>Platform Records</h3><small>Admissions, attendance, payments and cases</small></div>
            </div>
            <div class="sa-chart"><canvas id="saRecChart"></canvas></div>
        </div>
    </div>

    {{-- OPERATIONS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <div class="sa-sh"><h2>Platform Operations</h2><span>Attendance, library and transport at a glance</span></div>

    <div class="sa-ops-top">

        {{-- ATTENDANCE --}}
        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('attendance', 'success', 'xs') !!}
                <h3 style="font-size:17px">Attendance</h3>
                @if($routeHas('attendance.index'))<a class="sa-btn push" href="{{ route('attendance.index') }}">View All {!! $icon('arrow-right', 14) !!}</a>@endif
            </div>
            <div class="sa-tabs" id="attTabs">
                <button type="button" class="sa-tab on" data-g="students">Students</button>
                <button type="button" class="sa-tab" data-g="teachers">Teachers</button>
                <button type="button" class="sa-tab" data-g="staff">Staff</button>
                <span class="sa-muted" style="margin-left:auto;align-self:center">Today</span>
            </div>
            <div class="sa-att-stats">
                <div class="sa-att-stat"><strong id="attEmergency">0</strong><span>Emergency</span></div>
                <div class="sa-att-stat"><strong id="attAbsent">0</strong><span>Absent</span></div>
                <div class="sa-att-stat"><strong id="attLate">0</strong><span>Late</span></div>
            </div>
            <div class="sa-att-chart">
                <canvas id="saAttChart"></canvas>
                <div class="sa-bubble p"><strong id="attPresentB">0</strong><span>Present</span></div>
                <div class="sa-bubble a"><strong id="attAbsentB">0</strong><span>Absent</span></div>
                <div class="sa-att-empty" id="attEmpty">No attendance recorded yet.</div>
            </div>
        </div>

        {{-- LIBRARY --}}
        <div class="sa-panel">
            <div class="sa-chead" data-tilt>
                {!! $tile('library', 'warning', 'xs') !!}
                <div><small style="color:var(--sky);font-weight:700;letter-spacing:.06em;text-transform:uppercase">Library</small><h3 style="font-size:19px">Library Overview</h3></div>
                @if($routeHas('library.dashboard'))<a class="sa-btn push" href="{{ route('library.dashboard') }}">View Library {!! $icon('arrow-right', 14) !!}</a>@endif
            </div>
            <div class="sa-lib">
                <div>
                    <h4>Most Borrowed Books</h4>
                    <div class="sa-lib-list">
                        @forelse($topBooks->take(5) as $i => $book)
                            <div class="sa-bk" data-tilt>
                                <span class="rk">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                {!! $tile('library', $tones[$i % count($tones)], 'xs') !!}
                                <div style="min-width:0"><b title="{{ data_get($book, 'title') }}">{{ data_get($book, 'title', 'Untitled') }}</b><div class="bar"><i style="width:{{ min(100, ((int) data_get($book, 'borrows', 0) / $topMax) * 100) }}%"></i></div></div>
                                <span class="cnt">{{ number_format((int) data_get($book, 'borrows', 0)) }} issues</span>
                            </div>
                        @empty
                            <p class="sa-empty" style="padding:24px;text-align:center">No borrowing records yet.</p>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h4>Book Availability</h4>
                    <div class="sa-ring" style="--p:{{ $libPct }}" data-tilt><div><strong>{{ $libPct }}%</strong><span>Available</span></div></div>
                    <div class="sa-lib-mid"><strong>{{ number_format($libAvail) }} / {{ number_format($libTotal) }} titles</strong><span>Available across all shelves</span></div>
                    <div class="sa-chips">
                        <div class="sa-chip"><strong>{{ number_format($libIssued) }}</strong><span><i style="background:#3B82F6"></i>Issued</span></div>
                        <div class="sa-chip"><strong>{{ number_format($libReserved) }}</strong><span><i style="background:#64748B"></i>Reserved</span></div>
                        <div class="sa-chip"><strong>{{ number_format($libLow) }}</strong><span><i style="background:#D97706"></i>Low Stock</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TRANSPORT --}}
    <div class="sa-panel">
        <div class="sa-chead" data-tilt>
            {!! $tile('transport', 'success', 'xs') !!}
            <h3 style="font-size:17px">Transport Overview</h3>
            <span class="sa-live push"><i></i>Live operations · Updated {{ now()->format('h:i A') }}</span>
        </div>
        <div class="sa-tp">

            <div>
                <div class="sa-chead" style="justify-content:space-between;margin-bottom:12px">
                    <h4 style="margin:0"><span class="sa-live" style="margin-right:6px">LIVE</span>Live Route Status</h4>
                    @if($routeHas('transport.dashboard'))<a class="sa-link" href="{{ route('transport.dashboard') }}">View All</a>@endif
                </div>
                @forelse($routes->take(4) as $r)
                    @php $tone = $statusTone(data_get($r, 'status')); @endphp
                    <div class="sa-route {{ $tone }}" data-tilt>
                        <div class="t"><span>{{ data_get($r, 'name', 'Route') }}</span><em>{{ strtoupper(data_get($r, 'status', 'live')) }}</em></div>
                        <div class="m"><span>{{ (int) data_get($r, 'onboard', 0) }} Onboard</span><span>{{ data_get($r, 'eta', '—') }}</span><span>{{ (int) data_get($r, 'speed', 0) }} km/h</span></div>
                    </div>
                @empty
                    <p class="sa-empty">No live routes reported.</p>
                @endforelse
            </div>

            <div>
                <h4>Fleet Utilization</h4>
                <div class="sa-fstats">
                    <div class="sa-fstat"><strong>{{ $fActive }}/{{ $fTotal }}</strong><span>Active</span></div>
                    <div class="sa-fstat"><strong>{{ number_format((int) ($fl['trips'] ?? 0)) }}</strong><span>Trips</span></div>
                    <div class="sa-fstat"><strong>{{ number_format((float) ($fl['distance'] ?? 0)) }} km</strong><span>Distance</span></div>
                </div>
                <div class="sa-muted" style="font-weight:700;color:var(--navy)">Fleet Status</div>
                <div class="sa-seg">
                    <i style="width:{{ $fActive / $fSeg * 100 }}%;background:#16A34A"></i>
                    <i style="width:{{ $fIdle / $fSeg * 100 }}%;background:#D97706"></i>
                    <i style="width:{{ $fService / $fSeg * 100 }}%;background:#3B82F6"></i>
                    <i style="width:{{ $fOffline / $fSeg * 100 }}%;background:#DC2626"></i>
                </div>
                <div class="sa-seg-legend">
                    <span style="--c:#16A34A">Active {{ $fActive }}</span><span style="--c:#D97706">Idle {{ $fIdle }}</span><span style="--c:#3B82F6">Service {{ $fService }}</span><span style="--c:#DC2626">Offline {{ $fOffline }}</span>
                </div>
                <div class="sa-gauges">
                    <div><div class="sa-ring sm" style="--p:{{ $fActivePct }};--c:#16A34A" data-tilt><div><strong>{{ $fActivePct }}%</strong></div></div><small>Active</small></div>
                    <div><div class="sa-ring sm" style="--p:{{ $fOcc }};--c:#3B82F6" data-tilt><div><strong>{{ $fOcc }}%</strong></div></div><small>Occupancy</small></div>
                    <div><div class="sa-ring sm" style="--p:{{ $fOnTime }};--c:#D97706" data-tilt><div><strong>{{ $fOnTime }}%</strong></div></div><small>On-Time</small></div>
                </div>
            </div>

            <div>
                <h4>Live Fleet Map</h4>
                <div class="sa-fmap-wrap">
                    <div id="saFleetMap"></div>
                    <div class="sa-fmap-empty" id="saFleetEmpty">No vehicle locations reported yet.</div>
                </div>
                <div class="sa-sub">Live Vehicles</div>
                @forelse($vehiclePts->take(4) as $v)
                    @php $tone = $statusTone(data_get($v, 'status')); @endphp
                    <div class="sa-veh">
                        <span><i style="background:{{ ['green' => '#16A34A', 'red' => '#DC2626', 'amber' => '#D97706'][$tone] }}"></i>{{ data_get($v, 'route', data_get($v, 'name', 'Vehicle')) }}</span>
                        <em class="{{ $tone }}">{{ ['green' => 'On Route', 'red' => 'Traffic', 'amber' => 'Idle'][$tone] }}</em>
                    </div>
                @empty
                    <p class="sa-empty">No vehicles on the road.</p>
                @endforelse
            </div>

        </div>
    </div>

    {{-- HOSTEL --}}
    <div class="sa-hostel">
        <div class="sa-panel">
            <div class="sa-head">
                <h3 class="sa-title">Hostel Occupancy</h3>
                <div class="sa-head-actions">
                    <a href="{{ $hostelUrl }}" class="sa-link">Manage Hostels</a>
                <span class="sa-selwrap">
                    <select class="sa-select" id="saHostelSel" aria-label="Filter by hostel">
                        <option value="all">All Hostels</option>
                        @foreach($hBlocks as $i => $b)
                            <option value="{{ $i }}">{{ data_get($b, 'name') }}</option>
                        @endforeach
                    </select>
                    {!! $icon('chevron-down', 14) !!}
                </span>
                </div>
            </div>
            <div class="sa-hocc">
                <div class="sa-ring" id="saHostelRing" style="--p:{{ $hPct }};--c:#176B87" data-tilt>
                    <div><strong id="saHostelPct">{{ $hPct }}%</strong><span>Occupied</span></div>
                </div>
                <div class="sa-hrows">
                    @forelse($hBlocks as $i => $b)
                        @php $p = (float) data_get($b, 'pct', 0); @endphp
                        <div class="sa-hrow" data-idx="{{ $i }}" data-pct="{{ $p }}">
                            <span>{{ data_get($b, 'name') }}</span>
                            <strong>{{ rtrim(rtrim(number_format($p, 1), '0'), '.') }}%</strong>
                            <div class="sa-track"><div class="sa-fill" style="width:{{ min(100, $p) }}%;--c:{{ $hColors[$i % count($hColors)] }}"></div></div>
                        </div>
                    @empty
                        <p class="sa-empty">No hostel data reported yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="sa-panel">
            <div class="sa-head"><h3 class="sa-title">Bed Availability</h3></div>
            @foreach($hBeds as [$label, $val, $color])
                <div class="sa-bed" style="--c:{{ $color }}">
                    <i></i><span>{{ $label }}</span><strong>{{ number_format($val) }}</strong>
                    <div class="sa-track"><div class="sa-fill" style="width:{{ $val / $hTotal * 100 }}%"></div></div>
                </div>
            @endforeach
            <div class="sa-hfoot">
                <a href="{{ $hostelAllocationUrl }}" class="sa-link">View Allocation {!! $icon('arrow-right', 14) !!}</a>
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
        <a class="sa-ai-btn" href="{{ $aiChatUrl }}" style="text-decoration:none">Ask SchoolNexa AI {!! $icon('arrow-right', 16) !!}</a>
    </div>

</div>

<script>
    /* Calendar: working previous / next month navigation */
    (function () {
        var grid = document.getElementById('saCalGrid'), label = document.getElementById('saCalLabel');
        var prev = document.getElementById('saCalPrev'), next = document.getElementById('saCalNext');
        if (!grid || !label || !prev || !next) return;

        var now = new Date(), y = now.getFullYear(), m = now.getMonth();
        var names = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var dows  = ['Su','Mo','Tu','We','Th','Fr','Sa'];

        function render() {
            var first = new Date(y, m, 1).getDay();
            var days  = new Date(y, m + 1, 0).getDate();
            var html  = dows.map(function (d) { return '<span class="dow">' + d + '</span>'; }).join('');
            for (var i = 0; i < first; i++) html += '<span></span>';
            for (var d = 1; d <= days; d++) {
                var today = (d === now.getDate() && m === now.getMonth() && y === now.getFullYear());
                html += '<span class="d' + (today ? ' today' : '') + '">' + d + '</span>';
            }
            grid.innerHTML = html;
            label.textContent = names[m] + ' ' + y;
        }
        function shift(n) {
            m += n;
            if (m < 0)  { m = 11; y--; }
            if (m > 11) { m = 0;  y++; }
            render();
        }

        prev.addEventListener('click', function () { shift(-1); });
        next.addEventListener('click', function () { shift(1); });
    })();

    /* Hostel occupancy: "All Hostels" dropdown */
    (function () {
        var sel = document.getElementById('saHostelSel');
        var ring = document.getElementById('saHostelRing');
        var txt = document.getElementById('saHostelPct');
        if (!sel || !ring || !txt) return;

        var rows = document.querySelectorAll('.sa-hrow');
        var overall = ring.style.getPropertyValue('--p').trim();

        sel.addEventListener('change', function () {
            var v = sel.value, p = overall;
            rows.forEach(function (r) { r.style.display = (v === 'all' || r.getAttribute('data-idx') === v) ? '' : 'none'; });
            if (v !== 'all') {
                var r = document.querySelector('.sa-hrow[data-idx="' + v + '"]');
                p = r ? r.getAttribute('data-pct') : 0;
            }
            ring.style.setProperty('--p', p);
            txt.textContent = p + '%';
        });
    })();

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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    if (typeof Chart === 'undefined') return;

    var palette = ['#3B82F6', '#16A34A', '#5B3FA3', '#D97706', '#176B87', '#DC2626'];
    var base = { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } };
    var axes = { y: { beginAtZero: true, grid: { color: '#EEF2F6' }, ticks: { precision: 0 } }, x: { grid: { display: false } } };

    function sum(a) { return a.reduce(function (t, v) { return t + (Number(v) || 0); }, 0); }
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    function empty(canvas) { canvas.parentElement.innerHTML = '<p class="sa-empty">No data recorded yet.</p>'; }

    function donut(canvasId, legendId, labels, data, colors) {
        var c = document.getElementById(canvasId); if (!c) return;
        if (!sum(data)) { empty(c); return; }
        new Chart(c, { type: 'doughnut', data: { labels: labels, datasets: [{ data: data, backgroundColor: colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] }, options: Object.assign({}, base, { cutout: '68%' }) });
        var lg = document.getElementById(legendId);
        if (lg) lg.innerHTML = labels.map(function (l, i) { return '<span><i style="background:' + colors[i % colors.length] + '"></i>' + esc(l) + ' <b>' + Number(data[i]).toLocaleString() + '</b></span>'; }).join('');
    }

    donut('saOrgChart', 'saOrgLegend', @json($orgChartLabels), @json($orgChartData), palette);
    donut('saUserChart', 'saUserLegend', ['Students', 'Staff', 'Other accounts'], @json($userDistData), ['#3B82F6', '#5B3FA3', '#CBD5E1']);

    var resEl = document.getElementById('saResChart');
    var resData = @json($resourceData);
    if (resEl) {
        if (!sum(resData)) empty(resEl);
        else new Chart(resEl, { type: 'bar', data: { labels: @json($resourceLabels), datasets: [{ data: resData, backgroundColor: palette, borderRadius: 8 }] }, options: Object.assign({}, base, { scales: axes }) });
    }

    var trend = @json($userGrowth);
    var trendEl = document.getElementById('saTrendChart');
    if (trendEl) {
        var tdata = trend.map(function (x) { return x.total; });
        if (!sum(tdata)) empty(trendEl);
        else new Chart(trendEl, { type: 'line', data: { labels: trend.map(function (x) { return x.label; }), datasets: [{ label: 'New users', data: tdata, borderColor: '#176B87', backgroundColor: 'rgba(23,107,135,.14)', fill: true, tension: .35, pointRadius: 5, pointBackgroundColor: '#fff', pointBorderWidth: 2 }] }, options: Object.assign({}, base, { scales: axes }) });
    }

    /* ---- Attendance donut with Students / Teachers / Staff tabs ---- */
    var attGroups = @json($attGroups);
    var attEl = document.getElementById('saAttChart');
    if (attEl) {
        var attColors = ['#176B87', '#3B82F6', '#D97706', '#DC2626'];
        var attChart = new Chart(attEl, { type: 'doughnut', data: { labels: ['Present', 'Absent', 'Late', 'Emergency'], datasets: [{ data: [0, 0, 0, 0], backgroundColor: attColors, borderWidth: 3, borderColor: '#fff', hoverOffset: 6 }] }, options: Object.assign({}, base, { cutout: '48%' }) });
        var setTxt = function (id, v) { var e = document.getElementById(id); if (e) e.textContent = Number(v).toLocaleString(); };
        var showGroup = function (g) {
            var d = attGroups[g] || { present: 0, absent: 0, late: 0, emergency: 0 };
            attChart.data.datasets[0].data = [d.present, d.absent, d.late, d.emergency];
            attChart.update();
            setTxt('attPresentB', d.present); setTxt('attAbsentB', d.absent); setTxt('attAbsent', d.absent); setTxt('attLate', d.late); setTxt('attEmergency', d.emergency);
            var none = (d.present + d.absent + d.late + d.emergency) === 0;
            document.getElementById('attEmpty').style.display = none ? 'grid' : 'none';
            document.querySelectorAll('.sa-bubble').forEach(function (b) { b.style.display = none ? 'none' : 'grid'; });
        };
        document.querySelectorAll('#attTabs .sa-tab').forEach(function (t) {
            t.addEventListener('click', function () {
                document.querySelectorAll('#attTabs .sa-tab').forEach(function (x) { x.classList.remove('on'); });
                t.classList.add('on'); showGroup(t.getAttribute('data-g'));
            });
        });
        showGroup('students');
    }

    var recEl = document.getElementById('saRecChart');
    var recData = @json($recordData);
    if (recEl) {
        if (!sum(recData)) empty(recEl);
        else new Chart(recEl, { type: 'bar', data: { labels: @json($recordLabels), datasets: [{ data: recData, backgroundColor: ['#3B82F6', '#16A34A', '#D97706', '#DC2626'], borderRadius: 8 }] }, options: Object.assign({}, base, { indexAxis: 'y', scales: { x: { beginAtZero: true, grid: { color: '#EEF2F6' }, ticks: { precision: 0 } }, y: { grid: { display: false } } } }) });
    }
})();
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
/* Live fleet map: vehicle positions coloured by status */
(function () {
    var el = document.getElementById('saFleetMap');
    if (!el || typeof L === 'undefined') return;
    var pts = (@json($vehiclePts->values()->all())).filter(function (p) { return isFinite(parseFloat(p.lat)) && isFinite(parseFloat(p.lng)); });
    var colors = { live: '#16A34A', delayed: '#DC2626', idle: '#D97706' };
    var map = L.map(el, { scrollWheelZoom: false, zoomControl: true, attributionControl: false }).setView([20.59, 78.96], 4);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }
    var layers = pts.map(function (p) {
        var c = colors[String(p.status || 'live').toLowerCase()] || colors.live;
        return L.circleMarker([parseFloat(p.lat), parseFloat(p.lng)], { radius: 7, color: '#fff', weight: 2, fillColor: c, fillOpacity: .95 })
            .bindPopup('<b>' + esc(p.route || p.name || 'Vehicle') + '</b><br>' + esc(p.status || 'live')).addTo(map);
    });
    if (!layers.length) { document.getElementById('saFleetEmpty').style.display = 'grid'; }
    else if (layers.length === 1) map.setView(layers[0].getLatLng(), 13);
    else map.fitBounds(L.featureGroup(layers).getBounds().pad(.25));
    setTimeout(function () { map.invalidateSize(); }, 250);
})();
</script>

@endsection
