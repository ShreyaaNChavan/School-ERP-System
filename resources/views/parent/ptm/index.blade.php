@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $today = \Carbon\Carbon::today();
    $statusCls = fn ($s) => match (true) {
        in_array($s, ['approved', 'confirmed', 'scheduled', 'accepted']) => 'success',
        in_array($s, ['completed', 'done']) => 'info',
        in_array($s, ['rejected', 'cancelled', 'canceled', 'declined', 'missed']) => 'danger',
        in_array($s, ['pending', 'requested']) => 'warning',
        default => 'neutral',
    };
    $bucketOf = fn ($s) => match (true) {
        in_array($s, ['approved', 'confirmed', 'scheduled', 'accepted']) => 'upcoming',
        in_array($s, ['completed', 'done']) => 'completed',
        in_array($s, ['rejected', 'cancelled', 'canceled', 'declined', 'missed']) => 'closed',
        default => 'pending',
    };
    $rows = collect($meetings->items())->map(function ($m) use ($statusCls, $bucketOf, $today) {
        $s = strtolower((string) $m->status);
        $date = \Carbon\Carbon::parse($m->meeting_date);
        return ['m' => $m, 's' => $s, 'cls' => $statusCls($s), 'bucket' => $bucketOf($s), 'date' => $date,
                'teacher' => trim(($m->teacher_first_name ?? '') . ' ' . ($m->teacher_last_name ?? '')),
                'child' => trim($m->student_first_name . ' ' . $m->student_last_name)];
    });
    $next = $rows->filter(fn ($r) => $r['bucket'] === 'upcoming' && $r['date']->gte($today))->sortBy(fn ($r) => $r['date']->format('Y-m-d') . $r['m']->start_time)->first();
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

.pf-next{display:flex;align-items:center;gap:16px;padding:16px 20px;margin-bottom:17px;border-radius:13px;background:#F0F9FB;border:1px solid #BFE3EC}
.pf-next .pf-ico{background:#fff;color:var(--teal)}
.pf-next small{display:block;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:var(--teal)}
.pf-next strong{display:block;font-size:14px;color:var(--navy);margin-top:3px}
.pf-next span{font-size:12px;color:var(--muted)}
.pf-status.info{background:#EAF2FC;color:#2B6CB0}
.pf-purpose{max-width:260px;white-space:normal;overflow-wrap:anywhere}
.pf-more{border:0;background:none;color:var(--teal);font:700 11px Inter,sans-serif;cursor:pointer;padding:0;margin-left:4px}
.pf-submit-row{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.pf-count{margin-left:auto;font-size:11px;color:#94A3B8}
.pf-btn[disabled]{opacity:.7;cursor:progress}
.pf-spin{width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:pfSpin .7s linear infinite;display:none}
.pf-btn.loading .pf-spin{display:inline-block}
@keyframes pfSpin{to{transform:rotate(360deg)}}

</style>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">Connect with educators</div>
            <h1 class="pf-title">Parent–Teacher Meetings</h1>
            <div class="pf-sub">Request a meeting with a teacher assigned to one of your children.</div>
        </div>
    </div>

    @if(session('success'))
        <div class="pf-alert ok" role="status"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg>{{ session('success') }}</div>
    @endif

    @if($next)
        <div class="pf-next">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
            <div>
                <small>Next meeting</small>
                <strong>{{ $next['teacher'] ?: 'Teacher' }} &middot; {{ $next['child'] }}</strong>
                <span>{{ $next['date']->format('l, d M Y') }}, {{ substr($next['m']->start_time, 0, 5) }}&ndash;{{ substr($next['m']->end_time, 0, 5) }} &middot; {{ $next['m']->meeting_type }}</span>
            </div>
        </div>
    @endif

    {{-- REQUEST FORM --}}
    <div class="pf-panel">
        <h2>Request a meeting</h2>
        <p class="pf-panel-sub">Choose a child, a teacher and a convenient time. The teacher will confirm the request.</p>

        <form method="POST" action="{{ route('parent.ptm.store') }}" class="pf-form" id="pfForm">@csrf
            <div class="pf-field"><label>Child</label>
                <select name="student_id" required>
                    <option value="">Select child</option>
                    @foreach($context['children'] as $child)
                        <option value="{{ $child->id }}" @selected(old('student_id')==$child->id)>{{ $child->first_name }} {{ $child->last_name }} — {{ $child->admission_number }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pf-field"><label>Teacher</label>
                <select name="teacher_id" required>
                    <option value="">Select assigned teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" @selected(old('teacher_id')==$teacher->id)>{{ $teacher->first_name }} {{ $teacher->last_name }}{{ $teacher->designation ? ' · '.$teacher->designation : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pf-field"><label>Meeting date</label><input type="date" name="meeting_date" min="{{ date('Y-m-d') }}" value="{{ old('meeting_date') }}" required></div>
            <div class="pf-field"><label>Start time</label><input type="time" name="start_time" value="{{ old('start_time') }}" required><span class="pf-hint" id="pfEnds">Choose a start time and duration to see the end time.</span></div>
            <div class="pf-field"><label>Duration</label>
                <select name="duration_minutes">
                    @foreach([10,15,20,30,45,60] as $mins)
                        <option value="{{ $mins }}" @selected(old('duration_minutes',15)==$mins)>{{ $mins }} minutes</option>
                    @endforeach
                </select>
            </div>
            <div class="pf-field"><label>Meeting type</label>
                <select name="meeting_type" required>
                    @foreach(['Academic','Behavioural','Attendance','General','Career Guidance'] as $type)
                        <option @selected(old('meeting_type','General')===$type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pf-field full"><label>Purpose / topics to discuss</label>
                <textarea name="purpose" rows="3" required maxlength="1000" id="pfPurpose">{{ old('purpose') }}</textarea>
            </div>
            <div class="pf-field full pf-submit-row">
                <button class="pf-btn primary" id="pfSubmit"><span class="pf-spin"></span><span>Submit meeting request</span></button>
                <span class="pf-count" id="pfChars">0 / 1000</span>
            </div>
        </form>

        @if($errors->any())
            <div class="pf-alert err" role="alert" style="margin:14px 0 0">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
        @if($teachers->isEmpty())
            <p class="pf-sub" style="margin-top:12px">No active teacher availability is configured for your children's current sections yet.</p>
        @endif
    </div>

    {{-- MEETING HISTORY --}}
    <div class="pf-panel">
        <h2>Meeting history</h2>
        <p class="pf-panel-sub">All meeting requests and their current status.</p>

        <div class="pf-tools" style="margin-top:0">
            <div class="pf-tabs" role="tablist">
                <button type="button" class="pf-tab on" data-f="all">All <b>{{ $rows->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="pending">Pending <b>{{ $rows->where('bucket','pending')->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="upcoming">Upcoming <b>{{ $rows->where('bucket','upcoming')->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="completed">Completed <b>{{ $rows->where('bucket','completed')->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="closed">Closed <b>{{ $rows->where('bucket','closed')->count() }}</b></button>
            </div>
            <label class="pf-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" id="pfSearch" placeholder="Search child, teacher or purpose" aria-label="Search meetings" autocomplete="off">
            </label>
        </div>

        <div class="pf-table-wrap">
            <table class="pf-table" id="pfTable">
                <thead>
                    <tr>
                        <th data-sort="text">Child</th>
                        <th data-sort="text">Teacher</th>
                        <th data-sort="num">Date</th>
                        <th data-sort="text">Time</th>
                        <th data-sort="text">Type</th>
                        <th data-sort="text">Status</th>
                        <th>Purpose</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        @php $meeting = $r['m']; @endphp
                        <tr class="pf-row" data-bucket="{{ $r['bucket'] }}">
                            <td data-v="{{ \Illuminate\Support\Str::lower($r['child']) }}"><div class="pf-student"><span class="pf-av">{{ $ini($r['child']) }}</span>{{ $r['child'] }}</div></td>
                            <td data-v="{{ \Illuminate\Support\Str::lower($r['teacher']) }}">{{ $r['teacher'] ?: '—' }}</td>
                            <td data-v="{{ $r['date']->timestamp }}">
                                {{ $r['date']->format('d M Y') }}
                                @if($r['bucket'] === 'upcoming' && $r['date']->gte($today))
                                    <span class="pf-note amber">{{ $r['date']->isToday() ? 'Today' : 'In ' . $today->diffInDays($r['date']) . ' days' }}</span>
                                @endif
                            </td>
                            <td data-v="{{ substr($meeting->start_time, 0, 5) }}">{{ substr($meeting->start_time, 0, 5) }}–{{ substr($meeting->end_time, 0, 5) }}</td>
                            <td data-v="{{ \Illuminate\Support\Str::lower($meeting->meeting_type) }}">{{ $meeting->meeting_type }}</td>
                            <td data-v="{{ $r['s'] }}"><span class="pf-status {{ $r['cls'] }}"><i></i>{{ $meeting->status }}</span></td>
                            <td class="pf-purpose" data-full="{{ $meeting->purpose }}">{{ \Illuminate\Support\Str::limit($meeting->purpose, 70) }}@if(mb_strlen($meeting->purpose) > 70)<button type="button" class="pf-more">More</button>@endif</td>
                        </tr>
                    @endforeach

                    <tr id="pfNone" @if($rows->isNotEmpty()) hidden @endif>
                        <td colspan="7" class="pf-empty">
                            <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
                            <strong id="pfNoneTitle">{{ $rows->isEmpty() ? 'No meeting requests yet' : 'No matching meetings' }}</strong>
                            <p id="pfNoneText">{{ $rows->isEmpty() ? 'Your meeting requests will appear here once submitted.' : 'Try a different search or status.' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pf-foot">
            <span id="pfShown">Showing {{ $rows->count() }} of {{ $rows->count() }} on this page</span>
            <div>{{ $meetings->links() }}</div>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('pfPage'); if (!root) return;

    /* end-time preview + character counter + submit state */
    var form = document.getElementById('pfForm'), start = form.elements['start_time'], dur = form.elements['duration_minutes'], ends = document.getElementById('pfEnds');
    function fmt12(h, m) { return ((h + 11) % 12 + 1) + ':' + String(m).padStart(2, '0') + ' ' + (h < 12 ? 'AM' : 'PM'); }
    function preview() {
        if (!start.value) { ends.textContent = 'Choose a start time and duration to see the end time.'; return; }
        var p = start.value.split(':'), t = (+p[0]) * 60 + (+p[1]) + (+dur.value), eh = Math.floor(t / 60) % 24, em = t % 60;
        ends.textContent = 'Meeting ends at ' + fmt12(eh, em);
    }
    start.addEventListener('input', preview); dur.addEventListener('change', preview); preview();
    var purpose = document.getElementById('pfPurpose'), chars = document.getElementById('pfChars');
    function count() { chars.textContent = purpose.value.length + ' / 1000'; }
    purpose.addEventListener('input', count); count();
    var date = form.elements['meeting_date'];
    form.addEventListener('submit', function () {
        var b = document.getElementById('pfSubmit');
        if (form.checkValidity()) { b.classList.add('loading'); b.disabled = true; b.lastElementChild.textContent = 'Submitting...'; }
    });

    /* history: filter / search / sort / expand purpose */
    var table = document.getElementById('pfTable'), body = table.tBodies[0], rows = Array.prototype.slice.call(body.querySelectorAll('tr.pf-row'));
    var none = document.getElementById('pfNone'), shown = document.getElementById('pfShown'), tabs = Array.prototype.slice.call(root.querySelectorAll('.pf-tab')), search = document.getElementById('pfSearch'), filter = 'all';
    function apply() {
        var q = search.value.trim().toLowerCase(), n = 0;
        rows.forEach(function (r) {
            var hay = (r.textContent + ' ' + (r.querySelector('.pf-purpose').getAttribute('data-full') || '')).toLowerCase();
            var ok = (filter === 'all' || r.getAttribute('data-bucket') === filter) && (!q || hay.indexOf(q) > -1);
            r.hidden = !ok; if (ok) n++;
        });
        none.hidden = n !== 0;
        if (rows.length) { document.getElementById('pfNoneTitle').textContent = 'No matching meetings'; document.getElementById('pfNoneText').textContent = 'Try a different search or status.'; }
        shown.textContent = 'Showing ' + n + ' of ' + rows.length + ' on this page';
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { tabs.forEach(function (o) { o.classList.toggle('on', o === t); }); filter = t.getAttribute('data-f'); apply(); }); });
    search.addEventListener('input', apply);
    body.addEventListener('click', function (e) {
        var b = e.target.closest('.pf-more'); if (!b) return;
        var td = b.parentNode, open = b.getAttribute('data-open') === '1', full = td.getAttribute('data-full');
        td.firstChild.nodeValue = open ? full.slice(0, 70) + '...' : full;
        b.textContent = open ? 'More' : 'Less'; b.setAttribute('data-open', open ? '0' : '1');
    });

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