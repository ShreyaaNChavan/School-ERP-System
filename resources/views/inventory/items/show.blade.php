@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">
@include('library._styles')
@if(session('success'))<div class="sn-alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="sn-alert sn-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<div class="sn-head"><div><h1 class="sn-title">{{ $item->name }}</h1><div class="sn-sub">Item code: {{ $item->item_code }}</div></div><div class="sn-actions"><a class="sn-btn sn-btn-outline sn-back" href="{{ route('inventory.dashboard') }}">← Back to Dashboard</a></div><a class="sn-btn sn-btn-outline" href="{{ route('inventory.items.index') }}">Back</a></div><div class="sn-card"><div class="sn-grid">@foreach(['Category'=>$item->category->name??'—','Quantity'=>$item->quantity_on_hand.' '.$item->unit,'Reorder Level'=>$item->reorder_level,'Unit Cost'=>'₹'.number_format($item->unit_cost,2),'Stock Value'=>'₹'.number_format($item->quantity_on_hand*$item->unit_cost,2)] as $k=>$v)<div><div class="sn-label">{{ $k }}</div><div style="font-size:18px;font-weight:650;margin-top:6px">{{ $v }}</div></div>@endforeach</div><div style="margin-top:18px"><a class="sn-btn sn-btn-outline" href="{{ route('inventory.items.edit',$item) }}">Edit Item</a></div></div>

</div>
@endsection
