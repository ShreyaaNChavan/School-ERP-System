@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<style>
.cc-head{display:flex;justify-content:space-between;margin-bottom:18px}.cc-head h1{font-size:23px;color:#123B5D;margin:0 0 5px}.cc-head p{font-size:13px;color:#64748B;margin:0}.cc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font-size:13px;font-weight:650;cursor:pointer}.cc-primary{background:#123B5D;color:#fff;border-color:#123B5D}.cc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:22px}.cc-form{display:grid;gap:15px}.cc-field{display:grid;gap:7px}.cc-field label{font-size:12px;font-weight:650}.cc-field input,.cc-field textarea,.cc-field select{width:100%;padding:11px;border:1px solid #CBD5E1;border-radius:9px;font:13px Inter,sans-serif}.cc-field textarea{min-height:160px}
</style>
<div class="cc-head"><div><h1>Compose Message</h1><p>Start a secure in-app conversation with one or more users</p></div><a class="cc-btn" href="{{ route('communication.messages') }}">← Back to Messages</a></div>
<div class="cc-panel"><form method="POST" action="{{ route('communication.messages.send') }}" class="cc-form">@csrf
<div class="cc-field"><label>Subject *</label><input name="subject" required maxlength="255" value="{{ old('subject') }}"></div>
<div class="cc-field"><label>Recipient User IDs * (comma separated)</label><input name="recipient_ids_text" id="recipient_ids_text" placeholder="e.g. 4, 8, 12" required><input type="hidden" name="recipient_ids[]" id="recipient_ids"></div>
<div class="cc-field"><label>Message *</label><textarea name="message" required>{{ old('message') }}</textarea></div>
<div><button class="cc-btn cc-primary">Send Message</button></div>
</form></div>
<script>
document.querySelector('form').addEventListener('submit',function(e){
 const raw=document.getElementById('recipient_ids_text').value;
 const ids=raw.split(',').map(x=>x.trim()).filter(x=>/^\d+$/.test(x));
 if(!ids.length){e.preventDefault();alert('Enter at least one valid user ID.');return;}
 const holder=document.getElementById('recipient_ids').parentNode;
 document.getElementById('recipient_ids').remove();
 ids.forEach(id=>{const input=document.createElement('input');input.type='hidden';input.name='recipient_ids[]';input.value=id;holder.appendChild(input);});
});
</script>
</div></div>
@endsection
