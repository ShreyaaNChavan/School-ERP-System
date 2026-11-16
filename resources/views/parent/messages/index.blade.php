@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $now = \Carbon\Carbon::now();
    $rows = collect($items->items())->map(function ($i) use ($now) {
        $at = $i->last_message_at ? \Carbon\Carbon::parse($i->last_message_at) : null;
        return ['i' => $i, 'at' => $at, 'subject' => $i->subject ?: 'Conversation',
                'recent' => $at && $at->gte($now->copy()->subDays(7))];
    });
    $recent = $rows->where('recent', true)->count();
    $latest = $rows->filter(fn ($r) => $r['at'])->sortByDesc(fn ($r) => $r['at']->timestamp)->first();
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

.pf-stats.three{grid-template-columns:repeat(3,minmax(0,1fr))}
.pf-convo{display:flex;align-items:center;gap:14px;padding:14px 16px;border:1px solid var(--line);border-radius:13px;background:#fff;margin-bottom:10px;text-decoration:none;transition:transform .15s,box-shadow .15s,border-color .15s}
.pf-convo:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(18,59,93,.10);border-color:#9CC5D5}
.pf-convo .pf-av{width:44px;height:44px;font-size:15px}
.pf-convo-main{flex:1;min-width:0}
.pf-convo-top{display:flex;justify-content:space-between;align-items:baseline;gap:12px}
.pf-convo-top strong{font-size:14px;color:var(--navy);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pf-convo-top span{font-size:11px;color:#94A3B8;white-space:nowrap}
.pf-convo p{margin:4px 0 0;font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pf-convo .go{flex:0 0 auto;color:#94A3B8;transition:.15s}
.pf-convo:hover .go{color:var(--teal);transform:translateX(3px)}
.pf-select{min-width:150px}
@@media(max-width:900px){.pf-stats.three{grid-template-columns:1fr}}

</style>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">Direct communication</div>
            <h1 class="pf-title">CASPA Chat</h1>
            <div class="pf-sub">Private conversations with teachers assigned to your children.</div>
        </div>
        <div class="pf-head-actions">
            <a class="pf-btn primary" href="{{ route('parent.messages.create') }}"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>New message</a>
        </div>
    </div>

    <div class="pf-stats three">
        <div class="pf-stat tone-blue">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12Z"/></svg></span>
            <div><small>Conversations</small><strong data-count="{{ $items->total() }}">{{ $items->total() }}</strong><em>With your children's teachers</em></div>
        </div>
        <div class="pf-stat tone-green">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <div><small>Active this week</small><strong data-count="{{ $recent }}">{{ $recent }}</strong><em>On this page</em></div>
        </div>
        <div class="pf-stat tone-amber">
            <span class="pf-ico"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg></span>
            <div><small>Last activity</small><strong style="font-size:17px">{{ $latest ? $latest['at']->diffForHumans() : '—' }}</strong><em>{{ $latest ? \Illuminate\Support\Str::limit($latest['subject'], 28) : 'No messages yet' }}</em></div>
        </div>
    </div>

    <div class="pf-panel">
        <div class="pf-tools" style="margin-top:0">
            <label class="pf-search" style="flex:1;max-width:380px">
                <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" id="pfSearch" placeholder="Search subject or message" aria-label="Search conversations" autocomplete="off">
            </label>
            <select class="pf-select" id="pfSort" aria-label="Sort conversations">
                <option value="recent">Most recent first</option>
                <option value="oldest">Oldest first</option>
                <option value="az">Subject A to Z</option>
            </select>
        </div>

        <div id="pfList">
            @foreach($rows as $r)
                @php $item = $r['i']; @endphp
                <a class="pf-convo" href="{{ route('parent.messages.show', $item->id) }}"
                   data-ts="{{ $r['at']?->timestamp ?? 0 }}" data-subject="{{ \Illuminate\Support\Str::lower($r['subject']) }}">
                    <span class="pf-av">{{ mb_strtoupper(mb_substr($r['subject'], 0, 1)) }}</span>
                    <span class="pf-convo-main">
                        <span class="pf-convo-top">
                            <strong>{{ $r['subject'] }}</strong>
                            <span title="{{ $r['at'] ? $r['at']->format('d M Y, h:i A') : '' }}">{{ $r['at'] ? $r['at']->diffForHumans() : '—' }}</span>
                        </span>
                        <p>{{ \Illuminate\Support\Str::limit($item->last_message ?? 'No messages yet', 90) }}</p>
                    </span>
                    <svg class="go" viewBox="0 0 24 24" width="18" height="18"><path d="m9 6 6 6-6 6"/></svg>
                </a>
            @endforeach

            <div class="pf-empty" id="pfNone" style="text-align:center;padding:42px 15px" @if($rows->isNotEmpty()) hidden @endif>
                <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12Z"/></svg></span>
                <strong id="pfNoneTitle">{{ $rows->isEmpty() ? 'No conversations yet' : 'No matching conversations' }}</strong>
                <p id="pfNoneText">{{ $rows->isEmpty() ? "Start a conversation with one of your child's assigned teachers." : 'Try a different search.' }}</p>
                @if($rows->isEmpty())<a class="pf-btn primary" style="margin-top:14px" href="{{ route('parent.messages.create') }}">Start a conversation</a>@endif
            </div>
        </div>

        <div class="pf-foot">
            <span id="pfShown">Showing {{ $rows->count() }} of {{ $rows->count() }} on this page</span>
            <div>{{ $items->links() }}</div>
        </div>
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
    var list = document.getElementById('pfList'), none = document.getElementById('pfNone'), shown = document.getElementById('pfShown');
    var cards = Array.prototype.slice.call(list.querySelectorAll('.pf-convo')), search = document.getElementById('pfSearch'), sort = document.getElementById('pfSort');
    function apply() {
        var q = search.value.trim().toLowerCase(), n = 0;
        cards.forEach(function (c) { var ok = !q || c.textContent.toLowerCase().indexOf(q) > -1; c.hidden = !ok; if (ok) n++; });
        none.hidden = n !== 0;
        if (cards.length) { document.getElementById('pfNoneTitle').textContent = 'No matching conversations'; document.getElementById('pfNoneText').textContent = 'Try a different search.'; }
        shown.textContent = 'Showing ' + n + ' of ' + cards.length + ' on this page';
    }
    function order() {
        var v = sort.value;
        cards.sort(function (a, b) {
            if (v === 'az') return a.getAttribute('data-subject').localeCompare(b.getAttribute('data-subject'));
            var d = (+a.getAttribute('data-ts')) - (+b.getAttribute('data-ts'));
            return v === 'oldest' ? d : -d;
        });
        cards.forEach(function (c) { list.insertBefore(c, none); });
    }
    search.addEventListener('input', apply); sort.addEventListener('change', order); apply();
})();
</script>
@endsection