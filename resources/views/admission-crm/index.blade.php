@extends('layouts.schoolnexa')

@section('content')
@php
    $stages = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'interested' => 'Interested',
        'follow_up' => 'Follow-up',
        'application_started' => 'Application Started',
        'documents_pending' => 'Documents Pending',
        'converted' => 'Converted',
        'lost' => 'Lost',
    ];

    $stageColors = [
        'new' => '#3B82F6',
        'contacted' => '#6366F1',
        'interested' => '#8B5CF6',
        'follow_up' => '#D97706',
        'application_started' => '#0D9488',
        'documents_pending' => '#EA580C',
        'converted' => '#16A34A',
        'lost' => '#DC2626',
    ];

    $stageIcons = [
        'new' => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8"/><path d="M8 12h8"/>',
        'contacted' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/>',
        'interested' => '<path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/>',
        'follow_up' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'application_started' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 14h6"/>',
        'documents_pending' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'converted' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        'lost' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>',
    ];

    $groupedLeads = $leads->getCollection()->groupBy('status');
@endphp

<style>
    .crm-page { color:#25313C; font-family:Inter,sans-serif; }
    .crm-heading { font-size:22px; font-weight:700; color:#123B5D; }
    .crm-subtitle { font-size:13px; color:#64748B; margin-top:5px; }
    .crm-btn {
        display:inline-flex; align-items:center; justify-content:center;
        gap:7px; padding:0 13px; height:40px; border:1px solid #CBD5E1;
        border-radius:9px; background:#fff; color:#123B5D;
        font-size:13px; font-weight:600; text-decoration:none; cursor:pointer;
        white-space:nowrap;
    }
    .crm-btn:hover { background:#F8FAFC; }
    .crm-btn-primary { background:#123B5D; color:#fff; border-color:#123B5D; }
    .crm-btn-primary:hover { background:#194D73; color:#fff; }
    .crm-card {
        background:#fff; border:1px solid #E2E8F0; border-radius:14px;
        padding:18px;
    }
    .crm-kpi-label { font-size:12px; color:#64748B; font-weight:600; }
    .crm-kpi-value { font-size:26px; font-weight:700; margin-top:9px; color:#123B5D; }
    .crm-kpi-icon {
        width:38px;height:38px;border-radius:50%;display:flex;
        align-items:center;justify-content:center;flex-shrink:0;
    }
    .crm-kpi-icon svg {
        width:18px; height:18px; stroke:currentColor; fill:none;
        stroke-width:2; stroke-linecap:round; stroke-linejoin:round;
    }
    .crm-input {
        height:40px; border:1px solid #CBD5E1; border-radius:9px;
        padding:0 11px; font-size:13px; color:#25313C;
        background:#fff; width:100%; outline:none;
    }
    .crm-input:focus { border-color:#176B87; box-shadow:0 0 0 3px #176B871A; }
    .crm-label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; }
    .crm-table { width:100%; border-collapse:collapse; min-width:850px; }
    .crm-table th {
        background:#F8FAFC; color:#64748B; font-size:11px;
        font-weight:700; text-transform:uppercase; letter-spacing:.35px;
        padding:13px 15px; text-align:left; border-bottom:1px solid #E2E8F0;
    }
    .crm-table td {
        padding:14px 15px; font-size:13px; border-bottom:1px solid #F1F5F9;
        vertical-align:middle;
    }
    .crm-table tr:last-child td { border-bottom:0; }
    .crm-table tbody tr:hover { background:#FAFCFE; }
    .crm-status {
        display:inline-flex; padding:5px 9px; border-radius:20px;
        font-size:11px; font-weight:650; white-space:nowrap;
    }
    .crm-pipeline {
        display:grid; grid-template-columns:repeat(4,minmax(180px,1fr));
        gap:12px;
    }
    .crm-stage {
        background:#F8FAFC; border:1px solid #E2E8F0;
        border-radius:12px; padding:13px; min-height:110px;
    }
    .crm-stage-title {
        display:flex; align-items:center; justify-content:space-between;
        font-size:12px; font-weight:700; color:#334155; gap:6px;
    }
    .crm-stage-name { display:flex; align-items:center; gap:8px; }
    .crm-stage-icon {
        width:26px; height:26px; border-radius:50%; display:flex;
        align-items:center; justify-content:center; flex-shrink:0;
    }
    .crm-stage-icon svg {
        width:14px; height:14px; stroke:currentColor; fill:none;
        stroke-width:2; stroke-linecap:round; stroke-linejoin:round;
    }
    .crm-stage-count {
        background:#E2E8F0; border-radius:20px; padding:3px 8px;
        font-size:11px; color:#475569;
    }
    .crm-stage-bar { height:3px; border-radius:5px; margin:10px 0; }
    .crm-stage-lead {
        display:block; background:#fff; border:1px solid #E2E8F0;
        border-radius:8px; padding:10px; margin-top:8px;
        color:#25313C; text-decoration:none;
    }
    .crm-stage-lead:hover { border-color:#94A3B8; }
    .crm-stage-lead strong { display:block; font-size:12px; }
    .crm-stage-lead small { display:block; color:#64748B; font-size:11px; margin-top:4px; }
    .crm-section-title { font-size:16px; font-weight:700; color:#123B5D; }
    .crm-muted { color:#64748B; font-size:12px; }
    .crm-empty { color:#94A3B8; font-size:12px; padding:12px 0; }
    .crm-pagination { padding:14px 18px; border-top:1px solid #E2E8F0; }
    @media(max-width:1000px) {
        .crm-pipeline { grid-template-columns:repeat(2,minmax(180px,1fr)); }
    }
    @media(max-width:600px) {
        .crm-pipeline { grid-template-columns:1fr; }
        .crm-header { align-items:flex-start !important; flex-direction:column; }
        .crm-kpis { grid-template-columns:repeat(2,minmax(0,1fr)) !important; }
    }
</style>

<div class="crm-page">

    {{-- Page Header --}}
    <div class="crm-header" style="display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:22px;">
        <div>
            <div class="crm-heading">Admission CRM</div>
            <div class="crm-subtitle">Manage admission enquiries, prospective students and follow-ups.</div>
        </div>

        @if(auth()->user()->hasPermission('ADMISSIONS.CREATE'))
            <a href="{{ route('admission-crm.create') }}" class="crm-btn crm-btn-primary">
                <span style="font-size:17px;">+</span> Add New Lead
            </a>
        @endif
    </div>

    {{-- KPI Cards --}}
    <div class="crm-kpis" style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:22px;">

        {{-- Total Enquiries --}}
        <div class="crm-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="crm-kpi-label">Total Enquiries</span>
                <div class="crm-kpi-icon" style="background:#EEF2FF;color:#4F6BED;">
                    <svg viewBox="0 0 24 24">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 3v5h5"/>
                        <path d="M9 13h6"/>
                        <path d="M9 17h6"/>
                    </svg>
                </div>
            </div>
            <div class="crm-kpi-value">{{ number_format($stats['total']) }}</div>
            <div class="crm-muted">All admission leads</div>
        </div>

        {{-- New Leads --}}
        <div class="crm-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="crm-kpi-label">New Leads</span>
                <div class="crm-kpi-icon" style="background:#EFF6FF;color:#3B82F6;">
                    <svg viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M19 8v6"/>
                        <path d="M22 11h-6"/>
                    </svg>
                </div>
            </div>
            <div class="crm-kpi-value">{{ number_format($stats['new']) }}</div>
            <div class="crm-muted">Awaiting initial contact</div>
        </div>

        {{-- Follow-ups Due --}}
        <div class="crm-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="crm-kpi-label">Follow-ups Due</span>
                <div class="crm-kpi-icon" style="background:#FFF7E6;color:#F59E0B;">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </div>
            </div>
            <div class="crm-kpi-value">{{ number_format($stats['followups_due']) }}</div>
            <div class="crm-muted">Pending scheduled follow-ups</div>
        </div>

        {{-- Converted Admissions --}}
        <div class="crm-card">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span class="crm-kpi-label">Converted Admissions</span>
                <div class="crm-kpi-icon" style="background:#ECFDF3;color:#16A34A;">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="m8.5 12.5 2.5 2.5 4.5-5"/>
                    </svg>
                </div>
            </div>
            <div class="crm-kpi-value">{{ number_format($stats['converted']) }}</div>
            <div class="crm-muted">Successfully converted leads</div>
        </div>
    </div>

    {{-- Pipeline --}}
    <div class="crm-card" style="margin-bottom:22px;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:17px;">
            <div>
                <div class="crm-section-title">Admission Pipeline</div>
                <div class="crm-muted" style="margin-top:4px;">Track leads across admission stages.</div>
            </div>
            <span class="crm-muted">{{ $stats['total'] }} total leads</span>
        </div>

        <div class="crm-pipeline">
            @foreach($stages as $key => $label)
                @php
                    $stageLeads = $groupedLeads->get($key, collect());
                    $color = $stageColors[$key];
                @endphp

                <div class="crm-stage">
                    <div class="crm-stage-title">
                        <span class="crm-stage-name">
                            <span class="crm-stage-icon" style="background:{{ $color }}1A;color:{{ $color }};">
                                <svg viewBox="0 0 24 24">{!! $stageIcons[$key] !!}</svg>
                            </span>
                            <span>{{ $label }}</span>
                        </span>
                        <span class="crm-stage-count">{{ $stageLeads->count() }}</span>
                    </div>

                    <div class="crm-stage-bar" style="background:{{ $color }};"></div>

                    @forelse($stageLeads->take(3) as $lead)
                        <a href="{{ route('admission-crm.show', $lead) }}" class="crm-stage-lead">
                            <strong>{{ $lead->student_name }}</strong>
                            <small>
                                {{ $lead->prospective_grade ?: 'Grade not specified' }}
                                @if($lead->phone) · {{ $lead->phone }} @endif
                            </small>
                        </a>
                    @empty
                        <div class="crm-empty">No leads in this stage</div>
                    @endforelse

                    @if($stageLeads->count() > 3)
                        <div class="crm-muted" style="margin-top:8px;">
                            +{{ $stageLeads->count() - 3 }} more on this page
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Lead Directory --}}
    <div class="crm-card" style="padding:0;overflow:hidden;">
        <div style="padding:18px 18px 15px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;">
            <div>
                <div class="crm-section-title">Admission Leads</div>
                <div class="crm-muted" style="margin-top:4px;">View and manage all prospective students.</div>
            </div>
            <span class="crm-muted">{{ $leads->total() }} records found</span>
        </div>

        <form method="GET" action="{{ route('admission-crm.index') }}"
              style="padding:0 18px 17px;display:flex;gap:10px;flex-wrap:wrap;">

            <div style="flex:2;min-width:200px;">
                <input type="text" name="search" class="crm-input"
                       placeholder="Search student, parent, phone or email..."
                       value="{{ request('search') }}">
            </div>

            <div style="flex:1;min-width:160px;">
                <select name="status" class="crm-input">
                    <option value="">All Stages</option>
                    @foreach($stages as $key => $label)
                        <option value="{{ $key }}" @selected(request('status') === $key)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="flex:1;min-width:150px;">
                <select name="assigned_to" class="crm-input">
                    <option value="">All Assignees</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string)request('assigned_to') === (string)$user->id)>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="crm-btn">Apply Filters</button>
            <a href="{{ route('admission-crm.index') }}" class="crm-btn">Reset</a>
        </form>

        <div style="overflow-x:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Student</th>
                        <th>Parent / Guardian</th>
                        <th>Contact</th>
                        <th>Grade</th>
                        <th>Source</th>
                        <th>Assigned To</th>
                        <th>Stage</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($leads as $lead)
                        @php
                            $color = $stageColors[$lead->status] ?? '#64748B';
                            $label = $stages[$lead->status] ?? ucfirst(str_replace('_', ' ', $lead->status));
                        @endphp

                        <tr>
                            <td style="font-weight:650;color:#64748B;">
                                {{ $lead->lead_number }}
                            </td>

                            <td>
                                <div style="font-weight:650;color:#123B5D;">
                                    {{ $lead->student_name }}
                                </div>
                                <div class="crm-muted">{{ $lead->email ?: 'No email' }}</div>
                            </td>

                            <td>{{ $lead->parent_name ?: '—' }}</td>
                            <td>{{ $lead->phone ?: '—' }}</td>
                            <td>{{ $lead->prospective_grade ?: '—' }}</td>
                            <td>{{ $lead->source ?: '—' }}</td>
                            <td>{{ $lead->assignedUser?->name ?: 'Unassigned' }}</td>

                            <td>
                                <span class="crm-status"
                                      style="background:{{ $color }}18;color:{{ $color }};">
                                    {{ $label }}
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('admission-crm.show', $lead) }}"
                                   class="crm-btn" style="height:32px;padding:0 10px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align:center;padding:45px 15px;">
                                <div style="font-size:15px;font-weight:650;color:#334155;">
                                    No admission leads found
                                </div>
                                <div class="crm-muted" style="margin-top:6px;">
                                    Add a new enquiry to start managing your admission pipeline.
                                </div>

                                @if(auth()->user()->hasPermission('ADMISSIONS.CREATE'))
                                    <a href="{{ route('admission-crm.create') }}"
                                       class="crm-btn crm-btn-primary" style="margin-top:15px;">
                                        Add First Lead
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($leads->hasPages())
            <div class="crm-pagination">
                {{ $leads->links() }}
            </div>
        @endif
    </div>
</div>
@endsection