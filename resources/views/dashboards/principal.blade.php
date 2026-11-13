@extends('layouts.schoolnexa')

@section('title', 'Principal Dashboard')

@section('content')

@php
    use Illuminate\Support\Facades\Route;

    $s = $stats ?? [];

    // Demo data is used only when actual database values are unavailable.
    $demoMode = empty($stats);

    $defaults = [
        'students' => 1248,
        'staff' => 86,
        'teachers' => 64,
        'non_teaching_staff' => 22,
        'attendance' => 1136,
        'pending_approvals' => 8,
        'admissions' => 34,
        'overdue_invoices' => 42,
        'upcoming_exams' => 3,
        'today_staff_absent' => 5,
        'today_staff_present' => 81,
        'fees_invoiced' => 18500000,
        'fees_collected' => 14250000,
        'fees_outstanding' => 4250000,
    ];

    $s = array_merge($defaults, $s);

    $money = fn($v) => '₹'.number_format((float)($v ?? 0), 0);
    $num = fn($v) => number_format((float)($v ?? 0));

    $feeStatus = $feeStatus ?? [
        'Collected' => 1425,
        'Outstanding' => 425,
        'Overdue' => 175,
    ];

    $studentGender = $studentGender ?? [
        'Boys' => 665,
        'Girls' => 583,
    ];

    $staffDistribution = $staffDistribution ?? [
        'Teaching' => 64,
        'Non-teaching' => 22,
    ];

    $monthlyFees = $monthlyFees ?? [
        ['label'=>'Jun','total'=>1850000],
        ['label'=>'Jul','total'=>2100000],
        ['label'=>'Aug','total'=>1950000],
        ['label'=>'Sep','total'=>2450000],
        ['label'=>'Oct','total'=>2800000],
        ['label'=>'Nov','total'=>3100000],
    ];

    $attendanceTrend = $attendanceTrend ?? [
        ['label'=>'Mon','rate'=>92],
        ['label'=>'Tue','rate'=>94],
        ['label'=>'Wed','rate'=>91],
        ['label'=>'Thu','rate'=>96],
        ['label'=>'Fri','rate'=>93],
        ['label'=>'Sat','rate'=>95],
        ['label'=>'Sun','rate'=>0],
    ];

    $examPerformance = $examPerformance ?? [
        'A Grade'=>185,
        'B Grade'=>310,
        'C Grade'=>245,
        'D Grade'=>95,
        'Needs Improvement'=>38,
    ];

    $classStrength = $classStrength ?? [
        ['label'=>'Class 1','total'=>125],
        ['label'=>'Class 2','total'=>118],
        ['label'=>'Class 3','total'=>132],
        ['label'=>'Class 4','total'=>140],
        ['label'=>'Class 5','total'=>135],
        ['label'=>'Class 6','total'=>145],
        ['label'=>'Class 7','total'=>138],
        ['label'=>'Class 8','total'=>130],
    ];

    /*
    |--------------------------------------------------------------------------
    | Quick Actions
    |--------------------------------------------------------------------------
    | Route existence is checked here.
    | Authorization remains enforced by Laravel middleware/controllers.
    */

    $quickLinks = [
        ['Student Directory','STUDENTS','students.index','Student profiles and enrolments','S'],
        ['Fee Management','FINANCE','fees.dashboard','Collections, invoices and payments','₹'],
        ['Staff Directory','HUMAN RESOURCES','staff.index','Staff profiles and records','P'],
        ['Attendance','ATTENDANCE','attendance.index','Daily attendance and reports','A'],
        ['Examinations','EXAMINATIONS','examinations.index','Schedules and examination results','E'],
        ['Reports & Analytics','ANALYTICS','caspa.reports.home','School reports and analytics','R'],
        ['Approval Center','ADMINISTRATION','approvals.dashboard','Review pending approvals','✓'],
        ['Events & Calendar','OPERATIONS','events.dashboard','School events and calendar','C'],
        ['Transport','OPERATIONS','transport.dashboard','Routes, vehicles and tracking','T'],
        ['Hostel','OPERATIONS','hostel.dashboard','Hostel operations','H'],
        ['Library','OPERATIONS','library.dashboard','Books, loans and availability','L'],
        ['Student Support','STUDENT SERVICES','student-support.dashboard','Student welfare and support','U'],
    ];

    $availableQuickLinks = collect($quickLinks)
        ->filter(fn($item) => Route::has($item[2]))
        ->values();

    /*
    |--------------------------------------------------------------------------
    | 3D icon helpers (icons8 "3d-fluency" set, same as Super Admin dashboard)
    |--------------------------------------------------------------------------
    | Falls back to a glossy SVG tile if the picture cannot load.
    */

    $paths = [
        'students'   => '<path d="M22 9 12 4 2 9l10 5 10-5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5"/><path d="M22 9v6"/>',
        'staff'      => '<circle cx="9" cy="8" r="3.5"/><path d="M2 20c0-3.3 3.1-6 7-6s7 2.7 7 6"/><circle cx="17.5" cy="9" r="2.5"/><path d="M17.5 14c2.8 0 4.5 1.8 4.5 4"/>',
        'user'       => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-7 8-7s8 2.6 8 7"/>',
        'admissions' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M19 8v6M16 11h6"/>',
        'attendance' => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m9 15.5 2 2 4-4"/>',
        'wallet'     => '<rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M16 15h2"/><path d="M6 6V5a2 2 0 0 1 2-2h8"/>',
        'finance'    => '<path d="M7 5h10M7 9h10M7 5c5 0 7 1.5 7 4s-2 4-7 4l7 7"/>',
        'transport'  => '<rect x="4" y="3" width="16" height="15" rx="2.5"/><path d="M4 11h16"/><circle cx="8" cy="15" r=".8"/><circle cx="16" cy="15" r=".8"/><path d="M6 18v2M18 18v2"/>',
        'hostel'     => '<path d="M3 20V6"/><path d="M3 16h18v4"/><path d="M21 16v-4a3 3 0 0 0-3-3h-7v7"/><circle cx="7" cy="12" r="2"/>',
        'library'    => '<rect x="4" y="4" width="5" height="16" rx=".5"/><rect x="10" y="7" width="4" height="13" rx=".5"/><path d="m15.5 6 4-1 2 14-4 1Z"/>',
        'events'     => '<rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 10h18"/><path d="m12 13 1.2 2.4 2.6.4-1.9 1.8.5 2.6-2.4-1.3-2.4 1.3.5-2.6-1.9-1.8 2.6-.4Z"/>',
        'reports'    => '<path d="M3 3v18h18"/><path d="M8 17v-5M13 17V8M18 17v-9"/>',
        'emergency'  => '<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17v.01"/>',
        'safety'     => '<path d="M12 3 4 6v6c0 4.5 3.2 8 8 9 4.8-1 8-4.5 8-9V6Z"/><path d="m9 12 2 2 4-4"/>',
        'ai'         => '<path d="m12 3 1.8 4.7L18.5 9.5l-4.7 1.8L12 16l-1.8-4.7L5.5 9.5l4.7-1.8Z"/><path d="m19 15 .8 2 2 .8-2 .8-.8 2-.8-2-2-.8 2-.8Z"/>',
        'layers'     => '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>',
        'exams'      => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h5"/>',
    ];

    $icon = function (string $name, int $size = 20) use ($paths) {
        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
            . ($paths[$name] ?? $paths['layers']) . '</svg>';
    };

    $slugs = [
        'students'   => 'graduation-cap',
        'staff'      => 'conference-call',
        'user'       => 'group',
        'admissions' => 'books',
        'attendance' => 'calendar',
        'wallet'     => 'money-bag',
        'finance'    => 'money-bag',
        'transport'  => 'bus',
        'hostel'     => 'bed',
        'library'    => 'book',
        'events'     => 'star',
        'reports'    => 'bar-chart',
        'emergency'  => 'high-priority',
        'safety'     => 'shield',
        'ai'         => 'bot',
        'exams'      => 'open-book',
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

    $tones = ['sky', 'success', 'purple', 'warning', 'teal', 'navy'];

    /* Interactive 3D icon: sizes xs | sm | md | lg */
    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($icon, $ramp, $slugs) {
        [$a, $b, $c] = $ramp[$tone] ?? $ramp['sky'];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';

        $img = $slug
            ? '<img class="pd-3d-img" src="https://img.icons8.com/3d-fluency/188/' . $slug . '.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.pd-3d\').classList.add(\'fb\')">'
            : '';

        $fb = '<span class="pd-ico" style="--c1:' . $a . ';--c2:' . $b . ';--c3:' . $c . '">' . $icon($name, $px) . '</span>';

        return '<span class="pd-3d ' . $size . ($slug ? '' : ' fb') . '"><span class="pd-3d-in" style="animation-delay:' . $delay . '">' . $img . $fb . '</span></span>';
    };

    /* Quick action route -> icon */
    $quickIconMap = [
        'students.index'               => 'students',
        'fees.dashboard'               => 'finance',
        'staff.index'                  => 'staff',
        'attendance.index'             => 'attendance',
        'examinations.index'           => 'exams',
        'caspa.reports.home'           => 'reports',
        'approvals.dashboard'          => 'safety',
        'events.dashboard'             => 'events',
        'transport.dashboard'          => 'transport',
        'hostel.dashboard'             => 'hostel',
        'library.dashboard'            => 'library',
        'student-support.dashboard'    => 'user',
    ];

    /* Avatar colours for staff leave requests: [background, text] */
    $avatarColors = [
        ['#DDF3F7', '#176B87'],
        ['#EEE8FF', '#5B3FA3'],
        ['#FEF3C7', '#B45309'],
        ['#DBEAFE', '#1D4ED8'],
        ['#DCFCE7', '#15803D'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Calendar demo events
    |--------------------------------------------------------------------------
    */

    $today = now()->toDateString();

    $demoCalendarItems = [
        [
            'title'=>'Monthly Staff Meeting',
            'date'=>now()->startOfMonth()->addDays(4)->toDateString(),
            'type'=>'meeting',
            'time'=>'10:00 AM',
            'location'=>'Conference Room',
            'status'=>'Scheduled'
        ],
        [
            'title'=>'Unit Test Examination',
            'date'=>now()->startOfMonth()->addDays(8)->toDateString(),
            'type'=>'exam',
            'time'=>'09:00 AM',
            'location'=>'Examination Hall',
            'status'=>'Scheduled'
        ],
        [
            'title'=>'Parent Teacher Meeting',
            'date'=>now()->startOfMonth()->addDays(12)->toDateString(),
            'type'=>'ptm',
            'time'=>'11:00 AM',
            'location'=>'School Campus',
            'status'=>'Scheduled'
        ],
        [
            'title'=>'Science Exhibition',
            'date'=>now()->startOfMonth()->addDays(16)->toDateString(),
            'type'=>'event',
            'time'=>'09:30 AM',
            'location'=>'Main Auditorium',
            'status'=>'Upcoming'
        ],
        [
            'title'=>'School Holiday',
            'date'=>now()->startOfMonth()->addDays(20)->toDateString(),
            'type'=>'holiday',
            'time'=>'All Day',
            'location'=>'School',
            'status'=>'Holiday'
        ],
        [
            'title'=>'Academic Review Meeting',
            'date'=>now()->startOfMonth()->addDays(24)->toDateString(),
            'type'=>'meeting',
            'time'=>'02:00 PM',
            'location'=>'Principal Office',
            'status'=>'Scheduled'
        ],
    ];

    $calendarItems = collect($calendarItems ?? $demoCalendarItems)
        ->map(function($item) {
            return [
                'title' => data_get($item, 'title', 'School Activity'),
                'date' => \Illuminate\Support\Carbon::parse(
                    data_get($item, 'date', now())
                )->toDateString(),
                'type' => strtolower(data_get($item, 'type', 'event')),
                'time' => data_get($item, 'time', 'Time not specified'),
                'location' => data_get($item, 'location', 'School Campus'),
                'status' => data_get($item, 'status', 'Upcoming'),
            ];
        })->values();

    $upcomingItems = $upcomingItems ?? $calendarItems
        ->filter(fn($item) => $item['date'] >= $today)
        ->sortBy('date')
        ->take(6)
        ->values();

    $recentItems = $recentItems ?? collect([
        ['title'=>'Annual examination timetable published','date'=>now()->subDay()->format('d M Y'),'status'=>'Published'],
        ['title'=>'Staff meeting notification','date'=>now()->subDays(2)->format('d M Y'),'status'=>'Published'],
        ['title'=>'Parent communication notice','date'=>now()->subDays(3)->format('d M Y'),'status'=>'Published'],
    ]);

    $leaveRequests = $leaveRequests ?? collect([
        ['name'=>'Rahul Sharma','type'=>'Casual Leave','date'=>'14 Nov 2026','days'=>1],
        ['name'=>'Priya Deshmukh','type'=>'Medical Leave','date'=>'16 Nov 2026','days'=>2],
        ['name'=>'Amit Patil','type'=>'Earned Leave','date'=>'18 Nov 2026','days'=>1],
    ]);

    $criticalAlerts = $criticalAlerts ?? collect([
        ['title'=>'Fee collection pending','description'=>'42 invoices are currently overdue.','level'=>'warning'],
        ['title'=>'Staff attendance','description'=>'5 staff members are marked absent today.','level'=>'info'],
        ['title'=>'Pending approvals','description'=>'8 requests are awaiting your review.','level'=>'danger'],
    ]);

@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.pdash{
    --navy:#123B5D;
    --teal:#176B87;
    --purple:#5B3FA3;
    --blue:#3B82F6;
    --green:#16A34A;
    --amber:#D97706;
    --red:#DC2626;
    --line:#DCE4EC;
    --muted:#64748B;
    --ink:#25313C;
    font-family:Inter,system-ui,sans-serif;
    color:var(--ink);
    display:flex;
    flex-direction:column;
    gap:17px;
    max-width:1600px;
    margin:auto;
}
.pdash *{box-sizing:border-box}
.pdash h1,.pdash h2,.pdash h3,.pdash p{margin:0}
.pdash button,.pdash select{font-family:inherit}

.pd-head{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:18px;
    flex-wrap:wrap;
    padding:23px 26px;
    border:1px solid #D7E5F3;
    border-radius:16px;
    background:linear-gradient(110deg,#E6F0FA,#F8FBFF);
}
.pd-eyebrow{
    font-size:11px;
    font-weight:800;
    letter-spacing:.09em;
    text-transform:uppercase;
    color:var(--teal);
    margin-bottom:6px;
}
.pd-head h1{font-size:25px;font-weight:800;color:var(--navy)}
.pd-head p{font-size:13px;color:var(--muted);margin-top:6px}
.pd-head-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.pd-date{text-align:right;color:var(--muted);font-size:11px}
.pd-date strong{display:block;color:var(--navy);font-size:18px}

.pd-btn{
    min-height:38px;
    padding:0 14px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border:1px solid var(--line);
    border-radius:9px;
    background:#fff;
    color:var(--navy);
    font-size:12px;
    font-weight:700;
    text-decoration:none;
    cursor:pointer;
    transition:.15s;
}
.pd-btn:hover{border-color:var(--teal);background:#F0F9FF}
.pd-btn.primary{background:var(--navy);border-color:var(--navy);color:#fff}
.pd-btn.primary:hover{background:#1B527C}

.pd-filters{
    display:flex;
    align-items:end;
    gap:10px;
    flex-wrap:wrap;
    background:#fff;
    border:1px solid var(--line);
    border-radius:13px;
    padding:13px 16px;
}
.pd-filter{display:flex;flex-direction:column;gap:5px;min-width:170px}
.pd-filter label{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase}
.pd-filter select{
    height:38px;
    border:1px solid var(--line);
    border-radius:8px;
    padding:0 10px;
    font-size:12px;
    background:#fff;
    color:var(--ink);
}

.pd-section-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin:3px 0 0;
}
.pd-section-head h2{font-size:15px;font-weight:800;color:var(--navy)}
.pd-section-head span{font-size:11px;color:var(--muted)}

.pd-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.pd-kpi,.pd-panel{
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    padding:16px;
    min-width:0;
}
.pd-kpi{position:relative;transition:.18s}
.pd-kpi:hover{transform:translateY(-2px);box-shadow:0 9px 22px rgba(18,59,93,.09)}
.pd-kpi-top{display:flex;justify-content:space-between;gap:8px;align-items:center}
.pd-kpi small{font-size:10px;text-transform:uppercase;letter-spacing:.05em;font-weight:800;color:var(--muted)}
.pd-kpi-icon{
    width:30px;height:30px;
    border-radius:9px;
    background:var(--tint,#E0F2FE);
    color:var(--accent,var(--teal));
    display:grid;place-items:center;
    font-size:13px;font-weight:800;
}
.pd-kpi strong{display:block;font-size:25px;line-height:1.2;color:var(--navy);font-weight:800;margin:9px 0 3px}
.pd-kpi span{font-size:10px;color:var(--muted)}

.pd-grid-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.pd-grid-3{display:grid;grid-template-columns:1.2fr 1fr 1fr;gap:14px}
.pd-panel h3{font-size:14px;font-weight:800;color:var(--navy)}
.pd-panel-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:12px}
.pd-panel-head span{font-size:10px;color:var(--muted)}
.pd-chart{height:255px;position:relative}
.pd-chart.small{height:220px}
.pd-chart canvas{max-width:100%}

.pd-finance-strip{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:12px}
.pd-finance-stat{padding:11px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px}
.pd-finance-stat small{display:block;font-size:10px;color:var(--muted)}
.pd-finance-stat strong{display:block;font-size:17px;color:var(--navy);margin-top:4px}

.pd-shortcuts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.pd-shortcut{
    padding:13px;
    border:1px solid #E2E8F0;
    border-radius:11px;
    background:#F8FAFC;
    text-decoration:none;
    display:flex;
    flex-direction:row;
    align-items:center;
    gap:11px;
    min-height:78px;
    transition:.15s;
}
.pd-shortcut:hover{border-color:var(--teal);background:#fff;transform:translateY(-2px)}
.pd-shortcut-icon{
    width:36px;height:36px;flex-shrink:0;
    border-radius:10px;
    display:grid;place-items:center;
    background:#E0F2FE;color:var(--teal);
    font-size:12px;font-weight:800;
}
.pd-shortcut b{font-size:11px;color:var(--navy);display:block}
.pd-shortcut > div span{font-size:10px;color:var(--muted);display:block;margin-top:4px;line-height:1.4}

.pd-list{display:flex;flex-direction:column}
.pd-row{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:11px 0;border-bottom:1px solid #EEF2F6}
.pd-row:last-child{border:0}
.pd-row b{display:block;font-size:12px;color:var(--navy)}
.pd-row small{font-size:10px;color:var(--muted)}
.pd-badge{padding:4px 8px;border-radius:99px;background:#E0F2FE;color:#075985;font-size:10px;font-weight:700;white-space:nowrap}
.pd-empty{font-size:11px;color:var(--muted);padding:18px 0}
.pd-foot{font-size:10px;color:var(--muted)}
.pd-demo-label{font-size:9px;color:#92400E;background:#FEF3C7;padding:4px 8px;border-radius:20px;font-weight:700}

/* =====================================================
   COMPACT SCHOOL CALENDAR
   ===================================================== */

.pd-cal-row{
    display:grid;
    grid-template-columns:340px minmax(0,1fr);
    gap:14px;
    align-items:stretch;
}
.pd-cal-left{display:flex;flex-direction:column;gap:14px;min-width:0}
.pd-cal-agenda{flex:1}
.pd-cal-side{display:flex;flex-direction:column;min-width:0}
.pd-cal-side > .pd-panel{flex:1;display:flex;flex-direction:column}
.pd-cal-side #upcomingAgenda{flex:1;display:flex;flex-direction:column;justify-content:space-between}
.pd-cal-card{padding:14px}
.pd-cal-card .pd-panel-head{margin-bottom:10px}
.pd-cal-card .pd-panel-head h3{font-size:14px}
.pd-cal-link{
    font-size:11px;font-weight:700;color:var(--teal);
    text-decoration:none;white-space:nowrap;
}
.pd-cal-link:hover{text-decoration:underline}

/* Toolbar: ‹  [Month ▾] [Year ▾]  ›  */
.pd-cal-toolbar{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:6px;
    margin-bottom:10px;
}
.pd-cal-nav{
    width:28px;height:28px;
    display:grid;place-items:center;
    border:0;border-radius:8px;
    background:transparent;
    color:var(--navy);
    font-size:18px;line-height:1;
    cursor:pointer;
    transition:.12s;
}
.pd-cal-nav:hover{background:#EFF6FF}
.pd-cal-selects{display:flex;align-items:center;gap:6px;flex:1;justify-content:center}
.pd-cal-selects select{
    height:28px;
    border:1px solid var(--line);
    border-radius:8px;
    padding:0 6px;
    font-size:12px;
    font-weight:600;
    color:var(--navy);
    background:#fff;
    cursor:pointer;
}
.pd-cal-selects select:focus{outline:2px solid #BFDBFE;outline-offset:1px}

/* Month frame */
.pd-cal-frame{
    border:1px solid #E6ECF2;
    border-radius:12px;
    padding:6px;
    background:#fff;
}
.pd-cal-grid{
    display:grid;
    grid-template-columns:repeat(7,minmax(0,1fr));
    row-gap:2px;
}
.pd-cal-dow{
    text-align:center;
    font-size:10px;
    font-weight:700;
    color:var(--muted);
    padding:4px 0 6px;
}
.pd-cal-day{
    position:relative;
    height:34px;
    border:0;
    border-radius:8px;
    background:transparent;
    color:var(--ink);
    font-size:12px;
    font-weight:500;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
    transition:background .12s;
    padding:0;
}
.pd-cal-day:hover{background:#F0F5FA}
.pd-cal-day.other{color:#B7C1CC}
.pd-cal-day.sunday:not(.other):not(.today):not(.selected){color:var(--red)}
.pd-cal-day.today{background:var(--navy);color:#fff;font-weight:700}
.pd-cal-day.selected:not(.today){background:#E6F0FA;color:var(--navy);font-weight:700;box-shadow:inset 0 0 0 1.5px var(--blue)}
.pd-cal-day.today.selected{box-shadow:0 0 0 2px #BFDBFE}
.pd-cal-day:focus-visible{outline:2px solid var(--blue);outline-offset:1px}

/* Event dots sit at the bottom of the cell */
.pd-cal-dots{
    position:absolute;
    bottom:3px;left:0;right:0;
    display:flex;justify-content:center;gap:2px;
}
.pd-event-dot{width:5px;height:5px;border-radius:50%;display:inline-block}
.pd-event-dot.exam{background:#5B3FA3}
.pd-event-dot.meeting{background:#3B82F6}
.pd-event-dot.ptm{background:#D97706}
.pd-event-dot.event{background:#16A34A}
.pd-event-dot.holiday{background:#DC2626}
.pd-cal-day.today .pd-event-dot{box-shadow:0 0 0 1px #fff}

.pd-cal-footer{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
    margin-top:10px;
    flex-wrap:wrap;
}
.pd-cal-legend{display:flex;gap:9px;flex-wrap:wrap}
.pd-cal-legend span{display:flex;align-items:center;gap:4px;font-size:9px;color:var(--muted)}
.pd-cal-today{
    height:26px;padding:0 10px;
    border:1px solid var(--line);border-radius:7px;
    background:#fff;color:var(--navy);
    font-size:10px;font-weight:700;cursor:pointer;
}
.pd-cal-today:hover{background:#EFF6FF;border-color:var(--teal)}

/* Right-hand column: selected date + upcoming */
.pd-cal-side{display:flex;flex-direction:column;gap:14px;min-width:0}
.pd-selected-date{font-size:11px;color:var(--muted);margin:-6px 0 10px}

.pd-calendar-event{
    padding:9px 11px;
    border:1px solid #E7EDF3;
    border-radius:10px;
    margin-bottom:7px;
    background:#FBFDFF;
    border-left:3px solid var(--event-color,#176B87);
}
.pd-calendar-event:last-child{margin-bottom:0}
.pd-calendar-event b{font-size:11px;color:var(--navy);display:block}
.pd-calendar-event small{font-size:10px;color:var(--muted);display:block;margin-top:3px}
.pd-event-type{font-size:9px;font-weight:700;margin-top:5px;color:var(--event-color,#176B87);letter-spacing:.04em}

.pd-agenda-item{display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #EEF2F6}
.pd-agenda-item:last-child{border-bottom:0;padding-bottom:0}
.pd-agenda-item:first-child{padding-top:0}
.pd-agenda-date{
    width:40px;height:40px;flex-shrink:0;
    border-radius:9px;background:#EFF6FF;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    color:var(--navy);
}
.pd-agenda-date b{font-size:15px;line-height:1}
.pd-agenda-date small{font-size:8px;text-transform:uppercase;margin-top:3px}
.pd-agenda-content{flex:1;min-width:0}
.pd-agenda-content b{font-size:11px;color:var(--navy);display:block}
.pd-agenda-content small{font-size:10px;color:var(--muted);display:block;margin-top:3px}

.pd-alert{
    display:flex;gap:10px;padding:12px;
    border:1px solid #E8EDF3;border-radius:10px;
    margin-bottom:9px;background:#fff;
}
.pd-alert-indicator{width:7px;min-height:35px;border-radius:10px;flex-shrink:0;background:#D97706}
.pd-alert.danger .pd-alert-indicator{background:#DC2626}
.pd-alert.info .pd-alert-indicator{background:#3B82F6}
.pd-alert b{font-size:11px;color:var(--navy);display:block}
.pd-alert small{font-size:10px;color:var(--muted);display:block;margin-top:4px}

/* =====================================================
   INTERACTIVE 3D ICONS (KPI cards + Quick Actions)
   ===================================================== */

.pd-3d{
    position:relative;display:inline-block;flex-shrink:0;
    width:48px;height:48px;
    transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
    transition:transform .18s ease-out;
    will-change:transform;cursor:pointer;
}
.pd-3d.xs{width:42px;height:42px}
.pd-3d.sm{width:52px;height:52px}
.pd-3d.md{width:60px;height:60px}
.pd-3d-in{display:grid;place-items:center;width:100%;height:100%;animation:pdFloat 4.2s ease-in-out infinite}
.pd-3d-img{width:100%;height:100%;object-fit:contain;user-select:none;-webkit-user-drag:none;
           filter:drop-shadow(0 3px 3px rgba(18,59,93,.16))}
.pdash .pd-3d .pd-ico{display:none}
.pdash .pd-3d.fb .pd-3d-img{display:none}
.pdash .pd-3d.fb .pd-ico{
    --c1:#7DB5FF;--c2:#3B82F6;--c3:#1D4ED8;
    position:relative;display:grid;place-items:center;width:82%;height:82%;border-radius:30%;color:#fff;
    background:linear-gradient(150deg,var(--c1) 0%,var(--c2) 55%,var(--c3) 100%);
    box-shadow:0 4px 0 var(--c3), inset 0 2px 0 rgba(255,255,255,.55), inset 0 -3px 6px rgba(0,0,0,.16);
}
.pd-3d.fb .pd-ico svg{display:block;filter:drop-shadow(0 2px 2px rgba(0,0,0,.28))}
.pd-3d.pop .pd-3d-in{animation:pdPop .55s cubic-bezier(.3,1.6,.5,1), pdFloat 4.2s ease-in-out infinite .55s}
@keyframes pdFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-2px)}}
@keyframes pdPop{0%{transform:scale(1)}35%{transform:scale(1.28) rotate(-6deg)}70%{transform:scale(.94) rotate(3deg)}100%{transform:scale(1)}}
@media (prefers-reduced-motion: reduce){.pd-3d-in{animation:none !important}.pd-3d{transition:none}}

/* KPI card: icon on the left, figures on the right */
.pd-kpi{display:flex;align-items:center;gap:14px;padding:16px 16px 18px}
.pd-kpi .pd-3d{margin-bottom:0}
.pd-kpi-body{min-width:0;flex:1;position:relative;z-index:1}
.pd-kpi-body small{display:block;font-size:10px;text-transform:uppercase;letter-spacing:.05em;font-weight:800;color:var(--muted)}
.pd-kpi-body strong{display:block;font-size:25px;line-height:1.2;color:var(--navy);font-weight:800;margin:4px 0 2px}
.pd-kpi-body span{display:block;font-size:10px;color:var(--muted)}

/* Quick action tiles */
.pd-shortcut .pd-3d{margin-bottom:0}
.pd-shortcut > div{min-width:0}

/* Staff leave requests: initials avatar */
.pd-person{display:flex;align-items:center;gap:11px;min-width:0}
.pd-avatar{
    width:38px;height:38px;flex-shrink:0;
    display:grid;place-items:center;
    border-radius:50%;
    background:var(--bg,#E0F2FE);color:var(--fg,#176B87);
    font-size:12px;font-weight:800;letter-spacing:.02em;
    box-shadow:inset 0 0 0 1.5px rgba(255,255,255,.7), 0 2px 5px rgba(18,59,93,.12);
    transition:transform .18s ease, box-shadow .18s ease;
}
.pd-leave-row{padding:10px 8px;margin:0 -8px;border-radius:10px;border-bottom:1px solid #EEF2F6;transition:background .15s}
.pd-leave-row:hover{background:#F5F9FD}
.pd-leave-row:hover .pd-avatar{transform:scale(1.08);box-shadow:0 0 0 3px #fff, 0 0 0 4.5px var(--fg,#176B87)}
.pd-leave-row:hover .pd-badge{background:#FEF3C7;color:#92400E}

@media(max-width:1100px){
    .pd-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
    .pd-grid-3{grid-template-columns:1fr 1fr}
    .pd-grid-3 .pd-panel:first-child{grid-column:1/-1}
    .pd-shortcuts{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media(max-width:900px){
    .pd-cal-row{grid-template-columns:minmax(0,320px) minmax(0,1fr)}
}
@media(max-width:760px){
    .pd-cal-row{grid-template-columns:1fr}
    .pd-cal-card{width:100%}
}
@media(max-width:720px){
    .pd-grid-2,.pd-grid-3{grid-template-columns:1fr}
    .pd-grid-3 .pd-panel:first-child{grid-column:auto}
    .pd-shortcuts{grid-template-columns:repeat(2,minmax(0,1fr))}
    .pd-finance-strip{grid-template-columns:1fr}
    .pd-head{padding:18px}
    .pd-head h1{font-size:21px}
}
@media(max-width:420px){
    .pd-kpis{grid-template-columns:1fr 1fr}
    .pd-kpi{padding:12px}
    .pd-kpi strong{font-size:20px}
    .pd-shortcuts{grid-template-columns:1fr}
}
</style>

<div class="pdash">

    {{-- HEADER --}}

    <section class="pd-head">
        <div>
            <div class="pd-eyebrow">SchoolNexa / School Leadership</div>
            <h1>Principal Dashboard</h1>
            <p>Live overview of academics, students, staff, finance and school operations.</p>
        </div>

        <div class="pd-head-actions">
            <div class="pd-date">
                {{ now()->format('D, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </section>

    {{-- FILTERS --}}

    <form method="GET" action="{{ route('dashboard') }}" class="pd-filters">

        <div class="pd-filter">
            <label for="pd-year">Academic Year</label>
            <select name="academic_year_id" id="pd-year">
                <option value="">All academic years</option>
                @foreach(($academicYears ?? collect()) as $year)
                    <option value="{{ $year->id }}"
                        @selected(($selectedAcademicYearId ?? null) == $year->id)>
                        {{ $year->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="pd-filter">
            <label for="pd-campus">Campus</label>
            <select name="campus_id" id="pd-campus">
                <option value="">All assigned campuses</option>
                @foreach(($campuses ?? collect()) as $campus)
                    <option value="{{ $campus->id }}"
                        @selected(($selectedCampusId ?? null) == $campus->id)>
                        {{ $campus->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <button class="pd-btn primary" type="submit">Apply Filters</button>
        <a class="pd-btn" href="{{ route('dashboard') }}">Reset</a>

        <span class="pd-foot">Filters remain within your assigned school.</span>
    </form>

    {{-- SCHOOL KPIs --}}

    <div class="pd-section-head">
        <h2>School at a Glance</h2>
        <span>Current school scope</span>
    </div>

    @if($demoMode)
        <span class="pd-demo-label">DEMO DATA · Replace with database statistics</span>
    @endif

    <section class="pd-kpis">

        <article class="pd-kpi" data-tilt style="--accent:#176B87;--tint:#DDF3F7">
            {!! $tile('students', 'teal', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Total Students</small>
                <strong>{{ $num($s['students']) }}</strong>
                <span>Student profiles</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#5B3FA3;--tint:#EEE8FF">
            {!! $tile('staff', 'purple', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Total Staff</small>
                <strong>{{ $num($s['staff']) }}</strong>
                <span>{{ $num($s['teachers']) }} teaching · {{ $num($s['non_teaching_staff']) }} non-teaching</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#16A34A;--tint:#DCFCE7">
            {!! $tile('attendance', 'success', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Today's Student Attendance</small>
                <strong>{{ $num($s['attendance']) }}</strong>
                <span>Entries recorded today</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#D97706;--tint:#FEF3C7">
            {!! $tile('emergency', 'warning', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Pending Approvals</small>
                <strong>{{ $num($s['pending_approvals']) }}</strong>
                <span>Awaiting action</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#3B82F6;--tint:#DBEAFE">
            {!! $tile('admissions', 'sky', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Admission Leads</small>
                <strong>{{ $num($s['admissions']) }}</strong>
                <span>Admission enquiries</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#DC2626;--tint:#FEE2E2">
            {!! $tile('wallet', 'danger', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Overdue Fee Invoices</small>
                <strong>{{ $num($s['overdue_invoices']) }}</strong>
                <span>Unpaid invoices past due</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#176B87;--tint:#DDF3F7">
            {!! $tile('exams', 'teal', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Upcoming Examinations</small>
                <strong>{{ $num($s['upcoming_exams']) }}</strong>
                <span>Scheduled exam entries</span>
            </div>
        </article>

        <article class="pd-kpi" data-tilt style="--accent:#D97706;--tint:#FEF3C7">
            {!! $tile('user', 'warning', 'sm') !!}
            <div class="pd-kpi-body">
                <small>Staff Absent Today</small>
                <strong>{{ $num($s['today_staff_absent']) }}</strong>
                <span>Employee attendance records</span>
            </div>
        </article>

    </section>

    {{-- SCHOOL CALENDAR (COMPACT) --}}

    <div class="pd-section-head">
        <h2>School Calendar & Events</h2>
        <span>Academic activities and important dates</span>
    </div>

    <section class="pd-cal-row">

        <div class="pd-cal-left">

        {{-- Compact calendar card --}}
        <article class="pd-panel pd-cal-card">

            <div class="pd-panel-head">
                <h3>Schedule</h3>
                @if(Route::has('events.dashboard'))
                    <a class="pd-cal-link" href="{{ route('events.dashboard') }}">+ Manage Events</a>
                @endif
            </div>

            <div class="pd-cal-toolbar">
                <button type="button" class="pd-cal-nav" id="calendarPrev" aria-label="Previous month">‹</button>

                <div class="pd-cal-selects">
                    <select id="calendarMonthSelect" aria-label="Select month"></select>
                    <select id="calendarYearSelect" aria-label="Select year"></select>
                </div>

                <button type="button" class="pd-cal-nav" id="calendarNext" aria-label="Next month">›</button>
            </div>

            <div class="pd-cal-frame">
                <div class="pd-cal-grid" id="calendarGrid"></div>
            </div>

            <div class="pd-cal-footer">
                <div class="pd-cal-legend">
                    <span><i class="pd-event-dot event"></i> Event</span>
                    <span><i class="pd-event-dot exam"></i> Exam</span>
                    <span><i class="pd-event-dot ptm"></i> PTM</span>
                    <span><i class="pd-event-dot meeting"></i> Meeting</span>
                    <span><i class="pd-event-dot holiday"></i> Holiday</span>
                </div>
                <button type="button" class="pd-cal-today" id="calendarToday">Today</button>
            </div>

        </article>

            {{-- Selected date agenda --}}
            <article class="pd-panel pd-cal-agenda">
                <div class="pd-panel-head">
                    <h3>Selected Date Agenda</h3>
                </div>
                <div class="pd-selected-date" id="selectedDateTitle">Select a date</div>

                <div id="selectedDateEvents">
                    <div class="pd-empty">Select a date to view scheduled activities.</div>
                </div>
            </article>

        </div>

        {{-- Upcoming activities --}}
        <div class="pd-cal-side">

            <article class="pd-panel">
                <div class="pd-panel-head">
                    <h3>Upcoming Activities</h3>
                    <span>Next scheduled activities</span>
                </div>

                <div id="upcomingAgenda">

                    @forelse($upcomingItems as $item)
                        <div class="pd-agenda-item">
                            <div class="pd-agenda-date">
                                <b>{{ \Illuminate\Support\Carbon::parse(data_get($item,'date',now()))->format('d') }}</b>
                                <small>{{ \Illuminate\Support\Carbon::parse(data_get($item,'date',now()))->format('M') }}</small>
                            </div>
                            <div class="pd-agenda-content">
                                <b>{{ data_get($item,'title','School Activity') }}</b>
                                <small>
                                    {{ data_get($item,'time','') }}
                                    @if(data_get($item,'location'))
                                        · {{ data_get($item,'location') }}
                                    @endif
                                </small>
                            </div>
                            <span class="pd-badge">{{ data_get($item,'status','Upcoming') }}</span>
                        </div>
                    @empty
                        <div class="pd-empty">No upcoming activities found.</div>
                    @endforelse

                </div>
            </article>

        </div>

    </section>

    {{-- FINANCE --}}

    <div class="pd-section-head">
        <h2>Finance & Fee Intelligence</h2>
        <span>Invoice and payment records</span>
    </div>

    <section class="pd-grid-2">

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Fee Collection Overview</h3>
                <span>School financial position</span>
            </div>

            <div class="pd-finance-strip">
                <div class="pd-finance-stat">
                    <small>Total Invoiced</small>
                    <strong>{{ $money($s['fees_invoiced']) }}</strong>
                </div>
                <div class="pd-finance-stat">
                    <small>Fees Collected</small>
                    <strong>{{ $money($s['fees_collected']) }}</strong>
                </div>
                <div class="pd-finance-stat">
                    <small>Outstanding</small>
                    <strong>{{ $money($s['fees_outstanding']) }}</strong>
                </div>
            </div>

            <div class="pd-chart small">
                <canvas id="feeStatusChart"></canvas>
            </div>
        </article>

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Monthly Fee Collection</h3>
                <span>Last 6 months</span>
            </div>
            <div class="pd-chart">
                <canvas id="feeTrendChart"></canvas>
            </div>
        </article>

    </section>

    {{-- STUDENTS AND STAFF --}}

    <div class="pd-section-head">
        <h2>Students & Staff</h2>
        <span>People and workforce overview</span>
    </div>

    <section class="pd-grid-3">

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Student Gender Distribution</h3>
                <span>Profiles</span>
            </div>
            <div class="pd-chart small">
                <canvas id="genderChart"></canvas>
            </div>
        </article>

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Staff Composition</h3>
                <span>Active profiles</span>
            </div>
            <div class="pd-chart small">
                <canvas id="staffChart"></canvas>
            </div>
        </article>

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Staff Attendance Today</h3>
                <span>Recorded attendance</span>
            </div>

            <div class="pd-finance-strip">
                <div class="pd-finance-stat">
                    <small>Present</small>
                    <strong>{{ $num($s['today_staff_present']) }}</strong>
                </div>
                <div class="pd-finance-stat">
                    <small>Absent</small>
                    <strong>{{ $num($s['today_staff_absent']) }}</strong>
                </div>
                <div class="pd-finance-stat">
                    <small>Teaching</small>
                    <strong>{{ $num($s['teachers']) }}</strong>
                </div>
            </div>
        </article>

    </section>

    {{-- ACADEMICS --}}

    <div class="pd-section-head">
        <h2>Academic Performance & Attendance</h2>
        <span>Recorded academic data</span>
    </div>

    <section class="pd-grid-2">

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Student Attendance Trend</h3>
                <span>Last 7 days · present %</span>
            </div>
            <div class="pd-chart">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </article>

        <article class="pd-panel">
            <div class="pd-panel-head">
                <h3>Examination Outcome</h3>
                <span>Recorded results</span>
            </div>
            <div class="pd-chart">
                <canvas id="examChart"></canvas>
            </div>
        </article>

    </section>

    <section class="pd-panel">
        <div class="pd-panel-head">
            <h3>Class-wise Student Strength</h3>
            <span>Top 8 classes by active enrolment</span>
        </div>
        <div class="pd-chart">
            <canvas id="classChart"></canvas>
        </div>
    </section>

    {{-- SCHOOL OPERATIONS --}}

    <div class="pd-section-head">
        <h2>School Operations</h2>
        <span>Navigation and daily activities</span>
    </div>

    <section class="pd-panel">

        <div class="pd-panel-head">
            <h3>Quick Actions</h3>
            <span>{{ $availableQuickLinks->count() }} available modules</span>
        </div>

        <div class="pd-shortcuts">

            @forelse($availableQuickLinks as $link)
                <a class="pd-shortcut" data-tilt href="{{ route($link[2]) }}">
                    {!! $tile($quickIconMap[$link[2]] ?? 'layers', $tones[$loop->index % count($tones)], 'xs') !!}
                    <div>
                        <b>{{ $link[0] }}</b>
                        <span>{{ $link[3] }}</span>
                    </div>
                </a>
            @empty
                <div class="pd-empty">
                    No configured module routes were found. Verify the route names using
                    <code>php artisan route:list</code>.
                </div>
            @endforelse

        </div>

    </section>

    {{-- ALERTS AND LEAVE REQUESTS --}}

    <div class="pd-section-head">
        <h2>Leadership & Administration</h2>
        <span>Items requiring principal attention</span>
    </div>

    <section class="pd-grid-2">

        <article class="pd-panel">

            <div class="pd-panel-head">
                <h3>Critical Alerts</h3>
                <span>School notifications</span>
            </div>

            @forelse($criticalAlerts as $alert)
                <div class="pd-alert {{ data_get($alert,'level') }}">
                    <div class="pd-alert-indicator"></div>
                    <div>
                        <b>{{ data_get($alert,'title','Notification') }}</b>
                        <small>{{ data_get($alert,'description','') }}</small>
                    </div>
                </div>
            @empty
                <div class="pd-empty">No critical alerts at the moment.</div>
            @endforelse

        </article>

        <article class="pd-panel">

            <div class="pd-panel-head">
                <h3>Staff Leave Requests</h3>
                <span>Awaiting review</span>
            </div>

            @forelse($leaveRequests as $leave)
                @php
                    $lvName = (string) data_get($leave,'name','Staff Member');
                    $lvInitials = collect(preg_split('/\s+/', trim($lvName)))
                        ->filter()
                        ->take(2)
                        ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
                        ->implode('');
                    [$lvBg, $lvFg] = $avatarColors[$loop->index % count($avatarColors)];
                @endphp
                <div class="pd-row pd-leave-row">
                    <div class="pd-person">
                        <span class="pd-avatar" style="--bg:{{ $lvBg }};--fg:{{ $lvFg }}" aria-hidden="true">{{ $lvInitials ?: '?' }}</span>
                        <div>
                            <b>{{ $lvName }}</b>
                            <small>
                                {{ data_get($leave,'type','Leave') }}
                                · {{ data_get($leave,'date','') }}
                                · {{ data_get($leave,'days',1) }} day(s)
                            </small>
                        </div>
                    </div>
                    <span class="pd-badge">Pending</span>
                </div>
            @empty
                <div class="pd-empty">No pending leave requests.</div>
            @endforelse

            @if(Route::has('approvals.dashboard'))
                <div style="margin-top:12px">
                    <a class="pd-btn" href="{{ route('approvals.dashboard') }}">Open Approval Center</a>
                </div>
            @endif

        </article>

    </section>

    {{-- ANNOUNCEMENTS --}}

    <section class="pd-panel">

        <div class="pd-panel-head">
            <h3>Recent Announcements</h3>
            <span>Latest published notices</span>
        </div>

        <div class="pd-list">

            @forelse($recentItems as $item)
                <div class="pd-row">
                    <div>
                        <b>{{ data_get($item,'title','Announcement') }}</b>
                        <small>{{ data_get($item,'date','') }}</small>
                    </div>
                    <span class="pd-badge">{{ data_get($item,'status','Published') }}</span>
                </div>
            @empty
                <div class="pd-empty">No recent announcements found.</div>
            @endforelse

        </div>

    </section>

    {{-- PRINCIPAL INTELLIGENCE --}}

    <section class="pd-panel" style="background:linear-gradient(105deg,#F1EDFF,#FAF9FF);border-color:#DDD6FE">

        <div class="pd-panel-head">
            <div>
                <h3 style="color:#5B3FA3;font-size:16px">Principal Intelligence</h3>
                <span>CASPA AI · School leadership assistant</span>
            </div>

            @if(Route::has('principal-intelligence.index'))
                <a class="pd-btn primary"
                   href="{{ route('principal-intelligence.index') }}"
                   style="background:#5B3FA3;border-color:#5B3FA3">
                    Open Principal Intelligence
                </a>
            @elseif(Route::has('caspa-chat.index'))
                <a class="pd-btn primary"
                   href="{{ route('caspa-chat.index') }}"
                   style="background:#5B3FA3;border-color:#5B3FA3">
                    Open CASPA Chat
                </a>
            @endif
        </div>

        <p class="pd-foot">
            Ask CASPA AI about academic performance, attendance, fee collection,
            staff activities and school operations. AI responses must respect
            the signed-in user's school and permission scope.
        </p>

    </section>

    <div class="pd-foot">
        Figures are calculated from available database records.
        Demo figures are displayed where real data has not yet been supplied.
    </div>

</div>

{{-- CHART.JS --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
(function(){

    if(typeof Chart === 'undefined') return;

    const palette = [
        '#176B87','#3B82F6','#5B3FA3',
        '#16A34A','#D97706','#DC2626'
    ];

    const base = {
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{
                position:'bottom',
                labels:{
                    usePointStyle:true,
                    boxWidth:8,
                    font:{family:'Inter',size:10}
                }
            }
        }
    };

    const doughnut = (id,obj) => {
        const element = document.getElementById(id);

        if(!element) return;

        new Chart(element,{
            type:'doughnut',
            data:{
                labels:Object.keys(obj || {}),
                datasets:[{
                    data:Object.values(obj || {}),
                    backgroundColor:palette,
                    borderWidth:2,
                    borderColor:'#fff',
                    hoverOffset:7
                }]
            },
            options:{...base,cutout:'64%'}
        });
    };

    doughnut('feeStatusChart',@json($feeStatus));
    doughnut('genderChart',@json($studentGender));
    doughnut('staffChart',@json($staffDistribution));

    const trend = @json($monthlyFees);
    const feeEl = document.getElementById('feeTrendChart');

    if(feeEl){
        new Chart(feeEl,{
            type:'line',
            data:{
                labels:trend.map(x=>x.label),
                datasets:[{
                    label:'Collection (₹)',
                    data:trend.map(x=>x.total),
                    borderColor:'#176B87',
                    backgroundColor:'rgba(23,107,135,.12)',
                    fill:true,
                    tension:.35,
                    pointRadius:4
                }]
            },
            options:{
                ...base,
                plugins:{legend:{display:false}},
                scales:{
                    y:{beginAtZero:true,grid:{color:'#EEF2F6'}},
                    x:{grid:{display:false}}
                }
            }
        });
    }

    const att = @json($attendanceTrend);
    const attEl = document.getElementById('attendanceTrendChart');

    if(attEl){
        new Chart(attEl,{
            type:'line',
            data:{
                labels:att.map(x=>x.label),
                datasets:[{
                    label:'Attendance %',
                    data:att.map(x=>x.rate),
                    borderColor:'#3B82F6',
                    backgroundColor:'rgba(59,130,246,.12)',
                    fill:true,
                    tension:.35,
                    pointRadius:4
                }]
            },
            options:{
                ...base,
                plugins:{legend:{display:false}},
                scales:{
                    y:{
                        beginAtZero:true,
                        max:100,
                        ticks:{callback:v=>v+'%'}
                    },
                    x:{grid:{display:false}}
                }
            }
        });
    }

    const exam = @json($examPerformance);
    const examEl = document.getElementById('examChart');

    if(examEl){
        new Chart(examEl,{
            type:'bar',
            data:{
                labels:Object.keys(exam),
                datasets:[{
                    label:'Results',
                    data:Object.values(exam),
                    backgroundColor:'#5B3FA3',
                    borderRadius:6
                }]
            },
            options:{
                ...base,
                plugins:{legend:{display:false}},
                scales:{
                    y:{beginAtZero:true},
                    x:{grid:{display:false}}
                }
            }
        });
    }

    const cls = @json($classStrength);
    const clsEl = document.getElementById('classChart');

    if(clsEl){
        new Chart(clsEl,{
            type:'bar',
            data:{
                labels:cls.map(x=>x.label),
                datasets:[{
                    label:'Students',
                    data:cls.map(x=>x.total),
                    backgroundColor:'#176B87',
                    borderRadius:6
                }]
            },
            options:{
                ...base,
                plugins:{legend:{display:false}},
                scales:{
                    y:{beginAtZero:true},
                    x:{grid:{display:false}}
                }
            }
        });
    }

})();
</script>

{{-- INTERACTIVE COMPACT SCHOOL CALENDAR --}}

<script>
(function(){

    const events = @json($calendarItems);
    const grid = document.getElementById('calendarGrid');
    const monthSelect = document.getElementById('calendarMonthSelect');
    const yearSelect = document.getElementById('calendarYearSelect');
    const selectedDateTitle = document.getElementById('selectedDateTitle');
    const selectedDateEvents = document.getElementById('selectedDateEvents');

    if(!grid) return;

    const today = new Date();
    today.setHours(0,0,0,0);

    let currentMonth = new Date(today.getFullYear(),today.getMonth(),1);
    let selectedDate = new Date(today);

    const weekdays = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    const monthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    const colors = {
        exam:'#5B3FA3',
        meeting:'#3B82F6',
        ptm:'#D97706',
        event:'#16A34A',
        holiday:'#DC2626'
    };

    /* ---------- Month / year selects ---------- */

    monthSelect.innerHTML = monthNames.map((name,index) =>
        `<option value="${index}">${name}</option>`
    ).join('');

    // Year range: 3 years either side of today, widened to include any event year.
    const eventYears = events
        .map(e => parseInt(String(e.date).slice(0,4),10))
        .filter(y => !isNaN(y));

    const minYear = Math.min(today.getFullYear()-3, ...(eventYears.length ? eventYears : [today.getFullYear()]));
    const maxYear = Math.max(today.getFullYear()+3, ...(eventYears.length ? eventYears : [today.getFullYear()]));

    let yearOptions = '';
    for(let y=minYear; y<=maxYear; y++){
        yearOptions += `<option value="${y}">${y}</option>`;
    }
    yearSelect.innerHTML = yearOptions;

    /* ---------- Helpers ---------- */

    function formatDate(date){
        const y = date.getFullYear();
        const m = String(date.getMonth()+1).padStart(2,'0');
        const d = String(date.getDate()).padStart(2,'0');

        return `${y}-${m}-${d}`;
    }

    function getEvents(date){
        const key = formatDate(date);
        return events.filter(event => event.date === key);
    }

    function formatDisplayDate(date){
        return date.toLocaleDateString('en-IN',{
            weekday:'long',
            day:'numeric',
            month:'long',
            year:'numeric'
        });
    }

    function escapeHtml(value){
        return String(value ?? '').replace(/[&<>"']/g,function(char){
            return {
                '&':'&amp;',
                '<':'&lt;',
                '>':'&gt;',
                '"':'&quot;',
                "'":'&#039;'
            }[char];
        });
    }

    /* ---------- Selected-date agenda ---------- */

    function renderSelectedDate(){

        const selectedEvents = getEvents(selectedDate);

        selectedDateTitle.textContent = formatDisplayDate(selectedDate);

        if(!selectedEvents.length){
            selectedDateEvents.innerHTML =
                '<div class="pd-empty" style="padding:6px 0">No activities scheduled for this date.</div>';
            return;
        }

        selectedDateEvents.innerHTML = selectedEvents.map(event => {

            const color = colors[event.type] || colors.event;

            return `
                <div class="pd-calendar-event" style="--event-color:${color}">
                    <b>${escapeHtml(event.title)}</b>
                    <small>${escapeHtml(event.time || '')}${event.location ? ' · ' + escapeHtml(event.location) : ''}</small>
                    <div class="pd-event-type">${escapeHtml(event.type.toUpperCase())}</div>
                </div>
            `;

        }).join('');
    }

    /* ---------- Calendar grid ---------- */

    function renderCalendar(){

        const year = currentMonth.getFullYear();
        const month = currentMonth.getMonth();

        monthSelect.value = String(month);
        yearSelect.value = String(year);

        grid.innerHTML = weekdays.map(day =>
            `<div class="pd-cal-dow">${day}</div>`
        ).join('');

        const firstDay = new Date(year,month,1).getDay();
        const daysInMonth = new Date(year,month+1,0).getDate();
        const previousMonthDays = new Date(year,month,0).getDate();

        const totalCells = Math.ceil((firstDay+daysInMonth)/7)*7;

        for(let i=0;i<totalCells;i++){

            let date;

            if(i < firstDay){
                date = new Date(year,month-1,previousMonthDays-firstDay+i+1);
            }else if(i >= firstDay+daysInMonth){
                date = new Date(year,month+1,i-firstDay-daysInMonth+1);
            }else{
                date = new Date(year,month,i-firstDay+1);
            }

            const key = formatDate(date);
            const isCurrentMonth = date.getMonth() === month;
            const isToday = key === formatDate(today);
            const isSelected = key === formatDate(selectedDate);
            const isSunday = date.getDay() === 0;
            const dayEvents = getEvents(date);

            // Up to 3 unique event types as small dots
            const types = [...new Set(dayEvents.map(e => colors[e.type] ? e.type : 'event'))].slice(0,3);
            const dots = types.map(type => `<i class="pd-event-dot ${type}"></i>`).join('');

            const cell = document.createElement('button');

            cell.type = 'button';
            cell.className =
                'pd-cal-day' +
                (!isCurrentMonth ? ' other' : '') +
                (isSunday ? ' sunday' : '') +
                (isToday ? ' today' : '') +
                (isSelected ? ' selected' : '');

            cell.setAttribute('aria-label',
                formatDisplayDate(date) + (dayEvents.length ? ', ' + dayEvents.length + ' event(s)' : '')
            );

            cell.innerHTML = `
                <span>${date.getDate()}</span>
                ${dots ? `<span class="pd-cal-dots">${dots}</span>` : ''}
            `;

            cell.addEventListener('click',function(){
                selectedDate = new Date(date);
                selectedDate.setHours(0,0,0,0);

                if(!isCurrentMonth){
                    currentMonth = new Date(
                        selectedDate.getFullYear(),
                        selectedDate.getMonth(),
                        1
                    );
                }

                renderCalendar();
                renderSelectedDate();
            });

            grid.appendChild(cell);
        }
    }

    /* ---------- Controls ---------- */

    document.getElementById('calendarPrev').addEventListener('click',function(){
        currentMonth = new Date(currentMonth.getFullYear(),currentMonth.getMonth()-1,1);
        renderCalendar();
    });

    document.getElementById('calendarNext').addEventListener('click',function(){
        currentMonth = new Date(currentMonth.getFullYear(),currentMonth.getMonth()+1,1);
        renderCalendar();
    });

    document.getElementById('calendarToday').addEventListener('click',function(){
        currentMonth = new Date(today.getFullYear(),today.getMonth(),1);
        selectedDate = new Date(today);
        renderCalendar();
        renderSelectedDate();
    });

    monthSelect.addEventListener('change',function(){
        currentMonth = new Date(currentMonth.getFullYear(),parseInt(monthSelect.value,10),1);
        renderCalendar();
    });

    yearSelect.addEventListener('change',function(){
        currentMonth = new Date(parseInt(yearSelect.value,10),currentMonth.getMonth(),1);
        renderCalendar();
    });

    renderCalendar();
    renderSelectedDate();

})();
</script>

{{-- INTERACTIVE 3D ICONS: tilt toward the mouse, grow on hover, pop on click --}}

<script>
(function () {
    if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    document.querySelectorAll('.pdash [data-tilt]').forEach(function (card) {
        var art = card.querySelector('.pd-3d');
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