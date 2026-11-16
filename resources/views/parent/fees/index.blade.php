@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $today = \Carbon\Carbon::today();
    $total = (float) $totals->total;
    $paid = (float) $totals->paid;
    $balance = (float) $totals->balance;
    $collected = $total > 0 ? min(100, max(0, $paid / $total * 100)) : 0;

    $statusClass = fn ($s) => match (true) {
        str_contains($s, 'paid') && !str_contains($s, 'unpaid') && !str_contains($s, 'partial') => 'success',
        str_contains($s, 'overdue') => 'danger',
        str_contains($s, 'partial'), str_contains($s, 'pending'), str_contains($s, 'unpaid'), str_contains($s, 'issued') => 'warning',
        default => 'neutral',
    };
    $bucket = fn ($s) => match (true) {
        str_contains($s, 'overdue') => 'overdue',
        str_contains($s, 'paid') && !str_contains($s, 'unpaid') && !str_contains($s, 'partial') => 'paid',
        default => 'open',
    };
    $rows = collect($invoices->items())->map(function ($inv) use ($today, $statusClass, $bucket) {
        $status = strtolower((string) ($inv->status ?? ''));
        $due = $inv->due_date ? \Carbon\Carbon::parse($inv->due_date) : null;
        $bal = (float) $inv->balance_amount;
        $note = null; $tone = 'muted';
        if ($bal > 0 && $due) {
            $d = $today->diffInDays($due->copy()->startOfDay(), false);
            if ($d < 0) { $note = 'Overdue by ' . abs($d) . (abs($d) === 1 ? ' day' : ' days'); $tone = 'red'; }
            elseif ($d === 0) { $note = 'Due today'; $tone = 'amber'; }
            else { $note = 'Due in ' . $d . ($d === 1 ? ' day' : ' days'); $tone = $d <= 5 ? 'amber' : 'muted'; }
        }
        $tot = (float) $inv->total_amount;
        return ['inv' => $inv, 'status' => $status, 'cls' => $statusClass($status), 'bucket' => $bucket($status),
                'due' => $due, 'note' => $note, 'tone' => $tone, 'pct' => $tot > 0 ? min(100, (float) $inv->paid_amount / $tot * 100) : 0,
                'name' => trim($inv->first_name . ' ' . $inv->last_name)];
    });
    $students = $rows->pluck('name')->unique()->filter()->values();
    $ini = fn ($n) => mb_strtoupper(collect(preg_split('/\s+/', trim($n)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
    $overdueCount = $rows->where('bucket', 'overdue')->count();
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

</style>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">Accounts &amp; payments</div>
            <h1 class="pf-title">Fee Details</h1>
            <div class="pf-sub">Invoices and payment status for your linked children.</div>
        </div>
    </div>

    <div class="pf-stats">
        <div class="pf-stat tone-blue">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 8h6M9 12h6"/></svg></span>
            <div><small>Total invoiced</small><strong data-count="{{ $total }}" data-dec="2" data-pre="₹">₹{{ number_format($total, 2) }}</strong><em>Across all invoices</em></div>
        </div>
        <div class="pf-stat tone-green">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg></span>
            <div><small>Total paid</small><strong data-count="{{ $paid }}" data-dec="2" data-pre="₹">₹{{ number_format($paid, 2) }}</strong><em>{{ number_format($collected, 0) }}% of invoiced</em></div>
        </div>
        <div class="pf-stat {{ $balance > 0 ? 'tone-amber' : 'tone-green' }}">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18M7 15h3"/></svg></span>
            <div><small>Outstanding balance</small><strong class="{{ $balance > 0 ? 'red' : '' }}" data-count="{{ $balance }}" data-dec="2" data-pre="₹">₹{{ number_format($balance, 2) }}</strong><em>{{ $balance > 0 ? ($overdueCount ? $overdueCount . ' overdue on this page' : 'Payable to the school') : 'Nothing due' }}</em></div>
        </div>
    </div>

    <div class="pf-panel">
        <div class="pf-progress">
            <div class="pf-progress-top"><span>Payment progress</span><b>{{ number_format($collected, 0) }}% paid</b></div>
            <div class="pf-track"><i data-w="{{ $collected }}"></i></div>
        </div>

        <div class="pf-tools" style="margin-top:0">
            <div class="pf-tabs" role="tablist">
                <button type="button" class="pf-tab on" data-f="all">All <b>{{ $rows->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="open">Open <b>{{ $rows->where('bucket', 'open')->count() }}</b></button>
                <button type="button" class="pf-tab" data-f="overdue">Overdue <b>{{ $overdueCount }}</b></button>
                <button type="button" class="pf-tab" data-f="paid">Paid <b>{{ $rows->where('bucket', 'paid')->count() }}</b></button>
            </div>
            <div class="pf-filters">
                @if($students->count() > 1)
                    <select class="pf-select" id="pfStudent" aria-label="Filter by student">
                        <option value="">All students</option>
                        @foreach($students as $s)<option value="{{ \Illuminate\Support\Str::lower($s) }}">{{ $s }}</option>@endforeach
                    </select>
                @endif
                <label class="pf-search">
                    <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <input type="search" id="pfSearch" placeholder="Search invoice or student" aria-label="Search invoices" autocomplete="off">
                </label>
            </div>
        </div>

        <div class="pf-table-wrap">
            <table class="pf-table" id="pfTable">
                <thead>
                    <tr>
                        <th data-sort="text">Invoice</th>
                        <th data-sort="text">Student</th>
                        <th data-sort="num">Invoice date</th>
                        <th data-sort="num">Due date</th>
                        <th data-sort="num" class="r">Total</th>
                        <th data-sort="num" class="r">Paid</th>
                        <th data-sort="num" class="r">Balance</th>
                        <th data-sort="text">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $r)
                        @php $invoice = $r['inv']; @endphp
                        <tr class="pf-row" data-bucket="{{ $r['bucket'] }}" data-student="{{ \Illuminate\Support\Str::lower($r['name']) }}">
                            <td data-v="{{ \Illuminate\Support\Str::lower($invoice->invoice_number) }}"><strong>{{ $invoice->invoice_number }}</strong></td>
                            <td data-v="{{ \Illuminate\Support\Str::lower($r['name']) }}"><div class="pf-student"><span class="pf-av">{{ $ini($r['name']) }}</span>{{ $r['name'] }}</div></td>
                            <td data-v="{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->timestamp : '' }}">{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}</td>
                            <td data-v="{{ $r['due']?->timestamp }}">
                                {{ $r['due'] ? $r['due']->format('d M Y') : '—' }}
                                @if($r['note'])<span class="pf-note {{ $r['tone'] }}">{{ $r['note'] }}</span>@endif
                            </td>
                            <td class="r" data-v="{{ (float) $invoice->total_amount }}">₹{{ number_format((float) $invoice->total_amount, 2) }}</td>
                            <td class="r" data-v="{{ (float) $invoice->paid_amount }}">
                                ₹{{ number_format((float) $invoice->paid_amount, 2) }}
                                <span class="pf-mini"><i style="width:{{ $r['pct'] }}%"></i></span>
                            </td>
                            <td class="r" data-v="{{ (float) $invoice->balance_amount }}"><span class="pf-balance {{ (float) $invoice->balance_amount > 0 ? 'due' : '' }}">₹{{ number_format((float) $invoice->balance_amount, 2) }}</span></td>
                            <td data-v="{{ $r['status'] }}"><span class="pf-status {{ $r['cls'] }}"><i></i>{{ $invoice->status }}</span></td>
                            <td><a class="pf-btn sm" href="{{ route('parent.fees.show', $invoice->id) }}">Details</a></td>
                        </tr>
                    @endforeach

                    <tr id="pfNone" @if($rows->isNotEmpty()) hidden @endif>
                        <td colspan="9" class="pf-empty">
                            <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 8h6M9 12h4"/></svg></span>
                            <strong id="pfNoneTitle">{{ $rows->isEmpty() ? 'No fee invoices found' : 'No matching invoices' }}</strong>
                            <p id="pfNoneText">{{ $rows->isEmpty() ? 'No fee invoices found for your linked children.' : 'Try a different search or filter.' }}</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="pf-foot">
            <span id="pfShown">Showing {{ $rows->count() }} of {{ $rows->count() }} on this page</span>
            <div>{{ $invoices->links() }}</div>
        </div>
    </div>
</div>

<script>
(function () {
    var root = document.getElementById('pfPage'); if (!root) return;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    root.querySelectorAll('[data-count]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count')) || 0, dec = parseInt(el.getAttribute('data-dec'), 10) || 0, pre = el.getAttribute('data-pre') || '', start = null;
        if (target === 0 || reduce) return;
        function fmt(v) { return pre + v.toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }); }
        function step(ts) { if (!start) start = ts; var p = Math.min((ts - start) / 800, 1); el.textContent = fmt(target * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); else el.textContent = fmt(target); }
        requestAnimationFrame(step);
    });
    var bar = root.querySelector('.pf-track i');
    if (bar) requestAnimationFrame(function () { setTimeout(function () { bar.style.width = bar.getAttribute('data-w') + '%'; }, 60); });

    var table = document.getElementById('pfTable'), body = table.tBodies[0];
    var rows = Array.prototype.slice.call(body.querySelectorAll('tr.pf-row')), none = document.getElementById('pfNone'), shown = document.getElementById('pfShown');
    var tabs = Array.prototype.slice.call(root.querySelectorAll('.pf-tab')), search = document.getElementById('pfSearch'), stu = document.getElementById('pfStudent'), filter = 'all';
    function apply() {
        var q = search.value.trim().toLowerCase(), s = stu ? stu.value : '', n = 0;
        rows.forEach(function (r) {
            var ok = (filter === 'all' || r.getAttribute('data-bucket') === filter) && (!s || r.getAttribute('data-student') === s) && (!q || r.textContent.toLowerCase().indexOf(q) > -1);
            r.hidden = !ok; if (ok) n++;
        });
        none.hidden = n !== 0;
        if (rows.length) { document.getElementById('pfNoneTitle').textContent = 'No matching invoices'; document.getElementById('pfNoneText').textContent = 'Try a different search or filter.'; }
        shown.textContent = 'Showing ' + n + ' of ' + rows.length + ' on this page';
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { tabs.forEach(function (o) { o.classList.toggle('on', o === t); }); filter = t.getAttribute('data-f'); apply(); }); });
    search.addEventListener('input', apply); if (stu) stu.addEventListener('change', apply);

    table.querySelectorAll('th[data-sort]').forEach(function (th, idx) {
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
            rows.forEach(function (r) { body.insertBefore(r, none); });
        });
    });
    apply();
})();
</script>
@endsection