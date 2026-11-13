
@extends('layouts.schoolnexa')

@section('title', 'School Admin Dashboard')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $s = $stats ?? [];
    $number = fn ($value) => number_format((float) ($value ?? 0));
    $money = fn ($value) => '₹' . number_format((float) ($value ?? 0), 0);

    /*
     * Missing database values display as zero.
     * No fabricated/demo school figures are used.
     */
    $metrics = [
        'students' => $s['students'] ?? 0,
        'staff' => $s['staff'] ?? 0,
        'admission_leads' => $s['admissions'] ?? $s['admission_leads'] ?? 0,
        'pending_admissions' => $s['pending_admissions'] ?? 0,
        'attendance_today' => $s['attendance'] ?? $s['attendance_today'] ?? 0,
        'fees_collected' => $s['fees_collected'] ?? 0,
        'fees_outstanding' => $s['fees_outstanding'] ?? 0,
        'overdue_invoices' => $s['overdue_invoices'] ?? 0,
        'transport_vehicles' => $s['vehicles'] ?? $s['transport_vehicles'] ?? 0,
        'hostel_rooms' => $s['hostel_rooms'] ?? 0,
        'library_books' => $s['books'] ?? $s['library_books'] ?? 0,
        'pending_approvals' => $s['pending_approvals'] ?? 0,
        'safety_cases' => $s['safety_cases'] ?? 0,
        'events' => $s['events'] ?? 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Administrative module links
    |--------------------------------------------------------------------------
    | Only registered routes are displayed.
    */

    $sections = [
        'Admissions & Students' => [
            ['Admission CRM', 'admission-crm.index', 'Track enquiries, follow-ups and admission pipeline', 'AD'],
            ['Admission Applications', 'admission-applications.index', 'Review applications, documents and decisions', 'AP'],
            ['Student Directory', 'students.index', 'Student profiles, enrolments and status', 'ST'],
            ['Guardians & Parents', 'guardians.index', 'Guardian profiles and linked students', 'GP'],
            ['Certificates', 'certificates.dashboard', 'Generate and manage student certificates', 'CE'],
        ],

        'Academics & Assessment' => [
            ['Class & Sections', 'class-sections.index', 'Manage classes, sections and capacity', 'CL'],
            ['Subjects', 'subjects.index', 'Subject catalogue and class assignments', 'SU'],
            ['Timetables', 'timetables.index', 'School schedules and published timetables', 'TT'],
            ['Assignments', 'assignments.index', 'Assignments, submissions and review status', 'AS'],
            ['Homework', 'homework.index', 'Homework planning and student submissions', 'HW'],
            ['Examinations', 'examinations.index', 'Exam schedules and assessment setup', 'EX'],
            ['Marks Entry', 'marks-entry.index', 'Marks entry and result publishing', 'MK'],
            ['Report Cards', 'report-cards.index', 'Generate and publish student report cards', 'RC'],
            ['Study Materials', 'study-materials.index', 'Learning resources and uploaded materials', 'SM'],
        ],

        'People & Administration' => [
            ['Staff Directory', 'staff.index', 'Staff profiles and employment information', 'SF'],
            ['Employee Profiles', 'employee-profiles.index', 'View employee profile records', 'EP'],
            ['HR & Payroll', 'hr.dashboard', 'Attendance, leave, recruitment and payroll', 'HR'],
            ['Attendance', 'attendance.index', 'Student attendance and daily reports', 'AT'],
            ['Approval Center', 'approvals.dashboard', 'Requests awaiting administrative decisions', 'AC'],
            ['Role Builder', 'role-builder.dashboard', 'Roles, assignments and access configuration', 'RB'],
            ['Permission Matrix', 'permission-matrix.index', 'Review role-to-permission assignments', 'PM'],
        ],

        'Finance & Resources' => [
            ['Fee Management', 'fees.dashboard', 'Fee structures, invoices, payments and refunds', 'FE'],
            ['Inventory', 'inventory.dashboard', 'Assets, stock, vendors and purchase orders', 'IN'],
            ['Library', 'library.dashboard', 'Books, copies, issue and return records', 'LB'],
            ['Hostel', 'hostel.dashboard', 'Rooms, allocations, attendance and hostel fees', 'HO'],
        ],

        'Campus Operations & Safety' => [
            ['Transport', 'transport.dashboard', 'Routes, vehicles, drivers and tracking', 'TR'],
            ['Events', 'events.dashboard', 'School events, activities and calendar', 'EV'],
            ['Sports', 'sports.dashboard', 'Sports activities and participation', 'SP'],
            ['Clubs', 'clubs.dashboard', 'Clubs, memberships and activities', 'CB'],
            ['Student Support', 'student-support.dashboard', 'Complaints, safety and student welfare cases', 'SS'],
            ['Counselling', 'counselling.index', 'Counselling records and follow-ups', 'CO'],
            ['Communication Center', 'communication.dashboard', 'Announcements and school communication', 'CM'],
            ['PTM', 'ptm.dashboard', 'Parent-teacher meetings and history', 'PT'],
        ],

        'Reports & System' => [
            ['Reports & Analytics', 'caspa.reports.home', 'School-wide operational and academic reports', 'RA'],
            ['Audit Logs', 'audit-logs.dashboard', 'Review administrative activity history', 'AL'],
            ['System Settings', 'system-settings.dashboard', 'School and campus configuration', 'SS'],
            ['Integration Hub', 'integration-hub.dashboard', 'Connected services and integration status', 'IH'],
        ],
    ];

    $accent = [
        'Admissions & Students' => ['#EAF2FF', '#2457A6'],
        'Academics & Assessment' => ['#F0EAFE', '#6541A5'],
        'People & Administration' => ['#E6F6F5', '#13766E'],
        'Finance & Resources' => ['#FFF4E5', '#A65A08'],
        'Campus Operations & Safety' => ['#EAF7ED', '#247A43'],
        'Reports & System' => ['#EEF2F7', '#334B68'],
    ];

    $quickMetrics = [
        ['label' => 'Students', 'value' => $number($metrics['students']), 'note' => 'Student profiles', 'tone' => 'blue', 'symbol' => 'ST'],
        ['label' => 'Staff', 'value' => $number($metrics['staff']), 'note' => 'Staff profiles', 'tone' => 'purple', 'symbol' => 'SF'],
        ['label' => 'Admission Leads', 'value' => $number($metrics['admission_leads']), 'note' => 'Enquiries received', 'tone' => 'teal', 'symbol' => 'AD'],
        ['label' => 'Pending Admissions', 'value' => $number($metrics['pending_admissions']), 'note' => 'Awaiting processing', 'tone' => 'amber', 'symbol' => 'AP'],
        ['label' => 'Attendance Entries', 'value' => $number($metrics['attendance_today']), 'note' => 'Recorded today', 'tone' => 'green', 'symbol' => 'AT'],
        ['label' => 'Pending Approvals', 'value' => $number($metrics['pending_approvals']), 'note' => 'Require attention', 'tone' => 'red', 'symbol' => 'AC'],
        ['label' => 'Overdue Invoices', 'value' => $number($metrics['overdue_invoices']), 'note' => 'Past due date', 'tone' => 'amber', 'symbol' => 'FE'],
        ['label' => 'Safety Cases', 'value' => $number($metrics['safety_cases']), 'note' => 'Recorded cases', 'tone' => 'red', 'symbol' => 'SC'],
    ];

    $availableSections = collect($sections)->map(function ($items, $heading) {
        return [
            'heading' => $heading,
            'items' => collect($items)
                ->filter(fn ($item) => Route::has($item[1]))
                ->values(),
        ];
    })->filter(fn ($section) => $section['items']->isNotEmpty())->values();

    $recentItems = collect($recentItems ?? []);
    $upcomingItems = collect($upcomingItems ?? []);

    $monthlyFees = $monthlyFees ?? [];
@endphp

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.admin-dash {
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
    gap:16px;
    max-width:1600px;
    margin:auto;
}

.admin-dash * {box-sizing:border-box}
.admin-dash h1,
.admin-dash h2,
.admin-dash h3,
.admin-dash p {margin:0}

/* Header */

.ad-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
    padding:22px 24px;
    border:1px solid #D7E5F3;
    border-radius:16px;
    background:linear-gradient(110deg,#E6F0FA,#F8FBFF);
}

.ad-eyebrow {
    font-size:10px;
    font-weight:800;
    letter-spacing:.1em;
    text-transform:uppercase;
    color:var(--teal);
    margin-bottom:6px;
}

.ad-head h1 {
    font-size:24px;
    font-weight:800;
    color:var(--navy);
}

.ad-head p {
    font-size:12px;
    color:var(--muted);
    margin-top:6px;
}

.ad-head-right {
    display:flex;
    align-items:center;
    gap:12px;
}

.ad-date {
    text-align:right;
    color:var(--muted);
    font-size:11px;
}

.ad-date strong {
    display:block;
    color:var(--navy);
    font-size:17px;
}

.ad-btn {
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
}

.ad-btn.primary {
    background:var(--navy);
    border-color:var(--navy);
    color:#fff;
}

.ad-btn:hover {
    border-color:var(--teal);
    background:#F0F9FF;
}

/* Section headings */

.ad-section-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin:3px 0 0;
}

.ad-section-head h2 {
    font-size:15px;
    font-weight:800;
    color:var(--navy);
}

.ad-section-head span {
    font-size:10px;
    color:var(--muted);
}

/* KPI cards */

.ad-kpis {
    display:grid;
    grid-template-columns:repeat(4,minmax(0,1fr));
    gap:11px;
}

.ad-kpi,
.ad-panel {
    background:#fff;
    border:1px solid var(--line);
    border-radius:14px;
    padding:15px;
    min-width:0;
}

.ad-kpi {
    display:flex;
    align-items:center;
    gap:12px;
    transition:.16s;
}

.ad-kpi:hover {
    transform:translateY(-2px);
    box-shadow:0 8px 20px rgba(18,59,93,.08);
}

.ad-kpi-icon {
    width:40px;
    height:40px;
    border-radius:11px;
    display:grid;
    place-items:center;
    font-size:11px;
    font-weight:800;
    flex-shrink:0;
}

.ad-kpi.blue .ad-kpi-icon {background:#EAF2FF;color:#2457A6}
.ad-kpi.purple .ad-kpi-icon {background:#F0EAFE;color:#6541A5}
.ad-kpi.teal .ad-kpi-icon {background:#E6F6F5;color:#13766E}
.ad-kpi.amber .ad-kpi-icon {background:#FFF4E5;color:#A65A08}
.ad-kpi.green .ad-kpi-icon {background:#EAF7ED;color:#247A43}
.ad-kpi.red .ad-kpi-icon {background:#FEECEC;color:#B42318}

.ad-kpi small {
    display:block;
    font-size:9px;
    font-weight:800;
    letter-spacing:.05em;
    text-transform:uppercase;
    color:var(--muted);
}

.ad-kpi strong {
    display:block;
    font-size:23px;
    line-height:1.2;
    color:var(--navy);
    margin:4px 0 2px;
}

.ad-kpi span {
    display:block;
    font-size:10px;
    color:var(--muted);
}

/* Panels and grids */

.ad-grid-2 {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:13px;
}

.ad-panel-head {
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:12px;
}

.ad-panel-head h3 {
    font-size:13px;
    font-weight:800;
    color:var(--navy);
}

.ad-panel-head span {
    font-size:10px;
    color:var(--muted);
}

/* Module cards */

.ad-module-grid {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:9px;
}

.ad-module {
    display:flex;
    align-items:center;
    gap:10px;
    min-height:68px;
    padding:10px;
    border:1px solid #E3EAF1;
    border-radius:10px;
    text-decoration:none;
    background:#FBFDFF;
    transition:.15s;
    min-width:0;
}

.ad-module:hover {
    border-color:var(--teal);
    background:#fff;
    transform:translateY(-1px);
    box-shadow:0 5px 14px rgba(18,59,93,.06);
}

.ad-module-icon {
    width:34px;
    height:34px;
    display:grid;
    place-items:center;
    flex-shrink:0;
    border-radius:9px;
    font-size:10px;
    font-weight:800;
}

.ad-module b {
    display:block;
    color:var(--navy);
    font-size:11px;
    line-height:1.3;
}

.ad-module small {
    display:block;
    color:var(--muted);
    font-size:9px;
    line-height:1.35;
    margin-top:4px;
}

.ad-module-copy {min-width:0}

.ad-arrow {
    margin-left:auto;
    color:#94A3B8;
    font-size:16px;
}

.ad-module-section {
    display:flex;
    flex-direction:column;
    gap:10px;
}

.ad-module-section + .ad-module-section {
    margin-top:18px;
    padding-top:17px;
    border-top:1px solid #EEF2F6;
}

.ad-section-title {
    display:flex;
    align-items:center;
    gap:8px;
}

.ad-section-title i {
    width:8px;
    height:8px;
    border-radius:50%;
    display:inline-block;
}

.ad-section-title h3 {
    font-size:12px;
    font-weight:800;
    color:var(--navy);
}

.ad-section-title span {
    font-size:10px;
    color:var(--muted);
    margin-left:auto;
}

/* Finance */

.ad-finance {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:9px;
}

.ad-finance-item {
    padding:12px;
    border:1px solid #E5EBF1;
    border-radius:10px;
    background:#F8FAFC;
}

.ad-finance-item small {
    display:block;
    font-size:10px;
    color:var(--muted);
}

.ad-finance-item strong {
    display:block;
    font-size:18px;
    color:var(--navy);
    margin-top:5px;
}

.ad-chart {
    height:235px;
    position:relative;
}

.ad-chart.small {height:205px}

/* Lists */

.ad-list {
    display:flex;
    flex-direction:column;
}

.ad-row {
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:10px;
    padding:10px 0;
    border-bottom:1px solid #EEF2F6;
}

.ad-row:last-child {border:0}

.ad-row b {
    display:block;
    font-size:11px;
    color:var(--navy);
}

.ad-row small {
    display:block;
    font-size:10px;
    color:var(--muted);
    margin-top:3px;
}

.ad-pill {
    font-size:9px;
    font-weight:700;
    padding:4px 8px;
    border-radius:20px;
    background:#EAF2FF;
    color:#2457A6;
    white-space:nowrap;
}

.ad-empty {
    padding:18px 0;
    text-align:center;
    color:var(--muted);
    font-size:11px;
}

.ad-note {
    font-size:10px;
    color:var(--muted);
}

/* Responsive */

@media(max-width:1100px) {
    .ad-kpis {grid-template-columns:repeat(2,minmax(0,1fr))}
    .ad-module-grid {grid-template-columns:repeat(2,minmax(0,1fr))}
}

@media(max-width:760px) {
    .ad-grid-2 {grid-template-columns:1fr}
    .ad-head {padding:18px}
    .ad-head h1 {font-size:21px}
}

@media(max-width:460px) {
    .ad-kpis {grid-template-columns:1fr 1fr}
    .ad-kpi {padding:11px}
    .ad-kpi strong {font-size:19px}
    .ad-module-grid {grid-template-columns:1fr}
    .ad-finance {grid-template-columns:1fr}
}
</style>

<div class="admin-dash">

    {{-- HEADER --}}

    <header class="ad-head">
        <div>
            <div class="ad-eyebrow">SchoolNexa / Administration</div>
            <h1>School Admin Dashboard</h1>
            <p>
                Central overview of admissions, academics, people, finance,
                facilities, transport and school operations.
            </p>
        </div>

        <div class="ad-head-right">
            <div class="ad-date">
                {{ now()->format('D, d M Y') }}
                <strong>{{ now()->format('h:i A') }}</strong>
            </div>

            <a class="ad-btn" href="{{ route('dashboard') }}">
                Refresh Overview
            </a>
        </div>
    </header>

    {{-- KPI OVERVIEW --}}

    <div class="ad-section-head">
        <h2>School at a Glance</h2>
        <span>School-wide operational snapshot</span>
    </div>

    <section class="ad-kpis">
        @foreach($quickMetrics as $metric)
            <article class="ad-kpi {{ $metric['tone'] }}">
                <div class="ad-kpi-icon">
                    {{ $metric['symbol'] }}
                </div>

                <div>
                    <small>{{ $metric['label'] }}</small>
                    <strong>{{ $metric['value'] }}</strong>
                    <span>{{ $metric['note'] }}</span>
                </div>
            </article>
        @endforeach
    </section>

    {{-- ADMINISTRATIVE MODULES --}}

    <div class="ad-section-head">
        <h2>Administrative Control Center</h2>
        <span>Open a module to view and manage its records</span>
    </div>

    <section class="ad-panel">

        @forelse($availableSections as $section)

            @php
                $colors = $accent[$section['heading']] ?? ['#EEF2F7','#334B68'];
            @endphp

            <div class="ad-module-section">

                <div class="ad-section-title">
                    <i style="background:{{ $colors[1] }}"></i>

                    <h3>{{ $section['heading'] }}</h3>

                    <span>{{ $section['items']->count() }} modules</span>
                </div>

                <div class="ad-module-grid">

                    @foreach($section['items'] as $item)

                        <a class="ad-module" href="{{ route($item[1]) }}">

                            <span
                                class="ad-module-icon"
                                style="background:{{ $colors[0] }};color:{{ $colors[1] }}"
                            >
                                {{ $item[3] }}
                            </span>

                            <span class="ad-module-copy">
                                <b>{{ $item[0] }}</b>
                                <small>{{ $item[2] }}</small>
                            </span>

                            <span class="ad-arrow">›</span>

                        </a>

                    @endforeach

                </div>
            </div>

        @empty

            <div class="ad-empty">
                No module routes are currently available to this account.
            </div>

        @endforelse

    </section>

    {{-- FINANCE AND FACILITIES --}}

    <div class="ad-section-head">
        <h2>Finance & Facilities Snapshot</h2>
        <span>Values supplied by the dashboard controller</span>
    </div>

    <section class="ad-grid-2">

        <article class="ad-panel">

            <div class="ad-panel-head">
                <h3>Fee & Collection Position</h3>

                @if(Route::has('fees.dashboard'))
                    <a class="ad-btn" href="{{ route('fees.dashboard') }}">
                        Open Fees
                    </a>
                @endif
            </div>

            <div class="ad-finance">

                <div class="ad-finance-item">
                    <small>Fees Collected</small>
                    <strong>{{ $money($metrics['fees_collected']) }}</strong>
                </div>

                <div class="ad-finance-item">
                    <small>Outstanding</small>
                    <strong>{{ $money($metrics['fees_outstanding']) }}</strong>
                </div>

                <div class="ad-finance-item">
                    <small>Overdue Invoices</small>
                    <strong>{{ $number($metrics['overdue_invoices']) }}</strong>
                </div>

            </div>

            <div class="ad-chart small">
                <canvas id="adminFeeChart"></canvas>
            </div>

        </article>

        <article class="ad-panel">

            <div class="ad-panel-head">
                <h3>Campus Resources</h3>
                <span>Current recorded totals</span>
            </div>

            <div class="ad-finance">

                <div class="ad-finance-item">
                    <small>Transport Vehicles</small>
                    <strong>{{ $number($metrics['transport_vehicles']) }}</strong>
                </div>

                <div class="ad-finance-item">
                    <small>Hostel Rooms</small>
                    <strong>{{ $number($metrics['hostel_rooms']) }}</strong>
                </div>

                <div class="ad-finance-item">
                    <small>Library Books</small>
                    <strong>{{ $number($metrics['library_books']) }}</strong>
                </div>

            </div>

            <div class="ad-list" style="margin-top:10px">

                @if(Route::has('transport.dashboard'))
                    <div class="ad-row">
                        <div>
                            <b>Transport operations</b>
                            <small>Routes, fleet and vehicle tracking</small>
                        </div>

                        <a class="ad-btn" href="{{ route('transport.dashboard') }}">
                            Open
                        </a>
                    </div>
                @endif

                @if(Route::has('hostel.dashboard'))
                    <div class="ad-row">
                        <div>
                            <b>Hostel management</b>
                            <small>Rooms, allocations and hostel fees</small>
                        </div>

                        <a class="ad-btn" href="{{ route('hostel.dashboard') }}">
                            Open
                        </a>
                    </div>
                @endif

                @if(Route::has('library.dashboard'))
                    <div class="ad-row">
                        <div>
                            <b>Library operations</b>
                            <small>Books, issue and return activity</small>
                        </div>

                        <a class="ad-btn" href="{{ route('library.dashboard') }}">
                            Open
                        </a>
                    </div>
                @endif

            </div>

        </article>

    </section>

    {{-- RECENT AND UPCOMING ACTIVITY --}}

    <div class="ad-section-head">
        <h2>School Activity & Monitoring</h2>
        <span>Latest updates and upcoming work</span>
    </div>

    <section class="ad-grid-2">

        <article class="ad-panel">

            <div class="ad-panel-head">
                <h3>Recent School Updates</h3>
                <span>Latest recorded activity</span>
            </div>

            <div class="ad-list">

                @forelse($recentItems as $item)

                    <div class="ad-row">
                        <div>
                            <b>{{ data_get($item, 'title', 'School update') }}</b>

                            <small>
                                {{ data_get($item, 'date', data_get($item, 'created_at', '')) }}
                            </small>
                        </div>

                        <span class="ad-pill">
                            {{ data_get($item, 'status', 'Updated') }}
                        </span>
                    </div>

                @empty

                    <div class="ad-empty">
                        No recent activity records were provided.
                    </div>

                @endforelse

            </div>

        </article>

        <article class="ad-panel">

            <div class="ad-panel-head">
                <h3>Upcoming Activities</h3>

                @if(Route::has('events.dashboard'))
                    <a class="ad-btn" href="{{ route('events.dashboard') }}">
                        View Events
                    </a>
                @endif
            </div>

            <div class="ad-list">

                @forelse($upcomingItems as $item)

                    <div class="ad-row">
                        <div>
                            <b>{{ data_get($item, 'title', 'School activity') }}</b>

                            <small>
                                {{ data_get($item, 'date', '') }}
                                ·
                                {{ data_get($item, 'time', '') }}
                            </small>
                        </div>

                        <span class="ad-pill">
                            {{ data_get($item, 'status', 'Upcoming') }}
                        </span>
                    </div>

                @empty

                    <div class="ad-empty">
                        No upcoming activities were provided.
                    </div>

                @endforelse

            </div>

        </article>

    </section>

    <div class="ad-note">
        Dashboard figures are not generated by this Blade file.
        Missing controller values display as zero.
        Each module must enforce its own permissions and record scope.
    </div>

</div>

{{-- CHART.JS --}}

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
(function () {

    const el = document.getElementById('adminFeeChart');

    if (!el || typeof Chart === 'undefined') {
        return;
    }

    const collected = Number(@json($metrics['fees_collected']));
    const outstanding = Number(@json($metrics['fees_outstanding']));
    const overdue = Number(@json($metrics['overdue_invoices']));

    new Chart(el, {
        type: 'doughnut',

        data: {
            labels: [
                'Collected',
                'Outstanding',
                'Overdue invoices'
            ],

            datasets: [{
                data: [
                    collected,
                    outstanding,
                    overdue
                ],

                backgroundColor: [
                    '#176B87',
                    '#3B82F6',
                    '#D97706'
                ],

                borderWidth: 0,
                hoverOffset: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',

            plugins: {
                legend: {
                    position: 'bottom',

                    labels: {
                        boxWidth: 9,
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

})();
</script>

@endsection
