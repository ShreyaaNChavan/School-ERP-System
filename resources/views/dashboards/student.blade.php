@extends('layouts.schoolnexa')

@section('title', 'Student Dashboard')

@section('content')
@php
    use Illuminate\Support\Str;

    $s = $stats ?? [];
    $student = $studentProfile ?? $student ?? null;
    $attendance = $attendanceSummary ?? [];
    $fees = $feeSummary ?? [];
    $academic = $academicSummary ?? [];

    $money = fn($v) => '₹' . number_format((float)($v ?? 0), 0);
    $num = fn($v) => number_format((float)($v ?? 0));

    $fullName = trim(collect([$student->first_name ?? null, $student->middle_name ?? null, $student->last_name ?? null])->filter()->implode(' '));
    $fullName = $fullName ?: (auth()->user()->name ?? 'Student');
    $initials = collect(preg_split('/\s+/', $fullName))->filter()->take(2)->map(fn($p) => mb_substr($p, 0, 1))->implode('');

    $attendancePct = (float)($attendance['percentage'] ?? $s['attendance_percentage'] ?? 0);
    $assignmentPending = (int)($s['pending_assignments'] ?? 0);
    $upcomingExamsCount = (int)($s['upcoming_exams'] ?? 0);
    $average = $academic['average'] ?? $s['academic_average'] ?? null;

    $feeTotal = (float)($fees['total'] ?? $s['total_fees'] ?? 0);
    $feePaid = (float)($fees['paid'] ?? $s['fees_paid'] ?? 0);
    $balance = (float)($fees['balance'] ?? $s['fee_balance'] ?? max(0, $feeTotal - $feePaid));

    $presentDays = (float)($attendance['present'] ?? $s['present_days'] ?? 0);
    $absentDays  = (float)($attendance['absent'] ?? $s['absent_days'] ?? 0);
    $lateDays    = (float)($attendance['late'] ?? $s['late_days'] ?? 0);

    $todayTimetable = collect($todayTimetable ?? []);
    $assignments = collect($studentAssignments ?? $assignments ?? []);
    $exams = collect($studentExams ?? $exams ?? []);
    $resources = collect($learningResources ?? []);
    $announcements = collect($studentAnnouncements ?? $announcements ?? $recentItems ?? []);
    $events = collect($studentEvents ?? $upcomingItems ?? []);
    $subjects = collect($mySubjects ?? []);
    $attendanceTrend = collect($attendanceTrend ?? []);
    $subjectPerformance = collect($subjectPerformance ?? []);
    $achievements = collect($studentAchievements ?? []);   // [['category','academic_year','detail'], ...]
    $docs = collect($studentDocuments ?? []);
    $borrowed = collect($borrowedBooks ?? []);

    $trendLabels = $attendanceTrend->pluck('label')->filter()->values()->all();
    $trendValues = $attendanceTrend->map(fn($r) => (float) data_get($r, 'percentage', data_get($r, 'average', 0)))->values()->all();
    $subjectLabels = $subjectPerformance->map(fn($r) => data_get($r, 'subject_name', data_get($r, 'name', 'Subject')))->values()->all();
    $subjectValues = $subjectPerformance->map(fn($r) => (float) data_get($r, 'percentage', data_get($r, 'marks_percentage', data_get($r, 'average', 0))))->values()->all();

    $asgDone = 0; $asgLate = 0; $asgPending = 0;
    foreach ($assignments as $a) {
        $st = strtolower((string) data_get($a, 'submission_status', data_get($a, 'status', 'pending')));
        if (in_array($st, ['submitted', 'evaluated', 'completed'])) $asgDone++;
        elseif (in_array($st, ['late', 'overdue'])) $asgLate++;
        else $asgPending++;
    }
    $asgValues = [$asgDone, $asgPending, $asgLate];

    $perfIn = $studentPerformance ?? [];
    $perfRows = [
        ['Average Attendance',  $attendancePct > 0 ? $attendancePct : null],
        ['Average Marks',       $average !== null ? (float) $average : null],
        ['Homework Completion', $perfIn['homework'] ?? null],
        ['Student Engagement',  $perfIn['engagement'] ?? null],
    ];

    $trRaw = $studentTransport ?? null;
    $transport = is_array($trRaw) ? $trRaw : (is_object($trRaw) ? (array) $trRaw : []);
    $libS = $libraryStats ?? [];
    $libAvail = $libS['available'] ?? null;
    $libTotal = $libS['total'] ?? null;
    $libRate  = $libS['availability_rate'] ?? null;
    if ($libRate === null && $libAvail !== null && $libTotal > 0) { $libRate = round(($libAvail / $libTotal) * 100); }

    $routeFor = function ($primary, $fallback = null) {
        if ($primary && \Illuminate\Support\Facades\Route::has($primary)) return route($primary);
        if ($fallback && \Illuminate\Support\Facades\Route::has($fallback)) return route($fallback);
        return null;
    };
    $dateFormat = fn($date, $format = 'd M Y') => $date ? \Illuminate\Support\Carbon::parse($date)->format($format) : '—';

    /* 3D clipart (icons8 "3d-fluency"). Change a slug to change a picture. */
    $slugs = [
        'attendance' => 'calendar', 'tasks' => 'clipboard', 'exams' => 'open-book', 'academic' => 'graduation-cap',
        'assignments' => 'pencil', 'library' => 'book', 'timetable' => 'clock', 'fees' => 'money-bag', 'transport' => 'bus',
        'hostel' => 'bed', 'certs' => 'medal', 'help' => 'speech-bubble', 'career' => 'briefcase', 'notices' => 'megaphone',
        'events' => 'star', 'perf' => 'bar-chart', 'docs' => 'document', 'alumni' => 'diploma', 'ai' => 'sparkles',
        'hero' => 'school', 'layers' => 'organization', 'subjects' => 'books', 'trend' => 'combo-chart', 'pie' => 'pie-chart',
        'trophy' => 'trophy', 'resources' => 'folder-invoices', 'user' => 'user',
    ];
    $tile = function (string $name, string $size = 'md') use ($slugs) {
        $slug = $slugs[$name] ?? 'organization';
        $delay = '-' . (mt_rand(0, 30) / 10) . 's';
        return '<span class="sd-3d '.$size.'"><span class="sd-3d-in" style="animation-delay:'.$delay.'"><img class="sd-3d-img" src="https://img.icons8.com/3d-fluency/188/'.$slug.'.png" alt="" loading="lazy" draggable="false" onerror="this.style.display=\'none\';this.parentNode.classList.add(\'fb\')"><b class="sd-fb">'.e(Str::upper(Str::substr($name, 0, 2))).'</b></span></span>';
    };

    /* Quick access: always shows every service. Unconnected modules show a toast instead of a dead link. */
    $quickLinks = [
        ['My Attendance',   'attendance', 'attendance.index', null],
        ['Assignments',     'assignments', 'assignments.index', null],
        ['Exam Results',    'exams', 'examinations.index', null],
        ['Fee Details',     'fees', 'fees.dashboard', 'fees.invoices.index'],
        ['Digital Library', 'library', 'library.dashboard', 'library.loans.index'],
        ['My Timetable',    'timetable', 'timetables.index', null],
        ['Transport',       'transport', 'transport.dashboard', null],
        ['Hostel',          'hostel', 'hostel.dashboard', null],
        ['Certificates',    'certs', 'certificates.dashboard', null],
        ['Help & Grievance','help', 'student-support.dashboard', null],
        ['Career Guidance', 'career', 'career.dashboard', null],
        ['Alumni',          'alumni', 'alumni.dashboard', null],
       // ['CASPA AI',        'ai', 'student-learning.index', null],
    ];

    /* Initials badge for real records (e.g. "Bonafide Certificate" -> BC) */
    $inTones = [['#1D4ED8','#DBEAFE'],['#15803D','#DCFCE7'],['#B45309','#FEF3C7'],['#6D28D9','#EDE9FE'],['#0E7490','#CFFAFE'],['#B91C1C','#FEE2E2']];
    $ini = function ($text, $i = 0) use ($inTones) {
        $w = preg_split('/[\s\-_]+/', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY);
        $s = count($w) > 1 ? mb_substr($w[0], 0, 1) . mb_substr($w[1], 0, 1) : mb_substr($w[0] ?? '?', 0, 2);
        [$c, $b] = $inTones[$i % count($inTones)];
        return '<span class="sd-ini" style="--c:'.$c.';--bg:'.$b.'">'.e(Str::upper($s)).'</span>';
    };

    $hour  = now()->hour;
    $greet = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $kpiCards = [
        ['Attendance', number_format($attendancePct, 1).'%', $attendancePct >= 75 ? 'Attendance is on track' : 'Attendance needs attention', 'attendance', $attendancePct, 1, '%'],
        ['Pending Assignments', $num($assignmentPending), 'Awaiting submission', 'tasks', (float) $assignmentPending, 0, ''],
        ['Upcoming Exams', $num($upcomingExamsCount), 'Scheduled assessments', 'exams', (float) $upcomingExamsCount, 0, ''],
        ['Academic Average', $average !== null ? number_format((float) $average, 1).'%' : '—', 'Based on published results', 'academic', $average !== null ? (float) $average : null, 1, '%'],
    ];
@endphp

<div class="std-dashboard">

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.std-dashboard{
    --navy:#123B5D; --teal:#176B87; --purple:#5B3FA3; --sky:#3B82F6;
    --success:#16A34A; --warning:#D97706; --danger:#DC2626;
    --surface:#F8FAFC; --border:#CBD5E1; --text:#25313C; --muted:#64748B;
    font-family:'Inter',system-ui,-apple-system,'Segoe UI',sans-serif;
    color:var(--text); display:flex; flex-direction:column; gap:18px;
    width:100%; max-width:1500px; margin:0 auto;
}
.std-dashboard *{ box-sizing:border-box; }
.std-dashboard h1,.std-dashboard h2,.std-dashboard h3,.std-dashboard h4,.std-dashboard p{ margin:0; }
.std-dashboard a{ text-decoration:none; }
.std-dashboard svg{ display:block; flex-shrink:0; }

/* ================= 3D ICONS ================= */
.sd-3d{ position:relative; display:inline-block; flex-shrink:0; margin-bottom:8px; width:64px; height:64px;
    transform:perspective(520px) rotateX(var(--rx,0deg)) rotateY(var(--ry,0deg)) scale(var(--s,1));
    transition:transform .18s ease-out; will-change:transform; cursor:pointer; }
.sd-3d.xs{ width:40px; height:40px; margin-bottom:6px; }
.sd-3d.sm{ width:48px; height:48px; }
.sd-3d.lg{ width:92px; height:92px; margin-bottom:10px; }
.sd-3d.xl{ width:130px; height:130px; margin-bottom:12px; }
.sd-3d-in{ display:grid; place-items:center; width:100%; height:100%; animation:sdFloat 4.2s ease-in-out infinite; }
.sd-3d-img{ width:100%; height:100%; object-fit:contain; user-select:none; -webkit-user-drag:none; filter:drop-shadow(0 10px 8px rgba(18,59,93,.28)); }
.sd-3d::after{ content:''; position:absolute; left:20%; right:20%; bottom:-7px; height:9px; border-radius:50%; background:rgba(18,59,93,.22); filter:blur(5px); }
.sd-3d.xs::after{ bottom:-5px; height:6px; }
.sd-3d-in .sd-fb{ display:none; width:80%; height:80%; border-radius:30%; place-items:center; color:#fff; font-size:12px; font-weight:800;
    background:linear-gradient(150deg,#7DB5FF,#3B82F6 55%,#1D4ED8); box-shadow:0 4px 0 #1D4ED8; }
.sd-3d-in.fb .sd-fb{ display:grid; }
.sd-3d.pop .sd-3d-in{ animation:sdPop .55s cubic-bezier(.3,1.6,.5,1), sdFloat 4.2s ease-in-out infinite .55s; }
@keyframes sdFloat{ 0%,100%{ transform:translateY(0); } 50%{ transform:translateY(-5px); } }
@keyframes sdPop{ 0%{ transform:scale(1); } 35%{ transform:scale(1.28) rotate(-6deg); } 70%{ transform:scale(.94) rotate(3deg); } 100%{ transform:scale(1); } }
@@media (prefers-reduced-motion:reduce){ .sd-3d-in{ animation:none !important; } .sd-3d{ transition:none; } }

/* ================= WELCOME BANNER (same as admin) ================= */
.sd-welcome{ display:flex; align-items:center; justify-content:space-between; gap:20px; flex-wrap:wrap; padding:26px 30px; border-radius:18px; border:1px solid #DCE8F7; background:linear-gradient(105deg,#E3EFFC 0%,#EEF5FD 55%,#FFFFFF 100%); }
.sd-welcome-main{ display:flex; align-items:center; gap:18px; min-width:0; }
.sd-avatar{ width:68px; height:68px; border-radius:50%; display:grid; place-items:center; background:linear-gradient(140deg,#DCEBFA,#D9F2F0); border:2px solid #fff; box-shadow:0 4px 12px rgba(18,59,93,.15); color:var(--navy); font-size:22px; font-weight:800; overflow:hidden; flex:0 0 auto; }
.sd-avatar img{ width:100%; height:100%; object-fit:cover; }
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
.sd-btn{ display:inline-flex; align-items:center; gap:6px; padding:9px 15px; border:1px solid var(--border); border-radius:10px; background:#fff; color:var(--navy); font-size:12px; font-weight:700; cursor:pointer; font-family:inherit; white-space:nowrap; }
.sd-btn:hover{ border-color:var(--teal); background:#F0F9FF; }

/* ================= KPI CARDS (same as admin) ================= */
.sd-kpis{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; }
.sd-kpi{ display:flex; align-items:center; gap:16px; background:#fff; border:1px solid var(--border); border-radius:16px; padding:18px; transition:box-shadow .15s, transform .15s; }
.sd-kpi:hover{ box-shadow:0 10px 26px rgba(18,59,93,.12); transform:translateY(-2px); }
.sd-kpi-label{ font-size:12px; font-weight:600; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
.sd-kpi-value{ font-size:30px; font-weight:800; line-height:1.15; color:var(--navy); margin:2px 0; white-space:nowrap; }
.sd-kpi-note{ font-size:12px; color:var(--muted); }

/* ================= CARDS + MOSAIC ROWS ================= */
.sd-card{ display:flex; flex-direction:column; background:#fff; border:1px solid var(--border); border-radius:16px; padding:20px; min-width:0; transition:box-shadow .15s; }
.sd-card:hover{ box-shadow:0 8px 22px rgba(18,59,93,.08); }
.sd-row-grid{ display:grid; gap:16px; align-items:stretch; }
.g-a{ grid-template-columns:minmax(0,1fr) minmax(0,1.25fr) minmax(0,1fr); }
.g-b{ grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1.15fr); }
.g-c{ grid-template-columns:minmax(0,1.1fr) minmax(0,1.3fr) minmax(0,.9fr); }
.g-d{ grid-template-columns:minmax(0,.8fr) minmax(0,1.5fr); }
.g-e{ grid-template-columns:repeat(2,minmax(0,1fr)); }
.g-f{ grid-template-columns:minmax(0,1fr) minmax(0,1fr) minmax(0,1.25fr); }

.sd-head{ display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:14px; }
.sd-title{ display:flex; align-items:center; gap:12px; font-size:16px; font-weight:800; color:var(--navy); }
.sd-title small{ display:block; font-size:11px; font-weight:500; color:var(--muted); margin-top:2px; }
.sd-title .sd-3d{ margin-bottom:6px; }
.sd-kicker{ font-size:10px; font-weight:800; letter-spacing:.1em; text-transform:uppercase; color:var(--sky); }
.sd-viewall{ display:inline-flex; align-items:center; gap:6px; padding:8px 13px; border:1px solid var(--border); border-radius:10px; background:#F8FAFC; font-size:12px; font-weight:700; color:var(--navy); white-space:nowrap; }
.sd-viewall:hover{ border-color:var(--teal); background:#F0F9FF; }
.sd-body{ flex:1; display:flex; flex-direction:column; min-height:0; }
.sd-scroll{ flex:1; overflow-y:auto; max-height:300px; padding-right:3px; }
.sd-scroll::-webkit-scrollbar{ width:5px; } .sd-scroll::-webkit-scrollbar-thumb{ background:#CBD5E1; border-radius:9px; }

.sd-chart{ position:relative; flex:1; min-height:220px; }
.sd-chart.sm{ flex:none; height:170px; min-height:0; }
.sd-donut-c{ position:absolute; inset:0; display:grid; place-content:center; text-align:center; pointer-events:none; }
.sd-donut-c strong{ display:block; font-size:26px; font-weight:800; color:var(--navy); line-height:1.1; }
.sd-donut-c span{ font-size:10px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.05em; }
.sd-legend{ display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-top:12px; }
.sd-legend.two{ grid-template-columns:repeat(2,1fr); }
.sd-mini{ padding:9px 6px; border-radius:11px; background:#F1F5F9; text-align:center; }
.sd-mini strong{ display:block; font-size:16px; color:var(--navy); }
.sd-mini span{ font-size:10px; color:var(--muted); }
.sd-mini i{ display:inline-block; width:7px; height:7px; border-radius:50%; margin-right:4px; background:var(--d); }

.sd-pill{ display:inline-flex; padding:4px 11px; border-radius:999px; font-size:11px; font-weight:700; background:#F1F5F9; color:#475569; white-space:nowrap; }
.sd-pill.green{ background:#DCFCE7; color:#15803D; } .sd-pill.amber{ background:#FEF3C7; color:#B45309; } .sd-pill.red{ background:#FEE2E2; color:#B91C1C; } .sd-pill.blue{ background:#DBEAFE; color:#1D4ED8; }

/* tables */
.sd-table{ width:100%; border-collapse:collapse; }
.sd-table th{ text-align:left; color:var(--muted); font-size:10.5px; text-transform:uppercase; letter-spacing:.05em; font-weight:700; padding:9px 8px; border-bottom:1px solid #E2E8F0; }
.sd-table td{ font-size:13px; padding:14px 8px; border-bottom:1px solid #EEF2F6; color:var(--navy); vertical-align:middle; }
.sd-table tr:last-child td{ border-bottom:0; }
.sd-table small{ display:block; font-size:10.5px; color:var(--muted); margin-top:2px; }

/* list rows */
.sd-row{ display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid #EEF2F6; }
.sd-row:last-child{ border-bottom:0; padding-bottom:0; }
.sd-row-main{ flex:1; min-width:0; }
.sd-row-main b{ display:block; font-size:13px; color:var(--navy); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sd-row-main small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sd-row-side{ font-size:12px; font-weight:700; color:var(--navy); white-space:nowrap; text-align:right; }
.sd-date-box{ width:46px; height:50px; flex:0 0 auto; border-radius:12px; display:flex; flex-direction:column; align-items:center; justify-content:center; background:linear-gradient(160deg,#EFF6FF,#E0E7FF); color:#3730A3; }
.sd-date-box b{ font-size:17px; line-height:1; } .sd-date-box span{ font-size:9px; font-weight:800; text-transform:uppercase; }
.sd-time-chip{ min-width:66px; padding:8px; border-radius:10px; background:#F0F9FF; color:#0C4A6E; font-size:10.5px; font-weight:800; text-align:center; line-height:1.35; }
.sd-exam{ display:flex; align-items:center; justify-content:space-between; gap:10px; padding:13px; border-radius:12px; background:#F1F5F9; margin-bottom:10px; }
.sd-exam:last-child{ margin-bottom:0; }
.sd-exam b{ display:block; font-size:14px; color:var(--navy); } .sd-exam small{ display:block; font-size:11px; color:var(--muted); margin-top:4px; }
.sd-exam-date{ font-size:18px; font-weight:800; color:var(--sky); white-space:nowrap; }
.sd-doc{ display:flex; align-items:center; gap:12px; padding:10px 0; border-bottom:1px solid #E2E8F0; }
.sd-doc:last-child{ border-bottom:0; }
.sd-link{ font-size:12px; font-weight:700; color:var(--teal); white-space:nowrap; } .sd-link:hover{ text-decoration:underline; }

/* bars */
.sd-perf{ margin-bottom:18px; } .sd-perf:last-of-type{ margin-bottom:0; }
.sd-perf-top{ display:flex; justify-content:space-between; font-size:14px; color:var(--navy); } .sd-perf-top strong{ color:var(--teal); }
.sd-bar{ height:7px; background:#E2E8F0; border-radius:99px; overflow:hidden; margin-top:8px; }
.sd-bar.sm{ height:5px; margin-top:6px; }
.sd-bar i{ display:block; height:100%; border-radius:99px; background:var(--navy); transition:width .6s; }

.sd-seg{ display:inline-flex; padding:3px; border-radius:10px; background:#F1F5F9; }
.sd-seg button{ border:0; background:transparent; padding:5px 12px; border-radius:8px; font:700 11px 'Inter',sans-serif; color:var(--muted); cursor:pointer; }
.sd-seg button.on{ background:#fff; color:var(--teal); box-shadow:0 1px 4px rgba(15,23,42,.1); }

.sd-empty{ flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; padding:18px; color:var(--muted); font-size:12px; }
.sd-empty strong{ display:block; color:#475569; font-size:13px; margin:2px 0 3px; }

/* chip lists */
.sd-chip{ display:flex; align-items:center; gap:12px; padding:11px 12px; border:1px solid var(--border); border-radius:12px; margin-bottom:10px; background:var(--surface); }
.sd-chip:last-child{ margin-bottom:0; }
.sd-chip b{ display:block; font-size:13px; color:var(--navy); } .sd-chip small{ display:block; font-size:11px; color:var(--muted); margin-top:2px; }
.sd-chip .sd-3d{ margin-bottom:6px; }
.sd-ini{ width:42px; height:42px; flex:0 0 auto; border-radius:12px; display:grid; place-items:center; font-size:13px; font-weight:800; letter-spacing:.03em; color:var(--c,#1D4ED8); background:var(--bg,#DBEAFE); }

/* transport */
.sd-route{ background:#E8F6EF; border:1px solid #CDE9D7; border-radius:12px; padding:13px; display:flex; flex-direction:column; gap:7px; font-size:13px; color:#334155; }
.sd-route-name{ display:flex; align-items:center; gap:7px; font-size:14px; font-weight:800; color:#244C3B; }
.sd-route-name i{ width:8px; height:8px; border-radius:50%; background:var(--success); display:inline-block; }
.sd-two{ display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px; }
.sd-two div{ background:#F8FAFC; border:1px solid #EEF2F6; border-radius:9px; padding:9px; }
.sd-two small{ display:block; font-size:10px; color:var(--muted); } .sd-two b{ display:block; font-size:12px; color:var(--navy); margin-top:2px; }
.sd-bus{ display:flex; justify-content:center; margin-top:auto; padding-top:14px; }

/* library */
.sd-lib{ display:grid; grid-template-columns:minmax(0,1.5fr) minmax(0,1fr); gap:24px; flex:1; }
.sd-sub{ margin:0 0 12px; font-size:15px; font-weight:800; color:var(--navy); }
.sd-books{ border:1px solid #E2E8F0; border-radius:12px; }
.sd-book{ display:grid; grid-template-columns:24px 40px minmax(0,1fr) auto; align-items:center; gap:10px; padding:10px 12px; border-bottom:1px solid #EEF2F6; }
.sd-book:last-child{ border-bottom:0; }
.sd-book .rk{ font-size:11px; color:#94A3B8; font-weight:700; }
.sd-book b{ display:block; font-size:13px; color:var(--navy); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sd-book small{ font-size:11px; color:var(--muted); white-space:nowrap; }
.sd-book .sd-3d{ margin-bottom:6px; }
.sd-avail{ text-align:center; border-left:1px solid #E2E8F0; padding-left:24px; display:flex; flex-direction:column; align-items:center; }
.sd-avail .sd-sub{ align-self:flex-start; }
.sd-avail-note{ margin-top:12px; font-size:12px; font-weight:700; color:var(--navy); }

/* quick access */
.sd-quick-grid{ display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:12px; }
.sd-quick{ display:flex; flex-direction:column; align-items:center; gap:6px; padding:18px 8px 14px; border:1px solid var(--border); border-radius:14px; background:var(--surface); text-align:center; cursor:pointer; transition:.15s; }
.sd-quick:hover{ background:#fff; border-color:var(--teal); box-shadow:0 8px 18px rgba(23,107,135,.14); transform:translateY(-3px); }
.sd-quick b{ font-size:12.5px; font-weight:700; color:var(--navy); }

/* alumni */
.sd-alumni{ position:relative; overflow:hidden; flex-direction:row; align-items:stretch; justify-content:space-between; gap:20px; min-height:200px; padding:24px 28px; }
.sd-alumni-body{ position:relative; z-index:1; max-width:560px; display:flex; flex-direction:column; justify-content:center; gap:12px; }
.sd-alumni h3{ font-size:22px; font-weight:800; color:var(--navy); }
.sd-alumni p{ font-size:14px; line-height:1.5; }
.sd-alumni-art{ position:absolute; right:0; top:0; bottom:0; width:46%; max-width:520px; pointer-events:none; }
.sd-alumni-art svg{ width:100%; height:100%; }
.sd-btn-dark{ display:inline-flex; align-items:center; gap:10px; align-self:flex-start; height:44px; padding:0 22px; border-radius:10px; background:var(--navy); color:#fff; font-size:14px; font-weight:700; box-shadow:0 4px 0 #0A2740; transition:.15s; }
.sd-btn-dark:hover{ transform:translateY(-1px); background:#0F3250; color:#fff; }

/* AI */
.sd-ai{ border:2px solid #B9A8F0; border-radius:16px; padding:22px 26px; background:linear-gradient(100deg,#DDD6FE,#EDE9FE 60%,#F5F3FF); }
.sd-ai-top{ display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap; margin-bottom:14px; }
.sd-ai-lead{ display:flex; align-items:center; gap:16px; }
.sd-ai h3{ font-size:20px; font-weight:800; color:var(--purple); }
.sd-ai p{ margin-top:4px; font-size:14px; color:#4B5563; }
.sd-ai-btn{ display:inline-flex; align-items:center; gap:8px; background:var(--purple); color:#fff; border:0; border-radius:12px; padding:12px 22px; font:700 14px 'Inter',sans-serif; cursor:pointer; box-shadow:0 4px 0 #3E2A78; }
.sd-ai-btn:hover{ background:#4B3490; color:#fff; }
.sd-prompts{ display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin:18px 0 14px; }
.sd-prompt{ border:1.5px solid var(--navy); border-radius:10px; padding:13px 10px; background:#F8FAFC; color:var(--navy); font:600 14px 'Inter',sans-serif; cursor:pointer; transition:.15s; }
.sd-prompt:hover{ background:#fff; transform:translateY(-2px); box-shadow:0 6px 14px rgba(18,59,93,.15); }
.sd-ai-input{ display:flex; gap:8px; padding:5px; border-radius:11px; background:#fff; border:1px solid #DDD6FE; }
.sd-ai-input input{ flex:1; min-width:0; border:0; outline:0; padding:10px; font:13px 'Inter',sans-serif; }
.sd-ai-input button{ border:0; border-radius:8px; padding:0 18px; background:var(--purple); color:#fff; font:700 12px 'Inter',sans-serif; cursor:pointer; }
.sd-ai-input button:disabled{ opacity:.55; cursor:not-allowed; }
.sd-note{ font-size:11px; color:var(--muted); margin-top:8px; }
.sd-footer{ text-align:center; font-size:11px; color:var(--muted); padding:4px 0 14px; }
.sd-toast{ position:fixed; left:50%; bottom:28px; transform:translateX(-50%) translateY(20px); background:var(--navy); color:#fff; font-size:13px; font-weight:600; padding:10px 18px; border-radius:10px; opacity:0; pointer-events:none; transition:.25s; z-index:9999; }
.sd-toast.show{ opacity:1; transform:translateX(-50%); }
.sd-hidden{ display:none !important; }

@@media(max-width:1280px){
    .g-a,.g-b,.g-c,.g-e,.g-f{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-kpis{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-quick-grid{ grid-template-columns:repeat(4,minmax(0,1fr)); }
}
@@media(max-width:900px){
    .g-a,.g-b,.g-c,.g-d,.g-e,.g-f{ grid-template-columns:1fr; }
    .sd-lib{ grid-template-columns:1fr; } .sd-avail{ border-left:0; padding-left:0; border-top:1px solid #E2E8F0; padding-top:16px; }
    .sd-alumni-art{ display:none; }
    .sd-prompts{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-quick-grid{ grid-template-columns:repeat(3,minmax(0,1fr)); }
}
@@media(max-width:600px){
    .sd-welcome{ padding:20px; } .sd-welcome h1{ font-size:22px; } .sd-date{ text-align:left; }
    .sd-welcome-right{ width:100%; justify-content:space-between; }
    .sd-kpis{ grid-template-columns:1fr; }
    .sd-quick-grid{ grid-template-columns:repeat(2,minmax(0,1fr)); }
    .sd-prompts{ grid-template-columns:1fr; } .sd-ai-input{ flex-direction:column; } .sd-ai-input button{ height:38px; }
}
</style>

{{-- ================= WELCOME BANNER ================= --}}
<div class="sd-welcome" data-tilt>
    <div class="sd-welcome-main">
        <div class="sd-avatar">
            @if(!empty($student?->photo_path))
                <img src="{{ asset('storage/'.$student->photo_path) }}" alt="Student photo">
            @else
                {{ $initials }}
            @endif
        </div>
        <div>
            <div class="sd-eyebrow">{{ $greet }} <i></i> Student Workspace</div>
            <h1>Welcome Back, {{ $fullName }}!</h1>
            <p>Keep learning, stay curious and make today count.</p>
            <div class="sd-chips">
                <span>Admission No: {{ $student->admission_number ?? 'Not assigned' }}</span>
                <span>Class: {{ $className ?? 'Not assigned' }}</span>
                <span>Section: {{ $sectionName ?? '—' }}</span>
                <span>Roll No: {{ $rollNumber ?? '—' }}</span>
            </div>
        </div>
    </div>
    <div class="sd-welcome-right">
        @if($url = $routeFor('profile.edit'))
            <!-- <a href="{{ $url }}" class="sd-btn">My Profile</a> -->
        @endif
        {!! $tile('hero', 'lg') !!}
        <div class="sd-date">
            {{ now()->format('l, d M Y') }}
            <strong>{{ now()->format('h:i A') }}</strong>
            <small>{{ $academicYearName ?? 'Current Year' }} · {{ $campusName ?? 'School Campus' }}</small>
        </div>
    </div>
</div>

{{-- ================= KPI CARDS ================= --}}
<div class="sd-kpis">
    @foreach($kpiCards as [$label, $shown, $note, $ic, $cv, $dec, $suf])
        <div class="sd-kpi" data-tilt>
            {!! $tile($ic) !!}
            <div>
                <div class="sd-kpi-label">{{ $label }}</div>
                <div class="sd-kpi-value" @if($cv !== null) data-count="{{ $cv }}" data-dec="{{ $dec }}" data-suf="{{ $suf }}" @endif>{{ $shown }}</div>
                <div class="sd-kpi-note">{{ $note }}</div>
            </div>
        </div>
    @endforeach
</div>

{{-- ================= QUICK ACCESS (full width, all 12 always shown) ================= --}}
<section class="sd-card">
    <div class="sd-head">
        <h2 class="sd-title" data-tilt>{!! $tile('layers', 'xs') !!}<span>Quick Access<small>Open your most-used student services</small></span></h2>
    </div>
    <div class="sd-quick-grid">
        @foreach($quickLinks as [$label, $ic, $r1, $r2])
            @php $qUrl = $routeFor($r1, $r2); @endphp
            <a class="sd-quick" data-tilt href="{{ $qUrl ?? '#' }}" @if(!$qUrl) data-soon="{{ $label }} isn't connected yet." @endif>
                {!! $tile($ic, 'sm') !!}
                <b>{{ $label }}</b>
            </a>
        @endforeach
    </div>
</section>

{{-- ================= ROW A : Attendance chart | Homework | Achievements ================= --}}
<div class="sd-row-grid g-a">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('attendance', 'xs') !!}<span>Attendance<small>Your attendance trend</small></span></h2>
            @if($url = $routeFor('attendance.index'))<a class="sd-viewall" href="{{ $url }}">Details →</a>@endif
            <span class="sd-pill {{ $attendancePct >= 75 ? 'green' : 'amber' }}">{{ number_format($attendancePct, 1) }}%</span>
        </div>
        <div class="sd-body">
            @if($attendanceTrend->isNotEmpty())
                <div class="sd-chart"><canvas id="chTrend"></canvas></div>
            @else
                <div class="sd-chart"><canvas id="chAttendance"></canvas><div class="sd-donut-c"><strong>{{ number_format($attendancePct, 0) }}%</strong><span>Attendance</span></div></div>
            @endif
            <div class="sd-legend">
                <div class="sd-mini" style="--d:#16A34A"><strong>{{ $num($presentDays) }}</strong><span><i></i>Present</span></div>
                <div class="sd-mini" style="--d:#DC2626"><strong>{{ $num($absentDays) }}</strong><span><i></i>Absent</span></div>
                <div class="sd-mini" style="--d:#F59E0B"><strong>{{ $num($lateDays) }}</strong><span><i></i>Late</span></div>
            </div>
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('assignments', 'xs') !!}<span>Homework &amp; Assignments</span></h2>
            @if($url = $routeFor('assignments.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($assignments->isNotEmpty())
                <div class="sd-scroll">
                    <table class="sd-table">
                        <thead><tr><th>Assignment</th><th>Subject</th><th>Due</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach($assignments->take(8) as $a)
                            @php $st = strtolower((string) data_get($a, 'submission_status', data_get($a, 'status', 'pending'))); @endphp
                            <tr>
                                <td>{{ data_get($a, 'title', 'Assignment') }}</td>
                                <td>{{ data_get($a, 'subject_name', '—') }}</td>
                                <td>{{ $dateFormat(data_get($a, 'due_date'), 'M d') }}</td>
                                <td><span class="sd-pill {{ in_array($st, ['submitted','evaluated','completed']) ? 'green' : (in_array($st, ['late','overdue']) ? 'red' : 'amber') }}">{{ ucfirst($st) }}</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sd-empty">{!! $tile('assignments', 'lg') !!}<strong>No assignments to display</strong>Your homework and submission status will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('trophy', 'xs') !!}<span>My Achievements</span></h2>
            @if($url = $routeFor('student.achievements', 'achievements.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($achievements->isNotEmpty())
                <div class="sd-scroll">
                    <table class="sd-table">
                        <thead><tr><th>Category</th><th>AY</th><th>Detail</th></tr></thead>
                        <tbody>
                        @foreach($achievements->take(6) as $ac)
                            <tr>
                                <td>{{ data_get($ac, 'category', '—') }}</td>
                                <td>{{ data_get($ac, 'academic_year', '—') }}</td>
                                <td>{{ data_get($ac, 'detail', data_get($ac, 'title', '—')) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="sd-empty">{!! $tile('trophy', 'lg') !!}<strong>No achievements yet</strong>Awards and participation records will appear here.</div>
            @endif
        </div>
    </section>
</div>

{{-- ================= ROW B : Exams | Documents | Performance ================= --}}
<div class="sd-row-grid g-b">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('exams', 'xs') !!}<span>Upcoming Exams</span></h2>
            @if($url = $routeFor('examinations.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($exams->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($exams->take(5) as $exam)
                        @php $exDate = data_get($exam, 'exam_date'); @endphp
                        <div class="sd-exam" data-tilt>
                            <div>
                                <b>{{ data_get($exam, 'subject_name', data_get($exam, 'exam_name', data_get($exam, 'name', 'Examination'))) }}</b>
                                <small>{{ data_get($exam, 'start_time') ? $dateFormat(data_get($exam, 'start_time'), 'h:i A') : 'Time not announced' }}@if(data_get($exam, 'room_number')) , Room {{ data_get($exam, 'room_number') }}@endif</small>
                            </div>
                            <span class="sd-exam-date">{{ $exDate ? $dateFormat($exDate, 'd M') : 'TBA' }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('exams', 'lg') !!}<strong>No upcoming examinations</strong>Your exam schedule will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('docs', 'xs') !!}<span>Documents</span></h2>
            @if($url = $routeFor('certificates.dashboard', 'certificates.index'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($docs->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($docs->take(5) as $di => $doc)
                        @php $dUrl = data_get($doc, 'url') ?: (data_get($doc, 'path') ? asset('storage/'.data_get($doc, 'path')) : null); @endphp
                        <div class="sd-doc">
                            {!! $ini(data_get($doc, 'title', data_get($doc, 'name', 'Document')), $di) !!}
                            <div class="sd-row-main">
                                <b>{{ data_get($doc, 'title', data_get($doc, 'name', 'Document')) }}</b>
                                <small>{{ data_get($doc, 'created_at') ? $dateFormat(data_get($doc, 'created_at'), 'd M, h:i A') : 'School document' }}</small>
                            </div>
                            @if($dUrl)<a class="sd-link" href="{{ $dUrl }}" target="_blank" rel="noopener noreferrer">Open</a>@endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('docs', 'lg') !!}<strong>No documents yet</strong>ID card, reports and certificates will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('perf', 'xs') !!}<span>My Performance</span></h2>
            @if($url = $routeFor('report-cards.index'))<a class="sd-viewall" href="{{ $url }}">Report Cards →</a>@endif
        </div>
        <div class="sd-body">
            @foreach($perfRows as [$pl, $pval])
                @php $pv = is_numeric($pval) ? max(0, min(100, (float) $pval)) : null; @endphp
                <div class="sd-perf">
                    <div class="sd-perf-top"><span>{{ $pl }}</span><strong>{{ $pv !== null ? number_format($pv, 0).'%' : '—' }}</strong></div>
                    <div class="sd-bar"><i style="width:{{ $pv ?? 0 }}%"></i></div>
                </div>
            @endforeach
            <p class="sd-note" style="margin-top:auto;padding-top:12px">Calculated from your available records.</p>
        </div>
    </section>
</div>

{{-- ================= ROW C : Timetable | Subject chart | Assignment pie ================= --}}
<div class="sd-row-grid g-c">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('timetable', 'xs') !!}<span>Today's Timetable<small>Your classes today</small></span></h2>
            @if($url = $routeFor('timetables.index'))<a class="sd-viewall" href="{{ $url }}">Full →</a>@endif
        </div>
        <div class="sd-body">
            @if($todayTimetable->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($todayTimetable as $p)
                        <div class="sd-row">
                            <div class="sd-time-chip">{{ data_get($p, 'start_time', '—') }}<br>{{ data_get($p, 'end_time', '—') }}</div>
                            <div class="sd-row-main">
                                <b>{{ data_get($p, 'subject_name', 'Subject') }}</b>
                                <small>{{ data_get($p, 'teacher_name', '—') }} · Room {{ data_get($p, 'room_number', '—') }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('timetable', 'lg') !!}<strong>No timetable available</strong>Your scheduled classes will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('perf', 'xs') !!}<span>Subject Performance<small>Marks by subject</small></span></h2>
            @if($url = $routeFor('marks-entry.index'))<a class="sd-viewall" href="{{ $url }}">Marks →</a>@endif
            <div class="sd-seg" id="subjSwitch"><button type="button" class="on" data-type="bar">Bar</button><button type="button" data-type="radar">Radar</button></div>
        </div>
        <div class="sd-body">
            @if($subjectPerformance->isNotEmpty())
                <div class="sd-chart"><canvas id="chSubjects"></canvas></div>
            @else
                <div class="sd-empty">{!! $tile('perf', 'lg') !!}<strong>No published results yet</strong>Subject-wise marks will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('pie', 'xs') !!}<span>Assignment Status<small>Done · pending · late</small></span></h2>
            @if($url = $routeFor('assignments.index'))<a class="sd-viewall" href="{{ $url }}">Assignments →</a>@endif
        </div>
        <div class="sd-body">
            <div class="sd-chart"><canvas id="chAssignments"></canvas></div>
            <div class="sd-legend">
                <div class="sd-mini" style="--d:#16A34A"><strong>{{ $asgDone }}</strong><span><i></i>Done</span></div>
                <div class="sd-mini" style="--d:#F59E0B"><strong>{{ $asgPending }}</strong><span><i></i>Pending</span></div>
                <div class="sd-mini" style="--d:#DC2626"><strong>{{ $asgLate }}</strong><span><i></i>Late</span></div>
            </div>
        </div>
    </section>
</div>

{{-- ================= ROW D : Transport | Library ================= --}}
<div class="sd-row-grid g-d">

    <section class="sd-card">
        <div class="sd-head">
            <div>
                <div class="sd-kicker">Transport</div>
                <h2 class="sd-title" style="font-size:20px" data-tilt>My Transport</h2>
            </div>
            @if($url = $routeFor('transport.dashboard'))<a class="sd-viewall" href="{{ $url }}">View Details →</a>@endif
        </div>
        <div class="sd-body">
            @if(!empty($transport))
                <div class="sd-route">
                    <div class="sd-route-name"><i></i>{{ $transport['route_name'] ?? 'Assigned route' }}</div>
                    <span>{{ $transport['onboard_count'] ?? '—' }} Onboard</span>
                    <span>Pickup : {{ $transport['pickup_time'] ?? 'Not provided' }}</span>
                    <span>Vehicle : {{ $transport['registration_number'] ?? $transport['vehicle_number'] ?? 'Not assigned' }}</span>
                </div>
                @if(!empty($transport['driver_name']) || !empty($transport['driver_phone']))
                    <div class="sd-two">
                        <div><small>Driver</small><b>{{ $transport['driver_name'] ?? '—' }}</b></div>
                        <div><small>Contact</small><b>{{ $transport['driver_phone'] ?? '—' }}</b></div>
                    </div>
                @endif
            @else
                <p class="sd-note">No transport assigned. Route details appear here if you use school transport.</p>
            @endif
            <div class="sd-bus" data-tilt>{!! $tile('transport', 'xl') !!}</div>
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <div>
                <div class="sd-kicker">Library</div>
                <h2 class="sd-title" style="font-size:20px" data-tilt>Library Overview</h2>
            </div>
            @if($url = $routeFor('library.dashboard'))<a class="sd-viewall" href="{{ $url }}">View Library →</a>@endif
        </div>
        <div class="sd-lib">
            <div>
                <h3 class="sd-sub">My Borrowed Books</h3>
                <div class="sd-books">
                    @forelse($borrowed->take(4) as $i => $book)
                        @php
                            $loanPct = null;
                            try {
                                $iss = data_get($book, 'issued_at'); $due = data_get($book, 'due_at');
                                if ($iss && $due) {
                                    $t0 = \Illuminate\Support\Carbon::parse($iss)->timestamp; $t1 = \Illuminate\Support\Carbon::parse($due)->timestamp;
                                    if ($t1 > $t0) $loanPct = max(0, min(100, (now()->timestamp - $t0) / ($t1 - $t0) * 100));
                                }
                            } catch (\Throwable $e) { $loanPct = null; }
                        @endphp
                        <div class="sd-book" data-tilt>
                            <span class="rk">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            {!! $ini(data_get($book, 'title', 'Book'), $i) !!}
                            <div><b>{{ data_get($book, 'title', 'Library book') }}</b>@if($loanPct !== null)<div class="sd-bar sm"><i style="width:{{ $loanPct }}%"></i></div>@endif</div>
                            <small>Due {{ data_get($book, 'due_at') ? $dateFormat(data_get($book, 'due_at'), 'M d') : '—' }}</small>
                        </div>
                    @empty
                        <div class="sd-empty" style="padding:30px">{!! $tile('library', 'sm') !!}<strong>No borrowed books</strong>Books you borrow will appear here.</div>
                    @endforelse
                </div>
            </div>
            <div class="sd-avail">
                <h3 class="sd-sub">Book Availability</h3>
                <div class="sd-chart sm" style="width:170px;height:170px"><canvas id="chLibrary"></canvas>
                    <div class="sd-donut-c"><strong>{{ $libRate !== null ? number_format((float) $libRate, 0).'%' : '—' }}</strong><span>Available</span></div>
                </div>
                <div class="sd-avail-note">@if($libRate !== null){{ number_format((float) $libAvail) }} / {{ number_format((float) $libTotal) }} titles @else Library data unavailable @endif</div>
            </div>
        </div>
    </section>
</div>

{{-- ================= ROW E : Resources | Subjects | Fee donut ================= --}}
<div class="sd-row-grid g-e">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('resources', 'xs') !!}<span>Learning Resources<small>Shared by your teachers</small></span></h2>
            @if($url = $routeFor('study-materials.index'))<a class="sd-viewall" href="{{ $url }}">All Resources →</a>@endif
        </div>
        <div class="sd-body">
            @if($resources->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($resources->take(8) as $ri => $r)
                        <div class="sd-chip">
                            {!! $ini(data_get($r, 'title', 'Resource'), $ri) !!}
                            <div class="sd-row-main"><b>{{ data_get($r, 'title', 'Learning material') }}</b><small>{{ data_get($r, 'subject_name', 'Study resource') }}</small></div>
                            @if(data_get($r, 'url'))<a href="{{ data_get($r, 'url') }}" target="_blank" rel="noopener noreferrer" class="sd-link">Open</a>@endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('resources', 'lg') !!}<strong>No learning resources yet</strong>Notes, worksheets and PDFs will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('subjects', 'xs') !!}<span>My Subjects<small>Enrolled subjects &amp; teachers</small></span></h2>
            @if($url = $routeFor('subjects.index'))<a class="sd-viewall" href="{{ $url }}">All Subjects →</a>@endif
        </div>
        <div class="sd-body">
            @if($subjects->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($subjects->take(10) as $si => $sub)
                        <div class="sd-chip">
                            {!! $ini(data_get($sub, 'subject_name', data_get($sub, 'name', 'Subject')), $si) !!}
                            <div class="sd-row-main"><b>{{ data_get($sub, 'subject_name', data_get($sub, 'name', 'Subject')) }}</b><small>{{ data_get($sub, 'teacher_name', '') }}</small></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('subjects', 'lg') !!}<strong>Subjects will appear here</strong>Your enrolled subjects and teachers are listed here.</div>
            @endif
        </div>
    </section>

</div>

{{-- ================= ROW F : Announcements | Events | Progress trend ================= --}}
<div class="sd-row-grid g-f">

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('notices', 'xs') !!}<span>Announcements<small>Updates from school</small></span></h2>
            @if($url = $routeFor('communication.index', 'communication.dashboard'))<a class="sd-viewall" href="{{ $url }}">View All →</a>@endif
        </div>
        <div class="sd-body">
            @if($announcements->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($announcements->take(6) as $n)
                        <div class="sd-row">
                            <div class="sd-row-main">
                                <b>{{ data_get($n, 'title', 'School announcement') }}</b>
                                <small>{{ Str::limit(strip_tags((string) data_get($n, 'content', data_get($n, 'description', 'School update'))), 70) }}</small>
                            </div>
                            <div class="sd-row-side">{{ data_get($n, 'published_at') ? $dateFormat(data_get($n, 'published_at'), 'd M') : '' }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('notices', 'lg') !!}<strong>No new announcements</strong>School notices will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('events', 'xs') !!}<span>Upcoming Events<small>Activities at school</small></span></h2>
            @if($url = $routeFor('events.dashboard', 'events.index'))<a class="sd-viewall" href="{{ $url }}">Calendar →</a>@endif
        </div>
        <div class="sd-body">
            @if($events->isNotEmpty())
                <div class="sd-scroll">
                    @foreach($events->take(6) as $ev)
                        @php $evDate = data_get($ev, 'starts_at', data_get($ev, 'event_date')); @endphp
                        <div class="sd-row">
                            <div class="sd-date-box"><b>{{ $evDate ? $dateFormat($evDate, 'd') : '—' }}</b><span>{{ $evDate ? $dateFormat($evDate, 'M') : 'Soon' }}</span></div>
                            <div class="sd-row-main"><b>{{ data_get($ev, 'title', 'School event') }}</b><small>{{ data_get($ev, 'venue', 'School campus') }}</small></div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="sd-empty">{!! $tile('events', 'lg') !!}<strong>No upcoming events</strong>School events will appear here.</div>
            @endif
        </div>
    </section>

    <section class="sd-card">
        <div class="sd-head">
            <h2 class="sd-title" data-tilt>{!! $tile('trend', 'xs') !!}<span>Academic Progress<small>Recent assessments</small></span></h2>
            <span class="sd-pill blue">{{ $average !== null ? number_format((float) $average, 1).'%' : 'No data' }}</span>
        </div>
        <div class="sd-body">
            <div class="sd-chart"><canvas id="chProgress"></canvas></div>
        </div>
    </section>
</div>

{{-- ================= ALUMNI ================= --}}
@php $alumniUrl = $routeFor('alumni.dashboard', 'alumni.index'); @endphp
<section class="sd-card sd-alumni" data-tilt>
    <div class="sd-alumni-body">
        <div class="sd-title">{!! $tile('alumni', 'xs') !!}<h3>Alumni</h3></div>
        <p>Explore alumni profiles, events, career connections and opportunities.</p>
        <a class="sd-btn-dark" href="{{ $alumniUrl ?? '#' }}" @if(!$alumniUrl) data-soon="Alumni module isn't connected yet." @endif>Explore Alumni <span aria-hidden="true">→</span></a>
    </div>
    <div class="sd-alumni-art" aria-hidden="true">
        <svg viewBox="0 0 340 190" preserveAspectRatio="xMaxYMax meet" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="190" cy="120" rx="160" ry="80" fill="#EAF2FD"/>
            <circle cx="52" cy="40" r="3" fill="#F59E0B"/><circle cx="318" cy="52" r="3" fill="#EF4444"/><circle cx="268" cy="22" r="2.5" fill="#3B82F6"/><circle cx="118" cy="30" r="2.5" fill="#F59E0B"/>
            @foreach([[55,.85,'#1E3A8A'],[110,.8,'#2347A8'],[172,1.0,'#1E3A8A'],[238,.9,'#2B4FB5'],[298,.8,'#1E3A8A']] as [$gx, $gk, $gown])
                <g transform="translate({{ $gx }} {{ 175 - 120 * $gk }}) scale({{ $gk }})">
                    <path d="M-22 120 L-15 54 Q0 46 15 54 L22 120 Z" fill="{{ $gown }}"/>
                    <path d="M-6 54 L0 70 L6 54 Z" fill="#fff" opacity=".9"/>
                    <circle cx="0" cy="36" r="13" fill="#F1C39B"/>
                    <path d="M-20 25 L0 14 L20 25 L0 34 Z" fill="#0F2A57"/>
                    <rect x="-10" y="27" width="20" height="6" fill="#0F2A57"/>
                    <path d="M17 25 v15" stroke="#F4B942" stroke-width="2.2" stroke-linecap="round"/>
                </g>
            @endforeach
            <path d="M20 176 H330" stroke="#9BBF8A" stroke-width="3" stroke-linecap="round"/>
        </svg>
    </div>
</section>

{{-- ================= CASPA AI ================= --}}
<section class="sd-ai" id="sdStudentAi">
    <div class="sd-ai-top">
        <div class="sd-ai-lead" data-tilt>
            {!! $tile('ai', 'md') !!}
            <div>
                <h3>CASPA Student Learning</h3>
                <p>Your AI-powered study companion · What would you like help with today?</p>
            </div>
        </div>
        @if($url = $routeFor('student-learning.index'))
            <a class="sd-ai-btn" href="{{ $url }}">Open CASPA →</a>
        @endif
    </div>

    <div class="sd-prompts">
        <button class="sd-prompt" type="button" data-prompt="Explain a difficult topic in simple words">Explain a topic</button>
        <button class="sd-prompt" type="button" data-prompt="Create a short quiz for my current subjects">Create a practice quiz</button>
        <button class="sd-prompt" type="button" data-prompt="Make revision notes for my next exam">Make revision notes</button>
        <button class="sd-prompt" type="button" data-prompt="Help me create a study plan for this week">Plan my study week</button>
    </div>

    @if($url = $routeFor('student-learning.index'))
        <!-- <form class="sd-ai-input" method="GET" action="{{ $url }}">
            <input type="text" name="q" id="sdAiQuestion" placeholder="Ask CASPA to help you understand something..." autocomplete="off">
            <button type="submit">Ask CASPA →</button>
        </form> -->
    @else
        <div class="sd-ai-input">
            <input type="text" id="sdAiQuestion" placeholder="Open CASPA Student Learning to start asking questions..." disabled>
            <button type="button" disabled>Ask CASPA</button>
        </div>
        <div class="sd-note">CASPA entry route is not configured yet. Connect this panel to your student learning module.</div>
    @endif
</section>

<div class="sd-footer">SchoolNexa · Your learning journey, all in one place.</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
(function () {
    var root = document.querySelector('.std-dashboard');
    if (!root) return;

    /* ---------- CASPA prompt chips ---------- */
    root.querySelectorAll('[data-prompt]').forEach(function (b) {
        b.addEventListener('click', function () {
            var i = root.querySelector('#sdAiQuestion');
            if (i && !i.disabled) { i.value = b.dataset.prompt; i.focus(); }
        });
    });

    /* ---------- Charts ---------- */
    var D = {
        attendance: @json([$presentDays, $absentDays, $lateDays]),
        attendancePct: @json($attendancePct),
        asg: @json($asgValues),
        trendLabels: @json($trendLabels),
        trendValues: @json($trendValues),
        subjLabels: @json($subjectLabels),
        subjValues: @json($subjectValues),
        libRate: @json($libRate !== null ? (float) $libRate : null)
    };
    var sum = function (a) { return a.reduce(function (x, y) { return x + (Number(y) || 0); }, 0); };
    var GRAY = '#E2E8F0';
    var palette = ['#3B82F6', '#16A34A', '#F59E0B', '#7C3AED', '#176B87', '#DC2626', '#0EA5E9', '#D97706'];

    function noData(id, msg) {
        var el = root.querySelector('#' + id);
        if (el) el.parentElement.innerHTML = '<div class="sd-empty"><strong>No data yet</strong>' + msg + '</div>';
    }

    function centerPlugin(text, sub) {
        return {
            id: 'ct' + Math.random(),
            afterDraw: function (c) {
                var m = c.getDatasetMeta(0).data[0]; if (!m) return;
                var ctx = c.ctx; ctx.save(); ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                ctx.fillStyle = '#123B5D'; ctx.font = '800 22px Inter, sans-serif'; ctx.fillText(text, m.x, m.y - 6);
                ctx.fillStyle = '#64748B'; ctx.font = '600 10px Inter, sans-serif'; ctx.fillText(sub, m.x, m.y + 14);
                ctx.restore();
            }
        };
    }

    function doughnut(id, values, colors, labels, ctext, csub, type) {
        var el = root.querySelector('#' + id); if (!el) return;
        var empty = sum(values) === 0;
        new Chart(el, {
            type: type || 'doughnut',
            data: { labels: empty ? ['No data'] : labels, datasets: [{ data: empty ? [1] : values, backgroundColor: empty ? [GRAY] : colors, borderWidth: 3, borderColor: '#fff', hoverOffset: 8 }] },
            options: { responsive: true, maintainAspectRatio: false, cutout: type === 'pie' ? 0 : '68%', animation: { duration: 900 }, plugins: { legend: { display: false }, tooltip: { enabled: !empty } } },
            plugins: ctext ? [centerPlugin(ctext, csub)] : []
        });
    }

    function lineChart(id) {
        var el = root.querySelector('#' + id); if (!el) return;
        if (!D.trendLabels.length) { noData(id, 'Your trend appears once data is published.'); return; }
        var g = el.getContext('2d').createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, 'rgba(23,107,135,.40)'); g.addColorStop(1, 'rgba(23,107,135,0)');
        new Chart(el, {
            type: 'line',
            data: { labels: D.trendLabels, datasets: [{ data: D.trendValues, borderColor: '#176B87', backgroundColor: g, fill: true, tension: .4, pointRadius: 4, pointHoverRadius: 7, pointBackgroundColor: '#fff', pointBorderColor: '#176B87', pointBorderWidth: 2 }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, max: 100, grid: { color: '#EEF2F6' }, ticks: { callback: function (v) { return v + '%'; } } }, x: { grid: { display: false } } } }
        });
    }

    function init() {
        if (!window.Chart) return;
        Chart.defaults.font.family = 'Inter, ui-sans-serif, system-ui, sans-serif';

        /* Attendance (area chart if a trend exists, otherwise a doughnut) */
        if (root.querySelector('#chTrend')) lineChart('chTrend');
        doughnut('chAttendance', D.attendance, ['#16A34A', '#DC2626', '#F59E0B'], ['Present', 'Absent', 'Late'], D.attendancePct.toFixed(0) + '%', 'Attendance');

        /* Academic progress (trend line, falls back to a message) */
        lineChart('chProgress');

        /* Assignment pie + library ring */
        doughnut('chAssignments', D.asg, ['#16A34A', '#F59E0B', '#DC2626'], ['Done', 'Pending', 'Late'], null, null, 'pie');
        var lr = D.libRate;
        doughnut('chLibrary', lr === null ? [0, 0] : [lr, Math.max(0, 100 - lr)], ['#123B5D', '#E2E8F0'], ['Available', 'Issued'], null, null);

        /* Subject performance: bar / radar toggle */
        var subj = root.querySelector('#chSubjects'), chart;
        if (subj && D.subjLabels.length) {
            var build = function (type) {
                if (chart) chart.destroy();
                var bar = type === 'bar';
                chart = new Chart(subj, {
                    type: type,
                    data: { labels: D.subjLabels, datasets: [{ label: 'Performance', data: D.subjValues,
                        backgroundColor: bar ? D.subjLabels.map(function (_, i) { return palette[i % palette.length]; }) : 'rgba(59,130,246,.2)',
                        borderColor: '#3B82F6', borderWidth: bar ? 0 : 2, borderRadius: 8, maxBarThickness: 34, pointBackgroundColor: '#3B82F6' }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } },
                        scales: bar ? { y: { beginAtZero: true, max: 100, grid: { color: '#EEF2F6' }, ticks: { callback: function (v) { return v + '%'; } } }, x: { grid: { display: false } } }
                                    : { r: { beginAtZero: true, max: 100, ticks: { display: false } } } }
                });
            };
            build('bar');
            root.querySelectorAll('#subjSwitch button').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    root.querySelectorAll('#subjSwitch button').forEach(function (b) { b.classList.toggle('on', b === btn); });
                    build(btn.dataset.type);
                });
            });
        }
    }
    if (window.Chart) init(); else window.addEventListener('load', init);

    /* ---------- 3D tilt + KPI count-up ---------- */
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
        var target = parseFloat(el.getAttribute('data-count')) || 0, dec = parseInt(el.getAttribute('data-dec'), 10) || 0;
        var suf = el.getAttribute('data-suf') || '', start = null;
        if (target === 0 || reduce) return;
        function fmt(v) { return v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suf; }
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / 700, 1);
            el.textContent = fmt(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(target);
        }
        requestAnimationFrame(step);
    });

    /* ---------- Toast for modules that aren't connected yet ---------- */
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
@endsection