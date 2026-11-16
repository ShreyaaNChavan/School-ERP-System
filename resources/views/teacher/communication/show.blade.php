
@extends('layouts.schoolnexa')

@section('content')

@php
    $me = (int) auth()->id();

    $initials = function ($name) {
        return mb_strtoupper(
            collect(preg_split('/\s+/', trim((string) $name)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_substr($part, 0, 1))
                ->implode('')
        ) ?: 'U';
    };
@endphp

<div class="tc-page">

    <div class="tc-header">
        <div>
            <span class="tc-eyebrow">TEACHER COMMUNICATION</span>
            <h1>{{ $conversation->subject ?: 'Conversation' }}</h1>

            <div class="tc-contact-list">
                <span class="tc-contact-label">Conversation with:</span>

                @forelse($otherParticipants as $person)
                    <span class="tc-contact">
                        <span class="tc-contact-avatar">
                            {{ $initials($person->display_name) }}
                        </span>

                        <span>
                            <strong>{{ $person->display_name }}</strong>
                            <small>{{ $person->role }}</small>
                        </span>
                    </span>
                @empty
                    <span class="tc-contact-label">No other participants found</span>
                @endforelse
            </div>
        </div>

        <a href="{{ route('teacher.messages.index') }}" class="tc-btn tc-light">
            ← Back to messages
        </a>
    </div>

    @if(session('success'))
        <div class="tc-alert">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="tc-alert tc-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="tc-thread" id="tcThread">

        @php $lastDay = null; @endphp

        @forelse($messages as $message)

            @php
                $at = \Carbon\Carbon::parse($message->created_at);
                $mine = (int) $message->sender_id === $me;

                $day = $at->isToday()
                    ? 'Today'
                    : ($at->isYesterday() ? 'Yesterday' : $at->format('d M Y'));
            @endphp

            @if($day !== $lastDay)
                <div class="tc-day">{{ $day }}</div>
                @php $lastDay = $day; @endphp
            @endif

            <article class="tc-message {{ $mine ? 'mine' : '' }}">

                <div class="tc-message-avatar">
                    {{ $initials($mine ? 'You' : $message->sender_name) }}
                </div>

                <div class="tc-message-content">

                    <div class="tc-message-meta">
                        <strong>
                            {{ $mine ? 'You' : $message->sender_name }}
                        </strong>

                        <time title="{{ $at->format('d M Y, h:i A') }}">
                            {{ $at->format('h:i A') }}
                        </time>
                    </div>

                    <div class="tc-message-bubble">
                        {{ $message->body }}
                    </div>

                </div>
            </article>

        @empty

            <div class="tc-empty">
                No messages in this conversation yet.
            </div>

        @endforelse

    </section>

    <form method="POST"
          action="{{ route('teacher.messages.reply', $conversation->id) }}"
          class="tc-reply"
          id="tcReply">

        @csrf

        <label for="message">Reply to conversation</label>

        <textarea
            id="message"
            name="message"
            rows="4"
            maxlength="10000"
            required
            placeholder="Write your reply..."
        >{{ old('message') }}</textarea>

        <div class="tc-reply-footer">
            <span id="tcCount">0 / 10,000</span>

            <button type="submit" class="tc-btn tc-primary" id="tcSend">
                Send reply →
            </button>
        </div>

    </form>

</div>

<style>
.tc-page{
    max-width:1050px;
    margin:0 auto;
    padding:25px 24px;
    color:#172b4d;
    font-family:Inter,sans-serif;
}

.tc-header{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:18px;
    margin-bottom:23px;
}

.tc-eyebrow{
    font-size:10px;
    font-weight:800;
    letter-spacing:1.2px;
    color:#176b87;
}

.tc-header h1{
    font-size:27px;
    color:#123b5d;
    margin:6px 0 12px;
}

.tc-contact-list{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:10px;
}

.tc-contact-label{
    font-size:12px;
    color:#64748b;
}

.tc-contact{
    display:flex;
    align-items:center;
    gap:9px;
    padding:7px 11px;
    border:1px solid #e2eaf1;
    background:#fff;
    border-radius:12px;
}

.tc-contact-avatar{
    width:32px;
    height:32px;
    border-radius:50%;
    display:grid;
    place-items:center;
    background:#e5f2f6;
    color:#176b87;
    font-size:11px;
    font-weight:800;
}

.tc-contact strong{
    display:block;
    font-size:12px;
    color:#123b5d;
}

.tc-contact small{
    display:block;
    color:#64748b;
    font-size:10px;
    margin-top:2px;
}

.tc-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border:0;
    padding:11px 16px;
    border-radius:10px;
    text-decoration:none;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
}

.tc-primary{
    background:#123b5d;
    color:#fff;
}

.tc-primary:hover{
    background:#0e304d;
}

.tc-light{
    background:#edf4f8;
    color:#123b5d;
    white-space:nowrap;
}

.tc-thread{
    background:#fff;
    border:1px solid #e2eaf1;
    border-radius:16px;
    padding:22px;
    display:flex;
    flex-direction:column;
    gap:16px;
    min-height:210px;
    max-height:560px;
    overflow-y:auto;
}

.tc-day{
    align-self:center;
    padding:5px 12px;
    border-radius:20px;
    background:#f1f5f9;
    color:#64748b;
    font-size:10px;
    font-weight:700;
    text-transform:uppercase;
}

.tc-message{
    display:flex;
    align-items:flex-end;
    gap:10px;
    max-width:80%;
}

.tc-message.mine{
    align-self:flex-end;
    flex-direction:row-reverse;
}

.tc-message-avatar{
    width:34px;
    height:34px;
    flex:0 0 34px;
    border-radius:50%;
    background:#e5f2f6;
    color:#176b87;
    display:grid;
    place-items:center;
    font-size:10px;
    font-weight:800;
}

.tc-message.mine .tc-message-avatar{
    background:#dceafa;
    color:#174c80;
}

.tc-message-content{
    min-width:0;
}

.tc-message-meta{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:15px;
    margin-bottom:6px;
}

.tc-message-meta strong{
    font-size:11px;
    color:#123b5d;
}

.tc-message-meta time{
    font-size:10px;
    color:#94a3b8;
    white-space:nowrap;
}

.tc-message-bubble{
    background:#f1f5f9;
    color:#334155;
    border-radius:13px 13px 13px 4px;
    padding:12px 15px;
    font-size:13px;
    line-height:1.6;
    white-space:pre-wrap;
    overflow-wrap:anywhere;
}

.tc-message.mine .tc-message-bubble{
    background:#e8f1ff;
    border-radius:13px 13px 4px 13px;
}

.tc-reply{
    margin-top:17px;
    background:#fff;
    border:1px solid #e2eaf1;
    border-radius:15px;
    padding:19px;
}

.tc-reply label{
    display:block;
    font-size:13px;
    font-weight:700;
    color:#123b5d;
    margin-bottom:9px;
}

.tc-reply textarea{
    width:100%;
    box-sizing:border-box;
    border:1px solid #dbe5ee;
    border-radius:11px;
    padding:13px;
    font:inherit;
    font-size:13px;
    resize:vertical;
    min-height:105px;
}

.tc-reply textarea:focus{
    outline:none;
    border-color:#176b87;
    box-shadow:0 0 0 3px rgba(23,107,135,.1);
}

.tc-reply-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    margin-top:12px;
}

.tc-reply-footer span{
    font-size:11px;
    color:#94a3b8;
}

.tc-alert{
    padding:12px 15px;
    background:#ecfdf3;
    color:#166534;
    border-radius:10px;
    margin-bottom:15px;
    font-size:13px;
}

.tc-error{
    background:#fef2f2;
    color:#991b1b;
}

.tc-empty{
    text-align:center;
    padding:45px 15px;
    color:#64748b;
    font-size:13px;
}

@media(max-width:650px){
    .tc-page{padding:18px 12px}
    .tc-header{flex-direction:column}
    .tc-message{max-width:95%}
    .tc-thread{padding:14px}
}
</style>

<script>
(function(){
    const textarea = document.getElementById('message');
    const counter = document.getElementById('tcCount');
    const form = document.getElementById('tcReply');
    const button = document.getElementById('tcSend');
    const thread = document.getElementById('tcThread');

    if(thread){
        thread.scrollTop = thread.scrollHeight;
    }

    if(textarea && counter){
        const updateCount = () => {
            counter.textContent = textarea.value.length.toLocaleString() + ' / 10,000';
        };

        textarea.addEventListener('input', updateCount);
        updateCount();

        textarea.addEventListener('keydown', function(e){
            if((e.ctrlKey || e.metaKey) && e.key === 'Enter'){
                e.preventDefault();
                if(form.reportValidity()){
                    form.requestSubmit();
                }
            }
        });
    }

    if(form && button){
        form.addEventListener('submit', function(){
            if(form.checkValidity()){
                button.disabled = true;
                button.textContent = 'Sending...';
            }
        });
    }
})();
</script>

@endsection
