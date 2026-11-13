
@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">

<style>
/* =========================================
   SCHOOLNEXA - EVENT DETAILS
========================================= */

.event-wrap {
    max-width: 1150px;
    margin: 0 auto;
}

.event-wrap > * + * {
    margin-top: 22px;
}

/* PAGE HEADER */

.event-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}

.event-heading {
    display: flex;
    align-items: center;
    gap: 13px;
}

.event-heading-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #EAF4F8;
    color: #176B87;
    border-radius: 12px;
}

.event-heading-icon svg {
    width: 23px;
    height: 23px;
}

.event-head .sn-title {
    margin: 0;
    font-size: 24px;
    font-weight: 750;
    color: #123B5D;
}

.event-head .sn-sub {
    margin-top: 5px;
    font-size: 13px;
    color: #64748B;
}

/* BUTTONS */

.sn-btn-custom,
.sn-btn-custom-primary {
    min-height: 50px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 20px;
    border-radius: 11px;
    font-family: Inter, sans-serif;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
    transition: all .2s ease;
}

.sn-btn-custom {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    color: #123B5D;
}

.sn-btn-custom:hover {
    background: #F0F9FF;
    border-color: #176B87;
    color: #176B87;
}

.sn-btn-custom-primary {
    background: #123B5D;
    border: 1px solid #123B5D;
    color: #FFFFFF;
}

.sn-btn-custom-primary:hover {
    background: #0E304C;
    border-color: #0E304C;
    color: #FFFFFF;
}

.sn-btn-custom:focus-visible,
.sn-btn-custom-primary:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(23,107,135,.15);
}

.event-actions {
    display: flex;
    gap: 9px;
    flex-wrap: wrap;
    align-items: center;
}

/* SUCCESS MESSAGE */

.event-success {
    padding: 13px 16px;
    margin-bottom: 20px;
    background: #DCFCE7;
    border: 1px solid #BBF7D0;
    border-radius: 10px;
    color: #166534;
    font-size: 12px;
}

/* COMMON CARDS */

.event-card {
    padding: 23px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(15,23,42,.035);
}

.event-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 22px;
}

.event-card-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 750;
    color: #123B5D;
}

.event-card-header p {
    margin: 5px 0 0;
    color: #64748B;
    font-size: 11px;
}

.event-id {
    display: inline-flex;
    padding: 6px 10px;
    background: #F1F5F9;
    border-radius: 7px;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

/* EVENT STATUS */

.event-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: capitalize;
    white-space: nowrap;
}

.event-status.planned {
    background: #FEF3C7;
    color: #92400E;
}

.event-status.ongoing {
    background: #DBEAFE;
    color: #1D4ED8;
}

.event-status.completed {
    background: #DCFCE7;
    color: #166534;
}

.event-status.cancelled {
    background: #FEE2E2;
    color: #991B1B;
}

/* REGISTRATION KPI CARDS */

.event-kpis {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.event-kpi {
    position: relative;
    overflow: hidden;
    padding: 21px;
    background: #FFFFFF;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    transition: transform .2s ease, box-shadow .2s ease;
}

.event-kpi:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15,23,42,.06);
}

.event-kpi-label {
    color: #64748B;
    font-size: 12px;
    font-weight: 650;
}

.event-kpi-value {
    margin-top: 10px;
    color: #123B5D;
    font-size: 30px;
    line-height: 1.2;
    font-weight: 800;
}

.event-kpi-icon {
    position: absolute;
    top: 18px;
    right: 18px;
    width: 39px;
    height: 39px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    background: #EAF4F8;
    color: #176B87;
}

.event-kpi-icon svg {
    width: 19px;
    height: 19px;
}

.event-kpi-caption {
    margin-top: 9px;
    color: #94A3B8;
    font-size: 11px;
}

/* EVENT DETAILS */

.event-detail-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
}

.event-detail {
    padding: 16px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    transition: border-color .2s, background .2s;
}

.event-detail:hover {
    border-color: #B9DCE9;
    background: #F5FAFC;
}

.event-detail-label {
    margin-bottom: 8px;
    color: #64748B;
    font-size: 10px;
    font-weight: 750;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.event-detail-value {
    color: #25313C;
    font-size: 13px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.event-detail-icon {
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-detail-icon svg {
    width: 17px;
    height: 17px;
    color: #176B87;
    flex-shrink: 0;
}

.event-description-heading {
    margin: 23px 0 11px;
    color: #123B5D;
    font-size: 14px;
    font-weight: 750;
}

.event-description {
    padding: 17px;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    color: #475569;
    font-size: 13px;
    line-height: 1.8;
    white-space: pre-line;
}

/* REGISTRATION TABLE */

.event-muted {
    margin-top: 4px;
    color: #64748B;
    font-size: 11px;
}

.event-registration-id {
    color: #123B5D;
    font-size: 12px;
    font-weight: 750;
}

.event-participant-type {
    display: inline-flex;
    padding: 5px 9px;
    border-radius: 20px;
    background: #EEF2FF;
    color: #4338A3;
    font-size: 10px;
    font-weight: 700;
}

.event-registration-status {
    display: inline-flex;
    padding: 5px 10px;
    border-radius: 20px;
    background: #F1F5F9;
    color: #475569;
    font-size: 10px;
    font-weight: 700;
    text-transform: capitalize;
}

.event-registration-status.registered,
.event-registration-status.approved,
.event-registration-status.confirmed {
    background: #DCFCE7;
    color: #166534;
}

.event-registration-status.pending {
    background: #FEF3C7;
    color: #92400E;
}

.event-registration-status.cancelled,
.event-registration-status.rejected {
    background: #FEE2E2;
    color: #991B1B;
}

/* RESPONSIVE */

@media(max-width: 768px) {
    .event-kpis {
        grid-template-columns: 1fr;
    }

    .event-detail-grid {
        grid-template-columns: 1fr;
    }
}

@media(max-width: 650px) {
    .event-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .event-card {
        padding: 17px;
    }

    .event-card-header {
        align-items: flex-start;
        flex-direction: column;
    }

    .event-kpi {
        padding: 18px;
    }

    .event-actions {
        width: 100%;
    }

    .event-actions a {
        flex: 1;
    }
}
</style>

<div class="event-wrap">

    {{-- SUCCESS MESSAGE --}}

    @if(session('success'))
        <div class="event-success">
            {{ session('success') }}
        </div>
    @endif


    {{-- PAGE HEADER --}}

    <div class="sn-head event-head">

        <div class="event-heading">

            <div class="event-heading-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>

            <div>
                <h1 class="sn-title">Event Details</h1>
                <div class="sn-sub">
                    View event information and participant registration details.
                </div>
            </div>

        </div>

        <div class="sn-actions event-actions">

            <a href="{{ route('events.index') }}"
               class="sn-btn-custom">
                Back to Events
            </a>

            <a href="{{ route('events.edit', $record->id) }}"
               class="sn-btn-custom-primary">
                Edit Event
            </a>

        </div>

    </div>


    {{-- REGISTRATION KPI CARDS - TOP OF PAGE --}}

    <div class="event-kpis">

        {{-- TOTAL REGISTRATIONS --}}

        <div class="event-kpi">

            <div class="event-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="10" cy="7" r="4"/>
                    <path d="M20 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>

            <div class="event-kpi-label">
                Total Registrations
            </div>

            <div class="event-kpi-value">
                {{ number_format($registrationCount) }}
            </div>

            <div class="event-kpi-caption">
                Total participants registered for this event
            </div>

        </div>


        {{-- STUDENT REGISTRATIONS --}}

        <div class="event-kpi">

            <div class="event-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="10" cy="7" r="4"/>
                </svg>
            </div>

            <div class="event-kpi-label">
                Student Registrations
            </div>

            <div class="event-kpi-value">
                {{ number_format($studentRegistrations) }}
            </div>

            <div class="event-kpi-caption">
                Students registered for this event
            </div>

        </div>


        {{-- STAFF REGISTRATIONS --}}

        <div class="event-kpi">

            <div class="event-kpi-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="7" width="18" height="14" rx="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>

            <div class="event-kpi-label">
                Staff Registrations
            </div>

            <div class="event-kpi-value">
                {{ number_format($staffRegistrations) }}
            </div>

            <div class="event-kpi-caption">
                Staff members registered for this event
            </div>

        </div>

    </div>


    {{-- EVENT OVERVIEW --}}

    <div class="event-card">

        <div class="event-card-header">

            <div>
                <h2>{{ $record->title }}</h2>
                <p>Event ID: #{{ $record->id }}</p>
            </div>

            <span class="event-status {{ strtolower($record->status) }}">
                {{ ucfirst($record->status) }}
            </span>

        </div>

        <div class="event-detail-grid">

            <div class="event-detail">
                <div class="event-detail-label">Event Category</div>
                <div class="event-detail-value">
                    {{ $record->event_type ?: 'General' }}
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label">Campus</div>
                <div class="event-detail-value">
                    {{ $campus->name ?? 'All Campuses' }}
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label">Start Date & Time</div>
                <div class="event-detail-value event-detail-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="4" width="18" height="17" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>

                    {{ \Carbon\Carbon::parse($record->starts_at)->format('d F Y, h:i A') }}
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label">End Date & Time</div>
                <div class="event-detail-value event-detail-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>

                    {{ $record->ends_at
                        ? \Carbon\Carbon::parse($record->ends_at)->format('d F Y, h:i A')
                        : 'Not specified' }}
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label">Venue</div>
                <div class="event-detail-value">
                    {{ $record->venue ?: 'Not specified' }}
                </div>
            </div>

            <div class="event-detail">
                <div class="event-detail-label">Created On</div>
                <div class="event-detail-value">
                    {{ $record->created_at
                        ? \Carbon\Carbon::parse($record->created_at)->format('d F Y, h:i A')
                        : '—' }}
                </div>
            </div>

        </div>

        <h3 class="event-description-heading">
            Event Description
        </h3>

        <div class="event-description">
            {{ $record->description ?: 'No description has been added for this event.' }}
        </div>

    </div>


    {{-- PARTICIPANT REGISTRATIONS --}}

    <div class="event-card">

        <div class="event-card-header">

            <div>
                <h2>Participant Registrations</h2>
                <p>Latest registration records for this event.</p>
            </div>

            <span class="event-id">
                {{ number_format($registrationCount) }} Registrations
            </span>

        </div>

        <div class="sn-table-wrap">

            <table class="sn-table">

                <thead>
                    <tr>
                        <th>REGISTRATION ID</th>
                        <th>PARTICIPANT TYPE</th>
                        <th>PARTICIPANT ID</th>
                        <th>REGISTRATION DATE</th>
                        <th>STATUS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($registrations as $registration)

                        <tr>

                            <td>
                                <span class="event-registration-id">
                                    #{{ $registration->id }}
                                </span>
                            </td>

                            <td>
                                <span class="event-participant-type">
                                    {{ $registration->student_id
                                        ? 'Student'
                                        : ($registration->staff_profile_id ? 'Staff' : 'Unknown') }}
                                </span>
                            </td>

                            <td>
                                {{ $registration->student_id
                                    ?? $registration->staff_profile_id
                                    ?? '—' }}
                            </td>

                            <td>
                                {{ $registration->registered_at
                                    ? \Carbon\Carbon::parse($registration->registered_at)->format('d M Y, h:i A')
                                    : '—' }}
                            </td>

                            <td>
                                <span class="event-registration-status {{ strtolower($registration->registration_status) }}">
                                    {{ ucfirst($registration->registration_status) }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="sn-empty">
                                No participant registrations have been recorded for this event.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>
</div>

@endsection
