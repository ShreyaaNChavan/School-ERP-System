@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Create Purchase Order</h1><div class="sn-sub">Select vendor and add line items</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('inventory.purchase-orders.index') }}">Back</a></div>
<div class="sn-card"><form method="POST" action="{{ route('inventory.purchase-orders.store') }}">@csrf
<div class="sn-form"><div class="sn-field"><label>Vendor *</label><select name="vendor_id" required><option value="">Select vendor</option>@foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }}</option>@endforeach</select></div><div class="sn-field"><label>Order Number *</label><input name="order_number" required></div><div class="sn-field"><label>Order Date *</label><input type="date" name="order_date" value="{{ today()->format('Y-m-d') }}" required></div><div class="sn-field"><label>Expected Delivery</label><input type="date" name="expected_delivery_date"></div><div class="sn-field"><label>Tax Amount (₹)</label><input type="number" step="0.01" min="0" name="tax_amount" value="0"></div></div>
<h2 style="font-size:16px;margin:20px 0 10px">Order Items</h2><div id="lines"><div class="sn-form line" style="margin-bottom:10px"><div class="sn-field"><label>Inventory Item</label><select name="items[0][inventory_item_id]" required>@foreach($items as $i)<option value="{{ $i->id }}">{{ $i->name }} ({{ $i->item_code }})</option>@endforeach</select></div><div class="sn-field"><label>Quantity</label><input type="number" min="0.01" step="0.01" name="items[0][quantity]" value="1" required></div><div class="sn-field"><label>Unit Cost (₹)</label><input type="number" min="0" step="0.01" name="items[0][unit_cost]" value="0" required></div></div></div>
<div class="sn-actions"><button type="button" class="sn-btn" id="addLine">+ Add another item</button><button class="sn-btn sn-btn-primary">Create Order</button></div></form></div>
<script>let n=1;document.getElementById('addLine').addEventListener('click',()=>{let first=document.querySelector('.line');let clone=first.cloneNode(true);clone.querySelectorAll('select,input').forEach(el=>{el.name=el.name.replace(/items\\[0\\]/,'items['+n+']');if(el.tagName==='INPUT')el.value=el.name.includes('[quantity]')?'1':'0'});document.getElementById('lines').appendChild(clone);n++});</script>

</div>
@endsection
