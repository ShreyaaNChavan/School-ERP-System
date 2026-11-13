@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">Asset Register</h1><div class="sn-sub">Track school equipment and fixed assets</div></div><div class="sn-head-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a><a class="sn-btn sn-btn-primary" href="{{ route('inventory.assets.create') }}">+ Register Asset</a></div></div>
<div class="sn-card sn-table-wrap"><table class="sn-table"><thead><tr><th>Asset Tag</th><th>Asset</th><th>Category</th><th>Purchase Date</th><th>Condition</th><th>Location</th><th>Status</th><th>Action</th></tr></thead><tbody>@forelse($assets as $a)<tr><td>{{ $a->asset_tag }}</td><td>{{ $a->name }}<div class="sn-muted">{{ $a->serial_number }}</div></td><td>{{ $a->category->name??'—' }}</td><td>{{ $a->purchase_date?->format('d M Y')??'—' }}</td><td>{{ ucfirst($a->condition) }}</td><td>{{ $a->location }}</td><td>{{ ucfirst(str_replace('_',' ',$a->status)) }}</td><td><a href="{{ route('inventory.assets.edit',$a) }}">Edit</a></td></tr>@empty<tr><td colspan="8">No assets registered.</td></tr>@endforelse</tbody></table>{{ $assets->links() }}</div>

</div>
@endsection
