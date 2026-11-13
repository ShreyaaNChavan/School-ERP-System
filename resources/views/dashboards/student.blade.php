@extends('layouts.schoolnexa')

@section('title', 'Student Dashboard')

@section('content')
@php
    $s = $stats ?? [];
    $student = $studentProfile ?? $student ?? null;
    $attendance = $attendanceSummary ?? [];
    $fees = $feeSummary ?? [];
    $academic = $academicSummary ?? [];

    $money = fn($v) => '₹' . number_format((float)($v ?? 0), 0);
    $num = fn($v) => number_format((float)($v ?? 0));

    $fullName = trim(collect([
        $student->first_name ?? null,
        $student->middle_name ?? null,
        $student->last_name ?? null,
    ])->filter()->implode(' '));

    $fullName = $fullName ?: (auth()->user()->name ?? 'Student');

    $initials = collect(preg_split('/\s+/', $fullName))
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->implode('');

    $attendancePct = (float)($attendance['percentage'] ?? $s['attendance_percentage'] ?? 0);
    $assignmentPending = (int)($s['pending_assignments'] ?? 0);
    $upcomingExamsCount = (int)($s['upcoming_exams'] ?? 0);
    $average = $academic['average'] ?? $s['academic_average'] ?? null;
    $balance = (float)($fees['balance'] ?? $s['fee_balance'] ?? 0);

    $feeTotal = (float)($fees['total'] ?? $s['total_fees'] ?? 0);
    $feePaid = (float)($fees['paid'] ?? $s['fees_paid'] ?? 0);
    $feeProgress = $feeTotal > 0 ? min(100, max(0, ($feePaid / $feeTotal) * 100)) : 0;

    $todayTimetable = collect($todayTimetable ?? []);
    $assignments = collect($studentAssignments ?? $assignments ?? []);
    $exams = collect($studentExams ?? $exams ?? []);
    $resources = collect($learningResources ?? []);
    $announcements = collect($studentAnnouncements ?? $announcements ?? $recentItems ?? []);
    $events = collect($studentEvents ?? $upcomingItems ?? []);
    $payments = collect($feePayments ?? []);
    $subjects = collect($mySubjects ?? []);
    $attendanceTrend = collect($attendanceTrend ?? []);
    $subjectPerformance = collect($subjectPerformance ?? []);

    // Prepare chart data in PHP before passing it to JavaScript.
    // Keeping @json() arguments as simple variables avoids Blade parser errors.
    $trendLabels = $attendanceTrend->pluck('label')->filter()->values()->all();
    $trendValues = $attendanceTrend->map(function ($row) {
        return (float) data_get($row, 'percentage', data_get($row, 'average', 0));
    })->values()->all();

    $subjectLabels = $subjectPerformance->map(function ($row) {
        return data_get($row, 'subject_name', data_get($row, 'name', 'Subject'));
    })->values()->all();

    $subjectValues = $subjectPerformance->map(function ($row) {
        return (float) data_get(
            $row,
            'percentage',
            data_get($row, 'marks_percentage', data_get($row, 'average', 0))
        );
    })->values()->all();

    $quickLinks = [
        ['label' => 'My Attendance', 'icon' => 'calendar-check', 'route' => 'attendance.student', 'fallback' => 'attendance.index'],
        ['label' => 'Assignments', 'icon' => 'clipboard-list', 'route' => 'assignments.student.index', 'fallback' => 'assignments.index'],
        ['label' => 'Exam Results', 'icon' => 'chart-no-axes-column', 'route' => 'examinations.student.results', 'fallback' => 'examinations.results'],
        ['label' => 'Fee Details', 'icon' => 'wallet', 'route' => 'fees.student.index', 'fallback' => 'fees.index'],
        ['label' => 'Digital Library', 'icon' => 'book-open', 'route' => 'library.student.index', 'fallback' => 'library.index'],
        ['label' => 'My Timetable', 'icon' => 'clock', 'route' => 'timetable.student', 'fallback' => 'timetable.index'],
    ];

    $routeFor = function ($primary, $fallback = null) {
        if ($primary && \Illuminate\Support\Facades\Route::has($primary)) {
            return route($primary);
        }

        if ($fallback && \Illuminate\Support\Facades\Route::has($fallback)) {
            return route($fallback);
        }

        return null;
    };

    $dateFormat = fn($date, $format = 'd M Y') =>
        $date ? \Illuminate\Support\Carbon::parse($date)->format($format) : '—';
@endphp

<div class="std-dashboard">

<style>
.std-dashboard {
    --sd-navy:#123B5D;
    --sd-teal:#176B87;
    --sd-purple:#5B3FA3;
    --sd-sky:#3B82F6;
    --sd-green:#16A34A;
    --sd-amber:#D97706;
    --sd-red:#DC2626;
    --sd-text:#25313C;
    --sd-muted:#64748B;
    --sd-border:#E2E8F0;
    --sd-soft:#F8FAFC;
    color:var(--sd-text);
    font-family:Inter,ui-sans-serif,system-ui,sans-serif;
}

.std-dashboard * { box-sizing:border-box; }

.sd-top {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:18px;
    margin-bottom:20px;
}

.sd-eyebrow {
    font-size:11px;
    font-weight:700;
    letter-spacing:.09em;
    text-transform:uppercase;
    color:var(--sd-teal);
    margin-bottom:6px;
}

.sd-title {
    font-size:23px;
    line-height:1.25;
    font-weight:750;
    color:var(--sd-navy);
    margin:0;
}

.sd-subtitle {
    font-size:13px;
    color:var(--sd-muted);
    margin:6px 0 0;
}

.sd-top-actions {
    display:flex;
    gap:9px;
    flex-wrap:wrap;
}

.sd-btn {
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    min-height:39px;
    padding:0 14px;
    border-radius:9px;
    border:1px solid var(--sd-border);
    background:#fff;
    color:var(--sd-navy);
    font-size:12px;
    font-weight:650;
    text-decoration:none;
    cursor:pointer;
    transition:.18s;
}

.sd-btn:hover {
    border-color:#94A3B8;
    background:#F8FAFC;
}

.sd-btn-primary {
    background:var(--sd-navy);
    border-color:var(--sd-navy);
    color:#fff;
}

.sd-btn-primary:hover {
    background:#0D2F4A;
    color:#fff;
}

.std-dashboard svg {
    width:17px;
    height:17px;
    stroke:currentColor;
    fill:none;
    stroke-width:1.8;
    stroke-linecap:round;
    stroke-linejoin:round;
}

.sd-hero {
    position:relative;
    overflow:hidden;
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    padding:22px 24px;
    border-radius:16px;
    background:linear-gradient(115deg,#123B5D 0%,#176B87 66%,#2585A0 100%);
    color:#fff;
    margin-bottom:18px;
    box-shadow:0 8px 22px rgba(18,59,93,.12);
}

.sd-hero:after {
    content:"";
    position:absolute;
    width:250px;
    height:250px;
    border:1px solid rgba(255,255,255,.12);
    border-radius:50%;
    right:8%;
    top:-155px;
    box-shadow:0 0 0 28px rgba(255,255,255,.035),
               0 0 0 58px rgba(255,255,255,.025);
    pointer-events:none;
}

.sd-hero-main {
    display:flex;
    align-items:center;
    gap:15px;
    position:relative;
    z-index:1;
    min-width:0;
}

.sd-avatar {
    width:54px;
    height:54px;
    border-radius:15px;
    display:grid;
    place-items:center;
    background:rgba(255,255,255,.16);
    border:1px solid rgba(255,255,255,.25);
    font-size:19px;
    font-weight:750;
    overflow:hidden;
    flex:0 0 auto;
}

.sd-avatar img {
    width:100%;
    height:100%;
    object-fit:cover;
}

.sd-hero h2 {
    font-size:20px;
    font-weight:750;
    margin:0 0 5px;
    color:#fff;
}

.sd-hero p {
    font-size:12px;
    color:rgba(255,255,255,.82);
    margin:0;
}

.sd-hero-meta {
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    margin-top:9px;
}

.sd-hero-meta span {
    font-size:10px;
    font-weight:650;
    padding:5px 8px;
    border-radius:6px;
    background:rgba(255,255,255,.13);
    color:#fff;
}

.sd-hero-side {
    position:relative;
    z-index:1;
    text-align:right;
    min-width:145px;
}

.sd-hero-side small {
    display:block;
    font-size:11px;
    color:rgba(255,255,255,.75);
}

.sd-hero-side strong {
    display:block;
    font-size:20px;
    margin-top:3px;
    color:#fff;
}

.sd-kpis {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:13px;
    margin-bottom:18px;
}

.sd-kpi {
    background:#fff;
    border:1px solid var(--sd-border);
    border-radius:14px;
    padding:15px 16px;
    min-width:0;
    box-shadow:0 2px 7px rgba(15,23,42,.025);
    transition:transform .18s,box-shadow .18s;
}

.sd-kpi:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(15,23,42,.06);
}

.sd-kpi-top {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:8px;
}

.sd-kpi-label {
    font-size:11px;
    font-weight:650;
    color:var(--sd-muted);
}

.sd-kpi-icon {
    width:34px;
    height:34px;
    border-radius:10px;
    display:grid;
    place-items:center;
}

.sd-kpi-icon svg {
    width:17px;
    height:17px;
}

.sd-kpi-value {
    font-size:25px;
    line-height:1.15;
    font-weight:750;
    color:var(--sd-navy);
    margin-top:10px;
    white-space:nowrap;
}

.sd-kpi-foot {
    font-size:10px;
    color:var(--sd-muted);
    margin-top:5px;
}

.sd-kpi-foot.good { color:var(--sd-green); }
.sd-kpi-foot.warn { color:var(--sd-amber); }

.sd-bg-blue { background:#EFF6FF;color:#2563EB; }
.sd-bg-teal { background:#ECFEFF;color:#0F766E; }
.sd-bg-purple { background:#F5F3FF;color:#6D28D9; }
.sd-bg-amber { background:#FFFBEB;color:#B45309; }

.sd-layout {
    display:grid;
    grid-template-columns:minmax(0,1.55fr) minmax(280px,.85fr);
    gap:16px;
    margin-bottom:16px;
}

.sd-card {
    background:#fff;
    border:1px solid var(--sd-border);
    border-radius:14px;
    padding:17px 18px;
    min-width:0;
    box-shadow:0 2px 7px rgba(15,23,42,.025);
}

.sd-card-head {
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:12px;
    margin-bottom:14px;
}

.sd-card-title {
    font-size:14px;
    font-weight:750;
    color:var(--sd-navy);
    margin:0;
}

.sd-card-desc {
    font-size:11px;
    color:var(--sd-muted);
    margin:4px 0 0;
}

.sd-link {
    font-size:11px;
    font-weight:700;
    color:var(--sd-teal);
    text-decoration:none;
    white-space:nowrap;
}

.sd-link:hover { text-decoration:underline; }

.sd-pill {
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:4px 8px;
    border-radius:20px;
    font-size:10px;
    font-weight:700;
    background:#F1F5F9;
    color:#475569;
    white-space:nowrap;
}

.sd-pill.green { background:#ECFDF5;color:#15803D; }
.sd-pill.amber { background:#FFFBEB;color:#B45309; }
.sd-pill.red { background:#FEF2F2;color:#B91C1C; }
.sd-pill.blue { background:#EFF6FF;color:#1D4ED8; }

.sd-table-wrap { overflow-x:auto; }

.sd-table {
    width:100%;
    border-collapse:collapse;
    min-width:520px;
}

.sd-table th {
    text-align:left;
    background:#F8FAFC;
    color:#64748B;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.045em;
    font-weight:750;
    padding:10px 11px;
    border-bottom:1px solid var(--sd-border);
}

.sd-table td {
    font-size:11px;
    padding:11px;
    border-bottom:1px solid #EEF2F6;
    vertical-align:middle;
}

.sd-table tr:last-child td { border-bottom:0; }
.sd-subject { font-weight:700;color:#334155; }
.sd-secondary { font-size:10px;color:var(--sd-muted);margin-top:3px; }
.sd-time { font-weight:700;color:var(--sd-navy);white-space:nowrap; }

.sd-empty {
    padding:23px 12px;
    text-align:center;
    color:var(--sd-muted);
    font-size:11px;
}

.sd-empty strong {
    display:block;
    color:#475569;
    font-size:12px;
    margin-bottom:4px;
}

.sd-empty-icon {
    width:35px;
    height:35px;
    margin:0 auto 8px;
    border-radius:11px;
    background:#F1F5F9;
    color:#64748B;
    display:grid;
    place-items:center;
}

.sd-empty-icon svg { width:17px;height:17px; }

.sd-progress {
    height:8px;
    background:#EAF0F5;
    border-radius:20px;
    overflow:hidden;
}

.sd-progress span {
    height:100%;
    display:block;
    border-radius:20px;
    background:linear-gradient(90deg,#176B87,#3B82F6);
}

.sd-progress-row {
    display:flex;
    justify-content:space-between;
    gap:10px;
    font-size:10px;
    margin:12px 0 6px;
}

.sd-progress-row strong { color:var(--sd-navy); }

.sd-stat-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:8px;
    margin-top:14px;
}

.sd-stat {
    padding:10px;
    border-radius:10px;
    background:#F8FAFC;
    min-width:0;
}

.sd-stat strong {
    display:block;
    font-size:17px;
    color:var(--sd-navy);
    overflow-wrap:anywhere;
}

.sd-stat span {
    font-size:10px;
    color:var(--sd-muted);
}

.sd-chart {
    height:210px;
    position:relative;
}

.sd-chart canvas { max-width:100%; }

.sd-list {
    display:flex;
    flex-direction:column;
}

.sd-list-item {
    display:flex;
    align-items:flex-start;
    gap:10px;
    padding:11px 0;
    border-bottom:1px solid #EEF2F6;
}

.sd-list-item:last-child {
    border-bottom:0;
    padding-bottom:0;
}

.sd-list-icon {
    width:33px;
    height:33px;
    flex:0 0 auto;
    display:grid;
    place-items:center;
    border-radius:10px;
    background:#F1F5F9;
    color:var(--sd-teal);
}

.sd-list-icon svg { width:16px;height:16px; }

.sd-list-main { min-width:0;flex:1; }

.sd-list-main strong {
    font-size:11px;
    color:#334155;
    display:block;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}

.sd-list-main small {
    display:block;
    font-size:10px;
    color:var(--sd-muted);
    margin-top:3px;
}

.sd-list-side {
    font-size:10px;
    color:var(--sd-muted);
    text-align:right;
    white-space:nowrap;
}

.sd-tabs {
    display:flex;
    gap:6px;
    flex-wrap:wrap;
    border-bottom:1px solid var(--sd-border);
    margin-bottom:12px;
}

.sd-tab {
    border:0;
    background:transparent;
    padding:9px 12px;
    font-size:11px;
    font-weight:700;
    color:var(--sd-muted);
    cursor:pointer;
    border-bottom:2px solid transparent;
}

.sd-tab.active {
    color:var(--sd-teal);
    border-bottom-color:var(--sd-teal);
}

.sd-tab-panel { display:none; }
.sd-tab-panel.active { display:block; }

.sd-search {
    width:100%;
    max-width:260px;
    min-height:35px;
    border:1px solid var(--sd-border);
    border-radius:8px;
    padding:0 11px;
    font-size:11px;
    outline:none;
}

.sd-search:focus {
    border-color:var(--sd-teal);
    box-shadow:0 0 0 3px rgba(23,107,135,.09);
}

.sd-quick-grid {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:9px;
}

.sd-quick {
    display:flex;
    align-items:center;
    gap:10px;
    padding:11px;
    border:1px solid var(--sd-border);
    border-radius:10px;
    text-decoration:none;
    color:#334155;
    font-size:11px;
    font-weight:700;
    transition:.18s;
    background:#fff;
    min-width:0;
}

.sd-quick:hover {
    border-color:#94A3B8;
    background:#F8FAFC;
    transform:translateY(-1px);
}

.sd-quick-icon {
    width:31px;
    height:31px;
    border-radius:9px;
    display:grid;
    place-items:center;
    background:#EFF6FF;
    color:#2563EB;
    flex:0 0 auto;
}

.sd-quick-icon svg { width:16px;height:16px; }

.sd-ai {
    border-radius:16px;
    padding:20px;
    background:linear-gradient(120deg,#172F55,#344C83);
    color:#fff;
    margin-bottom:16px;
}

.sd-ai-top {
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:15px;
}

.sd-ai-mark {
    width:40px;
    height:40px;
    border-radius:12px;
    display:grid;
    place-items:center;
    background:rgba(255,255,255,.14);
}

.sd-ai-top h3 {
    margin:0;
    font-size:16px;
    color:#fff;
}

.sd-ai-top p {
    margin:4px 0 0;
    font-size:11px;
    color:rgba(255,255,255,.75);
}

.sd-ai-prompts {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    margin-bottom:12px;
}

.sd-ai-prompt {
    border:1px solid rgba(255,255,255,.25);
    border-radius:20px;
    padding:7px 11px;
    color:#fff;
    background:rgba(255,255,255,.09);
    font-size:10px;
    cursor:pointer;
}

.sd-ai-prompt:hover { background:rgba(255,255,255,.18); }

.sd-ai-input {
    display:flex;
    gap:8px;
    padding:5px;
    border-radius:10px;
    background:#fff;
}

.sd-ai-input input {
    flex:1;
    min-width:0;
    border:0;
    outline:0;
    padding:9px;
    font-size:12px;
    color:#25313C;
}

.sd-ai-input button {
    border:0;
    border-radius:8px;
    padding:0 16px;
    background:#176B87;
    color:#fff;
    font-size:11px;
    font-weight:700;
    cursor:pointer;
}

.sd-ai-input button:disabled { opacity:.6;cursor:not-allowed; }

.sd-note {
    font-size:10px;
    color:rgba(255,255,255,.72);
    margin-top:9px;
}

.sd-footer-note {
    text-align:center;
    font-size:10px;
    color:var(--sd-muted);
    padding:8px 0 15px;
}

.sd-hidden { display:none !important; }

@media(max-width:1000px) {
    .sd-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-layout { grid-template-columns:1fr; }
}

@media(max-width:650px) {
    .sd-top { flex-direction:column; }
    .sd-hero { align-items:flex-start;flex-direction:column; }
    .sd-hero-side { text-align:left; }
    .sd-quick-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-card { padding:14px; }
    .sd-title { font-size:20px; }
    .sd-ai-input { flex-direction:column; }
    .sd-ai-input button { min-height:38px; }
    .sd-card-head { flex-wrap:wrap; }
    .sd-search { max-width:100%; }
}

@media(max-width:420px) {
    .sd-kpis { grid-template-columns:1fr 1fr;gap:8px; }
    .sd-kpi { padding:12px; }
    .sd-kpi-value { font-size:21px; }
    .sd-quick-grid { grid-template-columns:1fr; }
    .sd-hero { padding:18px; }
    .sd-hero-main { align-items:flex-start; }
    .sd-hero h2 { font-size:17px; }
}
</style>

{{-- PAGE HEADER --}}
<div class="sd-top">
    <div>
        <div class="sd-eyebrow">Student workspace</div>
        <h1 class="sd-title">My Dashboard</h1>
        <p class="sd-subtitle">Your academics, activities and learning journey at a glance.</p>
    </div>

    <div class="sd-top-actions">
        @if($url = $routeFor('student.profile', 'students.profile'))
            <a href="{{ $url }}" class="sd-btn">
                <i data-lucide="user-round"></i>
                My Profile
            </a>
        @endif
    </div>
</div>

{{-- STUDENT HERO --}}
<section class="sd-hero">
    <div class="sd-hero-main">
        <div class="sd-avatar">
            @if(!empty($student?->photo_path))
                <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Student photo">
            @else
                {{ $initials }}
            @endif
        </div>

        <div>
            <h2>Welcome back, {{ $fullName }}!</h2>
            <p>Keep learning, stay curious and make today count.</p>

            <div class="sd-hero-meta">
                <span>Admission No: {{ $student->admission_number ?? 'Not assigned' }}</span>
                <span>Class: {{ $className ?? 'Not assigned' }}</span>
                <span>Section: {{ $sectionName ?? '—' }}</span>
                <span>Roll No: {{ $rollNumber ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="sd-hero-side">
        <small>Academic Year</small>
        <strong>{{ $academicYearName ?? 'Current Year' }}</strong>
        <small style="margin-top:5px">{{ $campusName ?? 'School Campus' }}</small>
    </div>
</section>

{{-- KPI CARDS --}}
<section class="sd-kpis">
    <div class="sd-kpi">
        <div class="sd-kpi-top">
            <span class="sd-kpi-label">Attendance</span>
            <span class="sd-kpi-icon sd-bg-teal"><i data-lucide="calendar-check"></i></span>
        </div>
        <div class="sd-kpi-value">{{ number_format($attendancePct, 1) }}%</div>
        <div class="sd-kpi-foot {{ $attendancePct >= 75 ? 'good' : 'warn' }}">
            {{ $attendancePct >= 75 ? 'Attendance is on track' : 'Attendance needs attention' }}
        </div>
    </div>

    <div class="sd-kpi">
        <div class="sd-kpi-top">
            <span class="sd-kpi-label">Pending Assignments</span>
            <span class="sd-kpi-icon sd-bg-purple"><i data-lucide="clipboard-list"></i></span>
        </div>
        <div class="sd-kpi-value">{{ $num($assignmentPending) }}</div>
        <div class="sd-kpi-foot">Assignments awaiting submission</div>
    </div>

    <div class="sd-kpi">
        <div class="sd-kpi-top">
            <span class="sd-kpi-label">Upcoming Exams</span>
            <span class="sd-kpi-icon sd-bg-blue"><i data-lucide="graduation-cap"></i></span>
        </div>
        <div class="sd-kpi-value">{{ $num($upcomingExamsCount) }}</div>
        <div class="sd-kpi-foot">Scheduled assessments</div>
    </div>

    <div class="sd-kpi">
        <div class="sd-kpi-top">
            <span class="sd-kpi-label">Academic Average</span>
            <span class="sd-kpi-icon sd-bg-amber"><i data-lucide="chart-no-axes-combined"></i></span>
        </div>
        <div class="sd-kpi-value">
            {{ $average !== null ? number_format((float)$average, 1).'%' : '—' }}
        </div>
        <div class="sd-kpi-foot">Based on published results</div>
    </div>
</section>

{{-- TIMETABLE AND ATTENDANCE --}}
<div class="sd-layout">
    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Today's timetable</h2>
                <p class="sd-card-desc">Your classes and schedule for today</p>
            </div>

            @if($url = $routeFor('timetable.student', 'timetable.index'))
                <a class="sd-link" href="{{ $url }}">Full timetable →</a>
            @endif
        </div>

        @if($todayTimetable->isNotEmpty())
            <div class="sd-table-wrap">
                <table class="sd-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Subject</th>
                            <th>Teacher</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($todayTimetable as $period)
                            <tr>
                                <td class="sd-time">
                                    {{ data_get($period, 'start_time', '—') }}
                                    - {{ data_get($period, 'end_time', '—') }}
                                </td>
                                <td>
                                    <div class="sd-subject">{{ data_get($period, 'subject_name', 'Subject') }}</div>
                                    <div class="sd-secondary">{{ data_get($period, 'period_name', 'Class period') }}</div>
                                </td>
                                <td>{{ data_get($period, 'teacher_name', '—') }}</td>
                                <td>{{ data_get($period, 'room_number', '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="calendar-clock"></i></div>
                <strong>No timetable available</strong>
                Your scheduled classes will appear here.
            </div>
        @endif
    </section>

    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Attendance overview</h2>
                <p class="sd-card-desc">Your attendance record</p>
            </div>

            <span class="sd-pill {{ $attendancePct >= 75 ? 'green' : 'amber' }}">
                {{ $attendancePct >= 75 ? 'On track' : 'Needs attention' }}
            </span>
        </div>

        <div class="sd-progress-row">
            <span>Overall attendance</span>
            <strong>{{ number_format($attendancePct, 1) }}%</strong>
        </div>

        <div class="sd-progress">
            <span style="width:{{ min(100, max(0, $attendancePct)) }}%"></span>
        </div>

        <div class="sd-stat-grid">
            <div class="sd-stat">
                <strong>{{ $num($attendance['present'] ?? $s['present_days'] ?? 0) }}</strong>
                <span>Present</span>
            </div>
            <div class="sd-stat">
                <strong>{{ $num($attendance['absent'] ?? $s['absent_days'] ?? 0) }}</strong>
                <span>Absent</span>
            </div>
            <div class="sd-stat">
                <strong>{{ $num($attendance['late'] ?? $s['late_days'] ?? 0) }}</strong>
                <span>Late</span>
            </div>
        </div>

        @if($attendancePct < 75)
            <div style="margin-top:14px;padding:10px;border-radius:9px;background:#FFFBEB;color:#92400E;font-size:10px">
                <strong>Attendance alert:</strong>
                Your attendance is below 75%. Attend upcoming classes regularly.
            </div>
        @endif

        @if($url = $routeFor('attendance.student', 'attendance.index'))
            <div style="margin-top:14px">
                <a href="{{ $url }}" class="sd-btn">View attendance</a>
            </div>
        @endif
    </section>
</div>

{{-- LEARNING SPACE --}}
<section class="sd-card" style="margin-bottom:16px">
    <div class="sd-card-head">
        <div>
            <h2 class="sd-card-title">My learning space</h2>
            <p class="sd-card-desc">Manage your academic work and learning materials</p>
        </div>

        <input
            type="search"
            class="sd-search"
            id="sdLearningSearch"
            placeholder="Search in this section..."
            aria-label="Search learning space"
        >
    </div>

    <div class="sd-tabs" role="tablist" aria-label="Learning space sections">
        <button class="sd-tab active" type="button" role="tab" aria-selected="true" data-tab="assignments">Assignments</button>
        <button class="sd-tab" type="button" role="tab" aria-selected="false" data-tab="exams">Exams</button>
        <button class="sd-tab" type="button" role="tab" aria-selected="false" data-tab="results">Results</button>
        <button class="sd-tab" type="button" role="tab" aria-selected="false" data-tab="resources">Learning Resources</button>
        <button class="sd-tab" type="button" role="tab" aria-selected="false" data-tab="subjects">My Subjects</button>
    </div>

    {{-- ASSIGNMENTS --}}
    <div class="sd-tab-panel active" id="sd-panel-assignments" role="tabpanel">
        @if($assignments->isNotEmpty())
            <div class="sd-table-wrap">
                <table class="sd-table sd-searchable">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Subject</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            @php
                                $status = strtolower((string)data_get(
                                    $assignment,
                                    'submission_status',
                                    data_get($assignment, 'status', 'pending')
                                ));
                            @endphp
                            <tr>
                                <td>
                                    <div class="sd-subject">{{ data_get($assignment, 'title', 'Assignment') }}</div>
                                    <div class="sd-secondary">{{ data_get($assignment, 'description', '') }}</div>
                                </td>
                                <td>{{ data_get($assignment, 'subject_name', '—') }}</td>
                                <td>{{ $dateFormat(data_get($assignment, 'due_date')) }}</td>
                                <td>
                                    <span class="sd-pill {{ in_array($status, ['submitted', 'evaluated', 'completed']) ? 'green' : (in_array($status, ['late', 'overdue']) ? 'red' : 'amber') }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($url = $routeFor('assignments.student.index', 'assignments.index'))
                                        <a href="{{ $url }}" class="sd-link">Open →</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="clipboard-check"></i></div>
                <strong>No assignments to display</strong>
                Your assigned homework and submission status will appear here.
            </div>
        @endif
    </div>

    {{-- EXAMS --}}
    <div class="sd-tab-panel" id="sd-panel-exams" role="tabpanel">
        @if($exams->isNotEmpty())
            <div class="sd-table-wrap">
                <table class="sd-table sd-searchable">
                    <thead>
                        <tr>
                            <th>Examination</th>
                            <th>Subject</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Maximum Marks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($exams as $exam)
                            <tr>
                                <td class="sd-subject">
                                    {{ data_get($exam, 'exam_name', data_get($exam, 'name', 'Examination')) }}
                                </td>
                                <td>{{ data_get($exam, 'subject_name', '—') }}</td>
                                <td>{{ $dateFormat(data_get($exam, 'exam_date')) }}</td>
                                <td>{{ data_get($exam, 'start_time', '—') }}</td>
                                <td>{{ data_get($exam, 'maximum_marks', '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="graduation-cap"></i></div>
                <strong>No upcoming examinations</strong>
                Your examination schedule will appear here.
            </div>
        @endif
    </div>

    {{-- RESULTS --}}
    <div class="sd-tab-panel" id="sd-panel-results" role="tabpanel">
        <div class="sd-layout" style="margin-bottom:0">
            <div>
                <h3 class="sd-card-title" style="margin-bottom:12px">Subject performance</h3>

                @if($subjectPerformance->isNotEmpty())
                    <div class="sd-chart">
                        <canvas id="sdSubjectChart"></canvas>
                    </div>
                @else
                    <div class="sd-empty">
                        <div class="sd-empty-icon"><i data-lucide="chart-column"></i></div>
                        <strong>No published results yet</strong>
                        Your subject-wise performance will appear here.
                    </div>
                @endif
            </div>

            <div>
                <h3 class="sd-card-title" style="margin-bottom:12px">Academic summary</h3>
                <div class="sd-stat-grid">
                    <div class="sd-stat">
                        <strong>{{ $average !== null ? number_format((float)$average, 1).'%' : '—' }}</strong>
                        <span>Average</span>
                    </div>
                    <div class="sd-stat">
                        <strong>{{ $academic['grade'] ?? '—' }}</strong>
                        <span>Grade</span>
                    </div>
                    <div class="sd-stat">
                        <strong>{{ $academic['rank'] ?? '—' }}</strong>
                        <span>Rank</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- RESOURCES --}}
    <div class="sd-tab-panel" id="sd-panel-resources" role="tabpanel">
        @if($resources->isNotEmpty())
            <div class="sd-quick-grid">
                @foreach($resources as $resource)
                    <div class="sd-quick">
                        <span class="sd-quick-icon"><i data-lucide="file-text"></i></span>
                        <span>
                            {{ data_get($resource, 'title', 'Learning material') }}
                            <small style="display:block;font-weight:500;color:#64748B;margin-top:3px">
                                {{ data_get($resource, 'subject_name', 'Study resource') }}
                            </small>
                        </span>
                        @if(data_get($resource, 'url'))
                            <a href="{{ data_get($resource, 'url') }}" target="_blank" rel="noopener noreferrer" class="sd-link">Open</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="library"></i></div>
                <strong>No learning resources available</strong>
                Notes, worksheets, PDFs and study materials shared by your teachers will appear here.
            </div>
        @endif
    </div>

    {{-- SUBJECTS --}}
    <div class="sd-tab-panel" id="sd-panel-subjects" role="tabpanel">
        @if($subjects->isNotEmpty())
            <div class="sd-quick-grid">
                @foreach($subjects as $subject)
                    <div class="sd-quick">
                        <span class="sd-quick-icon"><i data-lucide="book-marked"></i></span>
                        <span>
                            {{ data_get($subject, 'subject_name', data_get($subject, 'name', 'Subject')) }}
                            <small style="display:block;font-weight:500;color:#64748B;margin-top:3px">
                                {{ data_get($subject, 'teacher_name', '') }}
                            </small>
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="books"></i></div>
                <strong>Subjects will appear here</strong>
                Your enrolled subjects and assigned teachers will be listed here.
            </div>
        @endif
    </div>
</section>

{{-- ACADEMIC PROGRESS AND FEES --}}
<div class="sd-layout">
    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Academic progress</h2>
                <p class="sd-card-desc">Your performance over recent assessments</p>
            </div>

            <span class="sd-pill blue">
                {{ $average !== null ? number_format((float)$average, 1).'%' : 'No data yet' }}
            </span>
        </div>

        @if($attendanceTrend->isNotEmpty())
            <div class="sd-chart">
                <canvas id="sdProgressChart"></canvas>
            </div>
        @elseif($subjectPerformance->isNotEmpty())
            <div class="sd-chart">
                <canvas id="sdSubjectChartMain"></canvas>
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="trending-up"></i></div>
                <strong>Your progress chart is getting ready</strong>
                Once marks and assessment data are published, your performance trend will be shown here.
            </div>
        @endif
    </section>

    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Fees and payments</h2>
                <p class="sd-card-desc">Your fee account summary</p>
            </div>

            <span class="sd-pill {{ $balance > 0 ? 'amber' : 'green' }}">
                {{ $balance > 0 ? 'Payment due' : 'Up to date' }}
            </span>
        </div>

        <div class="sd-stat-grid" style="margin-top:0">
            <div class="sd-stat">
                <strong>{{ $money($feeTotal) }}</strong>
                <span>Total fees</span>
            </div>
            <div class="sd-stat">
                <strong>{{ $money($feePaid) }}</strong>
                <span>Paid</span>
            </div>
            <div class="sd-stat">
                <strong>{{ $money($balance) }}</strong>
                <span>Balance</span>
            </div>
        </div>

        <div class="sd-progress-row">
            <span>Payment progress</span>
            <strong>{{ $feeTotal > 0 ? number_format($feeProgress, 1).'%' : '—' }}</strong>
        </div>

        <div class="sd-progress">
            <span style="width:{{ $feeProgress }}%"></span>
        </div>

        @if($payments->isNotEmpty())
            <div class="sd-list" style="margin-top:9px">
                @foreach($payments->take(3) as $payment)
                    <div class="sd-list-item">
                        <span class="sd-list-icon"><i data-lucide="receipt"></i></span>
                        <div class="sd-list-main">
                            <strong>{{ data_get($payment, 'receipt_number', 'Payment') }}</strong>
                            <small>{{ $dateFormat(data_get($payment, 'paid_at')) }}</small>
                        </div>
                        <div class="sd-list-side">
                            <strong style="color:#15803D">
                                {{ $money(data_get($payment, 'amount', 0)) }}
                            </strong>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if($url = $routeFor('fees.student.index', 'fees.index'))
            <div style="margin-top:13px">
                <a class="sd-btn sd-btn-primary" href="{{ $url }}">View fee details</a>
            </div>
        @endif
    </section>
</div>

{{-- ANNOUNCEMENTS AND EVENTS --}}
<div class="sd-layout">
    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Announcements and notices</h2>
                <p class="sd-card-desc">Important updates shared by your school</p>
            </div>

            @if($url = $routeFor('announcements.student.index', 'announcements.index'))
                <a class="sd-link" href="{{ $url }}">View all →</a>
            @endif
        </div>

        @if($announcements->isNotEmpty())
            <div class="sd-list">
                @foreach($announcements->take(5) as $notice)
                    <div class="sd-list-item">
                        <span class="sd-list-icon"><i data-lucide="megaphone"></i></span>
                        <div class="sd-list-main">
                            <strong>{{ data_get($notice, 'title', 'School announcement') }}</strong>
                            <small>
                                {{ \Illuminate\Support\Str::limit(
                                    strip_tags((string)data_get($notice, 'content', data_get($notice, 'description', 'School update'))),
                                    105
                                ) }}
                            </small>
                        </div>
                        <div class="sd-list-side">
                            {{ data_get($notice, 'published_at') ? $dateFormat(data_get($notice, 'published_at'), 'd M') : '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="bell"></i></div>
                <strong>No new announcements</strong>
                School notices and important updates will appear here.
            </div>
        @endif
    </section>

    <section class="sd-card">
        <div class="sd-card-head">
            <div>
                <h2 class="sd-card-title">Upcoming events</h2>
                <p class="sd-card-desc">Activities and events at your school</p>
            </div>

            @if($url = $routeFor('events.student.index', 'events.index'))
                <a class="sd-link" href="{{ $url }}">View calendar →</a>
            @endif
        </div>

        @if($events->isNotEmpty())
            <div class="sd-list">
                @foreach($events->take(5) as $event)
                    <div class="sd-list-item">
                        <span class="sd-list-icon"><i data-lucide="calendar-days"></i></span>
                        <div class="sd-list-main">
                            <strong>{{ data_get($event, 'title', 'School event') }}</strong>
                            <small>{{ data_get($event, 'venue', 'School campus') }}</small>
                        </div>
                        <div class="sd-list-side">
                            @if(data_get($event, 'starts_at'))
                                {{ $dateFormat(data_get($event, 'starts_at'), 'd M, g:i A') }}
                            @elseif(data_get($event, 'event_date'))
                                {{ $dateFormat(data_get($event, 'event_date'), 'd M') }}
                            @else
                                Upcoming
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="sd-empty">
                <div class="sd-empty-icon"><i data-lucide="calendar-heart"></i></div>
                <strong>No upcoming events listed</strong>
                School events and activities will appear here.
            </div>
        @endif
    </section>
</div>

{{-- QUICK ACCESS --}}
<section class="sd-card" style="margin-bottom:16px">
    <div class="sd-card-head">
        <div>
            <h2 class="sd-card-title">Quick access</h2>
            <p class="sd-card-desc">Open your most-used student services</p>
        </div>
    </div>

    <div class="sd-quick-grid">
        @foreach($quickLinks as $quick)
            @php $quickUrl = $routeFor($quick['route'], $quick['fallback']); @endphp

            @if($quickUrl)
                <a class="sd-quick" href="{{ $quickUrl }}">
                    <span class="sd-quick-icon">
                        <i data-lucide="{{ $quick['icon'] }}"></i>
                    </span>
                    <span>{{ $quick['label'] }}</span>
                </a>
            @endif
        @endforeach

        @foreach([
            ['route' => 'student.transport', 'fallback' => 'transport.student.index', 'icon' => 'bus-front', 'label' => 'Transport'],
            ['route' => 'student.hostel', 'fallback' => 'hostel.student.index', 'icon' => 'building-2', 'label' => 'Hostel'],
            ['route' => 'student.certificates', 'fallback' => 'certificates.student.index', 'icon' => 'award', 'label' => 'Certificates'],
            ['route' => 'student.grievances', 'fallback' => 'grievances.student.index', 'icon' => 'message-square-warning', 'label' => 'Help & Grievance'],
            ['route' => 'student.career', 'fallback' => 'career.student.index', 'icon' => 'briefcase-business', 'label' => 'Career Guidance'],
        ] as $quick)
            @if($url = $routeFor($quick['route'], $quick['fallback']))
                <a class="sd-quick" href="{{ $url }}">
                    <span class="sd-quick-icon"><i data-lucide="{{ $quick['icon'] }}"></i></span>
                    <span>{{ $quick['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</section>

{{-- CASPA STUDENT LEARNING --}}
<section class="sd-ai" id="sdStudentAi">
    <div class="sd-ai-top">
        <span class="sd-ai-mark"><i data-lucide="sparkles"></i></span>
        <div>
            <h3>CASPA Student Learning</h3>
            <p>Your AI-powered study companion for learning, practice and revision.</p>
        </div>
    </div>

    <div class="sd-ai-prompts">
        <button class="sd-ai-prompt" type="button" data-prompt="Explain a difficult topic in simple words">
            Explain a topic
        </button>
        <button class="sd-ai-prompt" type="button" data-prompt="Create a short quiz for my current subjects">
            Create a practice quiz
        </button>
        <button class="sd-ai-prompt" type="button" data-prompt="Make revision notes for my next exam">
            Make revision notes
        </button>
        <button class="sd-ai-prompt" type="button" data-prompt="Help me create a study plan for this week">
            Plan my study week
        </button>
    </div>

    @if($url = $routeFor('student.learning', 'student.learning.index'))
        <form class="sd-ai-input" method="GET" action="{{ $url }}" id="sdAiForm">
            <input
                type="text"
                name="q"
                id="sdAiQuestion"
                placeholder="Ask CASPA to help you understand something..."
                autocomplete="off"
            >
            <button type="submit">
                Ask CASPA <span aria-hidden="true">→</span>
            </button>
        </form>
    @else
        <div class="sd-ai-input">
            <input
                type="text"
                id="sdAiQuestion"
                placeholder="Open CASPA Student Learning to start asking questions..."
                disabled
            >
            <button type="button" disabled>Ask CASPA</button>
        </div>

        <div class="sd-note">
            CASPA Student Learning entry route is not configured yet.
            Connect this panel to your student learning module when its route is ready.
        </div>
    @endif
</section>

<div class="sd-footer-note">
    SchoolNexa · Your learning journey, all in one place.
</div>

</div>

<script>
(function () {
    const root = document.querySelector('.std-dashboard');

    if (!root) return;

    /*
    |--------------------------------------------------------------------------
    | Learning space tabs
    |--------------------------------------------------------------------------
    */
    const tabs = root.querySelectorAll('.sd-tab');
    const panels = root.querySelectorAll('.sd-tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(item => {
                const active = item === tab;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            panels.forEach(panel => {
                panel.classList.toggle(
                    'active',
                    panel.id === 'sd-panel-' + tab.dataset.tab
                );
            });

            if (tab.dataset.tab === 'results') {
                setTimeout(initCharts, 80);
            }

            applyLearningSearch();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Learning space search
    |--------------------------------------------------------------------------
    */
    const search = root.querySelector('#sdLearningSearch');

    function applyLearningSearch() {
        if (!search) return;

        const term = search.value.trim().toLowerCase();
        const activePanel = root.querySelector('.sd-tab-panel.active');

        if (!activePanel) return;

        activePanel.querySelectorAll('tbody tr, .sd-quick').forEach(row => {
            row.classList.toggle(
                'sd-hidden',
                term !== '' && !row.textContent.toLowerCase().includes(term)
            );
        });
    }

    if (search) {
        search.addEventListener('input', applyLearningSearch);
    }

    /*
    |--------------------------------------------------------------------------
    | CASPA suggested prompts
    |--------------------------------------------------------------------------
    */
    root.querySelectorAll('[data-prompt]').forEach(button => {
        button.addEventListener('click', () => {
            const input = root.querySelector('#sdAiQuestion');

            if (input && !input.disabled) {
                input.value = button.dataset.prompt;
                input.focus();
            }
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Chart data from Laravel
    |--------------------------------------------------------------------------
    */
    const trendLabels = @json($trendLabels);
    const trendValues = @json($trendValues);
    const subjectLabels = @json($subjectLabels);
    const subjectValues = @json($subjectValues);

    const createdCharts = new Set();

    function drawLine(id) {
        const canvas = root.querySelector('#' + id);

        if (!canvas || !window.Chart || !trendLabels.length || createdCharts.has(id)) {
            return;
        }

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Attendance / Progress',
                    data: trendValues,
                    borderColor: '#176B87',
                    backgroundColor: 'rgba(23,107,135,.10)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3,
                    pointBackgroundColor: '#176B87'
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
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        createdCharts.add(id);
    }

    function drawSubject(id) {
        const canvas = root.querySelector('#' + id);

        if (!canvas || !window.Chart || !subjectLabels.length || createdCharts.has(id)) {
            return;
        }

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: subjectLabels,
                datasets: [{
                    label: 'Performance',
                    data: subjectValues,
                    backgroundColor: '#3B82F6',
                    borderRadius: 6,
                    maxBarThickness: 34
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
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });

        createdCharts.add(id);
    }

    function initCharts() {
        if (!window.Chart) return;

        drawLine('sdProgressChart');
        drawSubject('sdSubjectChart');
        drawSubject('sdSubjectChartMain');
    }

    /*
    |--------------------------------------------------------------------------
    | Load Chart.js only if it is not already available
    |--------------------------------------------------------------------------
    */
    if (window.Chart) {
        initCharts();
    } else {
        const script = document.createElement('script');

        script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
        script.onload = initCharts;
        script.onerror = () => {
            console.warn('Chart.js could not be loaded. Dashboard data remains available.');
        };

        document.head.appendChild(script);
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh Lucide icons
    |--------------------------------------------------------------------------
    */
    if (window.lucide) {
        window.lucide.createIcons();
    }
})();
</script>
@endsection