
@extends('layouts.schoolnexa')

@section('content')
@php
    $statuses = [
        'submitted' => 'Submitted',
        'under_review' => 'Under Review',
        'documents_pending' => 'Documents Pending',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'waitlisted' => 'Waitlisted',
    ];

    $statusColors = [
        'submitted' => ['#EFF6FF', '#2563EB'],
        'under_review' => ['#F5F3FF', '#7C3AED'],
        'documents_pending' => ['#FFF7ED', '#C2410C'],
        'approved' => ['#F0FDF4', '#16A34A'],
        'rejected' => ['#FEF2F2', '#DC2626'],
        'waitlisted' => ['#FFFBEB', '#B45309'],
    ];
@endphp

<style>
    .app-page {font-family:Inter,sans-serif;color:#25313C;}
    .app-heading {font-size:22px;font-weight:700;color:#123B5D;}
    .app-subtitle {font-size:13px;color:#64748B;margin-top:5px;}
    .app-card {background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:18px;}
    .app-kpi-label {font-size:12px;color:#64748B;font-weight:600;}
    .app-kpi-value {font-size:26px;font-weight:700;color:#123B5D;margin-top:9px;}
    .app-kpi-icon {width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
    .app-btn {display:inline-flex;align-items:center;justify-content:center;gap:7px;height:40px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;white-space:nowrap;}
    .app-btn:hover {background:#F8FAFC;}
    .app-primary {background:#123B5D;color:#fff;border-color:#123B5D;}
    .app-primary:hover {background:#194D73;color:#fff;}
    .app-input {width:100%;height:40px;border:1px solid #CBD5E1;border-radius:9px;padding:0 11px;font-size:13px;color:#25313C;background:#fff;outline:none;}
    .app-input:focus {border-color:#176B87;box-shadow:0 0 0 3px #176B871A;}
    .app-table {width:100%;border-collapse:collapse;min-width:900px;}
    .app-table th {background:#F8FAFC;color:#64748B;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.35px;padding:13px 15px;text-align:left;border-bottom:1px solid #E2E8F0;}
    .app-table td {padding:14px 15px;font-size:13px;border-bottom:1px solid #F1F5F9;vertical-align:middle;}
    .app-table tbody tr:last-child td {border-bottom:0;}
    .app-table tbody tr:hover {background:#FAFCFE;}
    .app-badge {display:inline-flex;padding:5px 9px;border-radius:20px;font-size:11px;font-weight:650;white-space:nowrap;}
    .app-section-title {font-size:16px;font-weight:700;color:#123B5D;}
    .app-muted {font-size:12px;color:#64748B;}
    .app-pagination {padding:14px 18px;border-top:1px solid #E2E8F0;}
    @media(max-width:700px) {
        .app-header {align-items:flex-start !important;flex-direction:column;}
        .app-kpis {grid-template-columns:repeat(2,minmax(0,1fr)) !important;}
    }
</style>

<div class="app-page">

    {{-- Header --}}
    <div class="app-header" style="display:flex;justify-content:space-between;align-items:center;gap:15px;margin-bottom:22px;">
        <div>
            <div class="app-heading">Admission Applications</div>
            <div class="app-subtitle">Manage applications, verify documents and track admission decisions.</div>
        </div>

        @if(auth()->user()->hasPermission('ADMISSIONS.CREATE'))
            <a href="{{ route('admission-applications.create') }}" class="app-btn app-primary">
                <span style="font-size:17px;">+</span> New Application
            </a>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="app-kpis" style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:13px;margin-bottom:22px;">

        <div class="app-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:5px;">
                <span class="app-kpi-label">Total Applications</span>
                <div class="app-kpi-icon" style="background:#EAF2F8;color:#123B5D;">▤</div>
            </div>
            <div class="app-kpi-value">{{ number_format($stats['total']) }}</div>
            <div class="app-muted">All applications</div>
        </div>

        <div class="app-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:5px;">
                <span class="app-kpi-label">Submitted</span>
                <div class="app-kpi-icon" style="background:#EFF6FF;color:#2563EB;">↗</div>
            </div>
            <div class="app-kpi-value">{{ number_format($stats['submitted']) }}</div>
            <div class="app-muted">New submissions</div>
        </div>

        <div class="app-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:5px;">
                <span class="app-kpi-label">Under Review</span>
                <div class="app-kpi-icon" style="background:#F5F3FF;color:#7C3AED;">◷</div>
            </div>
            <div class="app-kpi-value">{{ number_format($stats['under_review']) }}</div>
            <div class="app-muted">Being evaluated</div>
        </div>

        <div class="app-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:5px;">
                <span class="app-kpi-label">Approved</span>
                <div class="app-kpi-icon" style="background:#F0FDF4;color:#16A34A;">✓</div>
            </div>
            <div class="app-kpi-value">{{ number_format($stats['approved']) }}</div>
            <div class="app-muted">Approved applicants</div>
        </div>

        <div class="app-card">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:5px;">
                <span class="app-kpi-label">Rejected</span>
                <div class="app-kpi-icon" style="background:#FEF2F2;color:#DC2626;">×</div>
            </div>
            <div class="app-kpi-value">{{ number_format($stats['rejected']) }}</div>
            <div class="app-muted">Rejected applications</div>
        </div>
    </div>

    {{-- Application Directory --}}
    <div class="app-card" style="padding:0;overflow:hidden;">

        <div style="padding:18px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <div>
                <div class="app-section-title">Application Directory</div>
                <div class="app-muted" style="margin-top:4px;">Review and manage student admission applications.</div>
            </div>
            <span class="app-muted">{{ $applications->total() }} records found</span>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('admission-applications.index') }}"
              style="padding:0 18px 17px;display:flex;gap:10px;flex-wrap:wrap;">

            <div style="flex:2;min-width:200px;">
                <input type="text" name="search" class="app-input"
                       placeholder="Search application number, student or parent..."
                       value="{{ request('search') }}">
            </div>

            <div style="flex:1;min-width:155px;">
                <select name="status" class="app-input">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1;min-width:135px;">
                <select name="grade" class="app-input">
                    <option value="">All Classes</option>
                    @foreach(['Nursery','Jr. KG','Sr. KG','Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12'] as $grade)
                        <option value="{{ $grade }}" @selected(request('grade') === $grade)>
                            {{ $grade }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="app-btn">Apply Filters</button>
            <a href="{{ route('admission-applications.index') }}" class="app-btn">Reset</a>
        </form>

        {{-- Table --}}
        <div style="overflow-x:auto;">
            <table class="app-table">
                <thead>
                    <tr>
                        <th>Application No.</th>
                        <th>Applicant</th>
                        <th>Parent / Guardian</th>
                        <th>Contact</th>
                        <th>Applying For</th>
                        <th>CRM Lead</th>
                        <th>Submitted On</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($applications as $application)
                        @php
                            $colors = $statusColors[$application->status] ?? ['#F1F5F9','#475569'];
                            $label = $statuses[$application->status] ?? ucfirst(str_replace('_',' ',$application->status));
                        @endphp

                        <tr>
                            <td style="font-weight:650;color:#123B5D;">
                                {{ $application->application_number }}
                            </td>

                            <td>
                                <div style="font-weight:650;color:#123B5D;">
                                    {{ $application->applicant_name }}
                                </div>
                                <div class="app-muted">
                                    {{ $application->date_of_birth?->format('d M Y') ?: 'DOB not provided' }}
                                </div>
                            </td>

                            <td>{{ $application->parent_name ?: '—' }}</td>
                            <td>
                                {{ $application->parent_phone ?: '—' }}
                                @if($application->parent_email)
                                    <div class="app-muted">{{ $application->parent_email }}</div>
                                @endif
                            </td>

                            <td>{{ $application->applying_grade }}</td>
                            <td>
                                @if($application->lead)
                                    <a href="{{ route('admission-crm.show', $application->lead) }}"
                                       style="color:#176B87;text-decoration:none;font-weight:600;">
                                        {{ $application->lead->lead_number }}
                                    </a>
                                @else
                                    <span class="app-muted">Direct application</span>
                                @endif
                            </td>

                            <td>{{ $application->created_at?->format('d M Y') }}</td>

                            <td>
                                <span class="app-badge" style="background:{{ $colors[0] }};color:{{ $colors[1] }};">
                                    {{ $label }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admission-applications.show', $application) }}"
                                   class="app-btn" style="height:32px;padding:0 10px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:48px 15px;">
                                <div style="font-size:15px;font-weight:650;color:#334155;">
                                    No applications found
                                </div>
                                <div class="app-muted" style="margin-top:7px;">
                                    Applications submitted through the admission process will appear here.
                                </div>

                                @if(auth()->user()->hasPermission('ADMISSIONS.CREATE'))
                                    <a href="{{ route('admission-applications.create') }}"
                                       class="app-btn app-primary" style="margin-top:15px;">
                                        Create First Application
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($applications->hasPages())
            <div class="app-pagination">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
