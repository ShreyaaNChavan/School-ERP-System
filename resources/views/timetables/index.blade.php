@extends('layouts.schoolnexa')

@section('title', 'Timetable Management')

@section('content')
    @php
        $font = 'Inter, sans-serif';
        $navy = '#123B5D';
        $teal = '#176B87';
        $purple = '#5B3FA3';
        $surface = '#F8FAFC';
        $border = '#CBD5E1';
        $text = '#25313C';
        $muted = '#64748B';

        $statusStyles = [
            'draft' => ['#FEF3C7', '#92400E'],
            'published' => ['#DCFCE7', '#166534'],
            'archived' => ['#F1F5F9', '#475569'],
        ];
    @endphp

    <div style="font-family:{{ $font }}; color:{{ $text }}; padding:22px 24px;">

        {{-- PAGE HEADER --}}
        <div
            style="display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; margin-bottom:22px;">
            <div>
                <h1 style="font-size:22px; line-height:1.35; font-weight:700; color:{{ $navy }}; margin:0;">
                    Timetable Management
                </h1>
                <p style="font-size:13px; line-height:1.5; color:{{ $muted }}; margin:5px 0 0;">
                    Manage class schedules, teaching periods and teacher allocations.
                </p>
            </div>

            @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
                <button type="button" onclick="openTimetableModal()"
                    style="height:40px; padding:0 16px; border:0; border-radius:9px; background:{{ $navy }}; color:white; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">
                    + Create Timetable
                </button>
            @endif
        </div>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div
                style="padding:12px 15px; margin-bottom:16px; border:1px solid #BBF7D0; background:#F0FDF4; color:#166534; border-radius:10px; font-size:13px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div
                style="padding:13px 16px; margin-bottom:16px; border:1px solid #FECACA; background:#FEF2F2; color:#B91C1C; border-radius:10px; font-size:13px;">
                <div style="font-weight:650; margin-bottom:6px;">Please correct the following:</div>
                @foreach($errors->all() as $error)
                    <div style="margin-top:4px;">• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        {{-- KPI CARDS --}}
        <div style="display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:14px; margin-bottom:20px;">
            @foreach([
                    ['Total Timetables', $stats['total'], $navy, 'calendar-days'],
                    ['Published', $stats['published'], '#16A34A', 'check-circle'],
                    ['Drafts', $stats['draft'], '#D97706', 'file-edit'],
                    ['Archived', $stats['archived'], $muted, 'archive'],
                ] as $stat)
                <div style="background:white; border:1px solid {{ $border }}; border-radius:14px; padding:17px 18px;">
                    <div style="font-size:13px; font-weight:500; color:{{ $muted }};">
                        {{ $stat[0] }}
                    </div>
                    <div style="font-size:26px; line-height:1.3; font-weight:700; color:{{ $stat[2] }}; margin-top:7px;">
                        {{ $stat[1] }}
                    </div>
                </div>
            @endforeach
        </div>

        {{-- FILTER PANEL --}}
        <div
            style="background:white; border:1px solid {{ $border }}; border-radius:14px; padding:18px; margin-bottom:18px;">
            <form method="GET" action="{{ route('timetables.index') }}"
                style="display:grid; grid-template-columns:2fr 1.2fr 1fr auto auto; gap:12px; align-items:end;">

                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:7px;">Search
                        timetable</label>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Search name, class or section"
                        style="width:100%; height:40px; box-sizing:border-box; border:1px solid {{ $border }}; border-radius:8px; padding:0 11px; font-family:{{ $font }}; font-size:13px;">
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:7px;">Academic Year</label>
                    <select name="academic_year_id"
                        style="width:100%; height:40px; border:1px solid {{ $border }}; border-radius:8px; padding:0 10px; font-family:{{ $font }}; font-size:13px; background:white;">
                        <option value="">All Years</option>
                        @foreach(\App\Models\AcademicYear::orderByDesc('start_date')->get() as $year)
                            <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block; font-size:12px; font-weight:600; margin-bottom:7px;">Status</label>
                    <select name="status"
                        style="width:100%; height:40px; border:1px solid {{ $border }}; border-radius:8px; padding:0 10px; font-family:{{ $font }}; font-size:13px; background:white;">
                        <option value="">All Statuses</option>
                        @foreach(['draft', 'published', 'archived'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    style="height:40px; padding:0 17px; border:0; border-radius:8px; background:{{ $navy }}; color:white; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">
                    Apply
                </button>

                <a href="{{ route('timetables.index') }}"
                    style="height:40px; padding:0 12px; display:flex; align-items:center; justify-content:center; box-sizing:border-box; border:1px solid {{ $border }}; border-radius:8px; color:{{ $navy }}; font-size:13px; font-weight:600; text-decoration:none;">
                    Reset
                </a>
            </form>
        </div>

        {{-- DIRECTORY TABLE --}}
        <div style="background:white; border:1px solid {{ $border }}; border-radius:14px; overflow:hidden;">
            <div
                style="padding:17px 19px; display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #E2E8F0;">
                <div>
                    <h2 style="font-size:16px; line-height:1.4; font-weight:650; color:{{ $navy }}; margin:0;">
                        Timetable Directory
                    </h2>
                    <p style="font-size:12px; color:{{ $muted }}; margin:4px 0 0;">
                        Manage all academic schedules.
                    </p>
                </div>
                <span style="font-size:12px; color:{{ $muted }};">
                    {{ $timetables->total() }} records
                </span>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%; min-width:850px; border-collapse:collapse;">
                    <thead style="background:{{ $surface }};">
                        <tr>
                            @foreach(['Timetable', 'Class / Section', 'Academic Year', 'Version', 'Effective From', 'Status', 'Actions'] as $heading)
                                <th
                                    style="padding:13px 15px; text-align:left; font-size:12px; font-weight:650; color:{{ $muted }}; white-space:nowrap;">
                                    {{ $heading }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($timetables as $timetable)
                            @php
                                $colors = $statusStyles[$timetable->status] ?? $statusStyles['draft'];
                            @endphp
                            <tr style="border-top:1px solid #E2E8F0;">
                                <td style="padding:14px 15px; font-size:13px; font-weight:600;">
                                    {{ $timetable->name }}
                                </td>
                                <td style="padding:14px 15px; font-size:13px;">
                                    {{ $timetable->schoolClass->name ?? '-' }}
                                    <span style="color:{{ $muted }};">/</span>
                                    {{ $timetable->section->name ?? '-' }}
                                </td>
                                <td style="padding:14px 15px; font-size:13px;">
                                    {{ $timetable->academicYear->name ?? '-' }}
                                </td>
                                <td style="padding:14px 15px; font-size:13px;">v{{ $timetable->version }}</td>
                                <td style="padding:14px 15px; font-size:13px;">
                                    {{ $timetable->effective_from?->format('d M Y') ?? '-' }}
                                </td>
                                <td style="padding:14px 15px;">
                                    <span
                                        style="display:inline-block; padding:5px 10px; border-radius:20px; background:{{ $colors[0] }}; color:{{ $colors[1] }}; font-size:11px; font-weight:650;">
                                        {{ ucfirst($timetable->status) }}
                                    </span>
                                </td>
                                <td style="padding:14px 15px;">
                                    <div class="tt-actions">
                                        <a href="{{ route('timetables.show', $timetable) }}"
                                            class="tt-action">View</a>

                                        @if($timetable->status === 'draft' && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                            <a href="{{ route('timetables.edit', $timetable) }}"
                                                class="tt-action">Edit</a>
                                        @endif

                                        @if(auth()->user()->hasPermission('ACADEMICS.DELETE') && $timetable->status !== 'published')
                                            <form method="POST" action="{{ route('timetables.destroy', $timetable) }}"
                                                onsubmit="return confirm('Delete this timetable?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="tt-action tt-action-danger">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding:48px 20px; text-align:center;">
                                    <div style="font-size:16px; font-weight:650; color:{{ $navy }};">No timetables created yet
                                    </div>
                                    <p style="font-size:13px; color:{{ $muted }}; margin:7px 0 16px;">
                                        Create a timetable to organize classes, teachers and periods.
                                    </p>
                                    @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
                                        <button type="button" onclick="openTimetableModal()"
                                            style="height:38px; padding:0 16px; background:{{ $navy }}; color:white; border:0; border-radius:8px; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">
                                            + Create First Timetable
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="padding:15px 19px; border-top:1px solid #E2E8F0;">
                {{ $timetables->links() }}
            </div>
        </div>
    </div>

    {{-- CREATE TIMETABLE MODAL --}}
    <div id="timetableModal"
        style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,.55); padding:24px; overflow-y:auto; align-items:center; justify-content:center;">

        <div
            style="width:100%; max-width:1050px; max-height:calc(100vh - 48px); overflow-y:auto; background:white; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,.2);">

            <div
                style="position:sticky; top:0; z-index:2; background:white; padding:19px 23px; border-bottom:1px solid #E2E8F0; display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <h2 style="font-family:{{ $font }}; font-size:18px; font-weight:700; color:{{ $navy }}; margin:0;">
                        Create Timetable</h2>
                    <p style="font-family:{{ $font }}; font-size:12px; color:{{ $muted }}; margin:5px 0 0;">Configure the
                        class schedule and teaching periods.</p>
                </div>
                <button type="button" onclick="closeTimetableModal()" aria-label="Close"
                    style="width:34px; height:34px; border:1px solid {{ $border }}; background:white; border-radius:8px; font-size:20px; color:{{ $muted }}; cursor:pointer;">&times;</button>
            </div>

            <form method="POST" action="{{ route('timetables.store') }}" id="timetableForm">
                @csrf

                <div style="padding:22px 23px;">

                    <div style="font-size:16px; font-weight:650; color:{{ $navy }}; margin-bottom:15px;">Basic Information
                    </div>

                    <div class="tt-form-grid">
                        <div class="tt-field">
                            <label>Timetable Name *</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                placeholder="e.g. Class 8 A - Weekly Timetable" required>
                        </div>

                        <div class="tt-field">
                            <label>School *</label>
                            <select name="school_id" id="ttSchool" required>
                                <option value="">Select School</option>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" @selected(old('school_id', 1) == $school->id)>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tt-field">
                            <label>Campus *</label>
                            <select name="campus_id" id="ttCampus" required>
                                <option value="">Select Campus</option>
                                @foreach($campuses as $campus)
                                    <option value="{{ $campus->id }}" data-school="{{ $campus->school_id }}"
                                        @selected(old('campus_id', 1) == $campus->id)>{{ $campus->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tt-field">
                            <label>Academic Year *</label>
                            <select name="academic_year_id" required>
                                <option value="">Select Academic Year</option>
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" @selected(old('academic_year_id', 1) == $year->id)>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tt-field">
                            <label>Class *</label>
                            <select name="school_class_id" id="ttClass" required>
                                <option value="">Select Class</option>
                                @foreach($classes as $class)
                                    <option value="{{ $class->id }}" data-school="{{ $class->school_id }}"
                                        @selected(old('school_class_id') == $class->id)>
                                        {{ $class->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tt-field">
                            <label>Section *</label>
                            <select name="section_id" id="ttSection" required>
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" data-class="{{ $section->school_class_id }}"
                                        data-campus="{{ $section->campus_id }}" @selected(old('section_id') == $section->id)>
                                        {{ $section->schoolClass->name ?? '' }} - {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="tt-field">
                            <label>Effective From</label>
                            <input type="date" name="effective_from" value="{{ old('effective_from') }}">
                        </div>

                        <div class="tt-field">
                            <label>Effective Until</label>
                            <input type="date" name="effective_until" value="{{ old('effective_until') }}">
                        </div>
                    </div>

                    <div style="height:1px; background:#E2E8F0; margin:23px 0;"></div>

                    <div
                        style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin-bottom:15px;">
                        <div>
                            <h3 style="font-size:16px; font-weight:650; color:{{ $navy }}; margin:0;">Periods & Weekly
                                Schedule</h3>
                            <p style="font-size:12px; color:{{ $muted }}; margin:5px 0 0;">Add teaching periods and breaks,
                                then allocate subjects and teachers.</p>
                        </div>
                        <button type="button" onclick="addPeriod()"
                            style="height:36px; padding:0 13px; border:1px solid {{ $border }}; border-radius:8px; background:white; color:{{ $navy }}; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">
                            + Add Period
                        </button>
                    </div>

                    <div id="periodContainer"></div>

                    <div
                        style="padding:13px 15px; border-radius:9px; background:{{ $surface }}; border:1px solid #E2E8F0; margin-top:15px;">
                        <div style="font-size:12px; color:{{ $muted }}; line-height:1.6;">
                            <strong style="color:{{ $navy }};">Note:</strong>
                            Break periods are automatically excluded from subject and teacher allocation. Unassigned
                            teaching slots can be left empty and completed later.
                        </div>
                    </div>
                </div>

                <div
                    style="position:sticky; bottom:0; background:white; padding:15px 23px; border-top:1px solid #E2E8F0; display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeTimetableModal()"
                        style="height:39px; padding:0 16px; background:white; border:1px solid {{ $border }}; border-radius:8px; color:{{ $navy }}; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">Cancel</button>
                    <button type="submit"
                        style="height:39px; padding:0 18px; background:{{ $navy }}; border:0; border-radius:8px; color:white; font-family:{{ $font }}; font-size:13px; font-weight:600; cursor:pointer;">Save
                        as Draft</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        /* STANDARD DIRECTORY ACTION BUTTONS */
        .tt-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .tt-actions form {
            display: inline-flex;
            margin: 0;
        }

        .tt-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0 12px;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background: #fff;
            color: #123B5D;
            font: 600 13px 'Inter', sans-serif;
            text-decoration: none;
            cursor: pointer;
            white-space: nowrap;
            transition: .2s;
        }

        .tt-action:hover {
            background: #F8FAFC;
            color: #123B5D;
        }

        .tt-action-danger {
            color: #DC2626;
        }

        .tt-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 15px;
        }

        .tt-field label {
            display: block;
            font-family: Inter, sans-serif;
            font-size: 12px;
            font-weight: 600;
            color: #25313C;
            margin-bottom: 7px;
        }

        .tt-field input,
        .tt-field select {
            width: 100%;
            height: 40px;
            box-sizing: border-box;
            border: 1px solid #CBD5E1;
            border-radius: 8px;
            padding: 0 11px;
            font-family: Inter, sans-serif;
            font-size: 13px;
            color: #25313C;
            background: white;
            outline: none;
        }

        .tt-period {
            border: 1px solid #CBD5E1;
            border-radius: 11px;
            padding: 15px;
            margin-bottom: 12px;
            background: white;
        }

        .tt-period-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            margin-bottom: 13px;
        }

        .tt-period-title {
            font-size: 13px;
            font-weight: 650;
            color: #123B5D;
        }

        .tt-period-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 11px;
        }

        .tt-period-field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #64748B;
        }

        .tt-period-field input,
        .tt-period-field select {
            width: 100%;
            height: 38px;
            box-sizing: border-box;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            padding: 0 9px;
            font-family: Inter, sans-serif;
            font-size: 13px;
            background: white;
            color: #25313C;
        }

        .tt-day-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 15px;
        }

        .tt-day-card {
            border: 1px solid #E2E8F0;
            border-radius: 9px;
            padding: 11px;
            background: #F8FAFC;
        }

        .tt-day-card label {
            display: block;
            font-size: 12px;
            font-weight: 650;
            color: #123B5D;
            margin-bottom: 9px;
        }

        .tt-day-card select,
        .tt-day-card input {
            width: 100%;
            height: 35px;
            box-sizing: border-box;
            margin-top: 6px;
            padding: 0 8px;
            border: 1px solid #CBD5E1;
            border-radius: 7px;
            font-family: Inter, sans-serif;
            font-size: 12px;
            background: white;
        }

        .tt-remove {
            border: 0;
            background: #FEF2F2;
            color: #DC2626;
            border-radius: 7px;
            padding: 7px 10px;
            font-family: Inter, sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        @media(max-width:900px) {
            .tt-day-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .tt-period-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media(max-width:650px) {

            .tt-form-grid,
            .tt-day-grid,
            .tt-period-grid {
                grid-template-columns: 1fr;
            }

            #timetableModal {
                padding: 8px !important;
            }

            #timetableModal>div {
                max-height: calc(100vh - 16px) !important;
            }
        }
    </style>

    <script>
        const timetableModal = document.getElementById('timetableModal');
        const periodContainer = document.getElementById('periodContainer');

        const subjectOptions = {{ Illuminate\Support\Js::from(
        $classSubjects->map(function ($item) {
            return [
                'id' => $item->id,
                'class_id' => $item->school_class_id,
                'name' => $item->subject->name ?? 'Subject',
            ];
        })->values()
    ) }};

        const teacherOptions = {{ Illuminate\Support\Js::from(
        $teachers->map(function ($teacher) {
            return [
                'id' => $teacher->id,
                'name' => trim($teacher->first_name . ' ' . $teacher->last_name),
            ];
        })->values()
    ) }};

        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];

        let periodCount = 0;

        function openTimetableModal() {
            timetableModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            if (periodCount === 0) {
                addPeriod();
                addPeriod();
                addPeriod();
            }
        }

        function closeTimetableModal() {
            timetableModal.style.display = 'none';
            document.body.style.overflow = '';
        }

        timetableModal.addEventListener('click', function (e) {
            if (e.target === timetableModal) closeTimetableModal();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && timetableModal.style.display === 'flex') {
                closeTimetableModal();
            }
        });

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, char => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#039;'
            }[char]));
        }

        function addPeriod() {
            const index = periodCount++;
            const number = periodCount;

            let dayCards = '';

            days.forEach(day => {
                const dayTitle = day.charAt(0).toUpperCase() + day.slice(1);

                dayCards += `
                        <div class="tt-day-card">
                            <label>${dayTitle}</label>
                            <select name="periods[${index}][entries][${day}][class_subject_id]"
                                class="tt-subject" data-class-subject>
                                <option value="">Select Subject</option>
                            </select>
                            <select name="periods[${index}][entries][${day}][staff_profile_id]">
                                <option value="">Select Teacher</option>
                                ${teacherOptions.map(t => `<option value="${t.id}">${escapeHtml(t.name)}</option>`).join('')}
                            </select>
                            <input type="text" name="periods[${index}][entries][${day}][room_number]" placeholder="Room number (optional)">
                        </div>
                    `;
            });

            const html = `
                    <div class="tt-period" data-period-index="${index}">
                        <div class="tt-period-header">
                            <div class="tt-period-title">Period ${number}</div>
                            <button type="button" class="tt-remove" onclick="removePeriod(this)">Remove</button>
                        </div>

                        <input type="hidden" name="periods[${index}][period_number]" value="${number}">

                        <div class="tt-period-grid">
                            <div class="tt-period-field">
                                <label>Period Name</label>
                                <input name="periods[${index}][name]" value="Period ${number}" required>
                            </div>
                            <div class="tt-period-field">
                                <label>Period Type</label>
                                <select name="periods[${index}][period_type]" onchange="togglePeriodType(this)">
                                    <option value="teaching">Teaching</option>
                                    <option value="break">Break</option>
                                </select>
                            </div>
                            <div class="tt-period-field">
                                <label>Start Time</label>
                                <input type="time" name="periods[${index}][start_time]" value="${defaultStart(number)}" required>
                            </div>
                            <div class="tt-period-field">
                                <label>End Time</label>
                                <input type="time" name="periods[${index}][end_time]" value="${defaultEnd(number)}" required>
                            </div>
                        </div>

                        <div class="tt-day-grid">${dayCards}</div>
                    </div>
                `;

            periodContainer.insertAdjacentHTML('beforeend', html);
            const period = periodContainer.lastElementChild;
            populatePeriodSubjects(period);
            refreshPeriodNumbers();
        }

        function defaultStart(number) {
            const start = 8 * 60 + 30 + (number - 1) * 50;
            return minutesToTime(start);
        }

        function defaultEnd(number) {
            return minutesToTime(8 * 60 + 30 + (number - 1) * 50 + 40);
        }

        function minutesToTime(minutes) {
            const h = Math.floor(minutes / 60);
            const m = minutes % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        }

        function populatePeriodSubjects(period) {
            const classId = document.getElementById('ttClass').value;

            period.querySelectorAll('[data-class-subject]').forEach(select => {
                const current = select.value;
                select.innerHTML = '<option value="">Select Subject</option>';

                subjectOptions
                    .filter(s => String(s.class_id) === String(classId))
                    .forEach(s => {
                        select.insertAdjacentHTML(
                            'beforeend',
                            `<option value="${s.id}">${escapeHtml(s.name)}</option>`
                        );
                    });

                if (current) select.value = current;
            });
        }

        function refreshAllSubjects() {
            document.querySelectorAll('.tt-period').forEach(populatePeriodSubjects);
        }

        document.getElementById('ttClass').addEventListener('change', refreshAllSubjects);

        function togglePeriodType(select) {
            const period = select.closest('.tt-period');
            const isBreak = select.value === 'break';

            period.querySelector('.tt-day-grid').style.display = isBreak ? 'none' : 'grid';

            period.querySelectorAll('.tt-day-grid select, .tt-day-grid input').forEach(input => {
                input.disabled = isBreak;
                if (isBreak) input.value = '';
            });
        }

        function removePeriod(button) {
            button.closest('.tt-period').remove();
            refreshPeriodNumbers();
        }

        function refreshPeriodNumbers() {
            document.querySelectorAll('.tt-period').forEach((period, index) => {
                period.querySelector('.tt-period-title').textContent = 'Period ' + (index + 1);
                period.querySelector('input[type="hidden"]').value = index + 1;
            });
        }

        document.getElementById('ttSchool').addEventListener('change', function () {
            const school = this.value;
            document.querySelectorAll('#ttCampus option').forEach(option => {
                if (!option.value) return;
                option.hidden = option.dataset.school !== school;
            });
            document.getElementById('ttCampus').value = '';
            document.getElementById('ttClass').value = '';
            filterSections();
        });

        function filterSections() {
            const classId = document.getElementById('ttClass').value;
            const campusId = document.getElementById('ttCampus').value;

            document.querySelectorAll('#ttSection option').forEach(option => {
                if (!option.value) return;
                option.hidden = option.dataset.class !== classId ||
                    option.dataset.campus !== campusId;
            });

            document.getElementById('ttSection').value = '';
        }

        document.getElementById('ttClass').addEventListener('change', filterSections);
        document.getElementById('ttCampus').addEventListener('change', filterSections);

        document.getElementById('timetableForm').addEventListener('submit', function (e) {
            if (document.querySelectorAll('.tt-period').length === 0) {
                e.preventDefault();
                alert('Please add at least one period.');
            }
        });

        // Preserve validation input when redirected back from the server.
        @if($errors->any() && old('periods'))
            openTimetableModal();
            periodContainer.innerHTML = '';
            periodCount = 0;

            const oldPeriods = @json(old('periods'));
            Object.values(oldPeriods).forEach((oldPeriod, index) => {
                addPeriod();
                const period = periodContainer.lastElementChild;

                period.querySelector('[name$="[period_number]"]').value = oldPeriod.period_number;
                period.querySelector('[name$="[name]"]').value = oldPeriod.name;
                period.querySelector('[name$="[period_type]"]').value = oldPeriod.period_type;
                period.querySelector('[name$="[start_time]"]').value = oldPeriod.start_time;
                period.querySelector('[name$="[end_time]"]').value = oldPeriod.end_time;

                if (oldPeriod.period_type === 'break') {
                    togglePeriodType(period.querySelector('[name$="[period_type]"]'));
                } else {
                    Object.entries(oldPeriod.entries || {}).forEach(([day, entry]) => {
                        const subject = period.querySelector(`[name="periods[${index}][entries][${day}][class_subject_id]"]`);
                        const teacher = period.querySelector(`[name="periods[${index}][entries][${day}][staff_profile_id]"]`);
                        const room = period.querySelector(`[name="periods[${index}][entries][${day}][room_number]"]`);
                        if (subject) subject.value = entry.class_subject_id || '';
                        if (teacher) teacher.value = entry.staff_profile_id || '';
                        if (room) room.value = entry.room_number || '';
                    });
                }
            });
        @endif
    </script>
@endsection