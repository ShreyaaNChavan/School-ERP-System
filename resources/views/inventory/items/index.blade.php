@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Inventory Items</h1><div class="sn-sub">Track stock quantities and reorder levels</div></div><div class="sn-head-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a><a class="sn-btn sn-btn-primary" href="{{ route('inventory.items.create') }}">+ Add Item</a></div></div>
<form class="sn-filter"><input name="search" value="{{ request('search') }}" placeholder="Search item or code"><select name="category_id"><option value="">All categories</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->name }}</option>@endforeach</select><select name="stock"><option value="">All stock</option><option value="low" @selected(request('stock')==='low')>Low stock</option></select><button class="sn-btn sn-btn-primary">Apply</button><a class="sn-btn sn-btn-outline" href="{{ route('inventory.items.index') }}">Reset</a></form>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Item</th><th>Code</th><th>Category</th><th>Quantity</th><th>Reorder Level</th><th>Unit Cost</th><th>Actions</th></tr></thead><tbody>@forelse($items as $item)<tr><td>{{ $item->name }}</td><td>{{ $item->item_code }}</td><td>{{ $item->category->name ?? '—' }}</td><td>{{ $item->quantity_on_hand }} {{ $item->unit }}</td><td>{{ $item->reorder_level }}</td><td>₹{{ number_format($item->unit_cost,2) }}</td><td><div class="sn-row-actions"><a class="sn-action" href="{{ route('inventory.items.show',$item) }}">View</a><a class="sn-action" href="{{ route('inventory.items.edit',$item) }}">Edit</a><form class="sn-action-form" method="POST" action="{{ route('inventory.items.destroy',$item) }}" onsubmit="return confirm('Delete this item?')">@csrf @method('DELETE')<button type="submit" class="sn-action sn-danger">Delete</button></form></div></td></tr>@empty<tr><td colspan="7" class="sn-empty">No items found.</td></tr>@endforelse</tbody></table>{{ $items->links() }}</div>

</div>
@endsection
