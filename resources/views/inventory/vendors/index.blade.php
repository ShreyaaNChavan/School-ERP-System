@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Vendors</h1><div class="sn-sub">Manage suppliers and contact details</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div></div>
<div class="sn-card"><h2 class="sn-section-title">Add Vendor</h2><form method="POST" action="{{ route('inventory.vendors.store') }}">@csrf<div class="sn-form"><div class="sn-field"><label>Vendor Name *</label><input name="name" required></div><div class="sn-field"><label>Contact Person</label><input name="contact_person"></div><div class="sn-field"><label>Phone</label><input name="phone"></div><div class="sn-field"><label>Email</label><input type="email" name="email"></div><div class="sn-field"><label>Tax Identifier</label><input name="tax_identifier"></div><div class="sn-field"><label>Address</label><input name="address"></div></div><button class="sn-btn sn-btn-primary" style="margin-top:12px">Save Vendor</button></form></div>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Vendor</th><th>Contact</th><th>Phone</th><th>Email</th><th>Tax ID</th></tr></thead><tbody>@forelse($vendors as $v)<tr><td>{{ $v->name }}</td><td>{{ $v->contact_person }}</td><td>{{ $v->phone }}</td><td>{{ $v->email }}</td><td>{{ $v->tax_identifier }}</td></tr>@empty<tr><td colspan="5" class="sn-empty">No vendors found.</td></tr>@endforelse</tbody></table>{{ $vendors->links() }}</div>

</div>
@endsection
