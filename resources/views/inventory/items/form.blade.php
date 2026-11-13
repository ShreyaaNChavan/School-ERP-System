@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">{{ $item->exists?'Edit Inventory Item':'Add Inventory Item' }}</h1></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('inventory.items.index') }}">Back</a></div>
<div class="sn-card"><form method="POST" action="{{ $item->exists?route('inventory.items.update',$item):route('inventory.items.store') }}">@csrf @if($item->exists)@method('PUT')@endif<div class="sn-form">
<div class="sn-field"><label>Item Name *</label><input name="name" value="{{ old('name',$item->name) }}" required></div><div class="sn-field"><label>Item Code *</label><input name="item_code" value="{{ old('item_code',$item->item_code) }}" required></div>
<div class="sn-field"><label>Category</label><select name="inventory_category_id"><option value="">Uncategorized</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('inventory_category_id',$item->inventory_category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div><div class="sn-field"><label>Unit *</label><input name="unit" value="{{ old('unit',$item->unit??'piece') }}" required></div>
<div class="sn-field"><label>Quantity on Hand *</label><input type="number" step="0.01" min="0" name="quantity_on_hand" value="{{ old('quantity_on_hand',$item->quantity_on_hand??0) }}" required></div><div class="sn-field"><label>Reorder Level *</label><input type="number" step="0.01" min="0" name="reorder_level" value="{{ old('reorder_level',$item->reorder_level??0) }}" required></div><div class="sn-field"><label>Unit Cost (₹) *</label><input type="number" step="0.01" min="0" name="unit_cost" value="{{ old('unit_cost',$item->unit_cost??0) }}" required></div>
</div><button class="sn-btn sn-btn-primary" style="margin-top:18px">Save Item</button></form></div>

</div>
@endsection
