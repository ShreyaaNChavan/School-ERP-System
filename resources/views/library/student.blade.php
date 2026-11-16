@extends('layouts.schoolnexa')

@section('title', 'My Library')

@section('content')
@php
    $loans = $loans ?? collect();
    $totalIssued = $loans->whereIn('status', ['issued', 'overdue'])->count();
    $overdue = $loans->where('status', 'overdue')->count();
    $returnedCount = $loans->where('status', 'returned')->count();
    $outstandingFines = (float) $loans->sum('fine_amount');

    $today = \Carbon\Carbon::today();
    $fmt = fn ($v) => $v ? \Carbon\Carbon::parse($v)->format('d M Y') : null;

    /* Prepare each row once: due-date wording, loan progress and a sortable key */
    $rows = $loans->map(function ($loan) use ($today) {
        $status = strtolower((string) ($loan->status ?? 'unknown'));
        $issued = $loan->issued_at ? \Carbon\Carbon::parse($loan->issued_at) : null;
        $due = $loan->due_at ? \Carbon\Carbon::parse($loan->due_at) : null;
        $returned = $loan->returned_at ? \Carbon\Carbon::parse($loan->returned_at) : null;

        $note = null; $noteTone = 'muted'; $progress = null;
        if (!$returned && $due) {
            $days = $today->diffInDays($due->copy()->startOfDay(), false);
            if ($days < 0) { $note = 'Overdue by ' . abs($days) . ($days === -1 ? ' day' : ' days'); $noteTone = 'red'; }
            elseif ($days === 0) { $note = 'Due today'; $noteTone = 'amber'; }
            else { $note = 'Due in ' . $days . ($days === 1 ? ' day' : ' days'); $noteTone = $days <= 3 ? 'amber' : 'muted'; }
            if ($issued && $due->gt($issued)) {
                $progress = max(0, min(100, $issued->diffInSeconds(now()) / $issued->diffInSeconds($due) * 100));
            }
        } elseif ($returned) {
            $note = 'Returned'; $noteTone = 'green';
        }

        return [
            'loan' => $loan, 'status' => $status, 'issued' => $issued, 'due' => $due, 'returned' => $returned,
            'note' => $note, 'tone' => $noteTone, 'progress' => $progress,
        ];
    });

    $statusClass = fn ($s) => match (true) {
        $s === 'overdue' => 'danger',
        $s === 'issued' => 'warning',
        $s === 'returned' => 'success',
        default => 'neutral',
    };
@endphp

<div class="lb-page" id="lbPage">

    {{-- ===== Header ===== --}}
    <div class="lb-heading">
        <div>
            <span class="lb-eyebrow">STUDENT SERVICES</span>
            <h1>My Library</h1>
            <p>Track your issued books, due dates, returns and library fines.</p>
        </div>
        <span class="lb-count">{{ $loans->count() }} {{ \Illuminate\Support\Str::plural('record', $loans->count()) }}</span>
    </div>

    {{-- ===== Summary cards ===== --}}
    <div class="lb-stats">
        <div class="lb-stat tone-blue">
            <span class="lb-stat-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M4 21.5V5.5M9 7.5h7"/></svg></span>
            <div>
                <small>BOOKS ISSUED</small>
                <strong data-count="{{ $totalIssued }}">{{ $totalIssued }}</strong>
                <em>Currently with you</em>
            </div>
        </div>

        <div class="lb-stat tone-red">
            <span class="lb-stat-icon"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div>
                <small>OVERDUE BOOKS</small>
                <strong class="{{ $overdue > 0 ? 'red' : '' }}" data-count="{{ $overdue }}">{{ $overdue }}</strong>
                <em>{{ $overdue > 0 ? 'Please return these soon' : 'Nothing overdue' }}</em>
            </div>
        </div>

        <div class="lb-stat tone-amber">
            <span class="lb-stat-icon"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18M7 15h3"/></svg></span>
            <div>
                <small>OUTSTANDING FINES</small>
                <strong data-count="{{ $outstandingFines }}" data-dec="2" data-pre="₹">₹{{ number_format($outstandingFines, 2) }}</strong>
                <em>{{ $outstandingFines > 0 ? 'Payable at the library desk' : 'No fines due' }}</em>
            </div>
        </div>

        <div class="lb-stat tone-green">
            <span class="lb-stat-icon"><svg viewBox="0 0 24 24"><path d="M5 4h14v17l-7-4-7 4Z"/><path d="m9 10 2 2 4-4"/></svg></span>
            <div>
                <small>TOTAL LOAN RECORDS</small>
                <strong data-count="{{ $loans->count() }}">{{ $loans->count() }}</strong>
                <em>{{ $returnedCount }} returned</em>
            </div>
        </div>
    </div>

    @if($overdue > 0)
        <div class="lb-alert" role="alert">
            <svg viewBox="0 0 24 24"><path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17.5v.01"/></svg>
            <span>You have <strong>{{ $overdue }}</strong> overdue {{ \Illuminate\Support\Str::plural('book', $overdue) }}. Fines continue to accrue until they are returned.</span>
        </div>
    @endif

    {{-- ===== Issued books ===== --}}
    <div class="lb-panel">
        <div class="lb-panel-heading">
            <div>
                <h2>My Issued Books</h2>
                <p>Books assigned to your student account.</p>
            </div>
        </div>

        <div class="lb-tools">
            <div class="lb-tabs" role="tablist">
                <button type="button" class="lb-tab on" data-f="all">All <b>{{ $loans->count() }}</b></button>
                <button type="button" class="lb-tab" data-f="issued">Issued <b>{{ $loans->where('status', 'issued')->count() }}</b></button>
                <button type="button" class="lb-tab" data-f="overdue">Overdue <b>{{ $overdue }}</b></button>
                <button type="button" class="lb-tab" data-f="returned">Returned <b>{{ $returnedCount }}</b></button>
            </div>

            <label class="lb-search">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" id="lbSearch" placeholder="Search title, author or accession no." aria-label="Search books" autocomplete="off">
            </label>
        </div>

        <div class="lb-table-wrap">
            <table class="lb-table" id="lbTable">
                <thead>
                    <tr>
                        <th data-sort="text">Book</th>
                        <th data-sort="text">Author</th>
                        <th data-sort="text">Accession Number</th>
                        <th data-sort="date">Issued Date</th>
                        <th data-sort="date">Due Date</th>
                        <th data-sort="date">Returned Date</th>
                        <th data-sort="num">Fine</th>
                        <th data-sort="text">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        @php $loan = $r['loan']; @endphp
                        <tr data-status="{{ $r['status'] }}">
                            <td data-v="{{ \Illuminate\Support\Str::lower($loan->book_title ?? '') }}">
                                <div class="lb-book">
                                    <span class="lb-cover">{{ mb_strtoupper(mb_substr($loan->book_title ?? 'B', 0, 1)) }}</span>
                                    <div>
                                        <strong>{{ $loan->book_title ?? 'Unknown Book' }}</strong>
                                        <small>{{ $loan->category ?? 'Library book' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td data-v="{{ \Illuminate\Support\Str::lower($loan->author ?? '') }}">{{ $loan->author ?? '—' }}</td>
                            <td data-v="{{ $loan->accession_number ?? '' }}">{{ $loan->accession_number ?? '—' }}</td>
                            <td data-v="{{ $r['issued']?->timestamp }}">{{ $fmt($loan->issued_at) ?? '—' }}</td>
                            <td data-v="{{ $r['due']?->timestamp }}">
                                {{ $fmt($loan->due_at) ?? '—' }}
                                @if($r['note'] && !$r['returned'])
                                    <span class="lb-note {{ $r['tone'] }}">{{ $r['note'] }}</span>
                                @endif
                                @if($r['progress'] !== null)
                                    <span class="lb-bar {{ $r['tone'] }}"><i style="width:{{ $r['progress'] }}%"></i></span>
                                @endif
                            </td>
                            <td data-v="{{ $r['returned']?->timestamp }}">{{ $fmt($loan->returned_at) ?? 'Not returned' }}</td>
                            <td data-v="{{ (float) ($loan->fine_amount ?? 0) }}" class="{{ (float) ($loan->fine_amount ?? 0) > 0 ? 'lb-fine' : '' }}">₹{{ number_format((float) ($loan->fine_amount ?? 0), 2) }}</td>
                            <td data-v="{{ $r['status'] }}">
                                <span class="lb-status {{ $statusClass($r['status']) }}"><i></i>{{ ucfirst($r['status'] ?: 'unknown') }}</span>
                            </td>
                        </tr>
                    @endforeach

                    <tr class="lb-none" id="lbNone" @if($loans->isNotEmpty()) hidden @endif>
                        <td colspan="8" class="lb-empty">
                            <span class="lb-empty-icon"><svg viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M4 21.5V5.5"/></svg></span>
                            <strong id="lbNoneTitle">{{ $loans->isEmpty() ? 'No library records found' : 'No matching books' }}</strong>
                            <p id="lbNoneText">{{ $loans->isEmpty() ? 'You do not have any books issued from the library yet.' : 'Try a different search or filter.' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($loans->isNotEmpty())
            <div class="lb-foot"><span id="lbShown">Showing {{ $loans->count() }} of {{ $loans->count() }}</span></div>
        @endif
    </div>
</div>

<style>
.lb-page{--navy:#123B5D;--teal:#176B87;--line:#CBD5E1;--muted:#64748B;font-family:Inter,sans-serif;color:#25313C}
.lb-page *{box-sizing:border-box}
.lb-page svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.lb-heading{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:22px}
.lb-eyebrow{font-size:10px;font-weight:700;letter-spacing:1px;color:var(--teal)}
.lb-heading h1{font-size:23px;color:var(--navy);margin:6px 0}
.lb-heading p,.lb-panel-heading p{font-size:13px;color:var(--muted);margin:0}
.lb-count{padding:7px 13px;background:#EFF6FF;color:#1D4ED8;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}

.lb-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:19px}
.lb-stat,.lb-panel{background:#fff;border:1px solid var(--line);border-radius:13px}
.lb-stat{display:flex;align-items:center;gap:15px;padding:18px;transition:transform .15s,box-shadow .15s,border-color .15s}
.lb-stat:hover{transform:translateY(-3px);box-shadow:0 12px 26px rgba(18,59,93,.10);border-color:#9CC5D5}
.lb-stat-icon{flex:0 0 auto;width:46px;height:46px;border-radius:12px;display:grid;place-items:center;background:var(--bg);color:var(--fg)}
.tone-blue{--bg:#E8F0FE;--fg:#1D4ED8}.tone-red{--bg:#FDE8E8;--fg:#DC2626}.tone-amber{--bg:#FEF3E2;--fg:#B45309}.tone-green{--bg:#E4F6EA;--fg:#15803D}
.lb-stat small{font-size:10px;font-weight:700;color:var(--muted);letter-spacing:.04em}
.lb-stat strong{display:block;font-size:25px;color:var(--navy);margin:4px 0 2px;white-space:nowrap}
.lb-stat strong.red{color:#DC2626}
.lb-stat em{display:block;font-style:normal;font-size:11px;color:#94A3B8}

.lb-alert{display:flex;align-items:center;gap:11px;padding:12px 16px;margin-bottom:19px;border-radius:11px;background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:13px}
.lb-alert svg{flex:0 0 auto;width:19px;height:19px}

.lb-panel{padding:20px}
.lb-panel-heading{margin-bottom:16px}
.lb-panel-heading h2{font-size:15px;color:var(--navy);margin:0 0 5px}

.lb-tools{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px}
.lb-tabs{display:inline-flex;gap:4px;padding:4px;background:#F1F5F9;border-radius:11px;flex-wrap:wrap}
.lb-tab{display:inline-flex;align-items:center;gap:7px;border:0;background:transparent;padding:8px 13px;border-radius:8px;font:600 12px Inter,sans-serif;color:var(--muted);cursor:pointer;transition:.15s}
.lb-tab b{font-size:10px;padding:1px 7px;border-radius:99px;background:#E2E8F0;color:#475569}
.lb-tab:hover{color:var(--navy)}
.lb-tab.on{background:#fff;color:var(--teal);box-shadow:0 1px 4px rgba(15,23,42,.12)}
.lb-tab.on b{background:#DDF0F4;color:var(--teal)}
.lb-search{position:relative;flex:0 1 320px;min-width:200px}
.lb-search svg{position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:#94A3B8;pointer-events:none}
.lb-search input{width:100%;height:38px;padding:0 12px 0 36px;border:1px solid var(--line);border-radius:10px;font:500 12px Inter,sans-serif;background:#fff;color:#25313C}
.lb-search input:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(23,107,135,.14)}

.lb-table-wrap{overflow-x:auto;border:1px solid #E2E8F0;border-radius:11px}
.lb-table{width:100%;border-collapse:collapse;white-space:nowrap}
.lb-table th{background:#F8FAFC;color:var(--muted);font-size:11px;text-align:left;padding:13px 12px;border-bottom:1px solid var(--line);user-select:none;position:sticky;top:0}
.lb-table th[data-sort]{cursor:pointer}
.lb-table th[data-sort]:hover{color:var(--navy)}
.lb-table th[data-sort]::after{content:'';display:inline-block;margin-left:6px;border:4px solid transparent;border-top-color:#CBD5E1;border-bottom:0;vertical-align:middle}
.lb-table th.asc::after{border-top:0;border-bottom:4px solid var(--teal)}
.lb-table th.desc::after{border-top-color:var(--teal)}
.lb-table td{font-size:12px;padding:13px 12px;border-bottom:1px solid #EEF2F6;color:#475569;vertical-align:middle}
.lb-table tbody tr:not(.lb-none):hover{background:#F8FBFD}
.lb-table tbody tr:last-child td{border-bottom:0}
.lb-book{display:flex;align-items:center;gap:11px}
.lb-cover{flex:0 0 auto;width:34px;height:44px;border-radius:5px 8px 8px 5px;display:grid;place-items:center;background:linear-gradient(135deg,#E3EEF7,#CFE0EE);color:var(--navy);font-weight:700;font-size:14px;border-left:3px solid var(--navy)}
.lb-book strong{display:block;color:#25313C;font-size:13px}
.lb-book small{display:block;font-size:10px;color:#94A3B8;margin-top:3px}
.lb-note{display:block;font-size:10px;font-weight:600;margin-top:4px}
.lb-note.red{color:#DC2626}.lb-note.amber{color:#B45309}.lb-note.muted{color:#94A3B8}
.lb-bar{display:block;width:100px;height:4px;border-radius:99px;background:#E2E8F0;margin-top:6px;overflow:hidden}
.lb-bar i{display:block;height:100%;border-radius:99px;background:var(--teal)}
.lb-bar.amber i{background:#F59E0B}.lb-bar.red i{background:#DC2626}
.lb-fine{color:#DC2626;font-weight:700}
.lb-status{display:inline-flex;align-items:center;gap:6px;padding:5px 10px;border-radius:20px;font-size:10px;font-weight:700}
.lb-status i{width:6px;height:6px;border-radius:50%;background:currentColor}
.lb-status.warning{background:#FEF3C7;color:#92400E}
.lb-status.danger{background:#FEE2E2;color:#B91C1C}
.lb-status.success{background:#DCFCE7;color:#166534}
.lb-status.neutral{background:#F1F5F9;color:var(--muted)}
.lb-empty{text-align:center!important;padding:42px!important;white-space:normal}
.lb-empty-icon{width:54px;height:54px;margin:0 auto 12px;border-radius:50%;display:grid;place-items:center;background:#E8F0F6;color:var(--teal)}
.lb-empty-icon svg{width:26px;height:26px}
.lb-empty strong{display:block;font-size:13px;color:var(--navy)}
.lb-empty p{font-size:12px;color:var(--muted);margin:6px 0 0}
.lb-foot{margin-top:12px;font-size:11px;color:#94A3B8}

@@media(max-width:1000px){.lb-stats{grid-template-columns:repeat(2,1fr)}}
@@media(max-width:600px){.lb-stats{grid-template-columns:1fr}.lb-heading{align-items:flex-start;flex-direction:column}.lb-panel{padding:13px}.lb-search{flex-basis:100%}}
@@media(prefers-reduced-motion:reduce){.lb-stat,.lb-tab{transition:none}}
</style>

<script>
(function () {
    var root = document.getElementById('lbPage'); if (!root) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* count-up on the summary cards */
    root.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0, dec = parseInt(el.getAttribute('data-dec'), 10) || 0;
        var pre = el.getAttribute('data-pre') || '', start = null;
        if (target === 0 || reduce) return;
        function fmt(v) { return pre + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }); }
        function step(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / 700, 1);
            el.textContent = fmt(target * (1 - Math.pow(1 - p, 3)));
            if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(target);
        }
        requestAnimationFrame(step);
    });

    /* filter tabs + search + column sort */
    var table = document.getElementById('lbTable'); if (!table) return;
    var body = table.tBodies[0];
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr[data-status]'));
    var none = document.getElementById('lbNone'), shown = document.getElementById('lbShown');
    var tabs = Array.prototype.slice.call(root.querySelectorAll('.lb-tab')), search = document.getElementById('lbSearch');
    var filter = 'all';

    function apply() {
        var q = search.value.trim().toLowerCase(), n = 0;
        rows.forEach(function (r) {
            var ok = (filter === 'all' || r.getAttribute('data-status') === filter) && (!q || r.textContent.toLowerCase().indexOf(q) > -1);
            r.hidden = !ok; if (ok) n++;
        });
        if (none) {
            none.hidden = n !== 0;
            if (rows.length) {
                document.getElementById('lbNoneTitle').textContent = 'No matching books';
                document.getElementById('lbNoneText').textContent = 'Try a different search or filter.';
            }
        }
        if (shown) shown.textContent = 'Showing ' + n + ' of ' + rows.length;
    }
    tabs.forEach(function (t) {
        t.addEventListener('click', function () {
            tabs.forEach(function (o) { o.classList.toggle('on', o === t); });
            filter = t.getAttribute('data-f'); apply();
        });
    });
    search.addEventListener('input', apply);

    table.querySelectorAll('th[data-sort]').forEach(function (th, idx) {
        th.addEventListener('click', function () {
            var dir = th.classList.contains('asc') ? -1 : 1, type = th.getAttribute('data-sort');
            table.querySelectorAll('th').forEach(function (o) { o.classList.remove('asc', 'desc'); });
            th.classList.add(dir === 1 ? 'asc' : 'desc');
            rows.sort(function (a, b) {
                var x = a.cells[idx].getAttribute('data-v') || '', y = b.cells[idx].getAttribute('data-v') || '';
                if (type === 'text') return x.localeCompare(y) * dir;
                var nx = parseFloat(x), ny = parseFloat(y);
                if (isNaN(nx) && isNaN(ny)) return 0;
                if (isNaN(nx)) return 1;
                if (isNaN(ny)) return -1;
                return (nx - ny) * dir;
            });
            rows.forEach(function (r) { body.insertBefore(r, none); });
        });
    });
    apply();
})();
</script>
@endsection