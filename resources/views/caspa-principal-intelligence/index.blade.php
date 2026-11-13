@extends('layouts.schoolnexa')
@section('content')
@include('caspa-principal-intelligence._style')
<div class="ci-page">
 <div class="ci-head"><div><h1 class="ci-title">CASPA Principal Intelligence</h1><p class="ci-sub">SUBCASPA Principal Intelligence</p></div><div class="ci-actions"><a class="ci-btn" href="{{route('dashboard')}}">Back to Dashboard</a></div></div>
 <div class="ci-layout"><section class="ci-main">
  <div class="ci-hero"><h2>Ask for an analysis using the authorized school context.</h2><p>Powered by your existing Gemini integration. Responses are generated using the signed-in account's authorized context.</p></div>
  <div class="ci-stat"><span class="ci-chip">Signed in: {{auth()->user()->name}}</span><span class="ci-chip">Role: {{ucfirst($profile['role'])}}</span><span class="ci-chip">School scope: {{ $profile['school_id'] }}</span></div>
  <div class="ci-prompts"><button type="button" class="ci-prompt">Summarize current school indicators</button><button type="button" class="ci-prompt">Suggest attendance improvement actions</button><button type="button" class="ci-prompt">Prepare a school performance review</button></div>
  <form class="ci-compose" id="caspaForm">@csrf<textarea id="caspaMessage" name="message" maxlength="8000" required placeholder="Type your question here..."></textarea><div class="ci-compose-foot"><span class="ci-muted">Do not enter passwords, API keys or highly sensitive personal information.</span><button class="ci-btn primary" type="submit">Send to CASPA</button></div><div class="ci-loading" id="caspaLoading">CASPA is preparing your response...</div><div class="ci-error" id="caspaError"></div></form>
  <div class="ci-result" id="caspaResult"><h3>CASPA Response</h3><div class="ci-message ci-assistant" id="caspaAnswer"></div></div>
 </section><aside class="ci-side"><h3>My Recent Conversations</h3><div class="ci-history">@forelse($history as $item)<a href="{{route('principal-intelligence.conversation',$item->id)}}">{{ $item->title }}<div class="ci-muted">{{\Illuminate\Support\Carbon::parse($item->updated_at)->diffForHumans()}}</div></a>@empty<p class="ci-muted">Your conversations will appear here.</p>@endforelse</div><p class="ci-muted" style="margin-top:14px">Conversation history is private to your signed-in account and this CASPA module.</p></aside></div>
</div>
<script>
document.querySelectorAll('.ci-prompt').forEach(function(button){button.addEventListener('click',function(){document.getElementById('caspaMessage').value=button.textContent;document.getElementById('caspaMessage').focus();});});
document.getElementById('caspaForm').addEventListener('submit',async function(e){
 e.preventDefault();const loading=document.getElementById('caspaLoading'),error=document.getElementById('caspaError'),result=document.getElementById('caspaResult'),answer=document.getElementById('caspaAnswer'),message=document.getElementById('caspaMessage');
 loading.style.display='block';error.textContent='';result.style.display='none';
 try{const response=await fetch("{{route('principal-intelligence.ask')}}",{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('input[name="_token"]').value},body:JSON.stringify({message:message.value})});const data=await response.json();if(!response.ok)throw new Error(data.message||'Request failed. Please try again.');answer.textContent=data.answer||'No response returned.';result.style.display='block';message.value='';}
 catch(err){error.textContent=err.message;}finally{loading.style.display='none';}
});
</script>
@endsection