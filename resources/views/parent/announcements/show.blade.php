@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $prio = strtolower((string) $item->priority);
    $cls = match ($prio) { 'urgent', 'high', 'critical' => 'danger', 'medium', 'normal' => 'warning', default => 'neutral' };
    $when = $item->published_at ? \Carbon\Carbon::parse($item->published_at) : null;
    $words = str_word_count(strip_tags((string) $item->content));
    $mins = max(1, (int) ceil($words / 200));
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

.pf-article{max-width:860px}
.pf-article-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;padding-bottom:16px;margin-bottom:20px;border-bottom:1px solid #EEF2F6}
.pf-meta-line{display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--muted)}
.pf-meta-line span{display:inline-flex;align-items:center;gap:6px}
.pf-meta-line svg{width:14px;height:14px}
.pf-body{line-height:1.85;color:#40566D;white-space:pre-line;font-size:14px}
.pf-prog{position:fixed;left:0;top:0;height:3px;width:0;background:var(--teal);z-index:80;transition:width .1s}
@@media print{.pf-head-actions,.pf-btn,.pf-prog{display:none!important}.pf-page{padding:0}.pf-article{max-width:none}}

</style>

<div class="pf-prog" id="pfProg"></div>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">School notice</div>
            <h1 class="pf-title">{{ $item->title }}</h1>
            <div class="pf-sub">Published {{ $when ? $when->format('d M Y, h:i A') : '—' }}</div>
        </div>
        <div class="pf-head-actions">
            <button type="button" class="pf-btn" id="pfCopy"><svg viewBox="0 0 24 24"><path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3A4 4 0 0 0 11 18.7l1-1"/></svg><span>Copy link</span></button>
            <button type="button" class="pf-btn" onclick="window.print()"><svg viewBox="0 0 24 24"><path d="M7 9V3h10v6M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2"/><rect x="7" y="14" width="10" height="7" rx="1"/></svg>Print</button>
            <a class="pf-btn primary" href="{{ route('parent.announcements.index') }}"><svg viewBox="0 0 24 24"><path d="M15 6 9 12l6 6"/></svg>Back to notices</a>
        </div>
    </div>

    <div class="pf-panel pf-article">
        <div class="pf-article-head">
            <span class="pf-status {{ $cls }}"><i></i>{{ $item->priority }} priority</span>
            <div class="pf-meta-line">
                <span><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>{{ $when ? $when->format('d M Y') : '—' }}</span>
                <span><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>{{ $mins }} min read</span>
            </div>
        </div>

        <div class="pf-body">{{ $item->content }}</div>
    </div>
</div>

<script>
(function () {
    var prog = document.getElementById('pfProg');
    function onScroll() { var h = document.documentElement; var max = h.scrollHeight - h.clientHeight; prog.style.width = (max > 0 ? Math.min(100, h.scrollTop / max * 100) : 0) + '%'; }
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    var b = document.getElementById('pfCopy');
    b.addEventListener('click', function () {
        var label = b.querySelector('span'), done = function () { b.classList.add('done'); label.textContent = 'Link copied'; setTimeout(function () { b.classList.remove('done'); label.textContent = 'Copy link'; }, 1600); };
        if (navigator.clipboard) navigator.clipboard.writeText(location.href).then(done, done); else done();
    });
})();
</script>
@endsection