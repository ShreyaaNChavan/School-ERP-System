@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ptm-page">@include('ptm._style')<div class="ptm-head"><div><h1>Edit PTM Meeting</h1><div class="ptm-sub">Update meeting details and outcome</div></div><div class="ptm-actions"><a class="ptm-btn" href="{{ route('ptm.show',$item->id) }}">← Back to Details</a></div></div><div class="ptm-panel"><form method="POST" action="{{ route('ptm.update',$item->id) }}">@csrf @method('PUT')@include('ptm._form')<div class="ptm-actions" style="margin-top:18px"><button class="ptm-btn primary" type="submit">Save Changes</button><a class="ptm-btn" href="{{ route('ptm.show',$item->id) }}">Cancel</a></div></form></div></div>
@endsection
