@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">{{ $book->title }}</h1><div class="sn-sub">{{ $book->author }} · {{ $book->isbn }}</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('library.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('library.books.index') }}">Back to Catalogue</a></div>
<div class="sn-card"><h2 class="sn-section-title">Add Physical Copy</h2><form method="POST" action="{{ route('library.copies.store',$book) }}">@csrf<div class="sn-form"><div class="sn-field"><label>Accession Number *</label><input name="accession_number" required></div><div class="sn-field"><label>Shelf Location</label><input name="shelf_location"></div><div class="sn-field"><label>Campus ID (optional)</label><input type="number" name="campus_id"></div></div><button class="sn-btn sn-btn-primary" style="margin-top:12px">Add Copy</button></form></div>
<div class="sn-card"><h2 class="sn-section-title">Copies</h2><div class="sn-table-wrap"><table class="sn-table"><thead><tr><th>Accession No.</th><th>Shelf</th><th>Status</th><th>Update</th></tr></thead><tbody>@forelse($book->copies as $copy)<tr><td>{{ $copy->accession_number }}</td><td>{{ $copy->shelf_location ?: '—' }}</td><td>{{ ucfirst($copy->status) }}</td><td><form method="POST" action="{{ route('library.copies.update',$copy) }}">@csrf @method('PATCH')<input type="hidden" name="shelf_location" value="{{ $copy->shelf_location }}"><select name="status"><option value="available" @selected($copy->status==='available')>Available</option><option value="damaged" @selected($copy->status==='damaged')>Damaged</option><option value="lost" @selected($copy->status==='lost')>Lost</option><option value="maintenance" @selected($copy->status==='maintenance')>Maintenance</option></select> <button class="sn-btn">Update</button></form></td></tr>@empty<tr><td colspan="4">No copies added.</td></tr>@endforelse</tbody></table></div></div>

</div>
@endsection
