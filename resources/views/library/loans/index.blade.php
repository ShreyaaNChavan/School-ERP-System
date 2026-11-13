@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Library Transactions</h1><div class="sn-sub">Issued, returned and overdue books</div></div><div class="sn-head-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('library.dashboard') }}">← Back to Dashboard</a><a class="sn-btn sn-btn-primary" href="{{ route('library.loans.issue') }}">+ Issue Book</a></div></div>
<form class="sn-filter"><input name="search" value="{{ request('search') }}" placeholder="Search borrower or book"><select name="status"><option value="">All statuses</option><option value="issued" @selected(request('status')==='issued')>Issued</option><option value="returned" @selected(request('status')==='returned')>Returned</option></select><button class="sn-btn sn-btn-primary">Apply</button></form>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Book / Copy</th><th>Borrower</th><th>Issued</th><th>Due</th><th>Returned</th><th>Fine</th><th>Action</th></tr></thead><tbody>
@forelse($loans as $loan)<tr><td>{{ $loan->copy->book->title }}<div class="sn-muted">{{ $loan->copy->accession_number }}</div></td><td>{{ $loan->student ? trim($loan->student->first_name.' '.$loan->student->last_name) : ($loan->staff ? trim($loan->staff->first_name.' '.$loan->staff->last_name) : '—') }}</td><td>{{ $loan->issued_at->format('d M Y') }}</td><td>{{ $loan->due_at->format('d M Y') }}</td><td>{{ $loan->returned_at?->format('d M Y') ?: '—' }}</td><td>₹{{ number_format($loan->fine_amount,2) }}</td><td>@if($loan->status==='issued')<form method="POST" action="{{ route('library.loans.return',$loan) }}" onsubmit="return confirm('Confirm book return?')">@csrf @method('PATCH')<button class="sn-btn">Return</button></form>@else<span class="sn-badge">Returned</span>@endif</td></tr>@empty<tr><td colspan="7" class="sn-empty">No transactions found.</td></tr>@endforelse
</tbody></table>{{ $loans->links() }}</div>

</div>
@endsection
