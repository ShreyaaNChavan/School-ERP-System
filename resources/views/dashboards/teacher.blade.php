@extends('layouts.schoolnexa')

@section('title', 'Teacher Dashboard')

@section('content')
@php
    use Illuminate\Support\Str;

    $dashboardStats = $stats ?? [];

    $todayTimetable = collect($todayTimetable ?? []);
    $teacherClasses = collect($teacherClasses ?? []);
    $teacherAssignments = collect($teacherAssignments ?? []);
    $teacherExams = collect($teacherExams ?? []);
    $teacherAnnouncements = collect($teacherAnnouncements ?? []);
    $teacherEvents = collect($teacherEvents ?? []);
    $teacherTasks = collect($teacherTasks ?? []);
    $frequentAbsentees = collect($frequentAbsentees ?? []);
    $classPerformance = collect($classPerformance ?? []);
    $gradeDistribution = collect($gradeDistribution ?? []);
    $recentItems = collect($recentItems ?? []);
    $upcomingItems = collect($upcomingItems ?? []);
    $attendanceSummary = $attendanceSummary ?? [];
    $attendanceTrend = collect($attendanceTrend ?? []);
    $teacherProfile = $teacherProfile ?? null;
    $lessonPlanProgress = collect($lessonPlanProgress ?? []);

    $teacherName = auth()->user()->name ?? 'Teacher';
    $firstName = explode(' ', trim($teacherName))[0] ?? 'Teacher';
    $initials = collect(preg_split('/\s+/', trim($teacherName)))->filter()->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');

    $designation = data_get($teacherProfile, 'designation', 'Teacher');
    $department = data_get($teacherProfile, 'department', 'Department not assigned');
    $employeeNumber = data_get($teacherProfile, 'employee_number', 'Not assigned');

    $totalStudents = $dashboardStats['students'] ?? null;
    $todayPeriods = $dashboardStats['today_periods'] ?? null;
    $pendingReviews = $dashboardStats['pending_reviews'] ?? null;
    $pendingAssignments = $dashboardStats['pending_assignments'] ?? null;
    $upcomingExamCount = $dashboardStats['upcoming_exams'] ?? null;
    $completedReviews = $dashboardStats['completed_reviews'] ?? null;
    $assignmentsCreated = $dashboardStats['assignments_created'] ?? null;

    $presentCount = (float) ($attendanceSummary['present'] ?? 0);
    $absentCount = (float) ($attendanceSummary['absent'] ?? 0);
    $lateCount = (float) ($attendanceSummary['late'] ?? 0);
    $attendanceRate = $attendanceSummary['rate'] ?? null;

    $formatNumber = fn($v) => is_numeric($v) ? number_format((float) $v) : '—';
    $formatDate = function ($value, $format = 'd M Y') {
        if (!$value) return '—';
        try { return \Carbon\Carbon::parse($value)->format($format); } catch (\Throwable $e) { return '—'; }
    };
    $statusClass = function ($status) {
        $s = strtolower((string) $status);
        if (str_contains($s, 'complete') || str_contains($s, 'submit') || str_contains($s, 'present') || str_contains($s, 'approved')) return 'green';
        if (str_contains($s, 'absent') || str_contains($s, 'overdue') || str_contains($s, 'reject') || str_contains($s, 'high')) return 'red';
        if (str_contains($s, 'pending') || str_contains($s, 'review') || str_contains($s, 'upcoming') || str_contains($s, 'next')) return 'amber';
        return 'blue';
    };
    $routeFor = fn($name) => ($name && \Illuminate\Support\Facades\Route::has($name)) ? route($name) : null;

    $aiRoute = null;
    foreach (['caspa.teacher-assistant.index', 'caspa-chat.index', 'caspa.chat.index'] as $cand) {
        if (\Illuminate\Support\Facades\Route::has($cand)) { $aiRoute = $cand; break; }
    }

    /* chart data */
    $attendanceLabels = $attendanceTrend->map(fn($i) => data_get($i, 'label', data_get($i, 'date', '')))->values()->all();
    $attendanceValues = $attendanceTrend->map(fn($i) => (float) data_get($i, 'percentage', data_get($i, 'rate', 0)))->values()->all();
    $performanceLabels = $classPerformance->map(fn($i) => data_get($i, 'class_name', data_get($i, 'label', 'Class')))->values()->all();
    $performanceValues = $classPerformance->map(fn($i) => (float) data_get($i, 'average', data_get($i, 'percentage', 0)))->values()->all();
    $gradeLabels = $gradeDistribution->values()->map(fn($i) => data_get($i, 'grade', data_get($i, 'label', 'Grade')))->all();
    $gradeValues = $gradeDistribution->values()->map(fn($i) => (float) data_get($i, 'count', data_get($i, 'total', 0)))->all();
    $reviewValues = [(float) ($completedReviews ?? 0), (float) ($pendingReviews ?? 0)];

    /* 3D clipart (icons8 "3d-fluency") — change a slug to change a picture */
    $slugs = [
        'students' => 'user-group-man-man', 'classes' => 'calendar', 'review' => 'checked-checkbox', 'tasks' => 'clipboard',
        'timetable' => 'clock', 'myclasses' => 'classroom', 'homework' => 'pencil', 'assignments' => 'document', 'attendance' => 'checked-checkbox',
        'materials' => 'books', 'lesson' => 'open-book', 'comm' => 'speech-bubble', 'exams' => 'open-book', 'perf' => 'bar-chart',
        'pie' => 'pie-chart', 'trend' => 'combo-chart', 'notices' => 'megaphone', 'events' => 'star', 'ai' => 'sparkles',
        'hero' => 'school', 'layers' => 'organization', 'alert' => 'high-priority', 'activity' => 'bell', 'syllabus' => 'graduation-cap',
    ];
    $tile = function (string $name, string $size = 'md') use ($slugs) {
        $slug = $slugs[$name] ?? 'organization';
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        return '<span class="sd-3d '.$size.'"><span class="sd-3d-in" style="animation-delay:'.$delay.'"><img class="sd-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.style.display=\'none\';this.parentNode.classList.add(\'fb\')"><b class="sd-fb">'.e(Str::upper(Str::substr($name, 0, 2))).'</b></span></span>';
    };

    /* Initials badge for real records ("Grade 8 A" -> G8, "Algebra Worksheet" -> AW) */
    $inTones = [['#1D4ED8','#DBEAFE'],['#15803D','#DCFCE7'],['#B45309','#FEF3C7'],['#6D28D9','#EDE9FE'],['#0E7490','#CFFAFE'],['#B91C1C','#FEE2E2']];
    $ini = function ($text, $i = 0) use ($inTones) {
        $w = preg_split('/[\s\-_]+/', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY);
        $s = count($w) > 1 ? mb_substr($w[0], 0, 1) . mb_substr($w[1], 0, 1) : mb_substr($w[0] ?? '?', 0, 2);
        [$c, $b] = $inTones[$i % count($inTones)];
        return '<span class="sd-ini" style="--c:'.$c.';--bg:'.$b.'">'.e(Str::upper($s)).'</span>';
    };

    $quickLinks = [
        ['My Timetable',   'timetable',  'timetable.index'],
        ['My Classes',     'myclasses',  'class-sections.index'],
        ['Homework',       'homework',   'homework.index'],
        ['Assignments',    'assignments','assignments.index'],
        ['Attendance',     'attendance', 'attendance.index'],
        ['Study Materials','materials',  'learning-materials.index'],
        ['Lesson Planner', 'lesson',     'lesson-plans.index'],
        ['Communication',  'comm',       'communication.index'],
    ];

    $hour = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpiCards = [
        ['My Students', $formatNumber($totalStudents), 'Students in assigned sections', 'students', is_numeric($totalStudents) ? (float) $totalStudents : null],
        ["Today's Classes", $formatNumber($todayPeriods), 'Scheduled teaching periods', 'classes', is_numeric($todayPeriods) ? (float) $todayPeriods : null],
        ['Pending Reviews', $formatNumber($pendingReviews), 'Student submissions to review', 'review', is_numeric($pendingReviews) ? (float) $pendingReviews : null],
        ['Assignments Due', $formatNumber($pendingAssignments), 'Pending assignment activities', 'tasks', is_numeric($pendingAssignments) ? (float) $pendingAssignments : null],
    ];
@endphp

<div class="teacher-dash">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.teacher-dash{
    --navy:#123B5D; --teal:#176B87; --purple:#5B3FA3; --sky:#3B82F6;
    --success:#16A34A; --warning:#D97706; --danger:#DC2626;
    --surface:#F8FAFC; --border:#CBD5E1; --text:#25313C; --muted:#64748B;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;
    color:var(--text); display:flex; flex-direction:column; gap:18px;
    width:100%; max-width:1500px; margin:0 auto;
}
.teacher-dash *{ box-sizing:border-box; }
.teacher-dash h1,.teacher-dash h2,.teacher-dash h3,.teacher-dash p{ margin:0; }
.teacher-dash a{ text-decoration:none; }

/* 3D icons */
.sd-3d{ position:relative; display:inline-block; flex-shrink:0; margin-bottom:8px; width:64px; height:64px;
    transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1)); transition:transform .18s ease-out; will-change:transform; cursor:pointer; }
.sd-3d.xs{ width:40px; height:40px; margin-bottom:6px; } .sd-3d.sm{ width:48px; height:48px; }
.sd-3d.lg{ width:92px; height:92px; margin-bottom:10px; }
.sd-3d-in{ display:grid; place-items:center; width:100%; height:100%; animation:sdFloat 4.2s ease-in-out infinite; }
.sd-3d-img{ width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none; filter:drop-shadow(0 10px 8px rgba(18,59,93,.28)); }
.sd-3d::after{ content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%; background:rgba(18,59,93,.22); filter:blur(5px); }
.sd-3d.xs::after{ bottom:-5px; height:6px; }
.sd-3d-in .sd-fb{ display:none; width:80%; height:80%; border-radius:30%; place-items:center; color:#fff; font-size:12px; font-weight:800; background:linear-gradient(150deg,#7DB5FF,#3B82F6 55%,#1D4ED8); box-shadow:0 4px 0 #1D4ED8; }
.sd-3d-in.fb .sd-fb{ display:grid; }
.sd-3d.pop .sd-3d-in{ animation:sdPop .55s cubic-bezier(.3,1.6,.5,1), sdFloat 4.2s ease-in-out infinite .55s; }
@keyframes sdFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-5px); } }
@keyframes sdPop{ 0%{ transform:scale(1); } 35%{ transform:scale(1.28) rotate(-6deg); } 70%{ transform:scale(.94) rotate(3deg); } 100%{ transform:scale(1); } }
@@media (prefers-reduced-motion:reduce){ .sd-3d-in{ animation:none !important; } .sd-3d{ transition:none; } }

/* initials */
.sd-ini{ width:42px; height:42px; flex:0 0 auto; border-radius:12px; display:grid; place-items:center; font-size:13px; font-weight:800; letter-spacing:.03em; color:var(--c,#1D4ED8); background:var(--bg,#DBEAFE); }

/* banner */
.sd-welcome{ display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; padding:26px 30px; border-radius:18px; border:1px solid #DCE8F7; background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%); }
.sd-welcome-main{ display:flex; align-items:center; gap:18px; min-width:0; }
.sd-avatar{ width:68px; height:68px; border-radius:50%; display:grid; place-items:center; background:linear-gradient(140deg,#DCEBFA,#D9F2F0); border:2px solid #fff; box-shadow:0 4px 12px rgba(18,59,93,.15); color:var(--navy); font-size:22px; font-weight:800; flex:0 0 auto; }
.sd-eyebrow{ display:flex; align-items:center; gap:8px; font-size:11px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:var(--teal); margin-bottom:6px; }
.sd-eyebrow i{ width:4px; height:4px; border-radius:50%; background:var(--teal); display:inline-block; }
.sd-welcome h1{ font-size:28px; font-weight:800; color:var(--navy); letter-spacing:-.02em; }
.sd-welcome p{ margin-top:6px; font-size:14px; color:var(--muted); }
.sd-chips{ display:flex; gap:8px; flex-wrap:wrap; margin-top:10px; }
.sd-chips span{ font-size:11px; font-weight:650; padding:5px 11px; border-radius:999px; background:#fff; border:1px solid #DCE8F7; color:var(--navy); }
.sd-welcome-right{ display:flex; align-items:center; gap:26px; flex-wrap:wrap; }
.sd-date{ text-align:right; font-size:13px; color:var(--muted); }
.sd-date strong{ display:block; font-size:24px; font-weight:800; color:var(--navy); margin-top:2px; }
.sd-date small{ display:block; font-size:11px; margin-top:2px; }
.sd-btn{ display:inline-flex; align-items:center; gap:6px; padding:9px 15px; border:1px solid var(--border); border-radius:10px; background:#fff; color:var(--navy); font-size:12px; font-weight:700; white-space:nowrap; }
.sd-btn:hover{ border-color:var(--teal); background:#F0F9FF; }

/* KPIs */
.sd-kpis{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
.sd-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
.sd-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
.sd-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
.sd-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; white-space:nowrap; }
.sd-kpi-note{ font-size:12px; color:var(--muted); }

/* cards + rows */
.sd-card{ display:flex; flex-direction:column; background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; min-width:0; transition:box-shadow .15s; }
.sd-card:hover{ box-shadow:0 8px 22px rgba(18,59,93,.08); }
.sd-row-grid{ display:grid; gap:16px; align-items:stretch; }
.g-a{ grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); }
.g-b{ grid-template-columns:minmax(0,1.3fr) minmax(0,1fr); }
.g-c{ grid-template-columns:minmax(0,1.3fr) minmax(0,.9fr) minmax(0,1fr); }
.g-d{ grid-template-columns:minmax(0,1.3fr) minmax(0,.9fr) minmax(0,1fr); }
.g-e{ grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); }
.g-f{ grid-template-columns:repeat(3,minmax(0,1fr)); }
.sd-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; }
.sd-title{ display:flex; align-items:center; gap:12px; font-size:16px; font-weight:800; color:var(--navy); }
.sd-title small{ display:block; font-size:11px; font-weight:500; color:var(--muted); margin-top:2px; }
.sd-title .sd-3d{ margin-bottom:6px; }
.sd-viewall{ display:inline-flex; align-items:center; gap:6px; padding:8px 13px; border:1px solid var(--border); border-radius:10px; background:#F8FAFC; font-size:12px; font-weight:700; color:var(--navy); white-space:nowrap; }
.sd-viewall:hover{ border-color:var(--teal); background:#F0F9FF; }
.sd-body{ flex:1; display:flex; flex-direction:column; min-height:0; }
.sd-scroll{ flex:1; overflow-y:auto; max-height:320px; padding-right:3px; }
.sd-scroll::-webkit-scrollbar{ width:5px; } .sd-scroll::-webkit-scrollbar-thumb{ background:#CBD5E1; border-radius:9px; }
.sd-chart{ position:relative; flex:1; min-height:230px; }
.sd-donut-c{ position:absolute; inset:0; display:grid; place-content:center; text-align:center; pointer-events:none; }
.sd-donut-c strong{ display:block; font-size:26px; font-weight:800; color:var(--navy); line-height:1.1; }
.sd-donut-c span{ font-size:10px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }
.sd-legend{ display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:12px; }
.sd-legend.two{ grid-template-columns:repeat(2,1fr); }
.sd-mini{ padding:9px 6px; border-radius:11px; background:#F1F5F9; text-align:center; }
.sd-mini strong{ display:block; font-size:16px; color:var(--navy); } .sd-mini span{ font-size:10px; color:var(--muted); }
.sd-mini i{ display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:4px; background:var(--d); }
.sd-pill{ display:inline-flex; padding:4px 11px; border-radius:999px; font-size:11px; font-weight:700; background:#F1F5F9; color:#475569; white-space:nowrap; }
.sd-pill.green{ background:#DCFCE7; color:#15803D; } .sd-pill.amber{ background:#FEF3C7; color:#B45309; } .sd-pill.red{ background:#FEE2E2; color:#B91C1C; } .sd-pill.blue{ background:#DBEAFE; color:#1D4ED8; }

.sd-table{ width:100%; border-collapse:collapse; }
.sd-table th{ text-align:left; color:var(--muted); font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; font-weight:700; padding:9px 8px; border-bottom:1px solid #E2E8F0; white-space:nowrap; }
.sd-table td{ font-size:13px; padding:13px 8px; border-bottom:1px solid #EEF2F6; color:var(--navy); vertical-align:middle; }
.sd-table tr:last-child td{ border-bottom:0; }
.sd-table small{ display:block; font-size:10.5px; color:var(--muted); margin-top:2px; }
.sd-row{ display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid #EEF2F6; }
.sd-row:last-child{ border-bottom:0; }
.sd-row-main{ flex:1; min-width:0; }
.sd-row-main b{ display:block; font-size:13px; color:var(--navy); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sd-row-main small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sd-row-side{ font-size:12px; font-weight:700; color:var(--navy); white-space:nowrap; text-align:right; }
.sd-date-box{ width:46px; height:50px; flex:0 0 auto; border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; background:linear-gradient(160deg,#EFF6FF,#E0E7FF); color:#3730A3; }
.sd-date-box b{ font-size:17px; line-height:1; } .sd-date-box span{ font-size:9px; font-weight:800; text-transform:uppercase; }
.sd-time-chip{ min-width:72px; padding:8px; border-radius:10px; background:#F0F9FF; color:#0C4A6E; font-size:10.5px; font-weight:800; text-align:center; line-height:1.35; }
.sd-link{ font-size:12px; font-weight:700; color:var(--teal); white-space:nowrap; } .sd-link:hover{ text-decoration:underline; }
.sd-bar{ height:7px; background:#E2E8F0; border-radius:99px; overflow:hidden; margin-top:7px; }
.sd-bar i{ display:block; height:100%; border-radius:99px; background:linear-gradient(90deg,#176B87,#3B82F6); transition:width .6s; }
.sd-stat{ display:flex; justify-content:space-between; gap:10px; font-size:12px; color:var(--muted); } .sd-stat b{ color:var(--navy); }
.sd-seg{ display:inline-flex; padding:3px; border-radius:10px; background:#F1F5F9; }
.sd-seg button{ border:0; background:transparent; padding:5px 12px; border-radius:8px; font:700 11px 'Inter',sans-serif; color:var(--muted); cursor:pointer; }
.sd-seg button.on{ background:#fff; color:var(--teal); box-shadow:0 1px 4px rgba(15,23,42,.1); }
.sd-empty{ flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:18px; color:var(--muted); font-size:12px; }
.sd-empty strong{ display:block; color:#475569; font-size:13px; margin:2px 0 3px; }
.sd-chip{ display:flex; align-items:center; gap:12px; padding:11px 12px; border:1px solid var(--border); border-radius:12px; margin-bottom:10px; background:var(--surface); }
.sd-chip:last-child{ margin-bottom:0; }
.sd-class-grid{ display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.sd-class{ padding:14px; border:1px solid var(--border); border-radius:14px; background:var(--surface); transition:.15s; }
.sd-class:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.12); }
.sd-class-top{ display:flex; align-items:center; gap:12px; }
.sd-class-top b{ display:block; font-size:14px; color:var(--navy); } .sd-class-top small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; }
.sd-class-meta{ display:flex; justify-content:space-between; align-items:center; gap:8px; margin:12px 0 10px; padding-top:10px; border-top:1px solid #E2E8F0; font-size:11px; color:var(--muted); }

/* quick access */
.sd-quick-grid{ display:grid; grid-template-columns:repeat(8,minmax(0,1fr)); gap:12px; }
.sd-quick{ display:flex; flex-direction:column; align-items:center; gap:6px; padding:18px 8px 14px; border:1px solid var(--border); border-radius:14px; background:var(--surface); text-align:center; cursor:pointer; transition:.15s; }
.sd-quick:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); transform:translateY(-3px); }
.sd-quick b{ font-size:12.5px; font-weight:700; color:var(--navy); }

/* AI */
.sd-ai{ border:2px solid #B9A8F0; border-radius:16px; padding:22px 26px; background:linear-gradient(100deg,#DDD6FE,#EDE9FE 60%,#F5F3FF); }
.sd-ai-top{ display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:14px; }
.sd-ai-lead{ display:flex; align-items:center; gap:16px; }
.sd-ai h3{ font-size:20px; font-weight:800; color:var(--purple); }
.sd-ai p{ margin-top:4px; font-size:14px; color:#4B5563; }
.sd-ai-btn{ display:inline-flex; align-items:center; gap:8px; background:var(--purple); color:#fff; border-radius:12px; padding:12px 22px; font:700 14px 'Inter',sans-serif; box-shadow:0 4px 0 #3E2A78; }
.sd-ai-btn:hover{ background:#4B3490; color:#fff; }
.sd-prompts{ display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:14px; margin-top:6px; }
.sd-prompt{ border:1.5px solid var(--navy); border-radius:10px; padding:13px 10px; background:#F8FAFC; color:var(--navy); font:600 14px 'Inter',sans-serif; text-align:center; }
.sd-note{ font-size:11px; color:var(--muted); }
.sd-footer{ text-align:center; font-size:11px; color:var(--muted); padding:4px 0 14px; }
.sd-toast{ position:fixed; left:50%; bottom:28px; transform:translateX(-50%) translateY(20px); background:var(--navy); color:#fff; font-size:13px; font-weight:600; padding:10px 18px; border-radius:10px; opacity:0; pointer-events:none; transition:.25s; z-index:9999; }
.sd-toast.show{ opacity:1; transform:translateX(-50%); }

@@media(max-width:1280px){
    .g-c,.g-d,.g-f{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-quick-grid{ grid-template-columns:repeat(4,minmax(0,1fr)); }
    .sd-prompts{ grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@@media(max-width:900px){
    .g-a,.g-b,.g-c,.g-d,.g-e,.g-f{ grid-template-columns:1fr; }
    .sd-quick-grid{ grid-template-columns:repeat(3,minmax(0,1fr)); }
    .sd-class-grid{ grid-template-columns:1fr; }
    .sd-table-wrap{ overflow-x:auto; } .sd-table{ min-width:520px; }
}
@@media(max-width:600px){
    .sd-welcome{ padding:20px; } .sd-welcome h1{ font-size:22px; } .sd-date{ text-align:left; }
    .sd-welcome-right{ width:100%; justify-content:space-between; }
    .sd-kpis{ grid-template-columns:1fr; }
    .sd-quick-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-prompts{ grid-template-columns:1fr 1fr; }
}

/* to-do list */
.sd-todo-form{ display:flex; gap:6px; flex-wrap:wrap; margin-bottom:10px; }
.sd-todo-text{ flex:1 1 140px; min-width:0; height:38px; padding:0 11px; border:1px solid var(--border); border-radius:10px; font:inherit; font-size:12px; }
.sd-todo-date{ height:38px; padding:0 8px; border:1px solid var(--border); border-radius:10px; font:inherit; font-size:11px; color:var(--text); }
.sd-todo-text:focus,.sd-todo-date:focus{ outline:2px solid #C4B5FD; border-color:#C4B5FD; }
.sd-todo-add{ height:38px; padding:0 16px; border:0; border-radius:10px; background:var(--purple); color:#fff; font:700 12px 'Inter',sans-serif; cursor:pointer; box-shadow:0 3px 0 #3E2A78; }
.sd-todo-add:active{ transform:translateY(2px); box-shadow:0 1px 0 #3E2A78; }
.sd-todo-filters{ display:flex; gap:6px; margin-bottom:10px; }
.sd-todo-filters button{ padding:5px 12px; border:1px solid #E2E8F0; background:#fff; border-radius:99px; font:700 11px 'Inter',sans-serif; color:var(--muted); cursor:pointer; }
.sd-todo-filters button.on{ background:#F1EDFF; border-color:#C4B5FD; color:var(--purple); }
.sd-todo-list{ list-style:none; margin:0; padding:0; }
.sd-todo-item{ display:flex; align-items:center; gap:11px; padding:10px 0; border-bottom:1px solid #EEF2F6; }
.sd-todo-item:last-child{ border-bottom:0; }
.sd-todo-item input{ width:18px; height:18px; accent-color:#16A34A; cursor:pointer; flex:0 0 auto; }
.sd-todo-main{ flex:1; min-width:0; }
.sd-todo-main b{ display:block; font-size:13px; color:var(--navy); word-break:break-word; }
.sd-todo-main small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; }
.sd-todo-main small.late{ color:var(--danger); font-weight:700; }
.sd-todo-item.done b{ text-decoration:line-through; color:#94A3B8; }
.sd-todo-del{ border:0; background:transparent; color:#94A3B8; font-size:18px; line-height:1; cursor:pointer; padding:2px 7px; border-radius:7px; }
.sd-todo-del:hover{ background:#FEE2E2; color:var(--danger); }
.sd-todo-foot{ display:flex; justify-content:space-between; align-items:center; margin-top:10px; }
.sd-todo-clear{ border:0; background:none; color:var(--purple); font:700 11px 'Inter',sans-serif; cursor:pointer; }
.sd-todo-empty{ padding:18px 0; color:var(--muted); font-size:12px; text-align:center; }
</style>

{{-- ================= WELCOME BANNER ================= --}}
<div class="sd-welcome" data-tilt>
    <div class="sd-welcome-main">
        <div class="sd-avatar">{{ $initials }}</div>
        <div>
            <div class="sd-eyebrow">{{ $greet }} <i></i> Teacher Portal</div>
            <h1>Welcome Back, {{ $firstName }}!</h1>
            <p>Your teaching workspace for classes, attendance, assessments and student progress.</p>
            <div class="sd-chips">
                <span>{{ $designation }}</span>
                <span>{{ $department }}</span>
                <span>Employee ID: {{ $employeeNumber }}</span>
            </div>
        </div>
    </div>
    <div class="sd-welcome-right">
        <!-- @if($url = $routeFor('dashboard'))<a href="{{ $url }}" class="sd-btn">Dashboard</a>@endif -->
        {!! $tile('hero', 'lg') !!}
        <div class="sd-date">
            {{ now()->format('l, d M Y') }}
            <strong>{{ now()->format('h:i A') }}</strong>
            <small>{{ session('academic_year_name', 'Academic Year') }} · {{ session('campus_name', 'Assigned Campus') }}</small>
        </div>
    </div>
</div>

{{-- ================= KPI CARDS ================= --}}
<div class="sd-kpis">
    @foreach($kpiCards as [$label, $shown, $note, $ic, $cv])
        <div class="sd-kpi" data-tilt>
            {!! $tile($ic) !!}
            <div>
                <div class="sd-kpi-label">{{ $label }}</div>
                <div class="sd-kpi-value" @if($cv !== null) data-count="{{ $cv }}" @endif>{{ $shown }}</div>
                <div class="sd-kpi-note">{{ $note }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ================= QUICK ACCESS (right under KPIs) ================= --}}
<section class="sd-card">
    <div class="sd-head">
        <h2 class="sd-title" data-tilt>{!! $tile('layers', 'xs') !!}<span>Quick Access<small>Frequently used teacher tools</small></span></h2>
    </div>
    <div class="sd-quick-grid">
        @foreach($quickLinks as [$label, $ic, $r])
            @php $qUrl = $routeFor($r); @endphp
            <a class="sd-quick" data-tilt href="{{ $qUrl ?? '#' }}" @if(!$qUrl) data-soon="{{ $label }} isn't connected yet." @endif>
                {!! $tile($ic, 'sm') !!}
                <b>{{ $label }}</b>
            </a>
        @endforeach
    </div>
</section>

{{-- ================= ROW A : Timetable | Attendance donut ================= --}}
<div class="sd-row-grid g-a">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('timetable', 'xs') !!}<span>Today's Timetable<small>Your scheduled teaching periods</small></span></h2>
            @if($url = $routeFor('timetable.index'))<a class="sd-viewall" href="{{ $url }}">View Timetable →</a>@endif
        </div>
        <div class="sd-body">
            @if($todayTimetable->isNotEmpty())
                <div class="sd-scroll sd-table-wrap">
                    <table class="sd-table">
                        <thead><tr><th>Time</th><th>Class</th><th>Subject</th><th>Room</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                        @foreach($todayTimetable as $i => $period)
                            @php
                                $ps = data_get($period, 'status', 'Upcoming');
                                $st = data_get($period, 'start_time'); $en = data_get($period, 'end_time');
                                $cls = trim(data_get($period, 'class_name', '—').' '.data_get($period, 'section_name', ''));
                            @endphp
                            <tr>
                                <td><div class="sd-time-chip">{{ $st ? \Carbon\Carbon::parse($st)->format('h:i A') : '—' }}<br>{{ $en ? \Carbon\Carbon::parse($en)->format('h:i A') : '—' }}</div></td>
                                <td><div style="display:flex;align-items:center;gap:9px">{!! $ini($cls, $i) !!}<b>{{ $cls }}</b></div></td>
                                <td>{{ data_get($period, 'subject_name', '—') }}</td>
                                <td>{{ data_get($period, 'room_number', '—') }}</td>
                                <td><span class="sd-pill {{ $statusClass($ps) }}">{{ ucfirst($ps) }}</span></td>
                                <td>@if($url = $routeFor('attendance.index'))<a class="sd-link" href="{{ $url }}">Attendance →</a>@endif</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sd-empty">{!! $tile('timetable', 'lg') !!}<strong>No timetable entries today</strong>Your assigned periods will appear here once timetable data is connected.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('attendance', 'xs') !!}<span>Attendance Overview<small>Recorded attendance</small></span></h2>
            @if($url = $routeFor('attendance.index'))<a class="sd-viewall" href="{{ $url }}">Mark →</a>@endif
        </div>
        <div class="sd-body">
            <div class="sd-chart"><canvas id="chAttendance"></canvas>
                <div class="sd-donut-c"><strong>{{ $attendanceRate !== null ? number_format((float) $attendanceRate, 0).'%' : '—' }}</strong><span>Recorded</span></div>
            </div>
            <div class="sd-legend">
                <div class="sd-mini" style="--d:#16A34A"><strong>{{ $formatNumber($presentCount) }}</strong><span><i></i>Present</span></div>
                <div class="sd-mini" style="--d:#DC2626"><strong>{{ $formatNumber($absentCount) }}</strong><span><i></i>Absent</span></div>
                <div class="sd-mini" style="--d:#F59E0B"><strong>{{ $formatNumber($lateCount) }}</strong><span><i></i>Late</span></div>
            </div>
        </div>
    </section>
</div>

{{-- ================= ROW B : My Classes | Class performance ================= --}}
<div class="sd-row-grid g-b">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('myclasses', 'xs') !!}<span>My Classes &amp; Students<small>Assigned sections and subjects</small></span></h2>
            @if($url = $routeFor('class-sections.index'))<a class="sd-viewall" href="{{ $url }}">View Classes →</a>@endif
        </div>
        <div class="sd-body">
            @if($teacherClasses->isNotEmpty())
                <div class="sd-scroll">
                    <div class="sd-class-grid">
                        @foreach($teacherClasses as $i => $class)
                            @php $cn = trim(data_get($class, 'class_name', 'Class').' '.data_get($class, 'section_name', '')); @endphp
                            <article class="sd-class" data-tilt>
                                <div class="sd-class-top">
                                    {!! $ini($cn, $i) !!}
                                    <div><b>{{ $cn }}</b><small>{{ data_get($class, 'subject_name', 'Subject not specified') }}</small></div>
                                </div>
                                <div class="sd-class-meta">
                                    <span>{{ data_get($class, 'student_count', '—') }} Students</span>
                                    @if(data_get($class, 'is_class_teacher'))<span class="sd-pill green">Class Teacher</span>
                                    @else<span>{{ data_get($class, 'teacher_role', 'Subject Teacher') }}</span>@endif
                                </div>
                                @if(data_get($class, 'attendance_rate') !== null)
                                    <div class="sd-stat"><span>Attendance</span><b>{{ number_format((float) data_get($class, 'attendance_rate'), 1) }}%</b></div>
                                    <div class="sd-bar"><i style="width:{{ min(100, max(0, (float) data_get($class, 'attendance_rate'))) }}%"></i></div>
                                @endif
                                @if(data_get($class, 'average_marks') !== null)
                                    <div class="sd-stat" style="margin-top:10px"><span>Academic average</span><b>{{ number_format((float) data_get($class, 'average_marks'), 1) }}%</b></div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="sd-empty">{!! $tile('myclasses', 'lg') !!}<strong>No classes yet</strong>Your assigned classes will appear here once allocations are connected.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('perf', 'xs') !!}<span>Class Performance<small>Average marks</small></span></h2>
            <div class="sd-seg" id="perfSwitch"><button type="button" class="on" data-type="bar">Bar</button><button type="button" data-type="radar">Radar</button></div>
        </div>
        <div class="sd-body">
            @if($classPerformance->isNotEmpty())
                <div class="sd-chart"><canvas id="chPerformance"></canvas></div>
            @else
                <div class="sd-empty">{!! $tile('perf', 'lg') !!}<strong>No results yet</strong>Class analytics appear after examination results are available.</div>
            @endif
        </div>
    </section>
</div>

{{-- ================= ROW C : Assignments | Review donut | Exams ================= --}}
<div class="sd-row-grid g-c">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('homework', 'xs') !!}<span>Homework &amp; Assignment Reviews</span></h2>
            @if($url = $routeFor('assignments.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($teacherAssignments->isNotEmpty())
                <div class="sd-scroll sd-table-wrap">
                    <table class="sd-table">
                        <thead><tr><th>Assignment</th><th>Class</th><th>Due</th><th>Done</th><th>Pending</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($teacherAssignments->take(8) as $i => $a)
                            @php $as = data_get($a, 'status', 'Pending'); $at = data_get($a, 'title', 'Assignment'); @endphp
                            <tr>
                                <td><div style="display:flex;align-items:center;gap:9px">{!! $ini($at, $i) !!}<b>{{ Str::limit($at, 24) }}</b></div></td>
                                <td>{{ data_get($a, 'class_name', '—') }}</td>
                                <td>{{ $formatDate(data_get($a, 'due_date'), 'd M') }}</td>
                                <td>{{ data_get($a, 'submitted_count', '—') }}</td>
                                <td>{{ data_get($a, 'pending_count', '—') }}</td>
                                <td><span class="sd-pill {{ $statusClass($as) }}">{{ ucfirst($as) }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sd-empty">{!! $tile('homework', 'lg') !!}<strong>No assignments yet</strong>Assignments and submissions will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('pie', 'xs') !!}<span>Review Workload<small>Reviewed vs to review</small></span></h2>
        </div>
        <div class="sd-body">
            <div class="sd-chart"><canvas id="chReview"></canvas></div>
            <div class="sd-legend">
                <div class="sd-mini" style="--d:#3B82F6"><strong>{{ $formatNumber($assignmentsCreated) }}</strong><span><i></i>Created</span></div>
                <div class="sd-mini" style="--d:#16A34A"><strong>{{ $formatNumber($completedReviews) }}</strong><span><i></i>Reviewed</span></div>
                <div class="sd-mini" style="--d:#F59E0B"><strong>{{ $formatNumber($pendingReviews) }}</strong><span><i></i>To review</span></div>
            </div>
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('exams', 'xs') !!}<span>Upcoming Exams<small>{{ $formatNumber($upcomingExamCount) }} scheduled</small></span></h2>
            @if($url = $routeFor('examinations.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($teacherExams->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($teacherExams->take(6) as $exam)
                        @php $ed = data_get($exam, 'exam_date', data_get($exam, 'start_date')); @endphp
                        <div class="sd-row">
                            <div class="sd-date-box"><b>{{ $ed ? $formatDate($ed, 'd') : '—' }}</b><span>{{ $ed ? $formatDate($ed, 'M') : 'TBA' }}</span></div>
                            <div class="sd-row-main">
                                <b>{{ data_get($exam, 'name', data_get($exam, 'subject_name', 'Examination')) }}</b>
                                <small>{{ data_get($exam, 'class_name', '') }}@if(data_get($exam, 'start_time')) · {{ $formatDate(data_get($exam, 'start_time'), 'h:i A') }}@endif</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('exams', 'lg') !!}<strong>No upcoming examinations</strong>Exams assigned to you will appear here.</div>
            @endif
        </div>
    </section>
</div>

{{-- ================= ROW D : Attendance trend | Grade distribution | Attention ================= --}}
<div class="sd-row-grid g-d">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('trend', 'xs') !!}<span>Attendance Trend<small>Across your assigned classes</small></span></h2>
        </div>
        <div class="sd-body">
            @if($attendanceTrend->isNotEmpty())
                <div class="sd-chart"><canvas id="chTrend"></canvas></div>
            @else
                <div class="sd-empty">{!! $tile('trend', 'lg') !!}<strong>No trend data yet</strong>Historical attendance will appear here once connected.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('pie', 'xs') !!}<span>Grade Distribution<small>Student results</small></span></h2>
        </div>
        <div class="sd-body">
            @if($gradeDistribution->isNotEmpty())
                <div class="sd-chart"><canvas id="chGrades"></canvas></div>
            @else
                <div class="sd-empty">{!! $tile('pie', 'lg') !!}<strong>No grades yet</strong>Distribution appears after marks are published.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('alert', 'xs') !!}<span>Needs Attention<small>Frequent absentees</small></span></h2>
        </div>
        <div class="sd-body">
            @if($frequentAbsentees->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($frequentAbsentees->take(8) as $i => $stu)
                        @php $sn = data_get($stu, 'student_name', data_get($stu, 'name', 'Student')); @endphp
                        <div class="sd-row">
                            {!! $ini($sn, $i) !!}
                            <div class="sd-row-main"><b>{{ $sn }}</b><small>{{ data_get($stu, 'class_name', '') }} · {{ data_get($stu, 'absence_count', '—') }} absences</small></div>
                            <span class="sd-pill red">Review</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('alert', 'lg') !!}<strong>All clear</strong>No frequent-absence records are available.</div>
            @endif
        </div>
    </section>
</div>

{{-- ================= ROW E : Lesson planner | Recent activity ================= --}}
<div class="sd-row-grid g-e">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('syllabus', 'xs') !!}<span>Lesson Planner &amp; Syllabus<small>Subject-wise progress</small></span></h2>
            @if($url = $routeFor('lesson-plans.index'))<a class="sd-viewall" href="{{ $url }}">Open Planner →</a>@endif
        </div>
        <div class="sd-body">
            @if($lessonPlanProgress->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($lessonPlanProgress as $i => $lesson)
                        @php $lp = min(100, max(0, (float) data_get($lesson, 'progress', 0))); $ln = data_get($lesson, 'subject_name', 'Subject'); @endphp
                        <div class="sd-row" style="align-items:flex-start">
                            {!! $ini($ln, $i) !!}
                            <div class="sd-row-main">
                                <div class="sd-stat"><span style="color:var(--navy);font-weight:700">{{ $ln }}</span><b>{{ number_format($lp, 0) }}%</b></div>
                                <div class="sd-bar"><i style="width:{{ $lp }}%"></i></div>
                                <small style="white-space:normal">{{ data_get($lesson, 'completed_topics', '—') }} of {{ data_get($lesson, 'total_topics', '—') }} topics completed</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('syllabus', 'lg') !!}<strong>No lesson plans yet</strong>Your subject-wise syllabus progress will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('activity', 'xs') !!}<span>Recent Activity<small>Latest updates</small></span></h2>
        </div>
        <div class="sd-body">
            @if($recentItems->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($recentItems->take(8) as $i => $item)
                        @php $it = data_get($item, 'title', data_get($item, 'name', data_get($item, 'reference', 'Activity'))); $is = data_get($item, 'status', 'Updated'); @endphp
                        <div class="sd-row">
                            {!! $ini($it, $i) !!}
                            <div class="sd-row-main"><b>{{ $it }}</b><small>{{ $formatDate(data_get($item, 'created_at', data_get($item, 'date'))) }}</small></div>
                            <span class="sd-pill {{ $statusClass($is) }}">{{ ucfirst($is) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('activity', 'lg') !!}<strong>No recent activity</strong>Latest updates will appear here.</div>
            @endif
        </div>
    </section>
</div>

{{-- ================= ROW F : Announcements | Events | Tasks ================= --}}
<div class="sd-row-grid g-f">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('notices', 'xs') !!}<span>Announcements<small>Updates for teachers</small></span></h2>
            @if($url = $routeFor('communication.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($teacherAnnouncements->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($teacherAnnouncements->take(6) as $i => $n)
                        @php $nt = data_get($n, 'title', 'Announcement'); $pr = data_get($n, 'priority', 'Normal'); @endphp
                        <div class="sd-row">
                            {!! $ini($nt, $i) !!}
                            <div class="sd-row-main"><b>{{ $nt }}</b><small>{{ $formatDate(data_get($n, 'published_at', data_get($n, 'created_at'))) }}</small></div>
                            <span class="sd-pill {{ $statusClass($pr) }}">{{ ucfirst($pr) }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('notices', 'lg') !!}<strong>No announcements</strong>Recent teacher announcements will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('events', 'xs') !!}<span>Events &amp; Calendar<small>Upcoming at school</small></span></h2>
            @if($url = $routeFor('events.dashboard'))<a class="sd-viewall" href="{{ $url }}">Calendar →</a>@endif
        </div>
        <div class="sd-body">
            @if($teacherEvents->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($teacherEvents->take(6) as $ev)
                        @php $evd = data_get($ev, 'starts_at', data_get($ev, 'date')); @endphp
                        <div class="sd-row">
                            <div class="sd-date-box"><b>{{ $evd ? $formatDate($evd, 'd') : '—' }}</b><span>{{ $evd ? $formatDate($evd, 'M') : 'Soon' }}</span></div>
                            <div class="sd-row-main"><b>{{ data_get($ev, 'title', 'School event') }}</b><small>{{ data_get($ev, 'venue', 'Venue not announced') }}</small></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('events', 'lg') !!}<strong>No upcoming events</strong>School events will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card" id="sdTodo"
             data-key="sd_todo_{{ auth()->id() ?? 'guest' }}"
             data-seed="{{ json_encode($teacherTasks->map(fn($t) => ['title' => data_get($t, 'title', 'Task'), 'due' => data_get($t, 'due_date')])->values()) }}">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('tasks', 'xs') !!}<span>My Tasks<small id="sdTodoCount">To-do list</small></span></h2>
        </div>
        <div class="sd-body">
            <form class="sd-todo-form" autocomplete="off">
                <input type="text" class="sd-todo-text" placeholder="Add a task, e.g. Grade Class 8 quiz…" maxlength="120" aria-label="New task">
                <input type="date" class="sd-todo-date" aria-label="Due date">
                <button type="submit" class="sd-todo-add">Add</button>
            </form>
            <div class="sd-todo-filters">
                <button type="button" data-f="all" class="on">All</button>
                <button type="button" data-f="open">Pending</button>
                <button type="button" data-f="done">Done</button>
            </div>
            <div class="sd-bar" style="margin:0 0 6px"><i id="sdTodoBar" style="width:0;background:linear-gradient(90deg,#16A34A,#5BE08F)"></i></div>
            <div class="sd-scroll"><ul class="sd-todo-list" id="sdTodoList"></ul></div>
            <div class="sd-todo-foot">
                <span class="sd-note">Saved in this browser</span>
                <button type="button" class="sd-todo-clear" id="sdTodoClear">Clear completed</button>
            </div>
        </div>
    </section>
</div>

{{-- ================= CASPA TEACHER ASSISTANT ================= --}}
<section class="sd-ai">
    <div class="sd-ai-top">
        <div class="sd-ai-lead" data-tilt>
            {!! $tile('ai', 'md') !!}
            <div>
                <h3>CASPA Teacher Assistant</h3>
                <p>Your AI teaching companion for lesson planning, question generation, assignments and academic support.</p>
            </div>
        </div>
        @if($aiRoute)
            <a class="sd-ai-btn" href="{{ route($aiRoute) }}">Open CASPA Assistant →</a>
        @else
            <span class="sd-pill amber">Teacher Assistant route pending</span>
        @endif
    </div>
    <div class="sd-prompts">
        <div class="sd-prompt">Lesson Plans</div>
        <div class="sd-prompt">MCQ Generation</div>
        <div class="sd-prompt">Question Papers</div>
        <div class="sd-prompt">Teaching Notes</div>
        <div class="sd-prompt">Remedial Suggestions</div>
    </div>
</section>

<div class="sd-footer">SchoolNexa Teacher Portal · Showing information available to your assigned classes and subjects.</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var root = document.querySelector('.teacher-dash');
    if (!root) return;

    var D = {
        att: @json([$presentCount, $absentCount, $lateCount]),
        attRate: @json($attendanceRate !== null ? (float) $attendanceRate : null),
        review: @json($reviewValues),
        trendLabels: @json($attendanceLabels),
        trendValues: @json($attendanceValues),
        perfLabels: @json($performanceLabels),
        perfValues: @json($performanceValues),
        gradeLabels: @json($gradeLabels),
        gradeValues: @json($gradeValues)
    };
    var sum = function (a) { return a.reduce(function (x, y) { return x + (Number(y) || 0); }, 0); };
    var GRAY = '#E2E8F0';
    var palette = ['#3B82F6', '#16A34A', '#F59E0B', '#7C3AED', '#176B87', '#DC2626', '#0EA5E9', '#D97706'];

    function doughnut(id, values, colors, labels, type) {
        var el = root.querySelector('#' + id); if (!el) return;
        var empty = sum(values) === 0;
        new Chart(el, {
            type: type || 'doughnut',
            data: { labels: empty ? ['No data'] : labels, datasets: [{ data: empty ? [1] : values, backgroundColor: empty ? [GRAY] : colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: type === 'pie' ? 0 : '68%', animation: { duration: 900 },
                plugins: { legend: { display: false }, tooltip: { enabled: !empty } } }
        });
    }

    function init() {
        if (!window.Chart) return;
        Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

        doughnut('chAttendance', D.att, ['#16A34A', '#DC2626', '#F59E0B'], ['Present', 'Absent', 'Late']);
        doughnut('chReview', D.review, ['#16A34A', '#F59E0B'], ['Reviewed', 'To review'], 'pie');

        var grades = root.querySelector('#chGrades');
        if (grades && D.gradeLabels.length) {
            new Chart(grades, {
                type: 'doughnut',
                data: { labels: D.gradeLabels, datasets: [{ data: D.gradeValues, backgroundColor: palette, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] },
                options: { responsive: true, maintainAspectRatio: false, cutout: '62%', plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, font: { size: 11 } } } } }
            });
        }

        var tr = root.querySelector('#chTrend');
        if (tr && D.trendLabels.length) {
            var g = tr.getContext('2d').createLinearGradient(0, 0, 0, 260);
            g.addColorStop(0, 'rgba(23,107,135,.40)'); g.addColorStop(1, 'rgba(23,107,135,0)');
            new Chart(tr, {
                type: 'line',
                data: { labels: D.trendLabels, datasets: [{ data: D.trendValues, borderColor: '#176B87', backgroundColor: g, fill: true, tension: .4, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#fff', pointBorderColor: '#176B87', pointBorderWidth: 2 }] },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                    scales: { y: { min: 0, max: 100, grid: { color: '#EEF2F6' }, ticks: { callback: function (v) { return v + '%'; } } }, x: { grid: { display: false } } } }
            });
        }

        var perf = root.querySelector('#chPerformance'), chart;
        if (perf && D.perfLabels.length) {
            var build = function (type) {
                if (chart) chart.destroy();
                var bar = type === 'bar';
                chart = new Chart(perf, {
                    type: type,
                    data: { labels: D.perfLabels, datasets: [{ label: 'Average marks', data: D.perfValues,
                        backgroundColor: bar ? D.perfLabels.map(function (_, i) { return palette[i % palette.length]; }) : 'rgba(59,130,246,.2)',
                        borderColor: '#3B82F6', borderWidth: bar ? 0 : 2, borderRadius: 8, maxBarThickness: 36, pointBackgroundColor: '#3B82F6' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                        scales: bar ? { y: { min: 0, max: 100, grid: { color: '#EEF2F6' }, ticks: { callback: function (v) { return v + '%'; } } }, x: { grid: { display: false } } }
                                    : { r: { min: 0, max: 100, ticks: { display: false } } } }
                });
            };
            build('bar');
            root.querySelectorAll('#perfSwitch button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    root.querySelectorAll('#perfSwitch button').forEach(function (b) { b.classList.toggle('on', b === btn); });
                    build(btn.dataset.type);
                });
            });
        }
    }
    if (window.Chart) init(); else window.addEventListener('load', init);

    /* 3D tilt + KPI count-up */
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduce) {
        root.querySelectorAll('[data-tilt]').forEach(function (card) {
            var art = card.querySelector('.sd-3d'); if (!art) return;
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect(), x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
                var cl = function (v) { return Math.max(-22, Math.min(22, v)); };
                art.style.setProperty('--ry', cl(x * 44) + 'deg'); art.style.setProperty('--rx', cl(-y * 44) + 'deg'); art.style.setProperty('--s', '1.14');
            });
            card.addEventListener('mouseleave', function () { art.style.setProperty('--ry', '0deg'); art.style.setProperty('--rx', '0deg'); art.style.setProperty('--s', '1'); });
            card.addEventListener('click', function () { art.classList.remove('pop'); void art.offsetWidth; art.classList.add('pop'); });
        });
    }
    root.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0, start = null;
        if (target === 0 || reduce) return;
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / 700, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
            if (p < 1) requestAnimationFrame(step); else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(step);
    });

    /* toast for modules that aren't connected yet */
    var toast = document.createElement('div'); toast.className = 'sd-toast'; document.body.appendChild(toast);
    var timer;
    root.querySelectorAll('[data-soon]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            toast.textContent = a.getAttribute('data-soon'); toast.classList.add('show');
            clearTimeout(timer); timer = setTimeout(function () { toast.classList.remove('show'); }, 2200);
        });
    });
})();
</script>
<script>
/* To-do list: add / tick / delete / filter, saved in this browser (localStorage). Seeded from $teacherTasks. */
(function () {
    var box = document.getElementById('sdTodo'); if (!box) return;
    var key = box.getAttribute('data-key'), tasks = [], filter = 'all';
    var list = document.getElementById('sdTodoList'), bar = document.getElementById('sdTodoBar'), cnt = document.getElementById('sdTodoCount');
    var form = box.querySelector('form'), txt = box.querySelector('.sd-todo-text'), dt = box.querySelector('.sd-todo-date');
    function uid() { return 't' + Date.now().toString(36) + Math.random().toString(36).slice(2, 6); }
    function load() {
        try { var raw = localStorage.getItem(key); if (raw) { tasks = JSON.parse(raw) || []; return; } } catch (e) {}
        try { tasks = (JSON.parse(box.getAttribute('data-seed') || '[]') || []).map(function (t) { return { id: uid(), title: t.title, due: t.due ? String(t.due).slice(0, 10) : '', done: false }; }); } catch (e) { tasks = []; }
    }
    function save() { try { localStorage.setItem(key, JSON.stringify(tasks)); } catch (e) {} }
    function fmt(d) { try { return new Date(d + 'T00:00:00').toLocaleDateString(undefined, { day: '2-digit', month: 'short' }); } catch (e) { return d; } }
    function render() {
        var done = tasks.filter(function (t) { return t.done; }).length;
        cnt.textContent = tasks.length ? (done + ' of ' + tasks.length + ' done') : 'To-do list';
        bar.style.width = tasks.length ? Math.round(done / tasks.length * 100) + '%' : '0%';
        var d0 = new Date(), today = d0.getFullYear() + '-' + String(d0.getMonth() + 1).padStart(2, '0') + '-' + String(d0.getDate()).padStart(2, '0');
        var shown = tasks.filter(function (t) { return filter === 'all' || (filter === 'done' ? t.done : !t.done); });
        list.innerHTML = '';
        if (!shown.length) { list.innerHTML = '<li class="sd-todo-empty">' + (tasks.length ? 'Nothing in this view.' : 'No tasks yet. Add your first one above.') + '</li>'; return; }
        shown.forEach(function (t) {
            var li = document.createElement('li'); li.className = 'sd-todo-item' + (t.done ? ' done' : '');
            var late = t.due && !t.done && t.due < today;
            li.innerHTML = '<input type="checkbox" ' + (t.done ? 'checked' : '') + ' aria-label="Mark done"><div class="sd-todo-main"><b></b><small class="' + (late ? 'late' : '') + '">' +
                (t.due ? (late ? 'Overdue · ' : 'Due ') + fmt(t.due) : 'No deadline') + '</small></div><button type="button" class="sd-todo-del" aria-label="Delete task">×</button>';
            li.querySelector('b').textContent = t.title;
            li.querySelector('input').addEventListener('change', function () { t.done = this.checked; save(); render(); });
            li.querySelector('.sd-todo-del').addEventListener('click', function () { tasks = tasks.filter(function (x) { return x.id !== t.id; }); save(); render(); });
            list.appendChild(li);
        });
    }
    form.addEventListener('submit', function (e) {
        e.preventDefault(); var v = txt.value.trim(); if (!v) { txt.focus(); return; }
        tasks.unshift({ id: uid(), title: v, due: dt.value || '', done: false });
        txt.value = ''; dt.value = ''; save(); render(); txt.focus();
    });
    box.querySelectorAll('.sd-todo-filters button').forEach(function (b) {
        b.addEventListener('click', function () {
            box.querySelectorAll('.sd-todo-filters button').forEach(function (o) { o.classList.remove('on'); });
            b.classList.add('on'); filter = b.getAttribute('data-f'); render();
        });
    });
    document.getElementById('sdTodoClear').addEventListener('click', function () { tasks = tasks.filter(function (t) { return !t.done; }); save(); render(); });
    load(); render();
})();
</script>
@endsection