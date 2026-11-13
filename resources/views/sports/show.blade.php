@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<div class="sn-head"><div><h1>Record Details</h1><div class="sn-sub">View complete information</div></div><div class="sn-actions"><a class="sn-btn" href="{{ route('sports.index') }}">← Back to List</a><a class="sn-btn sn-primary" href="{{ route('sports.edit',$item->id) }}">Edit Record</a></div></div>
<div class="sn-panel"><div class="sn-form">@foreach((array)$item as $field=>$value)<div class="sn-field"><label>{{ ucwords(str_replace('_',' ',$field)) }}</label><div style="font-size:14px;padding:8px 0;color:#25313C">{{ $value === null || $value === '' ? '—' : $value }}</div></div>@endforeach</div></div></div>
@endsection
