@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Purchase Orders</h1><div class="sn-sub">Procurement requests and deliveries</div></div><div class="sn-head-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a><a class="sn-btn sn-btn-primary" href="{{ route('inventory.purchase-orders.create') }}">+ Create Purchase Order</a></div></div>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Order No.</th><th>Vendor</th><th>Order Date</th><th>Expected</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>@forelse($orders as $o)<tr><td>{{ $o->order_number }}</td><td>{{ $o->vendor->name }}</td><td>{{ $o->order_date->format('d M Y') }}</td><td>{{ $o->expected_delivery_date?->format('d M Y')??'—' }}</td><td>₹{{ number_format($o->total_amount,2) }}</td><td><span class="sn-badge">{{ ucfirst($o->status) }}</span></td><td><a href="{{ route('inventory.purchase-orders.show',$o) }}">View</a></td></tr>@empty<tr><td colspan="7">No purchase orders.</td></tr>@endforelse</tbody></table>{{ $orders->links() }}</div>

</div>
@endsection
