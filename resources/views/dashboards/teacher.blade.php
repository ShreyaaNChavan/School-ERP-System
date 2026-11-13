
@extends('layouts.schoolnexa')

@section('title', 'Teacher Dashboard')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | SchoolNexa Teacher Dashboard
    |--------------------------------------------------------------------------
    | Existing controller variables are retained:
    | $stats, $recentItems, $upcomingItems
    |
    | Additional variables are optional. Connect them from DashboardController
    | as the corresponding teacher modules become available.
    */

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
    $lessonPlanProgress = $lessonPlanProgress ?? [];

    $teacherName = auth()->user()->name ?? 'Teacher';

    $firstName = explode(' ', trim($teacherName))[0] ?? 'Teacher';

    $designation = data_get($teacherProfile, 'designation', 'Teacher');
    $department = data_get($teacherProfile, 'department', 'Department not assigned');
    $employeeNumber = data_get($teacherProfile, 'employee_number', 'Not assigned');

    $totalStudents = $dashboardStats['students'] ?? null;
    $assignedClasses = $dashboardStats['assigned_classes'] ?? null;
    $todayPeriods = $dashboardStats['today_periods'] ?? null;
    $pendingReviews = $dashboardStats['pending_reviews'] ?? null;

    $presentCount = $attendanceSummary['present'] ?? 0;
    $absentCount = $attendanceSummary['absent'] ?? 0;
    $lateCount = $attendanceSummary['late'] ?? 0;
    $attendanceTotal = $attendanceSummary['total'] ?? 0;
    $attendanceRate = $attendanceSummary['rate'] ?? null;

    $pendingAssignments = $dashboardStats['pending_assignments'] ?? null;
    $upcomingExamCount = $dashboardStats['upcoming_exams'] ?? null;
    $completedReviews = $dashboardStats['completed_reviews'] ?? null;

    $formatNumber = function ($value) {
        return is_numeric($value) ? number_format((float) $value) : '—';
    };

    $formatDate = function ($value, $format = 'd M Y') {
        if (!$value) {
            return '—';
        }

        try {
            return \Carbon\Carbon::parse($value)->format($format);
        } catch (\Throwable $e) {
            return '—';
        }
    };

    $statusClass = function ($status) {
        $status = strtolower((string) $status);

        if (
            str_contains($status, 'complete') ||
            str_contains($status, 'submit') ||
            str_contains($status, 'present') ||
            str_contains($status, 'approved')
        ) {
            return 'success';
        }

        if (
            str_contains($status, 'absent') ||
            str_contains($status, 'overdue') ||
            str_contains($status, 'reject')
        ) {
            return 'danger';
        }

        if (
            str_contains($status, 'pending') ||
            str_contains($status, 'review') ||
            str_contains($status, 'upcoming') ||
            str_contains($status, 'next')
        ) {
            return 'warning';
        }

        return 'info';
    };

    $quickLinks = [
        [
            'icon' => 'calendar',
            'title' => 'My Timetable',
            'description' => 'View your teaching schedule',
            'route' => 'timetable.index',
        ],
        [
            'icon' => 'users',
            'title' => 'My Classes',
            'description' => 'Open assigned sections',
            'route' => 'class-sections.index',
        ],
        [
            'icon' => 'clipboard',
            'title' => 'Homework',
            'description' => 'Create and review homework',
            'route' => 'homework.index',
        ],
        [
            'icon' => 'file',
            'title' => 'Assignments',
            'description' => 'Manage student assessments',
            'route' => 'assignments.index',
        ],
        [
            'icon' => 'check',
            'title' => 'Attendance',
            'description' => 'Mark class attendance',
            'route' => 'attendance.index',
        ],
        [
            'icon' => 'book',
            'title' => 'Study Materials',
            'description' => 'Manage learning resources',
            'route' => 'learning-materials.index',
        ],
        [
            'icon' => 'calendar-check',
            'title' => 'Lesson Planner',
            'description' => 'Plan lessons and syllabus',
            'route' => 'lesson-plans.index',
        ],
        [
            'icon' => 'message',
            'title' => 'Communication',
            'description' => 'School and parent messages',
            'route' => 'communication.index',
        ],
    ];

    $aiRoute = null;

    foreach ([
        'caspa.teacher-assistant.index',
        'caspa-chat.index',
        'caspa.chat.index',
    ] as $candidateRoute) {
        if (\Illuminate\Support\Facades\Route::has($candidateRoute)) {
            $aiRoute = $candidateRoute;
            break;
        }
    }

    $attendanceLabels = $attendanceTrend->map(function ($item) {
        return data_get($item, 'label', data_get($item, 'date', ''));
    })->values();

    $attendanceValues = $attendanceTrend->map(function ($item) {
        return (float) data_get($item, 'percentage', data_get($item, 'rate', 0));
    })->values();

    $performanceLabels = $classPerformance->map(function ($item) {
        return data_get($item, 'class_name', data_get($item, 'label', 'Class'));
    })->values();

    $performanceValues = $classPerformance->map(function ($item) {
        return (float) data_get($item, 'average', data_get($item, 'percentage', 0));
    })->values();

@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.teacher-dash {
    --navy:#123B5D;
    --teal:#176B87;
    --purple:#5B3FA3;
    --sky:#3B82F6;
    --green:#16A34A;
    --amber:#D97706;
    --red:#DC2626;
    --line:#CBD5E1;
    --soft-line:#E2E8F0;
    --ink:#25313C;
    --muted:#64748B;
    --surface:#F8FAFC;

    font-family:Inter,system-ui,sans-serif;
    color:var(--ink);
    display:flex;
    flex-direction:column;
    gap:16px;
    max-width:1500px;
    margin:auto;
}

.teacher-dash * { box-sizing:border-box; }

.teacher-dash h1,
.teacher-dash h2,
.teacher-dash h3,
.teacher-dash p { margin:0; }

.td-header {
    position:relative;
    overflow:hidden;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    padding:25px 27px;
    border-radius:17px;
    color:#fff;
    background:linear-gradient(115deg,#123B5D 0%,#176B87 70%,#2586A0 100%);
    box-shadow:0 8px 25px rgba(18,59,93,.12);
}

.td-header:after {
    content:"";
    position:absolute;
    width:260px;
    height:260px;
    border:1px solid rgba(255,255,255,.13);
    border-radius:50%;
    right:8%;
    top:-160px;
    box-shadow:
        0 0 0 35px rgba(255,255,255,.04),
        0 0 0 70px rgba(255,255,255,.03);
    pointer-events:none;
}

.td-header-main,
.td-header-side { position:relative;z-index:1; }

.td-eyebrow {
    font-size:10px;
    font-weight:800;
    letter-spacing:.13em;
    text-transform:uppercase;
    color:#B9E4EF;
    margin-bottom:8px;
}

.td-header h1 {
    font-size:25px;
    line-height:1.3;
    font-weight:800;
    color:#fff;
}

.td-header p {
    font-size:12px;
    color:#D6EAF0;
    margin-top:6px;
}

.td-header-side {
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.td-date {
    text-align:right;
    font-size:10px;
    color:#D6EAF0;
}

.td-date strong {
    display:block;
    color:#fff;
    font-size:16px;
    margin-top:4px;
}

.td-button {
    min-height:39px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:7px;
    padding:0 14px;
    border-radius:9px;
    border:1px solid var(--line);
    background:#fff;
    color:var(--navy);
    font-size:11px;
    font-weight:700;
    text-decoration:none;
    cursor:pointer;
}

.td-button.primary {
    background:var(--navy);
    color:#fff;
    border-color:var(--navy);
}

.td-button.light {
    background:rgba(255,255,255,.12);
    color:#fff;
    border-color:rgba(255,255,255,.45);
}

.td-button:hover { filter:brightness(.97); }

.td-profile {
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:14px;
    padding:14px 17px;
    background:#fff;
    border:1px solid var(--soft-line);
    border-radius:13px;
}

.td-profile-main {
    display:flex;
    align-items:center;
    gap:12px;
}

.td-avatar {
    width:46px;
    height:46px;
    display:grid;
    place-items:center;
    border-radius:14px;
    background:linear-gradient(140deg,#DCEBFA,#D9F2F0);
    color:var(--navy);
    font-size:16px;
    font-weight:800;
}

.td-profile-main b {
    display:block;
    font-size:13px;
    color:var(--navy);
}

.td-profile-main small {
    display:block;
    margin-top:4px;
    font-size:10px;
    color:var(--muted);
}

.td-profile-tags {
    display:flex;
    gap:7px;
    flex-wrap:wrap;
}

.td-tag {
    padding:6px 9px;
    background:#F1F5F9;
    border-radius:7px;
    color:#475569;
    font-size:9px;
    font-weight:700;
}

.td-kpis {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
}

.td-kpi,
.td-panel {
    background:#fff;
    border:1px solid var(--soft-line);
    border-radius:14px;
    padding:17px;
    min-width:0;
}

.td-kpi {
    position:relative;
    overflow:hidden;
}

.td-kpi-top {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:8px;
}

.td-kpi-label {
    font-size:10px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
    color:var(--muted);
}

.td-kpi-icon {
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    border-radius:10px;
    background:#EFF6FF;
    color:#2563EB;
}

.td-kpi-icon svg {
    width:17px;
    height:17px;
}

.td-kpi strong {
    display:block;
    margin:9px 0 4px;
    color:var(--navy);
    font-size:26px;
    line-height:1.2;
    font-weight:800;
}

.td-kpi small {
    color:var(--muted);
    font-size:10px;
}

.td-kpi.teal .td-kpi-icon { background:#E6F4F5;color:#0F766E; }
.td-kpi.green .td-kpi-icon { background:#DCFCE7;color:#15803D; }
.td-kpi.amber .td-kpi-icon { background:#FFF7ED;color:#C2410C; }
.td-kpi.purple .td-kpi-icon { background:#F3E8FF;color:#7E22CE; }

.td-grid {
    display:grid;
    grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);
    gap:14px;
    align-items:stretch;
}

.td-grid-three {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:14px;
    align-items:stretch;
}

.td-panel h2 {
    font-size:14px;
    color:var(--navy);
    font-weight:800;
    margin-bottom:13px;
}

.td-panel-header {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    margin-bottom:13px;
}

.td-panel-header h2 { margin:0; }

.td-panel-link {
    display:inline-flex;
    align-items:center;
    padding:7px 9px;
    border-radius:8px;
    background:#F8FAFC;
    color:var(--navy);
    font-size:10px;
    font-weight:700;
    text-decoration:none;
    white-space:nowrap;
}

.td-panel-link:hover { background:#EFF6FF; }

.td-muted {
    color:var(--muted);
    font-size:10px;
}

.td-shortcuts {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:9px;
}

.td-shortcut {
    display:flex;
    flex-direction:column;
    gap:7px;
    min-height:82px;
    padding:12px;
    border:1px solid var(--soft-line);
    border-radius:11px;
    background:#F8FAFC;
    text-decoration:none;
    transition:.18s;
}

.td-shortcut:hover {
    border-color:#9CC5D5;
    background:#F0F9FB;
    transform:translateY(-1px);
}

.td-shortcut-icon {
    width:27px;
    height:27px;
    display:grid;
    place-items:center;
    background:#E6F4F5;
    color:var(--teal);
    border-radius:8px;
}

.td-shortcut-icon svg { width:15px;height:15px; }

.td-shortcut b {
    font-size:10px;
    color:var(--navy);
}

.td-shortcut span {
    font-size:9px;
    color:var(--muted);
    line-height:1.4;
}

.td-badge {
    display:inline-flex;
    align-items:center;
    padding:4px 8px;
    border-radius:30px;
    background:#E0F2FE;
    color:#075985;
    font-size:9px;
    font-weight:800;
    white-space:nowrap;
}

.td-badge.success { background:#DCFCE7;color:#166534; }
.td-badge.warning { background:#FEF3C7;color:#92400E; }
.td-badge.danger { background:#FEE2E2;color:#991B1B; }
.td-badge.info { background:#E0F2FE;color:#075985; }

.td-empty {
    padding:16px 0;
    color:var(--muted);
    font-size:11px;
    line-height:1.6;
}

.td-table-wrap { overflow-x:auto; }

.td-table {
    width:100%;
    border-collapse:collapse;
    min-width:520px;
}

.td-table th {
    text-align:left;
    padding:10px;
    background:#F8FAFC;
    color:var(--muted);
    font-size:9px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
}

.td-table td {
    padding:11px 10px;
    border-bottom:1px solid #EEF2F6;
    color:var(--ink);
    font-size:10px;
}

.td-table tr:last-child td { border-bottom:0; }

.td-timetable-time {
    font-size:10px;
    color:var(--navy);
    font-weight:800;
    white-space:nowrap;
}

.td-class-card {
    padding:13px;
    border:1px solid #E2E8F0;
    background:#F8FAFC;
    border-radius:11px;
}

.td-class-card h3 {
    font-size:12px;
    font-weight:800;
    color:var(--navy);
}

.td-class-card p {
    font-size:10px;
    color:var(--muted);
    margin-top:5px;
}

.td-class-meta {
    display:flex;
    justify-content:space-between;
    gap:8px;
    margin-top:12px;
    padding-top:10px;
    border-top:1px solid #E2E8F0;
    font-size:9px;
    color:var(--muted);
}

.td-class-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}

.td-stat-line {
    display:flex;
    justify-content:space-between;
    gap:10px;
    font-size:10px;
    color:var(--muted);
}

.td-stat-line b { color:var(--navy); }

.td-progress {
    height:7px;
    overflow:hidden;
    border-radius:30px;
    background:#E8EEF4;
    margin:9px 0;
}

.td-progress span {
    display:block;
    height:100%;
    border-radius:30px;
    background:linear-gradient(90deg,#176B87,#3B82F6);
}

.td-mini-grid {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:8px;
    margin-top:14px;
}

.td-mini {
    padding:10px;
    background:#F8FAFC;
    border:1px solid #EEF2F6;
    border-radius:9px;
}

.td-mini b {
    display:block;
    color:var(--navy);
    font-size:16px;
}

.td-mini span {
    display:block;
    color:var(--muted);
    font-size:9px;
    margin-top:3px;
}

.td-record {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    padding:10px 0;
    border-bottom:1px solid #EEF2F6;
}

.td-record:last-child { border-bottom:0; }

.td-record b {
    display:block;
    color:var(--navy);
    font-size:10px;
}

.td-record small {
    display:block;
    color:var(--muted);
    font-size:9px;
    margin-top:4px;
}

.td-chart {
    height:215px;
    position:relative;
}

.td-ai {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    flex-wrap:wrap;
    padding:19px 21px;
    border:1px solid #DDD6FE;
    border-radius:14px;
    background:linear-gradient(105deg,#F1EDFF,#FAF9FF);
}

.td-ai h2 {
    color:var(--purple);
    font-size:15px;
    font-weight:800;
}

.td-ai p {
    color:#6B6484;
    font-size:10px;
    margin-top:5px;
}

.td-ai-tools {
    display:flex;
    gap:7px;
    flex-wrap:wrap;
    margin-top:12px;
}

.td-ai-tool {
    padding:6px 9px;
    background:#fff;
    border:1px solid #E4DDFC;
    color:#5B3FA3;
    border-radius:7px;
    font-size:9px;
    font-weight:700;
}

.td-task {
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding:10px 0;
    border-bottom:1px solid #EEF2F6;
}

.td-task:last-child { border-bottom:0; }

.td-task-icon {
    width:30px;
    height:30px;
    flex-shrink:0;
    display:grid;
    place-items:center;
    border-radius:9px;
    background:#EFF6FF;
    color:#2563EB;
}

.td-task-icon svg { width:15px;height:15px; }

.td-task b {
    display:block;
    color:var(--navy);
    font-size:10px;
}

.td-task small {
    display:block;
    color:var(--muted);
    font-size:9px;
    margin-top:4px;
}

.td-footer {
    text-align:center;
    color:var(--muted);
    font-size:9px;
    padding:3px 0;
}

@media(max-width:1100px) {
    .td-grid-three { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .td-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .td-shortcuts { grid-template-columns:repeat(3,minmax(0,1fr)); }
}

@media(max-width:850px) {
    .td-grid { grid-template-columns:1fr; }
}

@media(max-width:650px) {
    .td-header { padding:19px; }
    .td-header h1 { font-size:21px; }
    .td-header-side { width:100%;justify-content:space-between; }
    .td-date { text-align:left; }
    .td-kpis { grid-template-columns:repeat(2,minmax(0,1fr));gap:9px; }
    .td-grid-three { grid-template-columns:1fr; }
    .td-shortcuts { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .td-class-grid { grid-template-columns:1fr; }
    .td-kpi,.td-panel { padding:13px; }
}
</style>

<div class="teacher-dash">

    {{-- =========================================================
         1. WELCOME HEADER
    ========================================================== --}}
    <section class="td-header">
        <div class="td-header-main">
            <div class="td-eyebrow">SchoolNexa · Teacher Portal</div>

            <h1>
                Good
                {{ now()->format('H') < 12 ? 'morning' : (now()->format('H') < 17 ? 'afternoon' : 'evening') }},
                {{ $firstName }}!
            </h1>

            <p>
                Your teaching workspace for classes, attendance, assessments and student progress.
            </p>
        </div>

        <div class="td-header-side">
            <div class="td-date">
                {{ now()->format('D, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>

            <a class="td-button light" href="{{ route('dashboard') }}">
                Dashboard
            </a>
        </div>
    </section>

    {{-- =========================================================
         TEACHER PROFILE
    ========================================================== --}}
    <section class="td-profile">
        <div class="td-profile-main">
            <div class="td-avatar">
                {{ strtoupper(substr($teacherName, 0, 1)) }}
            </div>

            <div>
                <b>{{ $teacherName }}</b>
                <small>
                    {{ $designation }} · {{ $department }}
                </small>
            </div>
        </div>

        <div class="td-profile-tags">
            <span class="td-tag">
                Employee ID: {{ $employeeNumber }}
            </span>

            <span class="td-tag">
                {{ session('academic_year_name', 'Academic Year') }}
            </span>

            <span class="td-tag">
                {{ session('campus_name', 'Assigned Campus') }}
            </span>
        </div>
    </section>

    {{-- =========================================================
         2. KPI CARDS
    ========================================================== --}}
    <section class="td-kpis">

        <article class="td-kpi">
            <div class="td-kpi-top">
                <span class="td-kpi-label">My Students</span>
                <span class="td-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                        <circle cx="10" cy="7" r="4"/>
                        <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                </span>
            </div>
            <strong>{{ $formatNumber($totalStudents) }}</strong>
            <small>Students in assigned sections</small>
        </article>

        <article class="td-kpi teal">
            <div class="td-kpi-top">
                <span class="td-kpi-label">Today's Classes</span>
                <span class="td-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="17" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </span>
            </div>
            <strong>{{ $formatNumber($todayPeriods) }}</strong>
            <small>Scheduled teaching periods</small>
        </article>

        <article class="td-kpi amber">
            <div class="td-kpi-top">
                <span class="td-kpi-label">Pending Reviews</span>
                <span class="td-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                </span>
            </div>
            <strong>{{ $formatNumber($pendingReviews) }}</strong>
            <small>Student submissions to review</small>
        </article>

        <article class="td-kpi purple">
            <div class="td-kpi-top">
                <span class="td-kpi-label">Assignments Due</span>
                <span class="td-kpi-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6M8 13h8M8 17h8"/>
                    </svg>
                </span>
            </div>
            <strong>{{ $formatNumber($pendingAssignments) }}</strong>
            <small>Pending assignment activities</small>
        </article>

    </section>

    {{-- =========================================================
         3. TODAY'S TIMETABLE
    ========================================================== --}}
    <section class="td-panel">
        <div class="td-panel-header">
            <div>
                <h2>Today's Timetable</h2>
                <div class="td-muted">Your scheduled teaching periods</div>
            </div>

            @if(\Illuminate\Support\Facades\Route::has('timetable.index'))
                <a class="td-panel-link" href="{{ route('timetable.index') }}">
                    View Timetable →
                </a>
            @endif
        </div>

        @if($todayTimetable->isNotEmpty())
            <div class="td-table-wrap">
                <table class="td-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Class</th>
                            <th>Subject</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($todayTimetable as $period)
                            @php
                                $periodStatus = data_get($period, 'status', 'Upcoming');
                                $periodStart = data_get($period, 'start_time');
                                $periodEnd = data_get($period, 'end_time');
                            @endphp

                            <tr>
                                <td class="td-timetable-time">
                                    {{ $periodStart ? \Carbon\Carbon::parse($periodStart)->format('h:i A') : '—' }}
                                    -
                                    {{ $periodEnd ? \Carbon\Carbon::parse($periodEnd)->format('h:i A') : '—' }}
                                </td>

                                <td>
                                    <strong>
                                        {{ data_get($period, 'class_name', '—') }}
                                        {{ data_get($period, 'section_name', '') }}
                                    </strong>
                                </td>

                                <td>{{ data_get($period, 'subject_name', '—') }}</td>

                                <td>{{ data_get($period, 'room_number', '—') }}</td>

                                <td>
                                    <span class="td-badge {{ $statusClass($periodStatus) }}">
                                        {{ ucfirst($periodStatus) }}
                                    </span>
                                </td>

                                <td>
                                    @if(\Illuminate\Support\Facades\Route::has('attendance.index'))
                                        <a class="td-panel-link" href="{{ route('attendance.index') }}">
                                            Attendance
                                        </a>
                                    @else
                                        <span class="td-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="td-empty">
                No timetable entries are available for today. Your assigned periods will appear here when timetable data is connected.
            </div>
        @endif
    </section>

    {{-- =========================================================
         4. QUICK ACCESS
    ========================================================== --}}
    <section class="td-panel">
        <div class="td-panel-header">
            <h2>Quick Access</h2>
            <span class="td-muted">Frequently used teacher tools</span>
        </div>

        <div class="td-shortcuts">
            @foreach($quickLinks as $link)
                @if(\Illuminate\Support\Facades\Route::has($link['route']))
                    <a class="td-shortcut" href="{{ route($link['route']) }}">
                        <span class="td-shortcut-icon">
                            @if($link['icon'] === 'calendar')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            @elseif($link['icon'] === 'users')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87"/></svg>
                            @elseif($link['icon'] === 'clipboard')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="18" rx="2"/><path d="M9 4V2h6v2M8 11h8M8 15h8"/></svg>
                            @elseif($link['icon'] === 'file')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                            @elseif($link['icon'] === 'check')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                            @elseif($link['icon'] === 'book')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            @elseif($link['icon'] === 'calendar-check')
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18M9 16l2 2 4-4"/></svg>
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8A8.5 8.5 0 0 1 8.7 3.9a8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/></svg>
                            @endif
                        </span>

                        <b>{{ $link['title'] }}</b>
                        <span>{{ $link['description'] }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </section>

    {{-- =========================================================
         5. MY CLASSES AND ATTENDANCE
    ========================================================== --}}
    <section class="td-grid">

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>My Classes & Students</h2>

                @if(\Illuminate\Support\Facades\Route::has('class-sections.index'))
                    <a class="td-panel-link" href="{{ route('class-sections.index') }}">
                        View Classes →
                    </a>
                @endif
            </div>

            @if($teacherClasses->isNotEmpty())
                <div class="td-class-grid">
                    @foreach($teacherClasses as $class)
                        <article class="td-class-card">
                            <h3>
                                {{ data_get($class, 'class_name', 'Class') }}
                                {{ data_get($class, 'section_name', '') }}
                            </h3>

                            <p>{{ data_get($class, 'subject_name', 'Subject not specified') }}</p>

                            <div class="td-class-meta">
                                <span>
                                    {{ data_get($class, 'student_count', '—') }} Students
                                </span>

                                @if(data_get($class, 'is_class_teacher'))
                                    <span class="td-badge success">Class Teacher</span>
                                @else
                                    <span>{{ data_get($class, 'teacher_role', 'Subject Teacher') }}</span>
                                @endif
                            </div>

                            @if(data_get($class, 'attendance_rate') !== null)
                                <div style="margin-top:11px">
                                    <div class="td-stat-line">
                                        <span>Attendance</span>
                                        <b>{{ number_format((float)data_get($class, 'attendance_rate'), 1) }}%</b>
                                    </div>

                                    <div class="td-progress">
                                        <span style="width:{{ min(100,max(0,(float)data_get($class, 'attendance_rate'))) }}%"></span>
                                    </div>
                                </div>
                            @endif

                            @if(data_get($class, 'average_marks') !== null)
                                <div class="td-stat-line">
                                    <span>Academic average</span>
                                    <b>{{ number_format((float)data_get($class, 'average_marks'), 1) }}%</b>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="td-empty">
                    Your assigned classes will appear here when class and subject allocations are connected.
                </div>
            @endif
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Attendance Overview</h2>

                @if(\Illuminate\Support\Facades\Route::has('attendance.index'))
                    <a class="td-panel-link" href="{{ route('attendance.index') }}">
                        Mark Attendance →
                    </a>
                @endif
            </div>

            <div class="td-stat-line">
                <span>Attendance recorded</span>
                <b>
                    {{ $attendanceRate !== null ? number_format((float)$attendanceRate,1).'%' : '—' }}
                </b>
            </div>

            <div class="td-progress">
                <span style="width:{{ $attendanceRate !== null ? min(100,max(0,(float)$attendanceRate)) : 0 }}%"></span>
            </div>

            <div class="td-mini-grid">
                <div class="td-mini">
                    <b>{{ $formatNumber($presentCount) }}</b>
                    <span>Present</span>
                </div>

                <div class="td-mini">
                    <b>{{ $formatNumber($absentCount) }}</b>
                    <span>Absent</span>
                </div>

                <div class="td-mini">
                    <b>{{ $formatNumber($lateCount) }}</b>
                    <span>Late</span>
                </div>
            </div>

            <div style="margin-top:16px">
                <h2 style="font-size:11px">Students requiring attention</h2>

                @forelse($frequentAbsentees as $student)
                    <div class="td-record">
                        <div>
                            <b>{{ data_get($student, 'student_name', data_get($student, 'name', 'Student')) }}</b>
                            <small>
                                {{ data_get($student, 'class_name', '') }}
                                · {{ data_get($student, 'absence_count', '—') }} absences
                            </small>
                        </div>

                        <span class="td-badge danger">Review</span>
                    </div>
                @empty
                    <div class="td-empty">
                        No frequent-absence records are available.
                    </div>
                @endforelse
            </div>
        </div>

    </section>

    {{-- =========================================================
         6. HOMEWORK AND ASSIGNMENT MANAGEMENT
    ========================================================== --}}
    <section class="td-grid">

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Homework & Assignment Reviews</h2>

                @if(\Illuminate\Support\Facades\Route::has('assignments.index'))
                    <a class="td-panel-link" href="{{ route('assignments.index') }}">
                        View All →
                    </a>
                @endif
            </div>

            @if($teacherAssignments->isNotEmpty())
                <div class="td-table-wrap">
                    <table class="td-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Class</th>
                                <th>Due Date</th>
                                <th>Submitted</th>
                                <th>Pending</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($teacherAssignments as $assignment)
                                @php
                                    $assignmentStatus = data_get($assignment, 'status', 'Pending');
                                @endphp

                                <tr>
                                    <td>
                                        <strong>{{ data_get($assignment, 'title', 'Assignment') }}</strong>
                                    </td>

                                    <td>
                                        {{ data_get($assignment, 'class_name', '—') }}
                                    </td>

                                    <td>
                                        {{ $formatDate(data_get($assignment, 'due_date'), 'd M Y') }}
                                    </td>

                                    <td>
                                        {{ data_get($assignment, 'submitted_count', '—') }}
                                    </td>

                                    <td>
                                        {{ data_get($assignment, 'pending_count', '—') }}
                                    </td>

                                    <td>
                                        <span class="td-badge {{ $statusClass($assignmentStatus) }}">
                                            {{ ucfirst($assignmentStatus) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="td-empty">
                    Assignments and student submissions will appear here when assessment records are available.
                </div>
            @endif
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Assignment Summary</h2>
                <span class="td-muted">Current workload</span>
            </div>

            <div class="td-mini-grid">
                <div class="td-mini">
                    <b>{{ $formatNumber($dashboardStats['assignments_created'] ?? null) }}</b>
                    <span>Created</span>
                </div>

                <div class="td-mini">
                    <b>{{ $formatNumber($pendingReviews) }}</b>
                    <span>To review</span>
                </div>

                <div class="td-mini">
                    <b>{{ $formatNumber($completedReviews) }}</b>
                    <span>Reviewed</span>
                </div>
            </div>

            <div style="margin-top:17px">
                <h2 style="font-size:11px">Upcoming deadlines</h2>

                @forelse($teacherAssignments->filter(fn($item) => data_get($item, 'due_date'))->sortBy('due_date')->take(4) as $assignment)
                    <div class="td-record">
                        <div>
                            <b>{{ data_get($assignment, 'title', 'Assignment') }}</b>
                            <small>{{ data_get($assignment, 'class_name', '') }}</small>
                        </div>

                        <span class="td-badge warning">
                            {{ $formatDate(data_get($assignment, 'due_date'), 'd M') }}
                        </span>
                    </div>
                @empty
                    <div class="td-empty">No assignment deadlines available.</div>
                @endforelse
            </div>
        </div>

    </section>

    {{-- =========================================================
         7. EXAMINATION AND STUDENT PERFORMANCE
    ========================================================== --}}
    <section class="td-grid-three">

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Upcoming Examinations</h2>

                @if(\Illuminate\Support\Facades\Route::has('examinations.index'))
                    <a class="td-panel-link" href="{{ route('examinations.index') }}">
                        View All →
                    </a>
                @endif
            </div>

            @forelse($teacherExams as $exam)
                <div class="td-record">
                    <div>
                        <b>{{ data_get($exam, 'name', data_get($exam, 'subject_name', 'Examination')) }}</b>
                        <small>
                            {{ data_get($exam, 'class_name', '') }}
                            @if(data_get($exam, 'start_time'))
                                · {{ $formatDate(data_get($exam, 'start_time'), 'h:i A') }}
                            @endif
                        </small>
                    </div>

                    <span class="td-badge info">
                        {{ $formatDate(data_get($exam, 'exam_date', data_get($exam, 'start_date')), 'd M') }}
                    </span>
                </div>
            @empty
                <div class="td-empty">
                    Upcoming examinations assigned to you will appear here.
                </div>
            @endforelse

            <div class="td-stat-line" style="margin-top:14px">
                <span>Upcoming examination count</span>
                <b>{{ $formatNumber($upcomingExamCount) }}</b>
            </div>
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Class Performance</h2>
                <span class="td-muted">Average marks</span>
            </div>

            @if($classPerformance->isNotEmpty())
                <div class="td-chart">
                    <canvas id="teacherPerformanceChart"></canvas>
                </div>
            @else
                <div class="td-empty">
                    Class performance analytics will appear after examination results are available.
                </div>
            @endif
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Grade Distribution</h2>
                <span class="td-muted">Student results</span>
            </div>

            @if($gradeDistribution->isNotEmpty())
                <div class="td-chart">
                    <canvas id="teacherGradeChart"></canvas>
                </div>
            @else
                <div class="td-empty">
                    Grade distribution will appear after marks are published.
                </div>
            @endif
        </div>

    </section>

    {{-- =========================================================
         ATTENDANCE TREND
    ========================================================== --}}
    <section class="td-panel">
        <div class="td-panel-header">
            <div>
                <h2>Attendance Trend</h2>
                <div class="td-muted">Attendance across your assigned classes</div>
            </div>
        </div>

        @if($attendanceTrend->isNotEmpty())
            <div class="td-chart">
                <canvas id="teacherAttendanceChart"></canvas>
            </div>
        @else
            <div class="td-empty">
                Attendance trend data will appear here when historical attendance records are connected.
            </div>
        @endif
    </section>

    {{-- =========================================================
         8. CASPA TEACHER ASSISTANT
    ========================================================== --}}
    <section class="td-ai">
        <div>
            <div class="td-eyebrow" style="color:#7C3AED">
                AI POWERED TEACHING SUPPORT
            </div>

            <h2>CASPA Teacher Assistant</h2>

            <p>
                Your AI teaching companion for lesson planning, question generation,
                assignments and academic support.
            </p>

            <div class="td-ai-tools">
                <span class="td-ai-tool">Lesson Plans</span>
                <span class="td-ai-tool">MCQ Generation</span>
                <span class="td-ai-tool">Question Papers</span>
                <span class="td-ai-tool">Teaching Notes</span>
                <span class="td-ai-tool">Remedial Suggestions</span>
            </div>
        </div>

        @if($aiRoute)
            <a class="td-button primary"
               href="{{ route($aiRoute) }}"
               style="background:#5B3FA3;border-color:#5B3FA3">
                Open CASPA Assistant →
            </a>
        @else
            <span class="td-badge warning">Teacher Assistant route pending</span>
        @endif
    </section>

    {{-- =========================================================
         9. ANNOUNCEMENTS, EVENTS AND TASKS
    ========================================================== --}}
    <section class="td-grid-three">

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Teacher Announcements</h2>

                @if(\Illuminate\Support\Facades\Route::has('communication.index'))
                    <a class="td-panel-link" href="{{ route('communication.index') }}">
                        View All →
                    </a>
                @endif
            </div>

            @forelse($teacherAnnouncements as $announcement)
                <div class="td-record">
                    <div>
                        <b>{{ data_get($announcement, 'title', 'Announcement') }}</b>
                        <small>{{ $formatDate(data_get($announcement, 'published_at', data_get($announcement, 'created_at'))) }}</small>
                    </div>

                    <span class="td-badge {{ $statusClass(data_get($announcement, 'priority', 'Normal')) }}">
                        {{ ucfirst(data_get($announcement, 'priority', 'Normal')) }}
                    </span>
                </div>
            @empty
                <div class="td-empty">No recent teacher announcements.</div>
            @endforelse
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Events & Calendar</h2>

                @if(\Illuminate\Support\Facades\Route::has('events.dashboard'))
                    <a class="td-panel-link" href="{{ route('events.dashboard') }}">
                        View Calendar →
                    </a>
                @endif
            </div>

            @forelse($teacherEvents as $event)
                <div class="td-record">
                    <div>
                        <b>{{ data_get($event, 'title', 'School event') }}</b>
                        <small>{{ data_get($event, 'venue', 'Venue not announced') }}</small>
                    </div>

                    <span class="td-badge info">
                        {{ $formatDate(data_get($event, 'starts_at', data_get($event, 'date')), 'd M') }}
                    </span>
                </div>
            @empty
                <div class="td-empty">No upcoming teacher events.</div>
            @endforelse
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>My Tasks</h2>
                <span class="td-muted">To-do list</span>
            </div>

            @forelse($teacherTasks as $task)
                <div class="td-task">
                    <span class="td-task-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M9 11l3 3L22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                    </span>

                    <div>
                        <b>{{ data_get($task, 'title', 'Task') }}</b>
                        <small>
                            {{ data_get($task, 'due_date') ? 'Due '.$formatDate(data_get($task, 'due_date')) : 'No deadline' }}
                        </small>
                    </div>
                </div>
            @empty
                <div class="td-empty">
                    Your pending tasks and reminders will appear here.
                </div>
            @endforelse
        </div>

    </section>

    {{-- =========================================================
         LESSON PLAN AND RECENT ACTIVITY
    ========================================================== --}}
    <section class="td-grid">

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Lesson Planner & Syllabus Progress</h2>

                @if(\Illuminate\Support\Facades\Route::has('lesson-plans.index'))
                    <a class="td-panel-link" href="{{ route('lesson-plans.index') }}">
                        Open Planner →
                    </a>
                @endif
            </div>

            @if(!empty($lessonPlanProgress))
                @foreach($lessonPlanProgress as $lesson)
                    @php
                        $lessonProgress = min(100, max(0, (float)data_get($lesson, 'progress', 0)));
                    @endphp

                    <div style="margin-bottom:15px">
                        <div class="td-stat-line">
                            <span>{{ data_get($lesson, 'subject_name', 'Subject') }}</span>
                            <b>{{ number_format($lessonProgress, 0) }}%</b>
                        </div>

                        <div class="td-progress">
                            <span style="width:{{ $lessonProgress }}%"></span>
                        </div>

                        <div class="td-muted">
                            {{ data_get($lesson, 'completed_topics', '—') }}
                            of {{ data_get($lesson, 'total_topics', '—') }} topics completed
                        </div>
                    </div>
                @endforeach
            @else
                <div class="td-empty">
                    Your subject-wise lesson plans and syllabus progress will appear here.
                </div>
            @endif
        </div>

        <div class="td-panel">
            <div class="td-panel-header">
                <h2>Recent Activity</h2>
                <span class="td-muted">Latest updates</span>
            </div>

            @forelse($recentItems as $item)
                <div class="td-record">
                    <div>
                        <b>{{ data_get($item, 'title', data_get($item, 'name', data_get($item, 'reference', 'Activity'))) }}</b>

                        <small>
                            {{ $formatDate(data_get($item, 'created_at', data_get($item, 'date'))) }}
                        </small>
                    </div>

                    <span class="td-badge {{ $statusClass(data_get($item, 'status', 'Available')) }}">
                        {{ ucfirst(data_get($item, 'status', 'Updated')) }}
                    </span>
                </div>
            @empty
                <div class="td-empty">No recent activity available.</div>
            @endforelse
        </div>

    </section>

    <div class="td-footer">
        SchoolNexa Teacher Portal · Showing information available to your assigned classes and subjects.
    </div>

</div>

{{-- =============================================================
     CHARTS
============================================================= --}}
@if(
    $attendanceTrend->isNotEmpty() ||
    $classPerformance->isNotEmpty() ||
    $gradeDistribution->isNotEmpty()
)
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            if (typeof Chart === 'undefined') {
                return;
            }

            const attendanceCanvas = document.getElementById('teacherAttendanceChart');

            if (attendanceCanvas) {
                new Chart(attendanceCanvas, {
                    type: 'line',
                    data: {
                        labels: @json($attendanceLabels),
                        datasets: [{
                            label: 'Attendance',
                            data: @json($attendanceValues),
                            borderColor: '#176B87',
                            backgroundColor: 'rgba(23,107,135,.13)',
                            fill: true,
                            tension: .35,
                            borderWidth: 2,
                            pointRadius: 3
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: value => value + '%',
                                    font: { size: 10 }
                                },
                                grid: { color: '#EEF2F6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            const performanceCanvas = document.getElementById('teacherPerformanceChart');

            if (performanceCanvas) {
                new Chart(performanceCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($performanceLabels),
                        datasets: [{
                            label: 'Average Marks',
                            data: @json($performanceValues),
                            backgroundColor: '#176B87',
                            borderRadius: 6,
                            maxBarThickness: 35
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                min: 0,
                                max: 100,
                                ticks: {
                                    callback: value => value + '%',
                                    font: { size: 9 }
                                },
                                grid: { color: '#EEF2F6' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { size: 9 } }
                            }
                        }
                    }
                });
            }

            const gradeCanvas = document.getElementById('teacherGradeChart');

            if (gradeCanvas) {
                const gradeData = @json($gradeDistribution->values());

                new Chart(gradeCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: gradeData.map(item => item.grade ?? item.label ?? 'Grade'),
                        datasets: [{
                            data: gradeData.map(item => Number(item.count ?? item.total ?? 0)),
                            backgroundColor: [
                                '#176B87',
                                '#3B82F6',
                                '#5B3FA3',
                                '#16A34A',
                                '#D97706',
                                '#94A3B8'
                            ],
                            borderWidth: 2,
                            borderColor: '#FFFFFF'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 7,
                                    font: { size: 9 }
                                }
                            }
                        }
                    }
                });
            }

        });
    </script>
@endif

@endsection
