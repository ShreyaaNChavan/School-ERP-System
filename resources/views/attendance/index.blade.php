@extends('layouts.schoolnexa')

@section('title', 'Attendance Management')

@section('content')

@php
    $isStudent = auth()->user()
        ->roleAssignments()
        ->where('is_active', true)
        ->whereHas('role', function ($query) {
            $query->where('code', 'STUDENT')
                  ->where('is_active', true);
        })
        ->exists();

    $statusLabels = [
        'present' => 'Present',
        'late' => 'Late',
        'absent' => 'Absent',
        'holiday' => 'Holiday',
        'halfday' => 'Halfday',
        'leave' => 'On Leave'
    ];

    $statusShort = [
        'present' => 'P',
        'late' => 'L',
        'absent' => 'A',
        'holiday' => 'H',
        'halfday' => '½',
        'leave' => 'LV'
    ];

    $today = now()->toDateString();
    $currentStudent = $isStudent
        ? $enrollments->first()?->student
        : null;

    // Attendance % = (present + late + 0.5*halfday) / working days
    // Working days = marked days excluding holidays. Unmarked days are ignored.
    $calcAttendance = function ($records) {
        $working = 0;
        $attended = 0;

        foreach ($records as $record) {
            if (!$record || $record->status === 'holiday') {
                continue;
            }

            $working++;

            if (in_array($record->status, ['present', 'late'])) {
                $attended += 1;
            } elseif ($record->status === 'halfday') {
                $attended += 0.5;
            }
        }

        return [
            'working'  => $working,
            'attended' => $attended,
            'percent'  => $working > 0 ? round(($attended / $working) * 100, 1) : null,
        ];
    };

    $allRecords = collect($attendanceMap)->flatMap(function ($byDate) {
        return collect($byDate)->values();
    });

    $overall = $calcAttendance($allRecords);

    $pctClass = function ($p) {
        if ($p === null) return 'pct-none';
        if ($p >= 90) return 'pct-good';
        if ($p >= 75) return 'pct-warn';
        return 'pct-low';
    };
@endphp

<div class="att-page">

    {{-- HEADER --}}
    <div class="att-header">
        <div>
            <h1>{{ $isStudent ? 'My Attendance' : 'Attendance Management' }}</h1>
            <p>
                {{ $isStudent
                    ? 'View your attendance records, daily status and attendance history.'
                    : 'Track, mark and review student attendance across your school.'
                }}
            </p>
        </div>

        <a href="{{ route('attendance.report') }}" class="att-outline">
            {{ $isStudent ? 'My Attendance Report' : 'Attendance Reports' }}
        </a>
    </div>

    @if(session('success'))
        <div class="att-alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="att-alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('attendance.index') }}" class="att-filter-panel">

        <div class="att-filter-grid">

            <div>
                <label>Academic Year</label>

                <select name="academic_year_id" {{ $isStudent ? 'disabled' : '' }}>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            @selected($academicYearId == $year->id)>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>

                @if($isStudent)
                    <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                @endif
            </div>

            <div>
                <label>From Date</label>
                <input
                    type="date"
                    name="from_date"
                    value="{{ $startDate->format('Y-m-d') }}"
                >
            </div>

            <div>
                <label>To Date</label>
                <input
                    type="date"
                    name="to_date"
                    value="{{ $endDate->format('Y-m-d') }}"
                >
            </div>

            @if($isStudent)

                <div>
                    <label>My Class</label>
                    <input
                        type="text"
                        value="{{ $currentStudent ? ($enrollments->first()->schoolClass->name ?? '-') : '-' }}"
                        disabled
                    >
                </div>

                <div>
                    <label>My Section</label>
                    <input
                        type="text"
                        value="{{ $enrollments->first()->section->name ?? '-' }}"
                        disabled
                    >
                </div>

            @else

                <div>
                    <label>Class</label>

                    <select name="school_class_id" id="attClass">
                        <option value="">All Classes</option>

                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                @selected($classId == $class->id)>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Section</label>

                    <select name="section_id" id="attSection">
                        <option value="">All Sections</option>

                        @foreach($sections as $section)
                            <option
                                value="{{ $section->id }}"
                                data-class="{{ $section->school_class_id }}"
                                @selected($sectionId == $section->id)
                            >
                                {{ $section->schoolClass->name ?? 'Class' }}
                                - {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            @endif

            <div>
                <label>Subject</label>

                <select name="subject_id">
                    <option value="">Full Day / All Subjects</option>

                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}"
                            @selected($subjectId == $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>

        <div class="att-filter-actions">
            <button class="att-primary" type="submit">Apply Filters</button>

            <a class="att-outline" href="{{ route('attendance.index') }}">
                Reset
            </a>
        </div>

    </form>

    {{-- SUMMARY --}}
    <div class="att-summary-grid">

        <div class="att-stat">
            <span>{{ $isStudent ? 'My Enrollment' : 'Total Students' }}</span>
            <strong>{{ $enrollments->total() }}</strong>
            <small>{{ $isStudent ? 'Currently enrolled' : 'Selected section' }}</small>
        </div>

        <div class="att-stat">
            <span>Present</span>
            <strong>{{ $todayCounts['present'] }}</strong>
            <small>Selected date range today</small>
        </div>

        <div class="att-stat">
            <span>Absent</span>
            <strong>{{ $todayCounts['absent'] }}</strong>
            <small>Selected date range today</small>
        </div>

        <div class="att-stat">
            <span>Late</span>
            <strong>{{ $todayCounts['late'] }}</strong>
            <small>Selected date range today</small>
        </div>

        <div class="att-stat">
            <span>On Leave</span>
            <strong>{{ $todayCounts['leave'] }}</strong>
            <small>Selected date range today</small>
        </div>

        <div class="att-stat">
            <span>{{ $isStudent ? 'My Attendance %' : 'Attendance %' }}</span>
            <strong>{{ $overall['percent'] !== null ? $overall['percent'].'%' : '—' }}</strong>
            <small>{{ $startDate->format('d M') }} – {{ $endDate->format('d M') }}</small>
        </div>

    </div>

    {{-- ATTENDANCE REGISTER --}}
    <section class="att-panel">

        <div class="att-panel-head">

            <div>
                <h2>{{ $isStudent ? 'My Attendance Register' : 'Two-Week Attendance Register' }}</h2>

                <p>
                    {{ $startDate->format('d M Y') }}
                    –
                    {{ $endDate->format('d M Y') }}
                    ·
                    {{ $enrollments->total() }}
                    {{ $isStudent ? 'student' : 'enrolled students' }}
                </p>
            </div>

            <div class="att-legend">
                <span><b class="present">P</b> Present</span>
                <span><b class="late">L</b> Late</span>
                <span><b class="absent">A</b> Absent</span>
                <span><b class="holiday">H</b> Holiday</span>
                <span><b class="halfday">½</b> Halfday</span>
                <span><b class="leave">LV</b> Leave</span>
            </div>

        </div>

        <div class="att-table-wrap">

            <table class="att-matrix">

                <thead>
                    <tr>
                        <th class="student-col">
                            {{ $isStudent ? 'My Details' : 'Admission No. / Student' }}
                        </th>

                        <th>Roll No.</th>

                        @foreach($dates as $date)
                            <th class="date-col">
                                <span>{{ $date->format('D') }}</span>
                                <strong>{{ $date->format('d') }}</strong>
                                <small>{{ $date->format('M') }}</small>
                            </th>
                        @endforeach

                        <th class="pct-col">Attendance %</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($enrollments as $enrollment)

                        @php
                            $student = $enrollment->student;
                            $studentStats = $calcAttendance(
                                $dates->map(fn ($d) => $attendanceMap[$student->id][$d->format('Y-m-d')] ?? null)
                            );
                        @endphp

                        <tr>

                            <td class="student-col">
                                <strong>{{ $student->admission_number }}</strong>
                                <span>{{ $student->full_name }}</span>
                            </td>

                            <td class="roll-col">
                                {{ $enrollment->student->metadata['roll_number'] ?? '—' }}
                            </td>

                            @foreach($dates as $date)

                                @php($record = $attendanceMap[$student->id][$date->format('Y-m-d')] ?? null)

                                <td class="att-cell">

                                    @if($record)

                                        <span
                                            class="att-pill {{ $record->status }}"
                                            title="{{ $statusLabels[$record->status] ?? ucfirst($record->status) }}{{ $record->remarks ? ' · '.$record->remarks : '' }}"
                                        >
                                            {{ $statusShort[$record->status] ?? '?' }}
                                        </span>

                                    @else

                                        <span class="att-unmarked">—</span>

                                    @endif

                                </td>

                            @endforeach

                            <td class="pct-col">
                                @if($studentStats['percent'] !== null)
                                    <span class="att-pct {{ $pctClass($studentStats['percent']) }}"
                                          title="{{ $studentStats['attended'] }} / {{ $studentStats['working'] }} working days">
                                        {{ $studentStats['percent'] }}%
                                    </span>
                                @else
                                    <span class="att-unmarked">—</span>
                                @endif
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="{{ 3 + $dates->count() }}" class="att-empty">
                                No attendance records found for the selected period.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="att-pagination">

            <div class="att-pagination-info">
                Showing {{ $enrollments->firstItem() ?? 0 }}
                –
                {{ $enrollments->lastItem() ?? 0 }}
                of {{ $enrollments->total() }}
                {{ $isStudent ? 'records' : 'students' }}
            </div>

            {{ $enrollments->withQueryString()->links() }}

        </div>

    </section>

    {{-- ATTENDANCE MARKING: STAFF ONLY --}}
    @unless($isStudent)

        <section class="att-panel att-mark-panel">

            <div class="att-panel-head">
                <div>
                    <h2>Mark / Update Attendance</h2>
                    <p>Select a class and section above, then mark attendance for a date.</p>
                </div>
            </div>

            @if($sectionId && $enrollments->isNotEmpty())

                <form method="POST" action="{{ route('attendance.store') }}" id="attendanceForm">

                    @csrf

                    <input type="hidden" name="school_id"
                        value="{{ $enrollments->first()->student->school_id }}">

                    <input type="hidden" name="campus_id"
                        value="{{ $enrollments->first()->student->campus_id }}">

                    <input type="hidden" name="academic_year_id"
                        value="{{ $academicYearId }}">

                    <input type="hidden" name="school_class_id"
                        value="{{ $classId ?: $enrollments->first()->school_class_id }}">

                    <input type="hidden" name="section_id"
                        value="{{ $sectionId }}">

                    <input type="hidden" name="subject_id"
                        value="{{ $subjectId }}">

                    <div class="att-mark-toolbar">

                        <div>
                            <label>Attendance Date</label>
                            <input
                                type="date"
                                name="attendance_date"
                                value="{{ request('mark_date', $today) }}"
                                required
                            >
                        </div>

                        <div>
                            <label>Bulk status</label>

                            <select id="bulkStatus">
                                <option value="">Choose status</option>

                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach

                            </select>
                        </div>

                        <button type="button" id="applyBulk" class="att-outline">
                            Apply to all
                        </button>

                        <button type="submit" class="att-primary">
                            Save Attendance
                        </button>

                    </div>

                    <div class="att-mark-table-wrap">

                        <table class="att-mark-table">

                            <thead>
                                <tr>
                                    <th>Admission No.</th>
                                    <th>Roll No.</th>
                                    <th>Student</th>
                                    <th>Attendance Status</th>
                                    <th>Check-in</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($enrollments as $enrollment)

                                    @php($student = $enrollment->student)

                                    <tr>

                                        <td>{{ $student->admission_number }}</td>

                                        <td>{{ $student->metadata['roll_number'] ?? '—' }}</td>

                                        <td><strong>{{ $student->full_name }}</strong></td>

                                        <td>
                                            <select
                                                class="student-status"
                                                name="attendance[{{ $student->id }}][status]"
                                                required
                                            >
                                                @foreach($statusLabels as $value => $label)
                                                    <option
                                                        value="{{ $value }}"
                                                        @selected(($attendanceMap[$student->id][$today]->status ?? 'present') === $value)
                                                    >
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input
                                                type="time"
                                                name="attendance[{{ $student->id }}][check_in_time]"
                                                value="{{ $attendanceMap[$student->id][$today]->check_in_time ?? '' }}"
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                name="attendance[{{ $student->id }}][remarks]"
                                                value="{{ $attendanceMap[$student->id][$today]->remarks ?? '' }}"
                                                placeholder="Optional note"
                                            >
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </form>

            @else

                <div class="att-empty-message">
                    Select a specific class and section above to mark attendance.
                </div>

            @endif

        </section>

    @endunless

</div>

<style>
.att-page{font-family:Inter,sans-serif;color:#25313C;padding:22px 24px}
.att-header{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:18px}
.att-header h1{font-size:22px;font-weight:700;color:#123B5D;margin:0}
.att-header p{font-size:13px;color:#64748B;margin:5px 0 0}
.att-filter-panel,.att-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;margin-bottom:17px}
.att-filter-panel{padding:17px}
.att-filter-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
.att-filter-grid label,.att-mark-toolbar label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px}
.att-filter-grid input,.att-filter-grid select,.att-mark-toolbar input,.att-mark-toolbar select,.att-mark-table select,.att-mark-table input{width:100%;height:38px;border:1px solid #CBD5E1;border-radius:8px;padding:0 9px;background:#fff;color:#25313C;font:400 12px Inter,sans-serif;box-sizing:border-box}
.att-filter-grid input:disabled,.att-filter-grid select:disabled{background:#F8FAFC;color:#64748B;opacity:1}
.att-filter-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:13px}
.att-primary,.att-outline{display:inline-flex;align-items:center;justify-content:center;min-height:39px;padding:0 13px;border-radius:9px;font:600 13px Inter,sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap}
.att-primary{border:1px solid #123B5D;background:#123B5D;color:#fff}
.att-outline{border:1px solid #CBD5E1;background:#fff;color:#123B5D}
.att-summary-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:17px}
.att-stat{background:#fff;border:1px solid #CBD5E1;border-radius:12px;padding:14px 16px}
.att-stat span{display:block;font-size:11px;font-weight:600;color:#64748B;text-transform:uppercase}
.att-stat strong{display:block;font-size:26px;font-weight:700;color:#123B5D;margin-top:5px}
.att-stat small{font-size:11px;color:#94A3B8}
.att-panel{overflow:hidden}
.att-panel-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:16px 18px;border-bottom:1px solid #E2E8F0}
.att-panel-head h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.att-panel-head p{font-size:12px;color:#64748B;margin:4px 0 0}
.att-legend{display:flex;gap:9px;flex-wrap:wrap}
.att-legend span{display:flex;align-items:center;gap:4px;font-size:10px;color:#64748B}
.att-legend b{display:inline-flex;align-items:center;justify-content:center;width:21px;height:21px;border-radius:5px;font-size:10px}
.att-table-wrap,.att-mark-table-wrap{overflow:auto}
.att-matrix{border-collapse:separate;border-spacing:0;width:100%;min-width:850px}
.att-matrix th{position:sticky;top:0;background:#F8FAFC;padding:9px 7px;border-bottom:1px solid #E2E8F0;font-size:11px;color:#64748B;text-align:center}
.att-matrix th.student-col{text-align:left;min-width:210px}
.att-matrix td{padding:8px 7px;border-bottom:1px solid #E2E8F0;text-align:center;font-size:11px}
.att-matrix td.student-col{text-align:left;min-width:210px}
.att-matrix td.student-col strong{display:block;font-size:11px;color:#176B87;font-weight:600}
.att-matrix td.student-col span{display:block;font-size:12px;color:#25313C;margin-top:3px}
.att-matrix .roll-col{color:#64748B}
.att-matrix .pct-col{min-width:80px;font-weight:700}
.date-col span,.date-col strong,.date-col small{display:block}
.date-col strong{font-size:13px;color:#123B5D}
.date-col small{font-size:9px;font-weight:400}
.att-cell{min-width:38px}
.att-pill{display:inline-flex;align-items:center;justify-content:center;min-width:25px;height:25px;padding:0 4px;border-radius:5px;font-size:10px;font-weight:700}
.att-pct{display:inline-block;padding:3px 8px;border-radius:999px;font-size:11px;font-weight:700}
.present{background:#DCFCE7;color:#166534}
.late{background:#FEF3C7;color:#92400E}
.absent{background:#FEE2E2;color:#B91C1C}
.holiday{background:#CFFAFE;color:#155E75}
.halfday{background:#EDE9FE;color:#5B21B6}
.leave{background:#DBEAFE;color:#1D4ED8}
.pct-good{background:#DCFCE7;color:#166534}
.pct-warn{background:#FEF3C7;color:#92400E}
.pct-low{background:#FEE2E2;color:#B91C1C}
.pct-none{color:#CBD5E1}
.att-unmarked{color:#CBD5E1}
.att-mark-panel .att-panel-head{border-bottom:0}
.att-mark-toolbar{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;padding:0 18px 15px}
.att-mark-toolbar>div{min-width:160px}
.att-mark-table{width:100%;border-collapse:collapse;min-width:850px}
.att-mark-table th{background:#F8FAFC;text-align:left;padding:11px 13px;font-size:11px;color:#64748B;border-top:1px solid #E2E8F0;border-bottom:1px solid #E2E8F0}
.att-mark-table td{padding:9px 13px;border-bottom:1px solid #E2E8F0;font-size:12px}
.att-mark-table td strong{color:#123B5D}
.att-mark-table select,.att-mark-table input{min-width:115px}
.att-pagination{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:13px 17px;border-top:1px solid #E2E8F0;background:#fff}
.att-pagination-info{font:400 12px Inter,sans-serif;color:#64748B}
.att-pagination nav{margin-left:auto}
.att-pagination svg{width:16px;height:16px}
.att-empty,.att-empty-message{text-align:center;padding:28px!important;color:#64748B;font-size:13px}
.att-empty-message{padding:20px!important}
.att-alert-success,.att-alert-error{padding:12px 15px;border-radius:9px;margin-bottom:15px;font-size:13px}
.att-alert-success{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534}
.att-alert-error{background:#FEF2F2;border:1px solid #FECACA;color:#B91C1C}
@media(max-width:1100px){.att-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.att-summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:650px){.att-page{padding:14px}.att-filter-grid,.att-summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>

<script>
document.getElementById('attClass')?.addEventListener('change', function () {
    const id = this.value;

    document.querySelectorAll('#attSection option').forEach(option => {
        if (option.value) {
            option.hidden = option.dataset.class !== id;
        }
    });

    document.getElementById('attSection').value = '';
});

document.getElementById('applyBulk')?.addEventListener('click', function () {
    const status = document.getElementById('bulkStatus').value;

    if (!status) {
        alert('Choose a bulk status first.');
        return;
    }

    document.querySelectorAll('.student-status').forEach(select => {
        select.value = status;
    });
});
</script>

@endsection