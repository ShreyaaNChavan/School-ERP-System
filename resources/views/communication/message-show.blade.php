@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<style>
.cc-head{display:flex;justify-content:space-between;gap:12px;margin-bottom:18px}.cc-head h1{font-size:23px;color:#123B5D;margin:0 0 5px}.cc-head p{font-size:13px;color:#64748B;margin:0}.cc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font-size:13px;font-weight:650;cursor:pointer}.cc-primary{background:#123B5D;color:#fff;border-color:#123B5D}.cc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:20px}.cc-message{padding:14px;border:1px solid #E2E8F0;border-radius:12px;margin-bottom:10px;background:#F8FAFC}.cc-message small{color:#64748B}.cc-message p{white-space:pre-wrap;margin:8px 0 0;color:#25313C}.cc-field textarea{width:100%;min-height:110px;padding:11px;border:1px solid #CBD5E1;border-radius:9px;font:13px Inter,sans-serif}
</style>
<div class="cc-head"><div><h1>{{ $conversation->subject }}</h1><p>Conversation #{{ $conversation->id }}</p></div><a class="cc-btn" href="{{ route('communication.messages') }}">← Back to Messages</a></div>
<div class="cc-panel">@forelse($messages as $message)<div class="cc-message"><small>Sender ID: {{ $message->sender_id }} · {{ \Carbon\Carbon::parse($message->created_at)->format('d M Y, h:i A') }}</small><p>{{ $message->body }}</p></div>@empty<p>No messages yet.</p>@endforelse
<form method="POST" action="{{ route('communication.message.reply',$conversation->id) }}" style="margin-top:18px">@csrf<div class="cc-field"><textarea name="message" required placeholder="Write a reply..."></textarea></div><button class="cc-btn cc-primary" style="margin-top:10px">Send Reply</button></form></div>
</div></div>
@endsection
