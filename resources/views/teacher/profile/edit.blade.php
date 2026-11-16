@extends('layouts.schoolnexa')

@section('content')
<div class="tp-wrap">
    <div class="tp-heading">
        <div><span class="tp-eyebrow">TEACHER ACCOUNT</span><h1>Edit My Profile</h1><p>Update your name and contact number. Employment records are managed by school administration.</p></div>
        <a class="tp-btn tp-light" href="{{ route('teacher.profile.show') }}">Back to profile</a>
    </div>

    <form method="POST" action="{{ route('teacher.profile.update') }}" class="tp-card tp-form">
        @csrf
        @method('PUT')
        <div class="tp-form-grid">
            <label>First name<input name="first_name" value="{{ old('first_name', $teacher->first_name) }}" required maxlength="100">@error('first_name')<small>{{ $message }}</small>@enderror</label>
            <label>Middle name<input name="middle_name" value="{{ old('middle_name', $teacher->middle_name) }}" maxlength="100">@error('middle_name')<small>{{ $message }}</small>@enderror</label>
            <label>Last name<input name="last_name" value="{{ old('last_name', $teacher->last_name) }}" required maxlength="100">@error('last_name')<small>{{ $message }}</small>@enderror</label>
            <label>Phone number<input name="phone" value="{{ old('phone', $teacher->phone) }}" maxlength="30" placeholder="Enter contact number">@error('phone')<small>{{ $message }}</small>@enderror</label>
        </div>
        <div class="tp-note"><strong>Protected information</strong><span>Employee number, designation, department, school, campus, joining date and employment status cannot be changed from teacher login.</span></div>
        <div class="tp-actions"><a class="tp-btn tp-light" href="{{ route('teacher.profile.show') }}">Cancel</a><button class="tp-btn tp-primary" type="submit">Save changes</button></div>
    </form>
</div>
<style>
.tp-wrap{max-width:1000px;margin:0 auto;padding:24px;color:#172b4d}.tp-heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:22px}.tp-heading h1{font-size:28px;margin:4px 0}.tp-heading p{color:#64748b;margin:0}.tp-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.3px;color:#176b87}.tp-card{background:#fff;border:1px solid #e5edf4;border-radius:18px;padding:24px;box-shadow:0 8px 24px #123b5d0b}.tp-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.tp-form label{display:grid;gap:7px;font-size:13px;font-weight:700;color:#334155}.tp-form input{width:100%;border:1px solid #dbe5ee;border-radius:10px;padding:12px 13px;font:inherit;font-weight:500;color:#172b4d}.tp-form input:focus{outline:2px solid #b8dce8;border-color:#176b87}.tp-form small{color:#dc2626}.tp-note{display:grid;gap:4px;background:#f1f7fa;border-radius:12px;padding:14px;margin-top:22px;color:#475569;font-size:13px}.tp-note strong{color:#123b5d}.tp-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.tp-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border:0;border-radius:10px;padding:11px 16px;font-weight:700;cursor:pointer}.tp-primary{background:#123b5d;color:#fff}.tp-light{background:#edf3f7;color:#123b5d}@media(max-width:650px){.tp-heading{align-items:flex-start;flex-direction:column}.tp-form-grid{grid-template-columns:1fr}}
</style>
@endsection
