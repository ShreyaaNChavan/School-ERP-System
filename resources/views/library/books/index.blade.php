@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Book Catalogue</h1><div class="sn-sub">Manage titles, editions and physical copies</div></div><div class="sn-head-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('library.dashboard') }}">← Back to Dashboard</a><a class="sn-btn sn-btn-primary" href="{{ route('library.books.create') }}">+ Add Book</a></div></div>
<form class="sn-filter" method="GET"><input name="search" value="{{ request('search') }}" placeholder="Search title, author or ISBN"><select name="category"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ $c }}</option>@endforeach</select><button class="sn-btn sn-btn-primary">Apply</button><a class="sn-btn sn-btn-outline" href="{{ route('library.books.index') }}">Reset</a></form>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Book</th><th>ISBN</th><th>Category</th><th>Copies</th><th>Available</th><th>Actions</th></tr></thead><tbody>
@forelse($books as $book)<tr><td><strong>{{ $book->title }}</strong><div class="sn-muted">{{ $book->author }}</div></td><td>{{ $book->isbn ?: '—' }}</td><td>{{ $book->category ?: '—' }}</td><td>{{ $book->total_copies }}</td><td>{{ $book->available_copies }}</td><td><div class="sn-row-actions"><a class="sn-action" href="{{ route('library.books.show',$book) }}">View</a><a class="sn-action" href="{{ route('library.books.edit',$book) }}">Edit</a><form class="sn-action-form" method="POST" action="{{ route('library.books.destroy',$book) }}" onsubmit="return confirm('Delete this book?')">@csrf @method('DELETE')<button type="submit" class="sn-action sn-danger">Delete</button></form></div></td></tr>@empty<tr><td colspan="6" class="sn-empty">No books found.</td></tr>@endforelse
</tbody></table>{{ $books->links() }}</div>

</div>
@endsection
