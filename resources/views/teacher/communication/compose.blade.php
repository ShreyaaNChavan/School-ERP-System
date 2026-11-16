@extends('layouts.schoolnexa')

@section('content')
<div class="tc-wrap">
    <div class="tc-head"><div><span>TEACHER COMMUNICATION</span><h1>Compose message</h1><p>Choose parents linked to your assigned classes or your school's principal.</p></div><a href="{{ route('teacher.messages.index') }}" class="tc-btn tc-light">Back to messages</a></div>
    <form method="POST" action="{{ route('teacher.messages.send') }}" class="tc-card">
        @csrf
        <label>Subject<input name="subject" value="{{ old('subject') }}" maxlength="255" required></label>
        @error('subject')<small class="tc-error">{{ $message }}</small>@enderror
        <label>Recipients<select name="recipient_ids[]" multiple required size="8">
            @forelse($recipients as $recipient)
                <option value="{{ $recipient->id }}" @selected(in_array((string)$recipient->id, old('recipient_ids', []), true))>{{ $recipient->name }} — {{ $recipient->recipient_type }}{{ $recipient->email ? ' ('.$recipient->email.')' : '' }}</option>
            @empty
                <option disabled>No eligible recipients are currently available.</option>
            @endforelse
        </select><small>Use Ctrl (Windows) or Cmd (Mac) to select multiple recipients.</small></label>
        @error('recipient_ids')<small class="tc-error">{{ $message }}</small>@enderror
        <label>Message<textarea name="message" rows="8" maxlength="10000" required>{{ old('message') }}</textarea></label>
        @error('message')<small class="tc-error">{{ $message }}</small>@enderror
        <div class="tc-actions"><a href="{{ route('teacher.messages.index') }}" class="tc-btn tc-light">Cancel</a><button class="tc-btn tc-primary" type="submit" @disabled($recipients->isEmpty())>Send message</button></div>
    </form>
</div>
<style>
.tc-wrap{max-width:900px;margin:0 auto;padding:24px;color:#172b4d}.tc-head{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:22px}.tc-head span{font-size:11px;font-weight:800;letter-spacing:1.2px;color:#176b87}.tc-head h1{font-size:28px;margin:4px 0}.tc-head p{color:#64748b;margin:0}.tc-card{background:#fff;border:1px solid #e5edf4;border-radius:16px;padding:24px;display:grid;gap:16px}.tc-card label{display:grid;gap:7px;font-size:13px;font-weight:700}.tc-card input,.tc-card select,.tc-card textarea{width:100%;border:1px solid #dbe5ee;border-radius:10px;padding:12px;font:inherit;font-weight:500}.tc-card select{min-height:180px}.tc-card small{font-size:11px;color:#64748b;font-weight:400}.tc-error{color:#dc2626!important}.tc-actions{display:flex;justify-content:flex-end;gap:10px}.tc-btn{border:0;padding:11px 16px;border-radius:10px;text-decoration:none;font-weight:700;cursor:pointer}.tc-primary{background:#123b5d;color:#fff}.tc-light{background:#edf3f7;color:#123b5d}@media(max-width:650px){.tc-head{align-items:flex-start;flex-direction:column}}
</style>
@endsection
