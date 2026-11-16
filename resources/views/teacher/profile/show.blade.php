@extends('layouts.schoolnexa')

@section('content')
<div class="tp-wrap">
    <div class="tp-heading">
        <div>
            <span class="tp-eyebrow">TEACHER ACCOUNT</span>
            <h1>My Profile</h1>
            <p>View your staff information and keep your contact details up to date.</p>
        </div>
        <a class="tp-btn tp-primary" href="{{ route('teacher.profile.edit') }}">Edit personal details</a>
    </div>

    @if(session('success')) <div class="tp-alert">{{ session('success') }}</div> @endif

    <section class="tp-card tp-profile">
        <div class="tp-avatar">{{ mb_strtoupper(mb_substr($teacher->first_name, 0, 1).mb_substr($teacher->last_name, 0, 1)) }}</div>
        <div class="tp-person">
            <h2>{{ $teacher->full_name }}</h2>
            <p>{{ $teacher->designation ?: 'Teacher' }} · {{ $teacher->department ?: 'Department not specified' }}</p>
            <span class="tp-status">{{ ucfirst($teacher->status) }}</span>
        </div>
    </section>

    <div class="tp-grid">
        <section class="tp-card">
            <h3>Personal information</h3>
            <dl class="tp-details">
                <div><dt>First name</dt><dd>{{ $teacher->first_name }}</dd></div>
                <div><dt>Middle name</dt><dd>{{ $teacher->middle_name ?: '—' }}</dd></div>
                <div><dt>Last name</dt><dd>{{ $teacher->last_name }}</dd></div>
                <div><dt>Phone</dt><dd>{{ $teacher->phone ?: 'Not added' }}</dd></div>
                <div><dt>Login email</dt><dd>{{ $teacher->user?->email ?: '—' }}</dd></div>
            </dl>
        </section>
        <section class="tp-card">
            <h3>Employment information</h3>
            <dl class="tp-details">
                <div><dt>Employee number</dt><dd>{{ $teacher->employee_number }}</dd></div>
                <div><dt>Designation</dt><dd>{{ $teacher->designation ?: '—' }}</dd></div>
                <div><dt>Department</dt><dd>{{ $teacher->department ?: '—' }}</dd></div>
                <div><dt>Employment type</dt><dd>{{ $teacher->employment_type ?: '—' }}</dd></div>
                <div><dt>Joining date</dt><dd>{{ $teacher->joining_date?->format('d M Y') ?: '—' }}</dd></div>
            </dl>
        </section>
        <section class="tp-card tp-wide">
            <h3>School information</h3>
            <dl class="tp-details tp-details-3">
                <div><dt>School</dt><dd>{{ $teacher->school?->name ?: '—' }}</dd></div>
                <div><dt>Campus</dt><dd>{{ $teacher->campus?->name ?: '—' }}</dd></div>
                <div><dt>Account</dt><dd>{{ $teacher->user?->name ?: '—' }}</dd></div>
            </dl>
        </section>
    </div>
</div>
<style>
.tp-wrap{max-width:1180px;margin:0 auto;padding:24px;color:#172b4d}.tp-heading{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:22px}.tp-heading h1{font-size:28px;margin:4px 0}.tp-heading p{color:#64748b;margin:0}.tp-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.3px;color:#176b87}.tp-card{background:#fff;border:1px solid #e5edf4;border-radius:18px;padding:22px;box-shadow:0 8px 24px #123b5d0b}.tp-profile{display:flex;align-items:center;gap:18px;margin-bottom:18px}.tp-avatar{width:66px;height:66px;border-radius:18px;display:grid;place-items:center;background:#e8f4f8;color:#176b87;font-size:22px;font-weight:800}.tp-person h2{margin:0 0 5px;font-size:21px}.tp-person p{margin:0 0 9px;color:#64748b}.tp-status{font-size:12px;background:#ecfdf3;color:#15803d;padding:5px 10px;border-radius:20px;font-weight:700}.tp-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.tp-card h3{margin:0 0 16px;font-size:16px}.tp-wide{grid-column:1/-1}.tp-details{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin:0}.tp-details-3{grid-template-columns:repeat(3,1fr)}.tp-details dt{font-size:12px;color:#64748b;margin-bottom:5px}.tp-details dd{margin:0;font-weight:650;overflow-wrap:anywhere}.tp-btn{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;border-radius:10px;padding:11px 16px;font-weight:700}.tp-primary{background:#123b5d;color:#fff}.tp-alert{padding:12px 15px;border-radius:10px;background:#ecfdf3;color:#166534;margin-bottom:16px}@media(max-width:720px){.tp-heading{align-items:flex-start;flex-direction:column}.tp-grid{grid-template-columns:1fr}.tp-wide{grid-column:auto}.tp-details,.tp-details-3{grid-template-columns:1fr 1fr}}
</style>
@endsection
