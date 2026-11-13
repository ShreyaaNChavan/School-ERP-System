@extends('layouts.schoolnexa')
@section('content')
@include('role-builder._style')
@php($editing = !empty($role))
<div class="rb-page">
 <div class="rb-head"><div><h1 class="rb-title">{{ $editing ? 'Edit Role' : 'Create Role' }}</h1><p class="rb-sub">Configure role identity, scope and module-wise permissions.</p></div><div class="rb-actions"><a class="rb-btn" href="{{ $editing ? route('role-builder.show',$role->id) : route('role-builder.dashboard') }}">Back to {{ $editing ? 'Role Details' : 'Role Builder Dashboard' }}</a></div></div>
 @include('role-builder._flash')
 <form method="POST" action="{{ $editing ? route('role-builder.update',$role->id) : route('role-builder.store') }}">@csrf @if($editing) @method('PUT') @endif
 <section class="rb-panel"><h3>Role Information</h3><div class="rb-form-grid">
 <div class="rb-field"><label>Role Name *</label><input name="name" required maxlength="120" value="{{ old('name',$role->name ?? '') }}" placeholder="e.g. Transport Coordinator"></div>
 <div class="rb-field"><label>Role Code *</label><input name="code" required maxlength="100" value="{{ old('code',$role->code ?? '') }}" placeholder="e.g. TRANSPORT_COORDINATOR"><small class="rb-muted">Uppercase letters, numbers, underscore, dot and hyphen.</small></div>
 <div class="rb-field"><label>Scope *</label><select name="scope_type" required>@foreach(['organization','school','campus'] as $scope)<option value="{{ $scope }}" @selected(old('scope_type',$role->scope_type ?? 'school')===$scope)>{{ ucfirst($scope) }}</option>@endforeach</select></div>
 <div class="rb-field"><label>Description</label><textarea name="description" maxlength="1000" placeholder="Describe the role's responsibilities">{{ old('description',$role->description ?? '') }}</textarea></div>
 </div></section>
 <section class="rb-panel"><h3>Assign Permissions</h3><p class="rb-sub" style="margin:0 0 12px">Select the exact actions this role is allowed to perform. You can update these later.</p>
 <div class="rb-perm-grid">@foreach($permissions as $module => $items)<div class="rb-perm-group"><h4>{{ $module }}</h4>@foreach($items as $permission)<label class="rb-check"><input type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked(in_array($permission->id, old('permissions', $selected ?? [])))><span><strong>{{ $permission->name }}</strong><span class="rb-muted"> · {{ $permission->code }}</span></span></label>@endforeach</div>@endforeach</div>
 @if($permissions->isEmpty())<div class="rb-empty">No permissions are configured yet. Add permissions through Permission Matrix first.</div>@endif
 </section><div class="rb-actions"><button class="rb-btn primary" type="submit">{{ $editing ? 'Save Changes' : 'Create Role' }}</button><a class="rb-btn" href="{{ $editing ? route('role-builder.show',$role->id) : route('role-builder.dashboard') }}">Cancel</a></div>
 </form>
</div>
@endsection