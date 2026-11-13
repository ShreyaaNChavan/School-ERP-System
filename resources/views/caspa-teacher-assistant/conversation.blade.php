@extends('layouts.schoolnexa')
@section('content')
@include('caspa-teacher-assistant._style')
<div class="ci-page"><div class="ci-head"><div><h1 class="ci-title">{{ $conversation->title }}</h1><p class="ci-sub">Conversation history for {{auth()->user()->name}}.</p></div><div class="ci-actions"><a class="ci-btn" href="{{route('teacher-assistant.index')}}">Back to CASPA Teacher Assistant Dashboard</a></div></div>
<section class="ci-main">@foreach($messages as $message)<div class="ci-message {{$message->role==='user'?'ci-user':'ci-assistant'}}"><strong>{{ucfirst($message->role)}}</strong><div>{{ $message->content }}</div><div class="ci-muted">{{\Illuminate\Support\Carbon::parse($message->created_at)->format('d M Y, h:i A')}}</div></div>@endforeach</section></div>
@endsection