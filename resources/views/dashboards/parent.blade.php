@extends('layouts.schoolnexa')

@section('title', 'Parent Dashboard')

@section('content')
@php
    $children = $children ?? collect();
    $selectedChild = $selectedChild ?? null;
    $childEnrollment = $childEnrollment ?? null;

    $childAttendance = $childAttendance ?? [
        'present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0
    ];

    $childFees = $childFees ?? [
        'total' => 0, 'paid' => 0, 'balance' => 0, 'overdue' => 0
    ];

    $childAcademic = $childAcademic ?? [
        'average' => null, 'upcoming_exams' => 0, 'pending_assignments' => 0
    ];

    $childRecentResults = $childRecentResults ?? collect();
    $childAssignments = $childAssignments ?? collect();
    $childAnnouncements = $childAnnouncements ?? collect();
    $childEvents = $childEvents ?? collect();
    $childPayments = $childPayments ?? collect();
    $childAttendanceDays = $childAttendanceDays ?? collect();

    $childUpcomingExams = $childUpcomingExams ?? collect();
    $childDocuments = $childDocuments ?? collect();
    $childTransport = $childTransport ?? null;
    $childBorrowedBooks = $childBorrowedBooks ?? collect();
    $childLibraryStats = $childLibraryStats ?? [
        'available' => null,
        'total' => null,
        'availability_rate' => null,
    ];
    $childAchievements = $childAchievements ?? collect();
    $childAttendanceTrend = $childAttendanceTrend ?? collect();

    $childPerformance = $childPerformance ?? [
        'attendance' => $childAttendance['rate'] ?? null,
        'marks' => $childAcademic['average'] ?? null,
        'homework' => null,
        'engagement' => null,
    ];

    $money = fn($value) => '₹'.number_format((float)($value ?? 0), 2);

    $fullName = $selectedChild
        ? trim(collect([
            $selectedChild->first_name ?? '',
            $selectedChild->middle_name ?? '',
            $selectedChild->last_name ?? '',
        ])->filter()->implode(' '))
        : '';

    $initials = $selectedChild
        ? strtoupper(
            substr($selectedChild->first_name ?? 'S', 0, 1) .
            substr($selectedChild->last_name ?? '', 0, 1)
        )
        : 'ST';

    $classLabel = trim(
        ($childEnrollment->class_name ?? 'Class not assigned') .
        (!empty($childEnrollment->section_name)
            ? ' · '.$childEnrollment->section_name
            : '')
    );

    /* [title, subtitle, route, icon, tone] */
    $quickLinks = [
        ['Attendance', 'View attendance records', 'attendance.index', 'attendance', 'sky'],
        ['Fee Details', 'Invoices and payment history', 'fees.dashboard', 'fees', 'warning'],
        ['Examinations', 'Exam schedule and results', 'examinations.index', 'exams', 'purple'],
        ['Assignments', 'Homework and submissions', 'assignments.index', 'tasks', 'success'],
        ['School Notices', 'Announcements and circulars', 'communication.index', 'notices', 'teal'],
        ['Events Calendar', 'School events and activities', 'events.dashboard', 'events', 'navy'],
        ['Parent–Teacher Meetings', 'PTM schedule and history', 'ptm.dashboard', 'ptm', 'sky'],
        ['CASPA Chat', 'Ask questions about school services', 'caspa-chat.index', 'chat', 'purple'],
    ];

    $attendanceValue = $childPerformance['attendance'] ?? null;
    $marksValue = $childPerformance['marks'] ?? null;
    $homeworkValue = $childPerformance['homework'] ?? null;
    $engagementValue = $childPerformance['engagement'] ?? null;

    $performanceRows = [
        ['Average Attendance', $attendanceValue],
        ['Average Marks', $marksValue],
        ['Homework Completion', $homeworkValue],
        ['Student Engagement', $engagementValue],
    ];

    $examCollection = collect($childUpcomingExams);
    $documentCollection = collect($childDocuments);
    $borrowedCollection = collect($childBorrowedBooks);
    $achievementCollection = collect($childAchievements);
    $trendCollection = collect($childAttendanceTrend);

    $attendanceChartLabels = $trendCollection->map(function ($item) {
        return data_get($item, 'label', data_get($item, 'week', ''));
    })->values();

    $attendanceChartValues = $trendCollection->map(function ($item) {
        return (float) data_get($item, 'percentage', data_get($item, 'rate', 0));
    })->values();

    $transportDetails = is_array($childTransport)
        ? $childTransport
        : (is_object($childTransport) ? (array) $childTransport : []);

    $libraryAvailable = $childLibraryStats['available'] ?? null;
    $libraryTotal = $childLibraryStats['total'] ?? null;
    $libraryRate = $childLibraryStats['availability_rate'] ?? null;

    if ($libraryRate === null && $libraryAvailable !== null && $libraryTotal > 0) {
        $libraryRate = round(($libraryAvailable / $libraryTotal) * 100);
    }

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    /* ---------- Interactive 3D icons (icons8 "3d-fluency" set) ---------- */
    $fallbackPath = '<path d="m12 3 9 5-9 5-9-5Z"/><path d="m3 13 9 5 9-5"/>';

    $slugs = [
        'parent'     => 'family',
        'attendance' => 'calendar',
        'academic'   => 'graduation-cap',
        'fees'       => 'money-bag',
        'tasks'      => 'books',
        'exams'      => 'document',
        'notices'    => 'bell',
        'events'     => 'star',
        'ptm'        => 'conference-call',
        'chat'       => 'bot',
        'doc'        => 'document',
        'book'       => 'book',
        'bus'        => 'bus',
    ];

    $ramp = [
        'sky'     => ['#7DB5FF', '#3B82F6', '#1D4ED8'],
        'teal'    => ['#46B3D1', '#176B87', '#0E4A5E'],
        'success' => ['#5BE08F', '#16A34A', '#0F7A36'],
        'warning' => ['#FCC24A', '#D97706', '#9A5204'],
        'purple'  => ['#9C83E2', '#5B3FA3', '#3E2A78'],
        'navy'    => ['#3F7CAE', '#123B5D', '#0A2740'],
    ];

    $tile = function (string $name, string $tone = 'sky', string $size = 'md') use ($ramp, $slugs, $fallbackPath) {
        [$a, $b, $c] = $ramp[$tone] ?? $ramp['sky'];
        $px    = ['xs' => 16, 'sm' => 18, 'md' => 24, 'lg' => 32][$size] ?? 24;
        $slug  = $slugs[$name] ?? null;
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $px . '" height="' . $px . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $fallbackPath . '</svg>';

        $img = $slug
            ? '<img class="sa-3d-img" src="https://img.icons8.com/3d-fluency/188/' . $slug . '.png" alt="" loading="lazy" draggable="false" onerror="this.closest(\'.sa-3d\').classList.add(\'fb\')">'
            : '';

        $fb = '<span class="sa-ico" style="--c1:' . $a . ';--c2:' . $b . ';--c3:' . $c . '">' . $svg . '</span>';

        return '<span class="sa-3d ' . $size . ($slug ? '' : ' fb') . '"><span class="sa-3d-in" style="animation-delay:' . $delay . '">' . $img . $fb . '</span></span>';
    };
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.parent-dash {
    --navy:#123B5D; --teal:#176B87; --purple:#5B3FA3; --sky:#3B82F6;
    --green:#16A34A; --amber:#D97706; --red:#DC2626;
    --line:#CBD5E1; --surface:#F8FAFC; --ink:#25313C; --muted:#64748B;

    font-family:Inter,system-ui,sans-serif;
    color:var(--ink);
    display:flex; flex-direction:column; gap:20px;
    width:100%; max-width:1500px; margin:0 auto;
}
.parent-dash * { box-sizing:border-box; }
.parent-dash h1,.parent-dash h2,.parent-dash h3,.parent-dash p { margin:0; }
.parent-dash svg { display:block; flex-shrink:0; }
.parent-dash a { text-decoration:none; color:inherit; }

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
.sa-3d.xs::after{ bottom:-5px; height:6px; }
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

/* ---------- Welcome ---------- */
.pd-head{
    display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap;
    padding:26px 30px; border-radius:18px; border:1px solid #DCE8F7;
    background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%);
}
.pd-eyebrow{ display:flex; align-items:center; gap:8px; font-size:11px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:var(--teal); margin-bottom:6px; }
.pd-eyebrow i{ width:4px; height:4px; border-radius:50%; background:var(--teal); display:inline-block; }
.pd-head h1{ font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-.02em; }
.pd-head p{ margin-top:6px; font-size:14px; color:var(--muted); }
.pd-head-side{ display:flex; align-items:center; gap:26px; }
.pd-date{ text-align:right; font-size:13px; color:var(--muted); }
.pd-date strong{ display:block; font-size:24px; font-weight:800; color:var(--navy); margin-top:2px; }

.pd-btn{ display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:36px; padding:0 14px; border-radius:10px; border:1px solid var(--line); background:#fff; color:var(--navy) !important; font-size:12px; font-weight:700; cursor:pointer; transition:.15s; }
.pd-btn:hover{ border-color:var(--teal); }
.pd-btn.primary{ background:var(--purple); border-color:var(--purple); color:#fff !important; box-shadow:0 4px 0 #3E2A78; }
.pd-btn.primary:hover{ background:#4B3490; transform:translateY(-1px); }

/* ---------- Child bar ---------- */
.pd-childbar{ display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap; background:#fff; border:1px solid var(--line); border-radius:16px; padding:16px 20px; }
.pd-child-info{ display:flex; align-items:center; gap:14px; min-width:230px; }
.pd-avatar{ width:52px; height:52px; border-radius:16px; display:flex; align-items:center; justify-content:center; background:linear-gradient(140deg,#DCEBFA,#D9F2F0); color:var(--navy); font-size:17px; font-weight:800; overflow:hidden; }
.pd-avatar img{ width:100%; height:100%; object-fit:cover; }
.pd-child-info b{ display:block; color:var(--navy); font-size:15px; }
.pd-child-info small{ display:block; color:var(--muted); font-size:12px; margin-top:4px; }
.pd-selector{ display:flex; align-items:center; gap:9px; flex-wrap:wrap; }
.pd-selector label{ font-size:12px; color:var(--muted); font-weight:700; }
.pd-select{ min-width:220px; height:40px; padding:0 34px 0 12px; border:1px solid var(--line); border-radius:10px; background:#fff; color:var(--navy); font:600 12px Inter,sans-serif; }

/* ---------- KPIs ---------- */
.pd-kpis{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
.pd-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--line); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; min-width:0; }
.pd-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
.pd-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
.pd-kpi strong{ display:block; font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; }
.pd-kpi small{ font-size:12px; color:var(--muted); }

/* ---------- Panels ---------- */
.pd-grid{ display:grid; grid-template-columns:minmax(0,1.2fr) minmax(280px,.8fr); gap:16px; align-items:stretch; }
.pd-grid-three{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:16px; align-items:stretch; }
.pd-panel{ background:#fff; border:1px solid var(--line); border-radius:16px; padding:20px; min-width:0; }
.pd-panel h2{ font-size:15px; color:var(--navy); font-weight:700; margin-bottom:13px; }
.pd-panel-head{ display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:14px; }
.pd-panel-head h2{ margin:0; }
.pd-muted{ font-size:12px; color:var(--muted); }
.pd-panel-link{ font-size:12px; font-weight:600; color:var(--teal); white-space:nowrap; }

.pd-progress{ height:10px; background:#E2E8F0; border-radius:999px; overflow:hidden; margin:10px 0 8px; }
.pd-progress span{ display:block; height:100%; background:linear-gradient(90deg,var(--sky),var(--teal)); border-radius:999px; }
.pd-statline{ display:flex; justify-content:space-between; gap:8px; font-size:13px; color:var(--muted); }
.pd-statline b{ color:var(--navy); }
.pd-mini-grid{ display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; margin-top:16px; }
.pd-mini{ padding:14px; background:var(--surface); border:1px solid #E2E8F0; border-radius:12px; }
.pd-mini b{ display:block; color:var(--navy); font-size:22px; font-weight:800; }
.pd-mini span{ display:block; color:var(--muted); font-size:12px; margin-top:3px; }

/* Quick access */
.pd-shortcuts{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
.pd-shortcut{ display:flex; flex-direction:column; align-items:center; text-align:center; gap:4px; padding:16px 10px 14px; border:1px solid var(--line); background:var(--surface); border-radius:14px; transition:.15s; cursor:pointer; }
.pd-shortcut:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); }
.pd-shortcut b{ font-size:13px; color:var(--navy); font-weight:600; }
.pd-shortcut span.sub{ font-size:11px; color:var(--muted); line-height:1.4; }

/* Rows */
.pd-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid #EEF2F6; }
.pd-row:last-child{ border-bottom:0; padding-bottom:0; }
.pd-row b{ display:block; font-size:13px; color:var(--navy); font-weight:600; }
.pd-row small{ display:block; font-size:12px; color:var(--muted); margin-top:3px; }

.pd-badge{ display:inline-flex; padding:3px 10px; border-radius:999px; background:#D5EBF2; color:var(--teal); font-size:11px; font-weight:700; white-space:nowrap; }
.pd-badge.green{ background:#DCFCE7; color:var(--green); }
.pd-badge.amber{ background:#FEF3C7; color:var(--amber); }
.pd-badge.red{ background:#FEE2E2; color:var(--red); }
.pd-empty{ padding:8px 0; color:var(--muted); font-size:13px; line-height:1.6; }

/* Tables */
.pd-table-wrap{ overflow-x:auto; }
.pd-table{ width:100%; border-collapse:collapse; min-width:450px; }
.pd-table th{ text-align:left; background:var(--surface); color:var(--muted); font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; padding:10px 12px; border-bottom:1px solid var(--line); }
.pd-table th:first-child{ border-radius:10px 0 0 10px; }
.pd-table th:last-child{ border-radius:0 10px 10px 0; }
.pd-table td{ font-size:13px; padding:12px; border-bottom:1px solid #EEF2F6; color:var(--ink); }
.pd-table tr:last-child td{ border-bottom:0; }

/* Attendance days */
.pd-days{ display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:8px; }
.pd-day{ border:1px solid #E2E8F0; background:var(--surface); border-radius:10px; padding:10px 4px; text-align:center; }
.pd-day b{ display:block; font-size:15px; color:var(--navy); }
.pd-day span{ display:block; font-size:10px; color:var(--muted); margin-top:4px; }
.pd-day.present{ background:#F0FDF4; border-color:#BBF7D0; }
.pd-day.absent{ background:#FEF2F2; border-color:#FECACA; }
.pd-day.late{ background:#FFFBEB; border-color:#FDE68A; }

/* Exams */
.pd-exam-card{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:12px; background:var(--surface); border-radius:12px; margin-bottom:8px; border:1px solid #EEF2F6; }
.pd-exam-card:last-child{ margin-bottom:0; }
.pd-exam-card b{ display:block; font-size:13px; color:var(--navy); }
.pd-exam-card small{ display:block; font-size:12px; color:var(--muted); margin-top:3px; }
.pd-exam-date{ color:var(--sky); font-size:15px; font-weight:800; white-space:nowrap; }

/* Documents */
.pd-doc-item{ display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #EEF2F6; }
.pd-doc-item:last-child{ border-bottom:0; }
.pd-doc-item .sa-3d{ margin-bottom:0; }
.pd-doc-item b{ display:block; font-size:13px; color:var(--navy); }
.pd-doc-item small{ display:block; font-size:12px; color:var(--muted); margin-top:3px; }
.pd-doc-item a.open{ margin-left:auto; font-size:12px; color:var(--teal); font-weight:700; }

/* Performance */
.pd-performance-row{ margin:16px 0; }
.pd-performance-row:first-of-type{ margin-top:6px; }
.pd-performance-label{ display:flex; justify-content:space-between; gap:10px; font-size:13px; color:var(--ink); font-weight:600; }
.pd-performance-label strong{ color:var(--teal); }
.pd-performance-track{ height:10px; background:#E2E8F0; border-radius:999px; overflow:hidden; margin-top:7px; }
.pd-performance-track span{ display:block; height:100%; background:linear-gradient(90deg,var(--sky),var(--teal)); border-radius:999px; }

/* Transport */
.pd-transport-card{ background:#E8F6EF; border-radius:12px; padding:14px; display:flex; align-items:center; gap:14px; }
.pd-transport-card .sa-3d{ margin-bottom:0; }
.pd-transport-route{ font-size:14px; font-weight:700; color:#244C3B; }
.pd-transport-card small{ display:block; color:var(--muted); font-size:12px; margin-top:4px; }
.pd-transport-meta{ display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px; }
.pd-transport-meta div{ background:var(--surface); padding:12px; border:1px solid #E2E8F0; border-radius:12px; }
.pd-transport-meta small{ display:block; color:var(--muted); font-size:12px; }
.pd-transport-meta b{ display:block; color:var(--navy); font-size:13px; margin-top:4px; }

/* Library */
.pd-library-layout{ display:grid; grid-template-columns:minmax(0,1.4fr) minmax(130px,.6fr); gap:16px; }
.pd-book{ display:flex; align-items:center; gap:10px; padding:9px 0; border-bottom:1px solid #EEF2F6; }
.pd-book:last-child{ border-bottom:0; }
.pd-book-number{ font-size:11px; color:#94A3B8; }
.pd-book .sa-3d{ margin-bottom:0; }
.pd-book-info{ flex:1; min-width:0; }
.pd-book-info b{ display:block; font-size:13px; color:var(--navy); }
.pd-book-info small{ display:block; font-size:12px; color:var(--muted); margin-top:3px; }
.pd-library-meter{ border-left:1px solid #E2E8F0; padding-left:16px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.pd-donut{ width:108px; height:108px; border-radius:50%; display:grid; place-items:center; background:conic-gradient(#176B87 var(--rate,0%),#E2E8F0 0); position:relative; }
.pd-donut:before{ content:""; position:absolute; inset:11px; background:#fff; border-radius:50%; }
.pd-donut-content{ position:relative; text-align:center; }
.pd-donut-content b{ display:block; font-size:20px; color:var(--navy); font-weight:800; }
.pd-donut-content small{ display:block; font-size:10px; color:var(--green); }

.pd-chart-wrap{ height:210px; position:relative; }
.pd-trend-chart{height:210px;display:flex;gap:9px;padding:10px 2px 0;min-width:0}
.pd-trend-ylabels{width:34px;flex:0 0 34px;display:flex;flex-direction:column;justify-content:space-between;padding:0 0 25px;color:#94A3B8;font-size:9px;text-align:right}
.pd-trend-plot{position:relative;flex:1;min-width:0;height:100%;padding-bottom:25px}
.pd-trend-gridline{position:absolute;left:0;right:0;border-top:1px dashed #E2E8F0;pointer-events:none}
.pd-trend-gridline.g100{top:0}.pd-trend-gridline.g75{top:18.75%}.pd-trend-gridline.g50{top:37.5%}.pd-trend-gridline.g25{top:56.25%}.pd-trend-gridline.g0{top:75%;border-top-style:solid}
.pd-trend-columns{position:absolute;inset:0 0 0;display:flex;align-items:stretch;justify-content:space-around;gap:8px}
.pd-trend-column{position:relative;z-index:1;flex:1;min-width:0;display:flex;flex-direction:column;align-items:center;justify-content:flex-end}
.pd-trend-value{height:16px;font-size:9px;font-weight:700;color:#64748B;white-space:nowrap}
.pd-trend-bar-track{height:calc(100% - 41px);width:min(26px,65%);display:flex;align-items:flex-end;justify-content:center}
.pd-trend-bar{width:100%;min-height:2px;border-radius:5px 5px 2px 2px;background:linear-gradient(180deg,#3B82F6,#176B87);box-shadow:0 2px 5px rgba(23,107,135,.14)}
.pd-trend-label{height:25px;padding-top:7px;font-size:9px;color:#64748B;white-space:nowrap}
@media(max-width:480px){.pd-trend-columns{gap:3px}.pd-trend-label{font-size:8px}.pd-trend-value{font-size:8px}}
.pd-achievement{ display:grid; grid-template-columns:70px 1fr; gap:10px; padding:11px 0; border-bottom:1px solid #EEF2F6; }
.pd-achievement:last-child{ border-bottom:0; }
.pd-achievement-year{ font-size:12px; color:var(--muted); }
.pd-achievement b{ display:block; font-size:13px; color:var(--navy); }
.pd-achievement small{ display:block; font-size:12px; color:var(--muted); margin-top:3px; }

/* AI panel */
.pd-ai{ display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; padding:20px 26px; border-radius:18px; border:1px solid #DDD6FE; background:linear-gradient(100deg,#EDE9FE,#F5F3FF); }
.pd-ai-lead{ display:flex; align-items:center; gap:18px; }
.pd-ai-lead .sa-3d{ margin-bottom:8px; }
.pd-ai h2{ font-size:17px; color:var(--purple); font-weight:700; margin:0; }
.pd-ai p{ font-size:13px; color:var(--muted); margin-top:4px; max-width:640px; }

.pd-empty-state{ padding:30px; text-align:center; border:1px dashed var(--line); border-radius:16px; background:#fff; }
.pd-empty-state b{ display:block; color:var(--navy); font-size:16px; }
.pd-empty-state p{ font-size:13px; color:var(--muted); margin:7px 0 0; }
.pd-foot{ font-size:11px; color:var(--muted); text-align:center; }

@media(max-width:1280px){
    .pd-grid-three{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .pd-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:900px){
    .pd-grid,.pd-grid-three{ grid-template-columns:1fr; }
    .pd-shortcuts{ grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:650px){
    .pd-head{ padding:20px; }
    .pd-head h1{ font-size:22px; }
    .pd-head-side{ width:100%; justify-content:space-between; }
    .pd-kpis{ grid-template-columns:1fr; }
    .pd-selector{ width:100%; }
    .pd-select{ flex:1; min-width:0; }
    .pd-library-layout{ grid-template-columns:1fr; }
    .pd-library-meter{ border-left:0; border-top:1px solid #E2E8F0; padding:16px 0 0; }
}
</style>

<div class="parent-dash">

    {{-- WELCOME --}}
    <section class="pd-head" data-tilt>
        <div>
            <div class="pd-eyebrow">{{ $greet }} <i></i> Parent Portal</div>
            <h1>Welcome, {{ auth()->user()->name ?? 'Parent' }}!</h1>
            <p>One place to follow your child's learning, wellbeing and school journey.</p>
        </div>
        <div class="pd-head-side">
            {!! $tile('parent', 'sky', 'lg') !!}
            <div class="pd-date">
                {{ now()->format('l, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>
        </div>
    </section>

    @if(($childData ?? collect())->isNotEmpty())
        @foreach($childData as $childRecord)
            @php
                $selectedChild = $childRecord['student'];
                $childEnrollment = $childRecord['enrollment'];
                $childAttendance = $childRecord['attendance'];
                $childFees = $childRecord['fees'];
                $childAcademic = $childRecord['academic'];
                $childRecentResults = $childRecord['recentResults'];
                $childAssignments = $childRecord['assignments'];
                $childAnnouncements = $childRecord['announcements'];
                $childEvents = $childRecord['events'];
                $childPayments = $childRecord['payments'];
                $childAttendanceDays = $childRecord['attendanceDays'];
                $childUpcomingExams = $childRecord['upcomingExams'];
                $childDocuments = $childRecord['documents'];
                $childTransport = $childRecord['transport'];
                $childBorrowedBooks = $childRecord['borrowedBooks'];
                $childLibraryStats = $childRecord['libraryStats'];
                $childAchievements = $childRecord['achievements'];
                $childAttendanceTrend = $childRecord['attendanceTrend'];
                $childPerformance = $childRecord['performance'];
                $fullName = trim(collect([$selectedChild->first_name ?? '', $selectedChild->middle_name ?? '', $selectedChild->last_name ?? ''])->filter()->implode(' '));
                $initials = strtoupper(substr($selectedChild->first_name ?? 'S', 0, 1).substr($selectedChild->last_name ?? '', 0, 1));
                $classLabel = trim(($childEnrollment->class_name ?? 'Class not assigned').(!empty($childEnrollment->section_name) ? ' · '.$childEnrollment->section_name : ''));
                $attendanceValue = $childPerformance['attendance'] ?? null;
                $marksValue = $childPerformance['marks'] ?? null;
                $homeworkValue = $childPerformance['homework'] ?? null;
                $engagementValue = $childPerformance['engagement'] ?? null;
                $performanceRows = [['Average Attendance',$attendanceValue],['Average Marks',$marksValue],['Homework Completion',$homeworkValue],['Student Engagement',$engagementValue]];
                $examCollection = collect($childUpcomingExams);
                $documentCollection = collect($childDocuments);
                $borrowedCollection = collect($childBorrowedBooks);
                $achievementCollection = collect($childAchievements);
                $trendCollection = collect($childAttendanceTrend);
                $attendanceChartLabels = $trendCollection->map(fn($item) => data_get($item,'label',data_get($item,'week','')))->values();
                $attendanceChartValues = $trendCollection->map(fn($item) => (float)data_get($item,'percentage',data_get($item,'rate',0)))->values();
                $transportDetails = is_array($childTransport) ? $childTransport : (is_object($childTransport) ? (array)$childTransport : []);
                $libraryAvailable = $childLibraryStats['available'] ?? null;
                $libraryTotal = $childLibraryStats['total'] ?? null;
                $libraryRate = $childLibraryStats['availability_rate'] ?? null;
                if ($libraryRate === null && $libraryAvailable !== null && $libraryTotal > 0) $libraryRate = round(($libraryAvailable / $libraryTotal) * 100);
            @endphp

            {{-- CHILD PROFILE --}}
            <section class="pd-childbar">
                <div class="pd-child-info">
                    <div class="pd-avatar">
                        @if(!empty($selectedChild->photo_path))
                            <img src="{{ asset('storage/'.$selectedChild->photo_path) }}" alt="{{ $fullName }}">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div>
                        <b>{{ $fullName }}</b>
                        <small>{{ $classLabel }} · Admission No: {{ $selectedChild->admission_number ?: '—' }}</small>
                    </div>
                </div>
                <span class="pd-badge green">Linked to your account</span>
            </section>

        {{-- KPI CARDS --}}
        <section class="pd-kpis">
            <article class="pd-kpi" data-tilt>
                {!! $tile('attendance', 'teal') !!}
                <div>
                    <div class="pd-kpi-label">Attendance rate</div>
                    <strong>{{ number_format((float)$childAttendance['rate'],1) }}%</strong>
                    <small>{{ $childAttendance['present'] }} present · {{ $childAttendance['absent'] }} absent</small>
                </div>
            </article>

            <article class="pd-kpi" data-tilt>
                {!! $tile('academic', 'success') !!}
                <div>
                    <div class="pd-kpi-label">Academic average</div>
                    <strong>
                        {{ $childAcademic['average'] !== null ? number_format((float)$childAcademic['average'],1).'%' : '—' }}
                    </strong>
                    <small>Based on published exam results</small>
                </div>
            </article>

            <article class="pd-kpi" data-tilt>
                {!! $tile('fees', 'warning') !!}
                <div>
                    <div class="pd-kpi-label">Fee balance</div>
                    <strong>{{ $money($childFees['balance']) }}</strong>
                    <small>{{ $childFees['overdue'] }} overdue invoice(s)</small>
                </div>
            </article>

            <article class="pd-kpi" data-tilt>
                {!! $tile('tasks', 'purple') !!}
                <div>
                    <div class="pd-kpi-label">Learning tasks</div>
                    <strong>{{ (int)$childAcademic['pending_assignments'] }}</strong>
                    <small>{{ (int)$childAcademic['upcoming_exams'] }} upcoming exam(s)</small>
                </div>
            </article>
        </section>

        {{-- ATTENDANCE AND FEES --}}
        <section class="pd-grid">
            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Attendance Overview</h2>
                    <span class="pd-badge {{ $childAttendance['rate'] >= 75 ? 'green' : 'amber' }}">
                        {{ $childAttendance['rate'] >= 75 ? 'On track' : 'Needs attention' }}
                    </span>
                </div>

                <div class="pd-statline">
                    <span>Overall attendance</span>
                    <b>{{ number_format((float)$childAttendance['rate'],1) }}%</b>
                </div>
                <div class="pd-progress">
                    <span style="width:{{ min(100,max(0,(float)$childAttendance['rate'])) }}%"></span>
                </div>

                <div class="pd-mini-grid">
                    <div class="pd-mini"><b>{{ $childAttendance['present'] }}</b><span>Present</span></div>
                    <div class="pd-mini"><b>{{ $childAttendance['absent'] }}</b><span>Absent</span></div>
                    <div class="pd-mini"><b>{{ $childAttendance['late'] }}</b><span>Late</span></div>
                </div>

                <div style="margin-top:18px">
                    <div class="pd-panel-head">
                        <h2>Recent attendance</h2>
                        <span class="pd-muted">Latest 7 entries</span>
                    </div>

                    @if($childAttendanceDays->isNotEmpty())
                        <div class="pd-days">
                            @foreach($childAttendanceDays as $day)
                                @php
                                    $dayStatus = strtolower((string)$day->status);
                                    $dayClass = in_array($dayStatus,['present']) ? 'present'
                                        : (in_array($dayStatus,['absent']) ? 'absent'
                                        : (in_array($dayStatus,['late']) ? 'late' : ''));
                                @endphp
                                <div class="pd-day {{ $dayClass }}">
                                    <b>{{ \Carbon\Carbon::parse($day->attendance_date)->format('d') }}</b>
                                    <span>{{ \Carbon\Carbon::parse($day->attendance_date)->format('D') }} · {{ ucfirst($dayStatus) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="pd-empty">No attendance entries are available yet.</div>
                    @endif
                </div>
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Fee Summary</h2>
                    @if(\Illuminate\Support\Facades\Route::has('fees.dashboard'))
                        <a class="pd-panel-link" href="{{ route('fees.dashboard') }}">View fees →</a>
                    @endif
                </div>

                <div class="pd-statline"><span>Total invoiced</span><b>{{ $money($childFees['total']) }}</b></div>
                <div class="pd-statline" style="margin-top:12px">
                    <span>Paid</span><b style="color:#15803D">{{ $money($childFees['paid']) }}</b>
                </div>
                <div class="pd-progress">
                    <span style="width:{{ $childFees['total'] > 0 ? min(100,($childFees['paid']/$childFees['total'])*100) : 0 }}%;background:linear-gradient(90deg,#16A34A,#4ADE80)"></span>
                </div>
                <div class="pd-statline"><span>Outstanding</span><b style="color:#B45309">{{ $money($childFees['balance']) }}</b></div>
                <div class="pd-statline" style="margin-top:10px"><span>Overdue invoices</span><b>{{ $childFees['overdue'] }}</b></div>

                <div style="margin-top:18px">
                    <h2>Recent payments</h2>
                    @forelse($childPayments as $payment)
                        <div class="pd-row">
                            <div>
                                <b>{{ $payment->receipt_number ?: 'Payment' }}</b>
                                <small>
                                    {{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y') : 'Date unavailable' }}
                                    · {{ $payment->payment_method ?: '—' }}
                                </small>
                            </div>
                            <b>{{ $money($payment->amount) }}</b>
                        </div>
                    @empty
                        <div class="pd-empty">No payment records available.</div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- QUICK ACCESS --}}
        <section class="pd-panel">
            <div class="pd-panel-head">
                <h2>Quick Access</h2>
                <span class="pd-muted">Services for this child</span>
            </div>
            <div class="pd-shortcuts">
                @foreach($quickLinks as $link)
                    @if(\Illuminate\Support\Facades\Route::has($link[2]))
                        <a class="pd-shortcut" href="{{ route($link[2]) }}" data-tilt>
                            {!! $tile($link[3], $link[4], 'sm') !!}
                            <b>{{ $link[0] }}</b>
                            <span class="sub">{{ $link[1] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </section>

        {{-- UPCOMING EXAMS, DOCUMENTS, PERFORMANCE --}}
        <section class="pd-grid-three">

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Upcoming Exams</h2>
                    @if(\Illuminate\Support\Facades\Route::has('examinations.index'))
                        <a class="pd-panel-link" href="{{ route('examinations.index') }}">View All →</a>
                    @endif
                </div>

                @forelse($examCollection as $exam)
                    @php
                        $examDate = data_get($exam,'exam_date',data_get($exam,'start_date'));
                        $examTime = data_get($exam,'start_time');
                    @endphp
                    <div class="pd-exam-card">
                        <div>
                            <b>{{ data_get($exam,'subject_name',data_get($exam,'name','Examination')) }}</b>
                            <small>
                                {{ $examTime ? \Carbon\Carbon::parse($examTime)->format('h:i A') : 'Time not announced' }}
                                @if(data_get($exam,'room_number')) · Room {{ data_get($exam,'room_number') }} @endif
                            </small>
                        </div>
                        <div class="pd-exam-date">
                            {{ $examDate ? \Carbon\Carbon::parse($examDate)->format('d M') : 'TBA' }}
                        </div>
                    </div>
                @empty
                    <div class="pd-empty">Upcoming examination details will appear here when published by the school.</div>
                @endforelse
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Documents</h2>
                    @if(\Illuminate\Support\Facades\Route::has('certificates.index'))
                        <a class="pd-panel-link" href="{{ route('certificates.index') }}">View All →</a>
                    @endif
                </div>

                @forelse($documentCollection as $document)
                    <div class="pd-doc-item">
                        {!! $tile('doc', 'sky', 'xs') !!}
                        <div>
                            <b>{{ data_get($document,'title',data_get($document,'name','Document')) }}</b>
                            <small>
                                {{ data_get($document,'created_at') ? \Carbon\Carbon::parse(data_get($document,'created_at'))->format('d M, h:i A') : 'School document' }}
                            </small>
                        </div>
                        @if(data_get($document,'url'))
                            <a class="open" href="{{ data_get($document,'url') }}" target="_blank" rel="noopener">Open</a>
                        @elseif(data_get($document,'path'))
                            <a class="open" href="{{ asset('storage/'.data_get($document,'path')) }}" target="_blank" rel="noopener">Open</a>
                        @endif
                    </div>
                @empty
                    <div class="pd-empty">Student ID cards, academic reports and certificates will appear here when available.</div>
                @endforelse
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Child's Performance</h2>
                    <span class="pd-muted">Progress overview</span>
                </div>

                @foreach($performanceRows as $performance)
                    @php
                        $performanceValue = is_numeric($performance[1]) ? max(0,min(100,(float)$performance[1])) : null;
                    @endphp
                    <div class="pd-performance-row">
                        <div class="pd-performance-label">
                            <span>{{ $performance[0] }}</span>
                            <strong>{{ $performanceValue !== null ? number_format($performanceValue,0).'%' : '—' }}</strong>
                        </div>
                        <div class="pd-performance-track">
                            <span style="width:{{ $performanceValue ?? 0 }}%"></span>
                        </div>
                    </div>
                @endforeach

                <div class="pd-muted" style="margin-top:12px">
                    Performance indicators are calculated from available student records.
                </div>
            </div>

        </section>

        {{-- TRANSPORT AND LIBRARY --}}
        <section class="pd-grid">

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Transport</h2>
                    @if(\Illuminate\Support\Facades\Route::has('transport.dashboard'))
                        <a class="pd-panel-link" href="{{ route('transport.dashboard') }}">View Details →</a>
                    @endif
                </div>

                @if(!empty($transportDetails))
                    <div class="pd-transport-card">
                        {!! $tile('bus', 'success', 'sm') !!}
                        <div>
                            <div class="pd-transport-route">
                                {{ $transportDetails['route_name'] ?? 'Assigned route' }}
                            </div>
                            <small>{{ $transportDetails['onboard_count'] ?? '—' }} onboard</small>
                            <small>Pickup: {{ $transportDetails['pickup_time'] ?? 'Not provided' }}</small>
                            <small>
                                Vehicle: {{ $transportDetails['registration_number'] ?? $transportDetails['vehicle_number'] ?? 'Not assigned' }}
                            </small>
                        </div>
                    </div>
                    <div class="pd-transport-meta">
                        <div>
                            <small>Driver</small>
                            <b>{{ $transportDetails['driver_name'] ?? 'Not provided' }}</b>
                        </div>
                        <div>
                            <small>Driver contact</small>
                            <b>{{ $transportDetails['driver_phone'] ?? 'Not provided' }}</b>
                        </div>
                    </div>
                @else
                    <div class="pd-empty">
                        Transport route and vehicle information will appear here if the student is assigned to school transport.
                    </div>
                @endif
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Library Overview</h2>
                    @if(\Illuminate\Support\Facades\Route::has('library.dashboard'))
                        <a class="pd-panel-link" href="{{ route('library.dashboard') }}">View Library →</a>
                    @endif
                </div>

                <div class="pd-library-layout">
                    <div>
                        <div class="pd-panel-head">
                            <h2 style="font-size:13px">Borrowed Books</h2>
                            <span class="pd-muted">Current loans</span>
                        </div>

                        @forelse($borrowedCollection as $index => $book)
                            <div class="pd-book">
                                <span class="pd-book-number">{{ str_pad($index + 1,2,'0',STR_PAD_LEFT) }}</span>
                                {!! $tile('book', 'sky', 'xs') !!}
                                <div class="pd-book-info">
                                    <b>{{ data_get($book,'title','Library book') }}</b>
                                    <small>
                                        Due
                                        {{ data_get($book,'due_at') ? \Carbon\Carbon::parse(data_get($book,'due_at'))->format('d M') : 'date unavailable' }}
                                    </small>
                                </div>
                            </div>
                        @empty
                            <div class="pd-empty">No books are currently borrowed by this student.</div>
                        @endforelse
                    </div>

                    <div class="pd-library-meter">
                        @if($libraryRate !== null)
                            <div class="pd-donut" style="--rate:{{ max(0,min(100,(float)$libraryRate)) }}%">
                                <div class="pd-donut-content">
                                    <b>{{ number_format((float)$libraryRate,0) }}%</b>
                                    <small>Available</small>
                                </div>
                            </div>
                            <small class="pd-muted" style="margin-top:12px;text-align:center">
                                {{ $libraryAvailable ?? '—' }} / {{ $libraryTotal ?? '—' }} titles
                            </small>
                        @else
                            <div class="pd-donut" style="--rate:0%">
                                <div class="pd-donut-content">
                                    <b>—</b>
                                    <small>Availability</small>
                                </div>
                            </div>
                            <small class="pd-muted" style="margin-top:12px;text-align:center">Library data unavailable</small>
                        @endif
                    </div>
                </div>
            </div>

        </section>

        {{-- EXAM RESULTS AND ASSIGNMENTS --}}
        <section class="pd-grid">
            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Published Exam Results</h2>
                    <span class="pd-muted">This child only</span>
                </div>

                @if($childRecentResults->isNotEmpty())
                    <div class="pd-table-wrap">
                        <table class="pd-table">
                            <thead>
                                <tr>
                                    <th>Examination</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Published</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($childRecentResults as $result)
                                    <tr>
                                        <td>{{ $result->exam_name }}</td>
                                        <td>{{ $result->marks_obtained ?? '—' }} / {{ $result->maximum_marks ?? '—' }}</td>
                                        <td>{{ $result->grade ?: '—' }}</td>
                                        <td>
                                            {{ $result->published_at ? \Carbon\Carbon::parse($result->published_at)->format('d M Y') : '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="pd-empty">Published examination results will appear here.</div>
                @endif
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Upcoming Assignments</h2>
                    <span class="pd-muted">Class work</span>
                </div>

                @forelse($childAssignments as $assignment)
                    <div class="pd-row">
                        <div>
                            <b>{{ $assignment->title ?? 'Assignment' }}</b>
                            <small>
                                {{ !empty($assignment->due_date) ? \Carbon\Carbon::parse($assignment->due_date)->format('D, d M Y') : 'Due date not set' }}
                            </small>
                        </div>
                        <span class="pd-badge">{{ $assignment->status ?? 'Assigned' }}</span>
                    </div>
                @empty
                    <div class="pd-empty">No upcoming assignments found.</div>
                @endforelse
            </div>
        </section>

        {{-- ATTENDANCE TREND, HOMEWORK TABLE, ACHIEVEMENTS --}}
        <section class="pd-grid-three">

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Attendance</h2>
                    <span class="pd-muted">Recent trend</span>
                </div>

                @if($trendCollection->isNotEmpty())
                    <div class="pd-trend-chart" role="img" aria-label="Weekly attendance trend">
                        <div class="pd-trend-ylabels" aria-hidden="true">
                            <span>100%</span>
                            <span>75%</span>
                            <span>50%</span>
                            <span>25%</span>
                            <span>0%</span>
                        </div>
                        <div class="pd-trend-plot">
                            <div class="pd-trend-gridline g100"></div>
                            <div class="pd-trend-gridline g75"></div>
                            <div class="pd-trend-gridline g50"></div>
                            <div class="pd-trend-gridline g25"></div>
                            <div class="pd-trend-gridline g0"></div>
                            <div class="pd-trend-columns">
                                @foreach($trendCollection as $trendItem)
                                    @php
                                        $trendLabel = data_get($trendItem, 'label', data_get($trendItem, 'week', ''));
                                        $trendValue = min(100, max(0, (float) data_get($trendItem, 'percentage', data_get($trendItem, 'rate', 0))));
                                    @endphp
                                    <div class="pd-trend-column" title="{{ $trendLabel }}: {{ number_format($trendValue, 1) }}% attendance">
                                        <div class="pd-trend-value">{{ number_format($trendValue, 0) }}%</div>
                                        <div class="pd-trend-bar-track">
                                            <div class="pd-trend-bar" style="height:{{ max(2, $trendValue) }}%"></div>
                                        </div>
                                        <span class="pd-trend-label">{{ $trendLabel }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @else
                    <div class="pd-empty">
                        Attendance trend will appear when historical attendance records are available.
                    </div>
                @endif
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Homework &amp; Assignments</h2>
                    @if(\Illuminate\Support\Facades\Route::has('assignments.index'))
                        <a class="pd-panel-link" href="{{ route('assignments.index') }}">View All →</a>
                    @endif
                </div>

                @if($childAssignments->isNotEmpty())
                    <div class="pd-table-wrap">
                        <table class="pd-table" style="min-width:390px">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th>Class</th>
                                    <th>Due</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($childAssignments as $assignment)
                                    @php
                                        $assignmentStatus = strtolower((string)($assignment->status ?? 'pending'));
                                        $assignmentBadge = str_contains($assignmentStatus,'submit') ? 'green'
                                            : (str_contains($assignmentStatus,'overdue') ? 'red' : 'amber');
                                    @endphp
                                    <tr>
                                        <td>{{ $assignment->title ?? 'Assignment' }}</td>
                                        <td>{{ $assignment->class_name ?? $classLabel }}</td>
                                        <td>
                                            {{ !empty($assignment->due_date) ? \Carbon\Carbon::parse($assignment->due_date)->format('d M') : '—' }}
                                        </td>
                                        <td><span class="pd-badge {{ $assignmentBadge }}">{{ ucfirst($assignmentStatus) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="pd-empty">Homework and assignment records will appear here.</div>
                @endif
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Achievements</h2>
                    <span class="pd-muted">Awards and activities</span>
                </div>

                @forelse($achievementCollection as $achievement)
                    <div class="pd-achievement">
                        <div class="pd-achievement-year">
                            {{ data_get($achievement,'academic_year',data_get($achievement,'year','—')) }}
                        </div>
                        <div>
                            <b>{{ data_get($achievement,'category',data_get($achievement,'title','Achievement')) }}</b>
                            <small>
                                {{ data_get($achievement,'detail',data_get($achievement,'description','')) }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="pd-empty">
                        Student awards, sports achievements and activity participation will appear here.
                    </div>
                @endforelse
            </div>

        </section>

        {{-- ANNOUNCEMENTS AND EVENTS --}}
        <section class="pd-grid">
            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>School Announcements</h2>
                    @if(\Illuminate\Support\Facades\Route::has('communication.index'))
                        <a class="pd-panel-link" href="{{ route('communication.index') }}">View All →</a>
                    @endif
                </div>

                @forelse($childAnnouncements as $notice)
                    <div class="pd-row">
                        <div>
                            <b>{{ $notice->title }}</b>
                            <small>
                                {{ !empty($notice->date) ? \Carbon\Carbon::parse($notice->date)->format('d M Y, h:i A') : 'School notice' }}
                            </small>
                        </div>
                        <span class="pd-badge">{{ $notice->status ?? 'Published' }}</span>
                    </div>
                @empty
                    <div class="pd-empty">No recent announcements.</div>
                @endforelse
            </div>

            <div class="pd-panel">
                <div class="pd-panel-head">
                    <h2>Upcoming School Events</h2>
                    @if(\Illuminate\Support\Facades\Route::has('events.dashboard'))
                        <a class="pd-panel-link" href="{{ route('events.dashboard') }}">Calendar →</a>
                    @endif
                </div>

                @forelse($childEvents as $event)
                    <div class="pd-row">
                        <div>
                            <b>{{ $event->title }}</b>
                            <small>
                                {{ !empty($event->date) ? \Carbon\Carbon::parse($event->date)->format('D, d M Y · h:i A') : 'Date to be announced' }}
                            </small>
                        </div>
                        <span class="pd-badge">{{ $event->status ?? 'Upcoming' }}</span>
                    </div>
                @empty
                    <div class="pd-empty">No upcoming school events.</div>
                @endforelse
            </div>
        </section>

        @endforeach
    @else

        <section class="pd-empty-state">
            <b>No linked student profile found</b>
            <p>
                Your parent account does not currently have a student linked to it.
                Please contact the school office to connect your guardian profile.
            </p>
        </section>

    @endif

    {{-- CASPA PARENT ASSISTANT --}}
    <section class="pd-ai" data-tilt>
        <div class="pd-ai-lead">
            {!! $tile('chat', 'purple') !!}
            <div>
                <h2>CASPA Parent Assistant</h2>
                <p>Get help understanding school notices, fee processes and parent portal services.</p>
            </div>
        </div>

        @if(\Illuminate\Support\Facades\Route::has('caspa-chat.index'))
            <a class="pd-btn primary" href="{{ route('caspa-chat.index') }}">Open CASPA Chat</a>
        @endif
    </section>

    <div class="pd-foot">
        Parent access is restricted to student profiles linked to your guardian account.
        Figures are based on available school records.
    </div>

</div>

<script>
    /* Interactive 3D icons: tilt toward the mouse, grow on hover, pop on click */
    (function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        document.querySelectorAll('.parent-dash [data-tilt]').forEach(function (card) {
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