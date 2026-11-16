@extends('layouts.schoolnexa')

@section('title', 'Children Attendance')

@section('content')

@php
    $studentName = function ($child) {
        return trim($child->first_name . ' ' . ($child->middle_name ?? '') . ' ' . $child->last_name);
    };

    /* [background, colour, label-class] */
    $statusStyle = function ($status) {
        return match (strtolower($status ?? '')) {
            'present' => ['success', 'present'],
            'absent' => ['danger', 'absent'],
            'late' => ['warning', 'late'],
            'half_day' => ['purple', 'other'],
            'leave' => ['info', 'other'],
            default => ['neutral', 'other'],
        };
    };

    $attendanceRate = (float) ($stats['rate'] ?? 0);
    $rateTone = $attendanceRate >= 90 ? '#16A34A' : ($attendanceRate >= 75 ? '#D97706' : '#DC2626');
    $rateLabel = $attendanceRate >= 90 ? 'Excellent' : ($attendanceRate >= 75 ? 'Needs attention' : 'Below the expected level');

    $present = (int) ($stats['present'] ?? 0);
    $absent = (int) ($stats['absent'] ?? 0);
    $late = (int) ($stats['late'] ?? 0);
    $totalRecs = (int) ($stats['total'] ?? 0);
    $otherRecs = max(0, $totalRecs - $present - $absent - $late);
    $pct = fn ($n) => $totalRecs > 0 ? round($n / $totalRecs * 100, 1) : 0;

    $ini = fn ($n) => mb_strtoupper(collect(preg_split('/\s+/', trim($n)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
@endphp

<style>
.pf-page{--navy:#123B5D;--teal:#176B87;--line:#CBD5E1;--muted:#64748B;max-width:1200px;margin:0 auto;padding:8px 0 28px;font-family:Inter,sans-serif;color:#25313C}
.pf-page *{box-sizing:border-box}
.pf-page svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.pf-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.pf-kicker{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:var(--teal)}
.pf-title{font-size:24px;font-weight:800;margin:6px 0;color:var(--navy)}
.pf-sub{color:var(--muted);font-size:13px}
.pf-head-actions{display:flex;gap:9px;flex-wrap:wrap}
.pf-btn{display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 15px;border-radius:10px;border:1px solid var(--line);background:#fff;color:var(--navy)!important;font:600 12px Inter,sans-serif;text-decoration:none;cursor:pointer;transition:.15s}
.pf-btn svg{width:16px;height:16px}
.pf-btn:hover{border-color:var(--teal);background:#F0F9FB;transform:translateY(-1px)}
.pf-btn.primary{background:var(--navy);border-color:var(--navy);color:#fff!important}
.pf-btn.primary:hover{background:#0F3250}
.pf-btn.sm{height:32px;padding:0 12px;font-size:11px}
.pf-btn.done{border-color:#16A34A;background:#F0FDF4;color:#166534!important}

.pf-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin-bottom:18px}
.pf-stat,.pf-panel{background:#fff;border:1px solid var(--line);border-radius:13px}
.pf-stat{display:flex;align-items:center;gap:15px;padding:18px;transition:transform .15s,box-shadow .15s,border-color .15s}
.pf-stat:hover{transform:translateY(-3px);box-shadow:0 12px 26px rgba(18,59,93,.10);border-color:#9CC5D5}
.pf-ico{flex:0 0 auto;width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:var(--bg);color:var(--fg)}
.tone-blue{--bg:#E8F0FE;--fg:#1D4ED8}.tone-green{--bg:#E4F6EA;--fg:#15803D}.tone-amber{--bg:#FEF3E2;--fg:#B45309}.tone-red{--bg:#FDE8E8;--fg:#DC2626}
.pf-stat small{display:block;font-size:10px;font-weight:700;color:var(--muted);letter-spacing:.04em;text-transform:uppercase}
.pf-stat strong{display:block;font-size:24px;color:var(--navy);margin:4px 0 2px;white-space:nowrap}
.pf-stat strong.red{color:#DC2626}
.pf-stat em{display:block;font-style:normal;font-size:11px;color:#94A3B8}

.pf-panel{padding:20px;margin-bottom:17px}
.pf-panel h2,.pf-panel h3{font-size:15px;color:var(--navy);margin:0 0 5px;font-weight:800}
.pf-panel-sub{font-size:12px;color:var(--muted);margin:0 0 16px}

.pf-progress{margin-bottom:18px}
.pf-progress-top{display:flex;justify-content:space-between;font-size:12px;color:var(--muted);margin-bottom:8px}
.pf-progress-top b{color:var(--navy)}
.pf-track{height:9px;border-radius:99px;background:#E2E8F0;overflow:hidden}
.pf-track i{display:block;height:100%;width:0;border-radius:99px;background:linear-gradient(90deg,#16A34A,#4ADE80);transition:width .9s cubic-bezier(.2,.8,.2,1)}

.pf-tools{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin:16px 0 14px}
.pf-tabs{display:inline-flex;gap:4px;padding:4px;background:#F1F5F9;border-radius:11px;flex-wrap:wrap}
.pf-tab{display:inline-flex;align-items:center;gap:7px;border:0;background:transparent;padding:8px 13px;border-radius:8px;font:600 12px Inter,sans-serif;color:var(--muted);cursor:pointer;transition:.15s}
.pf-tab b{font-size:10px;padding:1px 7px;border-radius:99px;background:#E2E8F0;color:#475569}
.pf-tab:hover{color:var(--navy)}
.pf-tab.on{background:#fff;color:var(--teal);box-shadow:0 1px 4px rgba(15,23,42,.12)}
.pf-tab.on b{background:#DDF0F4;color:var(--teal)}
.pf-filters{display:flex;gap:8px;flex-wrap:wrap}
.pf-search{position:relative;min-width:220px}
.pf-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#94A3B8;pointer-events:none}
.pf-search input,.pf-select{height:38px;border:1px solid var(--line);border-radius:10px;font:500 12px Inter,sans-serif;background:#fff;color:#25313C}
.pf-search input{width:100%;padding:0 12px 0 36px}
.pf-select{padding:0 10px}
.pf-search input:focus,.pf-select:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(23,107,135,.14)}

.pf-table-wrap{overflow-x:auto;border:1px solid #E2E8F0;border-radius:11px}
.pf-table{width:100%;border-collapse:collapse;white-space:nowrap}
.pf-table th{background:#F8FAFC;color:var(--muted);font-size:11px;font-weight:700;text-align:left;padding:13px 12px;border-bottom:1px solid var(--line);user-select:none}
.pf-table th[data-sort]{cursor:pointer}
.pf-table th[data-sort]:hover{color:var(--navy)}
.pf-table th[data-sort]::after{content:'';display:inline-block;margin-left:6px;border:4px solid transparent;border-top-color:#CBD5E1;border-bottom:0;vertical-align:middle}
.pf-table th.asc::after{border-top:0;border-bottom:4px solid var(--teal)}
.pf-table th.desc::after{border-top-color:var(--teal)}
.pf-table td{font-size:12px;padding:13px 12px;border-bottom:1px solid #EEF2F6;color:#475569;vertical-align:middle}
.pf-table tbody tr:last-child td{border-bottom:0}
.pf-table tbody tr.pf-row:hover{background:#F8FBFD}
.pf-table td strong{color:#25313C}
.pf-table .r{text-align:right}
.pf-student{display:flex;align-items:center;gap:10px}
.pf-av{flex:0 0 auto;width:30px;height:30px;border-radius:50%;display:grid;place-items:center;background:#EAF1F8;color:var(--navy);font-size:11px;font-weight:700}
.pf-note{display:block;font-size:10px;font-weight:600;margin-top:3px}
.pf-note.red{color:#DC2626}.pf-note.amber{color:#B45309}.pf-note.muted{color:#94A3B8}
.pf-mini{display:block;width:90px;height:4px;border-radius:99px;background:#E2E8F0;margin-top:6px;overflow:hidden}
.pf-mini i{display:block;height:100%;border-radius:99px;background:#16A34A}
.pf-balance{color:var(--navy);font-weight:800}
.pf-balance.due{color:#DC2626}
.pf-status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:capitalize}
.pf-status i{width:6px;height:6px;border-radius:50%;background:currentColor}
.pf-status.success{background:#DCFCE7;color:#166534}
.pf-status.warning{background:#FEF3C7;color:#92400E}
.pf-status.danger{background:#FEE2E2;color:#B91C1C}
.pf-status.neutral{background:#F1F5F9;color:var(--muted)}
.pf-empty{text-align:center!important;padding:42px!important;white-space:normal}
.pf-empty-ico{width:54px;height:54px;margin:0 auto 12px;border-radius:50%;display:grid;place-items:center;background:#E8F0F6;color:var(--teal)}
.pf-empty-ico svg{width:26px;height:26px}
.pf-empty strong{display:block;font-size:13px;color:var(--navy)}
.pf-empty p{font-size:12px;color:var(--muted);margin:6px 0 0}
.pf-foot{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-top:14px;font-size:11px;color:#94A3B8}
.pf-foot nav svg{width:16px;height:16px}

@@media(max-width:900px){.pf-stats{grid-template-columns:1fr}}
@@media(max-width:600px){.pf-search{flex-basis:100%}.pf-panel{padding:13px}}
@@media(prefers-reduced-motion:reduce){.pf-stat,.pf-btn,.pf-tab,.pf-track i{transition:none}}

.pf-chips{display:flex;gap:8px;flex-wrap:wrap}
.pf-chip{display:inline-flex;align-items:center;gap:9px;padding:8px 13px 8px 8px;border:1px solid var(--line);border-radius:12px;background:#fff;font:600 12px Inter,sans-serif;color:var(--navy);cursor:pointer;transition:.15s}
.pf-chip:hover{border-color:var(--teal);transform:translateY(-1px)}
.pf-chip.on{border-color:var(--teal);background:#F0F9FB;box-shadow:0 0 0 3px rgba(23,107,135,.12)}
.pf-chip .pf-av{width:30px;height:30px}
.pf-chip small{display:block;font-size:10px;font-weight:500;color:var(--muted)}
.pf-field{display:flex;flex-direction:column;gap:6px}
.pf-field.full{grid-column:1/-1}
.pf-field label{font-size:12px;font-weight:700;color:#53677D}
.pf-field input,.pf-field select,.pf-field textarea{width:100%;border:1px solid var(--line);border-radius:10px;padding:10px 12px;background:#fff;color:#243B53;font:inherit;font-size:13px}
.pf-field input:focus,.pf-field select:focus,.pf-field textarea:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(23,107,135,.14)}
.pf-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.pf-hint{font-size:11px;color:#94A3B8}
.pf-alert{display:flex;align-items:center;gap:11px;padding:12px 16px;margin-bottom:17px;border-radius:11px;font-size:13px}
.pf-alert svg{flex:0 0 auto;width:19px;height:19px}
.pf-alert.ok{background:#F0FDF4;border:1px solid #BBF7D0;color:#166534}
.pf-alert.err{background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;flex-direction:column;align-items:flex-start;gap:4px}
@@media(max-width:650px){.pf-form{grid-template-columns:1fr}}

.pf-stats.four{grid-template-columns:repeat(4,minmax(0,1fr))}
.pf-rate{display:grid;grid-template-columns:auto 1fr;gap:26px;align-items:center}
.pf-ring{--p:0;--c:#16A34A;position:relative;width:132px;height:132px;border-radius:50%;display:grid;place-items:center;background:conic-gradient(var(--c) calc(var(--p) * 1%),#E2E8F0 0)}
.pf-ring::before{content:'';position:absolute;inset:12px;background:#fff;border-radius:50%}
.pf-ring div{position:relative;text-align:center}
.pf-ring strong{display:block;font-size:24px;color:var(--navy);line-height:1.1}
.pf-ring span{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.pf-split-bar{display:flex;height:12px;border-radius:99px;overflow:hidden;background:#E2E8F0;margin:12px 0 14px}
.pf-split-bar i{display:block;height:100%;transition:width .9s cubic-bezier(.2,.8,.2,1)}
.pf-legend{display:flex;gap:16px;flex-wrap:wrap;font-size:11px;color:var(--muted)}
.pf-legend span{display:inline-flex;align-items:center;gap:6px}
.pf-legend i{width:9px;height:9px;border-radius:50%;display:inline-block}
.pf-status.purple{background:#F0EBFF;color:#6D4BB5}
.pf-status.info{background:#EAF2FC;color:#2B6CB0}
.pf-range{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end}
.pf-range .pf-field{width:auto}
.pf-range .pf-field input{height:38px;padding:0 10px;font-size:12px}
.pf-presets{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.pf-preset{border:1px solid var(--line);background:#fff;border-radius:99px;padding:6px 12px;font:600 11px Inter,sans-serif;color:var(--muted);cursor:pointer;transition:.15s}
.pf-preset:hover{border-color:var(--teal);color:var(--teal)}
@@media(max-width:900px){.pf-stats.four{grid-template-columns:repeat(2,minmax(0,1fr))}.pf-rate{grid-template-columns:1fr;justify-items:center;text-align:center}}

</style>

<div class="pf-page" id="pfPage">

    {{-- PAGE HEADER --}}
    <div class="pf-head">
        <div>
            <div class="pf-kicker">Academics / Attendance</div>
            <h1 class="pf-title">Children Attendance</h1>
            <div class="pf-sub">Monitor attendance, punctuality and daily presence of your children.</div>
        </div>
        <span class="pf-status success" style="background:#EAF5FB;color:#176B87"><i></i>{{ $children->count() }} {{ $children->count() === 1 ? 'Linked Child' : 'Linked Children' }}</span>
    </div>

    {{-- LINKED CHILDREN (click to filter the history) --}}
    <div class="pf-chips" id="pfChildren">
        @forelse($children as $child)
            <button type="button" class="pf-chip" data-child="{{ \Illuminate\Support\Str::lower(trim($child->first_name . ' ' . $child->last_name)) }}">
                <span class="pf-av">{{ strtoupper(substr($child->first_name, 0, 1)) }}</span>
                <span>
                    {{ $studentName($child) }}
                    <small>Admission No: {{ $child->admission_number }} &middot; {{ ucfirst($child->status) }}</small>
                </span>
            </button>
        @empty
            <div class="pf-panel" style="width:100%;margin:0">No children are linked to this parent account.</div>
        @endforelse
    </div>

    {{-- STATISTICS --}}
    <div class="pf-stats four" style="margin-top:18px">
        <div class="pf-stat tone-green">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg></span>
            <div><small>Present</small><strong data-count="{{ $present }}">{{ $present }}</strong><em>Present attendance records</em></div>
        </div>
        <div class="pf-stat tone-red">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/></svg></span>
            <div><small>Absent</small><strong class="{{ $absent > 0 ? 'red' : '' }}" data-count="{{ $absent }}">{{ $absent }}</strong><em>Absence records</em></div>
        </div>
        <div class="pf-stat tone-amber">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><small>Late Arrivals</small><strong data-count="{{ $late }}">{{ $late }}</strong><em>Late attendance records</em></div>
        </div>
        <div class="pf-stat tone-blue">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
            <div><small>Total Records</small><strong data-count="{{ $totalRecs }}">{{ $totalRecs }}</strong><em>Attendance entries</em></div>
        </div>
    </div>

    {{-- ATTENDANCE RATE --}}
    <div class="pf-panel">
        <div class="pf-rate">
            <div class="pf-ring" id="pfRing" data-p="{{ min(100, max(0, $attendanceRate)) }}" style="--c:{{ $rateTone }}">
                <div><strong data-count="{{ $attendanceRate }}" data-dec="1" data-suf="%">{{ number_format($attendanceRate, 1) }}%</strong><span>Attendance</span></div>
            </div>
            <div>
                <h2>Overall Attendance Rate</h2>
                <p class="pf-panel-sub" style="margin-bottom:0">Calculated from present and late records against all attendance entries. <strong style="color:{{ $rateTone }}">{{ $rateLabel }}</strong></p>
                <div class="pf-split-bar" aria-hidden="true">
                    <i style="width:0;background:#16A34A" data-w="{{ $pct($present) }}"></i>
                    <i style="width:0;background:#F59E0B" data-w="{{ $pct($late) }}"></i>
                    <i style="width:0;background:#DC2626" data-w="{{ $pct($absent) }}"></i>
                    <i style="width:0;background:#94A3B8" data-w="{{ $pct($otherRecs) }}"></i>
                </div>
                <div class="pf-legend">
                    <span><i style="background:#16A34A"></i>Present {{ $pct($present) }}%</span>
                    <span><i style="background:#F59E0B"></i>Late {{ $pct($late) }}%</span>
                    <span><i style="background:#DC2626"></i>Absent {{ $pct($absent) }}%</span>
                    @if($otherRecs > 0)<span><i style="background:#94A3B8"></i>Other {{ $pct($otherRecs) }}%</span>@endif
                </div>
            </div>
        </div>
    </div>

    {{-- ATTENDANCE HISTORY --}}
    <div class="pf-panel">

        <h2>Attendance History</h2>
        <p class="pf-panel-sub">Detailed daily attendance records for all linked children.</p>

        {{-- DATE FILTER --}}
        <form method="GET" action="{{ route('parent.attendance.index') }}" class="pf-range" id="pfRangeForm">
            <div class="pf-field"><label>From Date</label><input type="date" name="date_from" value="{{ $dateFrom }}"></div>
            <div class="pf-field"><label>To Date</label><input type="date" name="date_to" value="{{ $dateTo }}"></div>
            <button type="submit" class="pf-btn primary">
                <svg viewBox="0 0 24 24"><path d="M4 5h16l-6 7v6l-4 2v-8Z"/></svg>
                Apply Filter
            </button>
            <a href="{{ route('parent.attendance.index') }}" class="pf-btn">Reset</a>
        </form>

        <div class="pf-presets" style="margin-top:12px">
            <span class="pf-hint" style="align-self:center">Quick range:</span>
            <button type="button" class="pf-preset" data-days="7">Last 7 days</button>
            <button type="button" class="pf-preset" data-days="30">Last 30 days</button>
            <button type="button" class="pf-preset" data-days="month">This month</button>
            <button type="button" class="pf-preset" data-days="90">Last 90 days</button>
        </div>

        @if($records->isEmpty())

            <div class="pf-empty" style="text-align:center;padding:42px 15px">
                <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"/></svg></span>
                <strong>No attendance records found</strong>
                <p>Attendance records for your linked children will appear here.</p>
            </div>

        @else

            <div class="pf-tools">
                <div class="pf-tabs" role="tablist">
                    <button type="button" class="pf-tab on" data-f="all">All <b>{{ $records->count() }}</b></button>
                    <button type="button" class="pf-tab" data-f="present">Present <b>{{ $records->filter(fn ($r) => $statusStyle($r->status)[1] === 'present')->count() }}</b></button>
                    <button type="button" class="pf-tab" data-f="late">Late <b>{{ $records->filter(fn ($r) => $statusStyle($r->status)[1] === 'late')->count() }}</b></button>
                    <button type="button" class="pf-tab" data-f="absent">Absent <b>{{ $records->filter(fn ($r) => $statusStyle($r->status)[1] === 'absent')->count() }}</b></button>
                </div>
                <label class="pf-search">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="pfSearch" placeholder="Search student, date or remarks" aria-label="Search records" autocomplete="off">
                </label>
            </div>

            <div class="pf-table-wrap">
                <table class="pf-table" id="pfTable">
                    <thead>
                        <tr>
                            <th data-sort="text">Student</th>
                            <th data-sort="num">Attendance Date</th>
                            <th data-sort="text">Session</th>
                            <th data-sort="text">Status</th>
                            <th data-sort="text">Check In</th>
                            <th data-sort="text">Check Out</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $record)
                            @php
                                [$cls, $bucket] = $statusStyle($record->status);
                                $d = \Carbon\Carbon::parse($record->attendance_date);
                                $full = trim($record->first_name . ' ' . $record->last_name);
                            @endphp
                            <tr class="pf-row" data-bucket="{{ $bucket }}" data-child="{{ \Illuminate\Support\Str::lower($full) }}">
                                <td data-v="{{ \Illuminate\Support\Str::lower($full) }}">
                                    <div class="pf-student">
                                        <span class="pf-av">{{ strtoupper(substr($record->first_name, 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $full }}</strong>
                                            <span class="pf-note muted">{{ $record->admission_number }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td data-v="{{ $d->timestamp }}">
                                    <strong>{{ $d->format('d M Y') }}</strong>
                                    <span class="pf-note muted">{{ $d->format('l') }}</span>
                                </td>
                                <td data-v="{{ \Illuminate\Support\Str::lower($record->session_name ?? '') }}">{{ $record->session_name ?: '—' }}</td>
                                <td data-v="{{ $record->status }}"><span class="pf-status {{ $cls }}"><i></i>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</span></td>
                                <td data-v="{{ $record->check_in_time }}">{{ $record->check_in_time ?: '—' }}</td>
                                <td data-v="{{ $record->check_out_time }}">{{ $record->check_out_time ?: '—' }}</td>
                                <td style="white-space:normal;min-width:140px">{{ $record->remarks ?: '—' }}</td>
                            </tr>
                        @endforeach

                        <tr id="pfNone" hidden>
                            <td colspan="7" class="pf-empty">
                                <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></span>
                                <strong>No matching records</strong>
                                <p>Try a different search, status or child.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pf-foot"><span id="pfShown">Showing {{ $records->count() }} of {{ $records->count() }}</span></div>

        @endif

    </div>

</div>

<script>
(function () {
    var root = document.getElementById('pfPage'); if (!root) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    root.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0, dec = parseInt(el.getAttribute('data-dec'), 10) || 0, pre = el.getAttribute('data-pre') || '', suf = el.getAttribute('data-suf') || '', start = null;
        if (target === 0 || reduce) return;
        function fmt(v) { return pre + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suf; }
        function step(ts) { if (!start) start = ts; var p = Math.min((ts - start) / 800, 1); el.textContent = fmt(target * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(target); }
        requestAnimationFrame(step);
    });
    var ring = document.getElementById('pfRing');
    if (ring) {
        var t = parseFloat(ring.getAttribute('data-p')) || 0, s = null;
        if (reduce) ring.style.setProperty('--p', t);
        else requestAnimationFrame(function step(ts) { if (!s) s = ts; var p = Math.min((ts - s) / 900, 1); ring.style.setProperty('--p', t * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); });
    }
    root.querySelectorAll('.pf-split-bar i').forEach(function (b) { setTimeout(function () { b.style.width = b.getAttribute('data-w') + '%'; }, 80); });

    /* quick date ranges */
    var form = document.getElementById('pfRangeForm');
    function iso(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }
    root.querySelectorAll('.pf-preset').forEach(function (b) {
        b.addEventListener('click', function () {
            var now = new Date(), from = new Date(), v = b.getAttribute('data-days');
            if (v === 'month') from = new Date(now.getFullYear(), now.getMonth(), 1); else from.setDate(now.getDate() - parseInt(v, 10));
            form.elements['date_from'].value = iso(from); form.elements['date_to'].value = iso(now); form.submit();
        });
    });

    /* filter, search, sort on the loaded records */
    var table = document.getElementById('pfTable'); if (!table) return;
    var body = table.tBodies[0], rows = Array.prototype.slice.call(body.querySelectorAll('tr.pf-row')), none = document.getElementById('pfNone'), shown = document.getElementById('pfShown');
    var tabs = Array.prototype.slice.call(root.querySelectorAll('.pf-tab')), search = document.getElementById('pfSearch');
    var chips = Array.prototype.slice.call(root.querySelectorAll('.pf-chip')), filter = 'all', child = '';
    function apply() {
        var q = search.value.trim().toLowerCase(), n = 0;
        rows.forEach(function (r) {
            var ok = (filter === 'all' || r.getAttribute('data-bucket') === filter) && (!child || r.getAttribute('data-child') === child) && (!q || r.textContent.toLowerCase().indexOf(q) > -1);
            r.hidden = !ok; if (ok) n++;
        });
        none.hidden = n !== 0; shown.textContent = 'Showing ' + n + ' of ' + rows.length;
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { tabs.forEach(function (o) { o.classList.toggle('on', o === t); }); filter = t.getAttribute('data-f'); apply(); }); });
    chips.forEach(function (c) { c.addEventListener('click', function () { var on = c.classList.contains('on'); chips.forEach(function (o) { o.classList.remove('on'); }); child = on ? '' : c.getAttribute('data-child'); if (!on) c.classList.add('on'); apply(); }); });
    search.addEventListener('input', apply);

    function wireSort(table, body, rows, anchor) {
        table.querySelectorAll('th[data-sort]').forEach(function (th) {
            var idx = Array.prototype.indexOf.call(th.parentNode.children, th);
            th.addEventListener('click', function () {
                var dir = th.classList.contains('asc') ? -1 : 1, type = th.getAttribute('data-sort');
                table.querySelectorAll('th').forEach(function (o) { o.classList.remove('asc', 'desc'); });
                th.classList.add(dir === 1 ? 'asc' : 'desc');
                rows.sort(function (a, b) {
                    var x = a.cells[idx].getAttribute('data-v') || '', y = b.cells[idx].getAttribute('data-v') || '';
                    if (type === 'text') return x.localeCompare(y) * dir;
                    var nx = parseFloat(x), ny = parseFloat(y);
                    if (isNaN(nx) && isNaN(ny)) return 0; if (isNaN(nx)) return 1; if (isNaN(ny)) return -1;
                    return (nx - ny) * dir;
                });
                rows.forEach(function (r) { body.insertBefore(r, anchor); });
            });
        });
    }
    wireSort(table, body, rows, none);
    apply();
})();
</script>

@endsection