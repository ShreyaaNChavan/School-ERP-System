@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $total = (float) $item->total_amount;
    $paid = (float) $item->paid_amount;
    $balance = (float) $item->balance_amount;
    $pct = $total > 0 ? min(100, max(0, $paid / $total * 100)) : 0;
    $status = strtolower((string) data_get($item, 'status', ''));
    $statusCls = match (true) {
        str_contains($status, 'overdue') => 'danger',
        str_contains($status, 'paid') && !str_contains($status, 'unpaid') && !str_contains($status, 'partial') => 'success',
        $status !== '' => 'warning',
        default => 'neutral',
    };
    $dueDate = data_get($item, 'due_date') ? \Carbon\Carbon::parse($item->due_date) : null;
    $invDate = data_get($item, 'invoice_date') ? \Carbon\Carbon::parse($item->invoice_date) : null;
    $itemsTotal = collect($items)->sum(fn ($r) => (float) $r->amount);
    $name = trim($item->first_name . ' ' . $item->last_name);
    $ini = mb_strtoupper(collect(preg_split('/\s+/', $name))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
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

.pf-ring{--p:0;position:relative;width:132px;height:132px;border-radius:50%;margin:0 auto;display:grid;place-items:center;background:conic-gradient(#16A34A calc(var(--p) * 1%),#E2E8F0 0)}
.pf-ring::before{content:'';position:absolute;inset:12px;background:#fff;border-radius:50%}
.pf-ring div{position:relative;text-align:center}
.pf-ring strong{display:block;font-size:24px;color:var(--navy);line-height:1.1}
.pf-ring span{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.05em}
.pf-summary{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr);gap:17px;margin-bottom:17px}
.pf-summary .pf-panel{margin-bottom:0}
.pf-summary .pf-stats{grid-template-columns:repeat(3,minmax(0,1fr));margin:0}
.pf-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-top:18px;padding-top:16px;border-top:1px solid #EEF2F6}
.pf-meta small{display:block;font-size:11px;color:var(--muted)}
.pf-meta strong{display:block;font-size:13px;color:#25313C;margin-top:5px}
.pf-who{display:flex;align-items:center;gap:12px}
.pf-who .pf-av{width:40px;height:40px;font-size:13px}
.pf-table tfoot td{font-weight:800;color:var(--navy);background:#F8FAFC;border-bottom:0}
.pf-method{display:inline-flex;padding:4px 10px;border-radius:20px;background:#EFF6FF;color:#1D4ED8;font-size:10px;font-weight:700;text-transform:capitalize}
.pf-timeline{list-style:none;margin:0;padding:0}
.pf-timeline li{position:relative;display:flex;justify-content:space-between;align-items:center;gap:14px;padding:13px 0 13px 28px;border-bottom:1px solid #EEF2F6}
.pf-timeline li:last-child{border-bottom:0}
.pf-timeline li::before{content:'';position:absolute;left:5px;top:50%;width:11px;height:11px;margin-top:-5px;border-radius:50%;background:#16A34A;box-shadow:0 0 0 4px #DCFCE7}
.pf-timeline strong{display:block;font-size:13px;color:#25313C}
.pf-timeline small{display:block;font-size:11px;color:var(--muted);margin-top:3px}
.pf-timeline .amt{font-size:14px;font-weight:800;color:#166534;white-space:nowrap}
.pf-timeline .pf-method{margin-left:8px}
@@media(max-width:900px){.pf-summary{grid-template-columns:1fr}.pf-summary .pf-stats{grid-template-columns:1fr}}
@@media print{.pf-head-actions,.pf-btn{display:none!important}.pf-page{padding:0}.pf-stat:hover{transform:none;box-shadow:none}}
</style>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">Invoice details</div>
            <h1 class="pf-title">{{ $item->invoice_number }}</h1>
            <div class="pf-sub">{{ $item->first_name }} {{ $item->last_name }} · Admission {{ $item->admission_number }}</div>
        </div>
        <div class="pf-head-actions">
            <button type="button" class="pf-btn" data-copy="{{ $item->invoice_number }}"><svg viewBox="0 0 24 24"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V6a2 2 0 0 1 2-2h9"/></svg><span>Copy number</span></button>
            <button type="button" class="pf-btn" onclick="window.print()"><svg viewBox="0 0 24 24"><path d="M7 9V3h10v6M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><rect x="7" y="14" width="10" height="7" rx="1"/></svg>Print</button>
            <a class="pf-btn primary" href="{{ route('parent.fees.index') }}"><svg viewBox="0 0 24 24"><path d="M15 6 9 12l6 6"/></svg>Back to fees</a>
        </div>
    </div>

    <div class="pf-summary">
        <div class="pf-panel">
            <div class="pf-stats">
                <div class="pf-stat tone-blue" style="flex-direction:column;align-items:flex-start;gap:10px">
                    <span class="pf-ico"><svg viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2Z"/><path d="M9 8h6M9 12h6"/></svg></span>
                    <div><small>Invoice total</small><strong data-count="{{ $total }}" data-dec="2" data-pre="₹">₹{{ number_format($total, 2) }}</strong></div>
                </div>
                <div class="pf-stat tone-green" style="flex-direction:column;align-items:flex-start;gap:10px">
                    <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/></svg></span>
                    <div><small>Paid</small><strong data-count="{{ $paid }}" data-dec="2" data-pre="₹">₹{{ number_format($paid, 2) }}</strong></div>
                </div>
                <div class="pf-stat {{ $balance > 0 ? 'tone-amber' : 'tone-green' }}" style="flex-direction:column;align-items:flex-start;gap:10px">
                    <span class="pf-ico"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18M7 15h3"/></svg></span>
                    <div><small>Balance</small><strong class="{{ $balance > 0 ? 'red' : '' }}" data-count="{{ $balance }}" data-dec="2" data-pre="₹">₹{{ number_format($balance, 2) }}</strong></div>
                </div>
            </div>

            <div class="pf-meta">
                <div class="pf-who"><span class="pf-av">{{ $ini }}</span><div><small>Student</small><strong style="margin-top:2px">{{ $name }}</strong></div></div>
                <div><small>Status</small><strong style="margin-top:6px"><span class="pf-status {{ $statusCls }}"><i></i>{{ $status !== '' ? $status : ($balance > 0 ? 'open' : 'paid') }}</span></strong></div>
                <div><small>Invoice date</small><strong>{{ $invDate ? $invDate->format('d M Y') : '—' }}</strong></div>
                <div><small>Due date</small><strong>{{ $dueDate ? $dueDate->format('d M Y') : '—' }}</strong></div>
            </div>
        </div>

        <div class="pf-panel" style="text-align:center">
            <h3 style="text-align:left">Payment progress</h3>
            <p class="pf-panel-sub" style="text-align:left">Share of this invoice already paid.</p>
            <div class="pf-ring" id="pfRing" data-p="{{ $pct }}"><div><strong>{{ number_format($pct, 0) }}%</strong><span>Paid</span></div></div>
            <p class="pf-sub" style="margin-top:14px">₹{{ number_format($paid, 2) }} of ₹{{ number_format($total, 2) }}</p>
        </div>
    </div>

    <div class="pf-panel">
        <h3>Invoice items</h3>
        <p class="pf-panel-sub">Fee types included in this invoice.</p>
        <div class="pf-table-wrap">
            <table class="pf-table">
                <thead><tr><th>Fee type</th><th>Description</th><th class="r">Qty</th><th class="r">Amount</th></tr></thead>
                <tbody>
                    @forelse($items as $row)
                        <tr class="pf-row">
                            <td><strong>{{ $row->fee_type_name ?? 'Fee' }}</strong></td>
                            <td style="white-space:normal">{{ $row->description }}</td>
                            <td class="r">{{ $row->quantity }}</td>
                            <td class="r">₹{{ number_format((float) $row->amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="pf-empty"><strong>No line items</strong><p>No fee items were recorded for this invoice.</p></td></tr>
                    @endforelse
                </tbody>
                @if(collect($items)->isNotEmpty())
                    <tfoot><tr><td colspan="3" class="r">Items total</td><td class="r">₹{{ number_format($itemsTotal, 2) }}</td></tr></tfoot>
                @endif
            </table>
        </div>
    </div>

    <div class="pf-panel">
        <h3>Payment history</h3>
        <p class="pf-panel-sub">Completed payments recorded against this invoice.</p>
        @if(collect($payments)->isNotEmpty())
            <ul class="pf-timeline">
                @foreach($payments as $payment)
                    <li>
                        <div>
                            <strong>{{ $payment->receipt_number }}<span class="pf-method">{{ $payment->payment_method }}</span></strong>
                            <small>{{ $payment->paid_at ? \Carbon\Carbon::parse($payment->paid_at)->format('d M Y, h:i A') : '—' }}</small>
                        </div>
                        <span class="amt">₹{{ number_format((float) $payment->amount, 2) }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="pf-empty">
                <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M3 10.5h18"/></svg></span>
                <strong>No payments yet</strong>
                <p>No completed payments recorded.</p>
            </div>
        @endif
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
    var ring = document.getElementById('pfRing');
    if (ring) {
        var t = parseFloat(ring.getAttribute('data-p')) || 0, s = null;
        if (reduce) ring.style.setProperty('--p', t);
        else requestAnimationFrame(function step(ts) { if (!s) s = ts; var p = Math.min((ts - s) / 900, 1); ring.style.setProperty('--p', t * (1 - Math.pow(1 - p, 3))); if (p < 1) requestAnimationFrame(step); });
    }
    root.querySelectorAll('[data-copy]').forEach(function (b) {
        b.addEventListener('click', function () {
            var label = b.querySelector('span'), done = function () { b.classList.add('done'); if (label) label.textContent = 'Copied'; setTimeout(function () { b.classList.remove('done'); if (label) label.textContent = 'Copy number'; }, 1600); };
            if (navigator.clipboard) navigator.clipboard.writeText(b.getAttribute('data-copy')).then(done, done); else done();
        });
    });
})();
</script>
@endsection