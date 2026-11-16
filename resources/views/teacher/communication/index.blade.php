
@extends('layouts.schoolnexa')

@section('content')

@php
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
            <h1>Messages</h1>
            <p>Communicate with parents of your assigned students and your school principal.</p>
        </div>

        <a href="{{ route('teacher.messages.compose') }}" class="tc-btn tc-primary">
            <span>＋</span> Compose message
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

    <div class="tc-summary">
        <div>
            <div class="tc-summary-icon">✉</div>
            <div>
                <strong>{{ $items->total() }}</strong>
                <span>Total conversations</span>
            </div>
        </div>
        <div class="tc-summary-note">
            Select a conversation to view messages and reply.
        </div>
    </div>

    <section class="tc-panel">

        <div class="tc-panel-head">
            <div>
                <h2>Your conversations</h2>
                <p>Messages exchanged with your contacts</p>
            </div>
        </div>

        <div class="tc-list">

            @forelse($items as $item)

                @php
                    $person = $item->other_participant_name ?: 'Unknown participant';
                    $role = $item->other_participant_type ?: 'Participant';

                    $senderIsYou = (int) $item->last_sender_id === (int) auth()->id();

                    $lastSender = $senderIsYou
                        ? 'You'
                        : ($item->last_sender_name ?: 'Participant');
                @endphp

                <a class="tc-row"
                   href="{{ route('teacher.messages.show', $item->id) }}">

                    <div class="tc-avatar">
                        {{ $initials($person) }}
                    </div>

                    <div class="tc-content">

                        <div class="tc-topline">
                            <div class="tc-person">
                                <strong>{{ $person }}</strong>
                                <span class="tc-role">{{ $role }}</span>
                            </div>

                            <time>
                                @if($item->last_message_at)
                                    {{ \Carbon\Carbon::parse($item->last_message_at)->format('d M Y, h:i A') }}
                                @endif
                            </time>
                        </div>

                        <div class="tc-subject">
                            {{ $item->subject ?: 'No subject' }}
                        </div>

                        <p class="tc-preview">
                            @if($item->last_message)
                                <strong>{{ $lastSender }}:</strong>
                                {{ \Illuminate\Support\Str::limit($item->last_message, 105) }}
                            @else
                                No messages yet. Open conversation.
                            @endif
                        </p>

                    </div>

                    <div class="tc-arrow">›</div>

                </a>

            @empty

                <div class="tc-empty">
                    <div class="tc-empty-icon">✉</div>
                    <h3>No conversations yet</h3>
                    <p>Start a conversation with a parent or your school principal.</p>
                    <a href="{{ route('teacher.messages.compose') }}" class="tc-btn tc-primary">
                        Compose your first message
                    </a>
                </div>

            @endforelse

        </div>

        @if($items->hasPages())
            <div class="tc-pagination">
                {{ $items->links() }}
            </div>
        @endif

    </section>

</div>

<style>
.tc-page{
    max-width:1150px;
    margin:0 auto;
    padding:28px 24px;
    color:#172b4d;
    font-family:Inter,sans-serif;
}

.tc-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    margin-bottom:25px;
}

.tc-eyebrow{
    color:#176b87;
    font-size:10px;
    font-weight:800;
    letter-spacing:1.3px;
}

.tc-header h1{
    font-size:29px;
    font-weight:800;
    margin:6px 0;
    color:#123b5d;
}

.tc-header p{
    margin:0;
    color:#64748b;
    font-size:13px;
}

.tc-btn{
    display:inline-flex;
    justify-content:center;
    align-items:center;
    gap:8px;
    padding:12px 17px;
    border-radius:10px;
    text-decoration:none;
    font-size:12px;
    font-weight:700;
    border:0;
    cursor:pointer;
}

.tc-primary{
    background:#123b5d;
    color:#fff!important;
}

.tc-primary:hover{
    background:#0d304d;
}

.tc-summary{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    background:#fff;
    border:1px solid #e2eaf1;
    border-radius:13px;
    padding:16px 19px;
    margin-bottom:18px;
}

.tc-summary>div:first-child{
    display:flex;
    align-items:center;
    gap:13px;
}

.tc-summary-icon{
    width:43px;
    height:43px;
    display:grid;
    place-items:center;
    background:#e8f4f8;
    color:#176b87;
    border-radius:12px;
    font-size:21px;
}

.tc-summary strong{
    display:block;
    color:#123b5d;
    font-size:20px;
}

.tc-summary span{
    color:#64748b;
    font-size:11px;
}

.tc-summary-note{
    color:#94a3b8;
    font-size:11px;
}

.tc-panel{
    background:#fff;
    border:1px solid #e2eaf1;
    border-radius:15px;
    overflow:hidden;
}

.tc-panel-head{
    padding:20px;
    border-bottom:1px solid #edf1f5;
}

.tc-panel-head h2{
    font-size:16px;
    margin:0 0 5px;
    color:#123b5d;
}

.tc-panel-head p{
    margin:0;
    color:#94a3b8;
    font-size:12px;
}

.tc-row{
    display:flex;
    align-items:center;
    gap:15px;
    padding:18px 20px;
    text-decoration:none;
    color:inherit;
    border-bottom:1px solid #edf1f5;
    transition:background .15s;
}

.tc-row:last-child{
    border-bottom:0;
}

.tc-row:hover{
    background:#f7fbfd;
}

.tc-avatar{
    flex:0 0 46px;
    width:46px;
    height:46px;
    border-radius:14px;
    display:grid;
    place-items:center;
    background:#e5f2f6;
    color:#176b87;
    font-weight:800;
    font-size:13px;
}

.tc-content{
    flex:1;
    min-width:0;
}

.tc-topline{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
}

.tc-person{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:9px;
}

.tc-person strong{
    font-size:13px;
    color:#123b5d;
}

.tc-role{
    display:inline-flex;
    padding:4px 9px;
    border-radius:20px;
    background:#eff6fa;
    color:#176b87;
    font-size:10px;
    font-weight:700;
}

.tc-topline time{
    color:#94a3b8;
    font-size:10px;
    white-space:nowrap;
}

.tc-subject{
    color:#334155;
    font-size:12px;
    font-weight:700;
    margin-top:7px;
}

.tc-preview{
    color:#64748b;
    font-size:12px;
    margin:5px 0 0;
    overflow:hidden;
    text-overflow:ellipsis;
}

.tc-preview strong{
    color:#176b87;
}

.tc-arrow{
    color:#94a3b8;
    font-size:25px;
    padding:0 4px;
}

.tc-empty{
    text-align:center;
    padding:55px 20px;
}

.tc-empty-icon{
    width:55px;
    height:55px;
    display:grid;
    place-items:center;
    margin:0 auto 14px;
    background:#e8f4f8;
    color:#176b87;
    border-radius:16px;
    font-size:23px;
}

.tc-empty h3{
    color:#123b5d;
    font-size:16px;
    margin:0 0 7px;
}

.tc-empty p{
    color:#94a3b8;
    font-size:12px;
    margin:0 0 17px;
}

.tc-alert{
    padding:13px 16px;
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

.tc-pagination{
    padding:15px 20px;
    border-top:1px solid #edf1f5;
}

.tc-pagination svg{
    width:16px;
}

@media(max-width:650px){
    .tc-page{
        padding:18px 12px;
    }

    .tc-header{
        align-items:flex-start;
        flex-direction:column;
    }

    .tc-summary{
        align-items:flex-start;
        flex-direction:column;
    }

    .tc-row{
        padding:15px 12px;
        gap:10px;
    }

    .tc-avatar{
        width:39px;
        height:39px;
        flex-basis:39px;
    }

    .tc-topline{
        align-items:flex-start;
        flex-direction:column;
        gap:5px;
    }

    .tc-arrow{
        display:none;
    }
}
</style>

@endsection
