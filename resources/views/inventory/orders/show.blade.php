@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Purchase Order {{ $order->order_number }}</h1><div class="sn-sub">{{ $order->vendor->name }} · {{ $order->order_date->format('d M Y') }}</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('inventory.purchase-orders.index') }}">Back</a></div>
<div class="sn-card"><div class="sn-grid"><div><div class="sn-label">Subtotal</div><div class="sn-kpi">₹{{ number_format($order->subtotal,2) }}</div></div><div><div class="sn-label">Tax</div><div class="sn-kpi">₹{{ number_format($order->tax_amount,2) }}</div></div><div><div class="sn-label">Total</div><div class="sn-kpi">₹{{ number_format($order->total_amount,2) }}</div></div><div><div class="sn-label">Status</div><div class="sn-kpi" style="font-size:20px">{{ ucfirst($order->status) }}</div></div></div>
<h2 style="font-size:16px;margin-top:20px">Line Items</h2><table class="sn-table"><thead><tr><th>Item</th><th>Quantity</th><th>Unit Cost</th><th>Total</th></tr></thead><tbody>@foreach($order->items as $line)<tr><td>{{ $line->item->name }}</td><td>{{ $line->quantity }}</td><td>₹{{ number_format($line->unit_cost,2) }}</td><td>₹{{ number_format($line->total_cost,2) }}</td></tr>@endforeach</tbody></table>
@if($order->status!=='received' && $order->status!=='cancelled')<form method="POST" action="{{ route('inventory.purchase-orders.status',$order) }}" class="sn-actions" style="margin-top:18px">@csrf @method('PATCH')<select name="status" required style="padding:9px;border:1px solid #CBD5E1;border-radius:8px">@foreach(['pending','approved','ordered','received','cancelled'] as $s)<option value="{{ $s }}" @selected($order->status===$s)>{{ ucfirst($s) }}</option>@endforeach</select><button class="sn-btn sn-btn-primary">Update Status</button></form><p class="sn-muted">Stock quantities increase only when the order is marked Received. Draft orders must first be moved to Pending, Approved or Ordered.</p>@endif
</div>

</div>
@endsection
