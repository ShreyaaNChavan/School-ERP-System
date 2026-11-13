@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Inventory Categories</h1><div class="sn-sub">Organise inventory and asset types</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div></div>
<div class="sn-card"><form method="POST" action="{{ route('inventory.categories.store') }}" class="sn-actions">@csrf<input name="name" placeholder="Category name" required style="padding:10px;border:1px solid #CBD5E1;border-radius:8px"><input name="description" placeholder="Description" style="padding:10px;border:1px solid #CBD5E1;border-radius:8px;min-width:240px"><button class="sn-btn sn-btn-primary">Add Category</button></form></div>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Category</th><th>Description</th><th>Items</th><th>Action</th></tr></thead><tbody>@forelse($categories as $c)<tr><td>{{ $c->name }}</td><td>{{ $c->description }}</td><td>{{ $c->items_count }}</td><td><form method="POST" action="{{ route('inventory.categories.destroy',$c) }}">@csrf @method('DELETE')<button class="sn-btn">Delete</button></form></td></tr>@empty<tr><td colspan="4">No categories created.</td></tr>@endforelse</tbody></table>{{ $categories->links() }}</div>

</div>
@endsection
