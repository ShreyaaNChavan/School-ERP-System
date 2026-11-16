@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')
@php
    $me = (int) auth()->id();
    $ini = fn ($n) => mb_strtoupper(collect(preg_split('/\s+/', trim((string) $n)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('')) ?: 'U';
    $msgs = collect($messages)->values();
    $others = $msgs->filter(fn ($m) => (int) $m->sender_id !== $me)->pluck('sender_name')->filter()->unique()->values();
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

.pf-thread-head{display:flex;align-items:center;gap:14px;margin-bottom:0}
.pf-thread-head .pf-av{width:46px;height:46px;font-size:15px}
.pf-thread-head h2{font-size:17px;margin:0}
.pf-chat{display:flex;flex-direction:column;gap:12px;max-height:540px;overflow-y:auto;padding:18px 4px 6px;margin-top:16px;border-top:1px solid #EEF2F6;scroll-behavior:smooth}
.pf-day{align-self:center;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;color:#94A3B8;background:#F1F5F9;border-radius:99px;padding:4px 12px;margin:4px 0}
.pf-msg{display:flex;gap:10px;max-width:78%}
.pf-msg.me{align-self:flex-end;flex-direction:row-reverse}
.pf-msg .pf-av{width:32px;height:32px;font-size:11px;align-self:flex-end}
.pf-msg.me .pf-av{background:#DDF0F4;color:var(--teal)}
.pf-bubble{padding:11px 14px;border-radius:14px 14px 14px 4px;background:#F1F5F9;color:#25313C}
.pf-msg.me .pf-bubble{background:#E3F2F6;border-radius:14px 14px 4px 14px}
.pf-bubble b{display:block;font-size:11px;color:var(--navy);margin-bottom:3px}
.pf-bubble div{white-space:pre-wrap;font-size:14px;line-height:1.65;color:#40566D;overflow-wrap:anywhere}
.pf-bubble time{display:block;font-size:10px;color:#94A3B8;margin-top:6px}
.pf-msg.me .pf-bubble time{text-align:right}
.pf-reply{margin-top:16px;padding-top:16px;border-top:1px solid #EEF2F6}
.pf-reply textarea{width:100%;border:1px solid var(--line);border-radius:12px;padding:12px 14px;font:inherit;font-size:13px;resize:vertical;min-height:96px}
.pf-reply textarea:focus{outline:none;border-color:var(--teal);box-shadow:0 0 0 3px rgba(23,107,135,.14)}
.pf-reply-foot{display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-top:10px}
.pf-count{margin-left:auto;font-size:11px;color:#94A3B8}
.pf-btn[disabled]{opacity:.7;cursor:progress}
.pf-spin{width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:pfSpin .7s linear infinite;display:none}
.pf-btn.loading .pf-spin{display:inline-block}
@keyframes pfSpin{to{transform:rotate(360deg)}}
.pf-jump{position:sticky;bottom:6px;align-self:center;display:none}
@@media(max-width:650px){.pf-msg{max-width:92%}}

</style>

<div class="pf-page" id="pfPage">

    <div class="pf-head">
        <div>
            <div class="pf-kicker">CASPA Chat</div>
            <h1 class="pf-title">{{ $conversation->subject ?: 'Conversation' }}</h1>
            <div class="pf-sub">{{ $msgs->count() }} {{ $msgs->count() === 1 ? 'message' : 'messages' }}@if($others->isNotEmpty()) &middot; with {{ $others->implode(', ') }}@endif</div>
        </div>
        <div class="pf-head-actions">
            <a class="pf-btn" href="{{ route('parent.messages.index') }}"><svg viewBox="0 0 24 24"><path d="M15 6 9 12l6 6"/></svg>Back to conversations</a>
        </div>
    </div>

    <div class="pf-panel">

        <div class="pf-chat" id="pfChat">
            @php $lastDay = null; @endphp
            @forelse($msgs as $message)
                @php
                    $at = \Carbon\Carbon::parse($message->created_at);
                    $mine = (int) $message->sender_id === $me;
                    $day = $at->isToday() ? 'Today' : ($at->isYesterday() ? 'Yesterday' : $at->format('d M Y'));
                @endphp
                @if($day !== $lastDay)
                    <div class="pf-day">{{ $day }}</div>
                    @php $lastDay = $day; @endphp
                @endif
                <div class="pf-msg {{ $mine ? 'me' : '' }}">
                    <span class="pf-av" title="{{ $message->sender_name }}">{{ $ini($message->sender_name) }}</span>
                    <div class="pf-bubble">
                        <b>{{ $mine ? 'You' : $message->sender_name }}</b>
                        <div>{{ $message->body }}</div>
                        <time title="{{ $at->format('d M Y, h:i A') }}">{{ $at->format('h:i A') }}</time>
                    </div>
                </div>
            @empty
                <div class="pf-empty" style="text-align:center;padding:36px 10px">
                    <span class="pf-empty-ico"><svg viewBox="0 0 24 24"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12Z"/></svg></span>
                    <strong>No messages yet</strong>
                    <p>No messages in this conversation.</p>
                </div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('parent.messages.reply', $conversation->id) }}" class="pf-reply" id="pfReply">@csrf
            <label for="pfText" style="display:block;font-size:12px;font-weight:700;color:#53677D;margin-bottom:8px">Reply</label>
            <textarea id="pfText" name="message" rows="4" maxlength="10000" required placeholder="Write your reply..."></textarea>
            <div class="pf-reply-foot">
                <button class="pf-btn primary" id="pfSend"><span class="pf-spin"></span><svg viewBox="0 0 24 24" class="pf-plane"><path d="m4 12 16-8-6 16-3-7Z"/></svg><span>Send reply</span></button>
                <span class="pf-hint">Ctrl + Enter to send</span>
                <span class="pf-count" id="pfChars">0 / 10,000</span>
            </div>
        </form>

        @if($errors->any())
            <div class="pf-alert err" role="alert" style="margin:14px 0 0">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
        @endif
    </div>
</div>

<script>
(function () {
    var chat = document.getElementById('pfChat'), form = document.getElementById('pfReply'), text = document.getElementById('pfText'), chars = document.getElementById('pfChars'), send = document.getElementById('pfSend');
    if (chat) chat.scrollTop = chat.scrollHeight;
    function count() { chars.textContent = text.value.length.toLocaleString() + ' / 10,000'; }
    text.addEventListener('input', count); count();
    text.addEventListener('keydown', function (e) { if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); if (form.reportValidity()) form.requestSubmit(); } });
    form.addEventListener('submit', function () {
        if (form.checkValidity()) { send.classList.add('loading'); send.disabled = true; send.lastElementChild.textContent = 'Sending...'; var p = send.querySelector('.pf-plane'); if (p) p.style.display = 'none'; }
    });
})();
</script>
@endsection