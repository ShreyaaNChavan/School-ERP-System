@extends('layouts.schoolnexa')
@section('content')
@include('integration-hub._style')
@php($editing=!empty($integration))
<div class="sx-page"><div class="sx-head"><div><h1 class="sx-title">{{$editing?'Edit Integration':'Add Integration'}}</h1><p class="sx-sub">Configure provider details and encrypted credentials.</p></div><div class="sx-actions"><a class="sx-btn" href="{{$editing?route('integration-hub.show',$integration->id):route('integration-hub.dashboard')}}">Back to {{$editing?'Integration Details':'Integration Hub Dashboard'}}</a></div></div>
@if($errors->any())<div class="sx-alert sx-error">@foreach($errors->all() as $e)<div>{{$e}}</div>@endforeach</div>@endif
<form method="POST" action="{{$editing?route('integration-hub.update',$integration->id):route('integration-hub.store')}}">@csrf @if($editing)@method('PUT')@endif
<section class="sx-panel"><div class="sx-form">
<div class="sx-field"><label>Integration Name *</label><input name="name" required value="{{old('name',$integration->name??'')}}" placeholder="e.g. SMS Gateway"></div>
<div class="sx-field"><label>Provider *</label><input name="provider" required value="{{old('provider',$integration->provider??'')}}" placeholder="e.g. MSG91, Google, Microsoft"></div>
<div class="sx-field"><label>Category *</label><select name="category" required>@foreach(['Communication','Payments','Learning','Storage','Identity','Analytics','Other'] as $c)<option value="{{$c}}" @selected(old('category',$integration->category??'')===$c)>{{$c}}</option>@endforeach</select></div>
<div class="sx-field"><label>Endpoint URL</label><input type="url" name="endpoint_url" value="{{old('endpoint_url',$integration->endpoint_url??'')}}" placeholder="https://api.example.com"></div>
<div class="sx-field" style="grid-column:1/-1"><label>API Key / Secret {{ $editing?'(leave blank to keep existing)':'' }}</label><input type="password" name="api_key" autocomplete="new-password" placeholder="{{$editing?'Leave blank to keep current credential':'Enter secret key'}}"></div>
<div class="sx-field" style="grid-column:1/-1"><label>Description</label><textarea name="description">{{old('description',$integration->description??'')}}</textarea></div>
</div></section><button class="sx-btn primary">{{$editing?'Save Changes':'Save Integration'}}</button></form></div>
@endsection