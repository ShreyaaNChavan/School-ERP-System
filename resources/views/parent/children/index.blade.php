@extends('layouts.schoolnexa')
@section('title', 'Parent Portal')
@section('content')

<style>
.px-wrap{max-width:1200px;margin:0 auto;padding:8px 0 28px;color:#172b45}
.px-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:22px;flex-wrap:wrap}
.px-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#16809a}
.px-title{font-size:27px;font-weight:800;margin:4px 0;color:#123b5d}
.px-sub{color:#718096;font-size:13px}
.px-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin:18px 0}
.px-card{background:#fff;border:1px solid #e5edf3;border-radius:16px;padding:18px;box-shadow:0 5px 18px rgba(18,59,93,.045)}
.px-stat{font-size:24px;font-weight:800;color:#123b5d;margin-top:6px}
.px-label{font-size:12px;color:#718096;font-weight:700}
.px-table{width:100%;border-collapse:collapse}
.px-table th,.px-table td{padding:13px 12px;text-align:left;border-bottom:1px solid #edf1f5;font-size:13px;vertical-align:middle}
.px-table th{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#718096;background:#f8fafc}
.px-btn{display:inline-flex;align-items:center;justify-content:center;padding:9px 14px;border-radius:10px;background:#176b87;color:white!important;text-decoration:none;font-size:13px;font-weight:700;border:0;cursor:pointer}
.px-btn.secondary{background:#edf5f8;color:#176b87!important}
.px-badge{display:inline-block;padding:5px 9px;border-radius:30px;background:#edf5f8;color:#176b87;font-size:11px;font-weight:800;text-transform:capitalize}
.px-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:13px}
.px-field{display:flex;flex-direction:column;gap:6px}
.px-field.full{grid-column:1/-1}
.px-field label{font-size:12px;font-weight:700;color:#53677d}
.px-field input,.px-field select,.px-field textarea{width:100%;border:1px solid #dce5ec;border-radius:10px;padding:10px 12px;background:#fff;color:#243b53;font:inherit;font-size:13px}
.px-empty{padding:32px;text-align:center;color:#718096}
.px-alert{padding:12px 15px;border-radius:10px;background:#eff8f3;color:#23734a;margin-bottom:15px;font-size:13px}
@media(max-width:650px){.px-form{grid-template-columns:1fr}.px-table{min-width:650px}.px-table-wrap{overflow-x:auto}.px-title{font-size:23px}}
</style>

<div class="px-wrap">

<div class="px-head"><div><div class="px-kicker">Family portal</div><h1 class="px-title">My Children</h1><div class="px-sub">Profiles and current class details for children linked to your account.</div></div></div>
<div class="px-grid">
    <div class="px-card"><div class="px-label">Linked children</div><div class="px-stat">{{ $children->count() }}</div></div>
</div>
<div class="px-card">
    @if($children->isEmpty())<div class="px-empty">No student profiles are currently linked to your parent account. Please contact the school office.</div>
    @else
    <div class="px-table-wrap"><table class="px-table"><thead><tr><th>Student</th><th>Admission No.</th><th>Class</th><th>Section</th><th>Status</th></tr></thead><tbody>
    @foreach($children as $child)
    <tr><td><strong>{{ trim($child->first_name.' '.($child->middle_name ?? '').' '.$child->last_name) }}</strong></td><td>{{ $child->admission_number }}</td><td>{{ $child->class_name }}</td><td>{{ $child->section_name }}</td><td><span class="px-badge">{{ $child->status }}</span></td></tr>
    @endforeach
    </tbody></table></div>
    @endif
</div>

</div>
@endsection
