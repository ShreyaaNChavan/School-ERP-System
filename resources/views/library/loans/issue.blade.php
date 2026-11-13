@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Issue a Book</h1><div class="sn-sub">Assign an available copy to a student or staff member</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('library.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('library.loans.index') }}">Loan Register</a></div>
<div class="sn-card"><form method="POST" action="{{ route('library.loans.store') }}">@csrf<div class="sn-form">
<div class="sn-field full"><label>Available Book Copy *</label><select name="library_copy_id" required><option value="">Select copy</option>@foreach($copies as $copy)<option value="{{ $copy->id }}">{{ $copy->book->title }} — {{ $copy->accession_number }}</option>@endforeach</select></div>
<div class="sn-field"><label>Borrower Type *</label><select name="borrower_type" id="borrowerType" required><option value="student">Student</option><option value="staff">Staff</option></select></div>
<div class="sn-field"><label>Borrower *</label><select name="borrower_id" id="borrowerId" required><optgroup label="Students">@foreach($students as $s)<option data-type="student" value="{{ $s->id }}">{{ trim($s->first_name.' '.$s->last_name) }} (Student)</option>@endforeach</optgroup><optgroup label="Staff">@foreach($staff as $s)<option data-type="staff" value="{{ $s->id }}">{{ trim($s->first_name.' '.$s->last_name) }} (Staff)</option>@endforeach</optgroup></select></div>
<div class="sn-field"><label>Issue Date *</label><input type="date" name="issued_at" value="{{ today()->format('Y-m-d') }}" required></div><div class="sn-field"><label>Due Date *</label><input type="date" name="due_at" value="{{ today()->addDays(14)->format('Y-m-d') }}" required></div>
</div><button class="sn-btn sn-btn-primary" style="margin-top:18px">Confirm Issue</button></form></div>
<script>const bt=document.getElementById('borrowerType'),bi=document.getElementById('borrowerId');function filterBorrowers(){let t=bt.value;[...bi.options].forEach(o=>{if(o.value)o.hidden=o.dataset.type!==t});let first=[...bi.options].find(o=>o.dataset.type===t);if(first)bi.value=first.value;}bt.addEventListener('change',filterBorrowers);filterBorrowers();</script>

</div>
@endsection
