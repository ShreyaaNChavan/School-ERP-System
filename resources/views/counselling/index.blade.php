
@extends('layouts.schoolnexa')

@section('content')

<style>
    .counselling-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .counselling-page * {
        box-sizing: border-box;
    }

    .c-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 22px;
    }

    .c-head h1 {
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 5px;
        color: #123B5D;
    }

    .c-head p {
        margin: 0;
        color: #64748B;
        font-size: 13px;
    }

    .c-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #fff;
        color: #123B5D;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .c-btn:hover {
        background: #F8FAFC;
        color: #123B5D;
    }

    .c-btn-primary {
        background: #123B5D;
        border-color: #123B5D;
        color: white;
    }

    .c-btn-primary:hover {
        background: #176B87;
        color: white;
    }

    .c-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .c-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 18px;
        min-width: 0;
    }

    .c-kpi-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .c-kpi-value {
        color: #123B5D;
        font-size: 27px;
        font-weight: 700;
        line-height: 1.2;
    }

    .c-kpi-bottom {
        margin-top: 10px;
        font-size: 11px;
        color: #94A3B8;
    }

    .c-kpi-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        font-size: 17px;
        margin-bottom: 14px;
        background: #EFF6FF;
        color: #3B82F6;
    }

    .c-main-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.65fr) minmax(280px, 1fr);
        gap: 16px;
        margin-bottom: 18px;
    }

    .c-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0;
    }

    .c-section-subtitle {
        color: #94A3B8;
        font-size: 11px;
        margin-top: 5px;
    }

    .c-chart {
        height: 210px;
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        gap: 12px;
        padding: 22px 8px 0;
        border-bottom: 1px solid #E2E8F0;
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 48px,
            #F1F5F9 49px,
            transparent 50px
        );
    }

    .c-chart-item {
        flex: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        min-width: 0;
    }

    .c-chart-bar {
        width: min(34px, 75%);
        min-height: 3px;
        background: #176B87;
        border-radius: 6px 6px 0 0;
        transition: height .2s ease;
    }

    .c-chart-bar:hover {
        background: #3B82F6;
    }

    .c-chart-month {
        color: #64748B;
        font-size: 10px;
        padding-bottom: 8px;
    }

    .c-type-list {
        display: flex;
        flex-direction: column;
        gap: 17px;
        margin-top: 23px;
    }

    .c-type-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        font-size: 12px;
        margin-bottom: 7px;
    }

    .c-type-name {
        color: #475569;
        font-weight: 600;
    }

    .c-type-count {
        color: #123B5D;
        font-weight: 700;
    }

    .c-progress {
        height: 7px;
        border-radius: 10px;
        background: #F1F5F9;
        overflow: hidden;
    }

    .c-progress-fill {
        height: 100%;
        background: #176B87;
        border-radius: 10px;
    }

    .c-upcoming-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
        margin-top: 20px;
    }

    .c-upcoming-item {
        display: flex;
        gap: 11px;
        padding-bottom: 13px;
        border-bottom: 1px solid #F1F5F9;
    }

    .c-upcoming-item:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .c-date-box {
        min-width: 43px;
        height: 46px;
        background: #EFF6FF;
        border-radius: 9px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: #176B87;
    }

    .c-date-box strong {
        font-size: 16px;
        line-height: 18px;
    }

    .c-date-box span {
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .c-upcoming-info {
        min-width: 0;
        flex: 1;
    }

    .c-upcoming-info strong {
        display: block;
        font-size: 12px;
        color: #25313C;
        margin-bottom: 4px;
    }

    .c-upcoming-info span {
        font-size: 11px;
        color: #64748B;
        display: block;
        margin-bottom: 3px;
    }

    .c-table-wrap {
        overflow-x: auto;
        margin-top: 18px;
    }

    .c-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    .c-table th {
        text-align: left;
        padding: 12px 13px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 11px;
        font-weight: 700;
        border-bottom: 1px solid #E2E8F0;
        white-space: nowrap;
    }

    .c-table td {
        padding: 13px;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
        color: #475569;
        vertical-align: middle;
    }

    .c-table tr:last-child td {
        border-bottom: 0;
    }

    .c-person {
        font-weight: 650;
        color: #25313C;
    }

    .c-muted {
        font-size: 11px;
        color: #94A3B8;
        margin-top: 4px;
    }

    .c-badge {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
        white-space: nowrap;
    }

    .c-status-open {
        background: #FEF3C7;
        color: #92400E;
    }

    .c-status-completed {
        background: #DCFCE7;
        color: #166534;
    }

    .c-status-cancelled {
        background: #FEE2E2;
        color: #991B1B;
    }

    /* FINALIZED SCHOOLNEXA TABLE ACTION BUTTONS */
    .c-table-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
    }

    .c-table-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 50px;
        padding: 0 16px;
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        background: #FFFFFF;
        color: #123B5D;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s ease;
    }

    .c-table-action:hover {
        background: #F8FAFC;
        color: #123B5D;
        text-decoration: none;
    }

    .c-filter {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }

    .c-filter input,
    .c-filter select {
        height: 38px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 0 10px;
        font-family: inherit;
        font-size: 12px;
        color: #334155;
        background: #fff;
        outline: none;
    }

    .c-filter input {
        width: 205px;
    }

    .c-empty {
        text-align: center;
        padding: 35px 15px;
        color: #94A3B8;
        font-size: 12px;
    }

    .c-pagination {
        padding-top: 16px;
    }

    .c-pagination nav p {
        font-size: 12px;
        color: #64748B;
    }

    @media(max-width: 1050px) {
        .c-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .c-main-grid {
            grid-template-columns: 1fr;
        }
    }

    @media(max-width: 600px) {
        .c-head {
            align-items: flex-start;
            flex-direction: column;
        }

        .c-kpi-grid {
            gap: 10px;
        }

        .c-card {
            padding: 14px;
        }

        .c-kpi-value {
            font-size: 23px;
        }

        .c-filter {
            width: 100%;
        }

        .c-filter input,
        .c-filter select {
            width: 100%;
        }

        .c-table-action {
            min-height: 42px;
            padding: 0 12px;
            font-size: 13px;
        }
    }
</style>

<div class="counselling-page">

    {{-- PAGE HEADER --}}
    <div class="c-head">
        <div>
            <h1>Counselling Management</h1>
            <p>Manage counselling sessions, student support and follow-ups.</p>
        </div>

        <a href="{{ route('counselling.create') }}" class="c-btn c-btn-primary">
            <span>＋</span> Schedule Session
        </a>
    </div>

    {{-- KPI CARDS --}}
    <div class="c-kpi-grid">

        <div class="c-card">
            <div class="c-kpi-icon">▤</div>
            <div class="c-kpi-label">Total Sessions</div>
            <div class="c-kpi-value">{{ number_format($stats['total']) }}</div>
            <div class="c-kpi-bottom">All counselling records</div>
        </div>

        <div class="c-card">
            <div class="c-kpi-icon" style="background:#EFF6FF;color:#3B82F6;">◷</div>
            <div class="c-kpi-label">Scheduled Sessions</div>
            <div class="c-kpi-value">{{ number_format($stats['scheduled']) }}</div>
            <div class="c-kpi-bottom">Upcoming open sessions</div>
        </div>

        <div class="c-card">
            <div class="c-kpi-icon" style="background:#DCFCE7;color:#16A34A;">✓</div>
            <div class="c-kpi-label">Completed Sessions</div>
            <div class="c-kpi-value">{{ number_format($stats['completed']) }}</div>
            <div class="c-kpi-bottom">Successfully completed</div>
        </div>

        <div class="c-card">
            <div class="c-kpi-icon" style="background:#FEF3C7;color:#D97706;">!</div>
            <div class="c-kpi-label">Follow-ups Due</div>
            <div class="c-kpi-value">{{ number_format($stats['followups_due']) }}</div>
            <div class="c-kpi-bottom">Require attention</div>
        </div>

    </div>

    {{-- ANALYTICS --}}
    <div class="c-main-grid">

        <div class="c-card">
            <div>
                <h3 class="c-section-title">Counselling Overview</h3>
                <div class="c-section-subtitle">
                    Monthly counselling sessions — {{ now()->year }}
                </div>
            </div>

            @php
                $monthLabels = [
                    1 => 'Jan', 2 => 'Feb', 3 => 'Mar',
                    4 => 'Apr', 5 => 'May', 6 => 'Jun',
                    7 => 'Jul', 8 => 'Aug', 9 => 'Sep',
                    10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
                ];

                $chartValues = [];

                foreach (range(1, 12) as $month) {
                    $chartValues[$month] = $monthlySessions->get($month)?->total ?? 0;
                }

                $chartMax = max(1, max($chartValues));
            @endphp

            <div class="c-chart">
                @foreach($chartValues as $month => $value)
                    <div class="c-chart-item">
                        <div
                            class="c-chart-bar"
                            style="height:{{ max(2, ($value / $chartMax) * 145) }}px;"
                            title="{{ $value }} sessions"
                        ></div>

                        <div class="c-chart-month">
                            {{ $monthLabels[$month] }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="c-section-subtitle" style="margin-top:12px;">
                Total sessions this year:
                <strong style="color:#123B5D;">
                    {{ array_sum($chartValues) }}
                </strong>
            </div>
        </div>

        <div class="c-card">
            <h3 class="c-section-title">Session Type Breakdown</h3>
            <div class="c-section-subtitle">
                Distribution by counselling category
            </div>

            @php
                $typeTotal = max(1, $sessionTypes->sum('total'));
            @endphp

            <div class="c-type-list">
                @forelse($sessionTypes as $type)
                    <div>
                        <div class="c-type-row">
                            <span class="c-type-name">
                                {{ ucwords(str_replace('_', ' ', $type->session_type ?: 'Other')) }}
                            </span>

                            <span class="c-type-count">{{ $type->total }}</span>
                        </div>

                        <div class="c-progress">
                            <div
                                class="c-progress-fill"
                                style="width:{{ ($type->total / $typeTotal) * 100 }}%;"
                            ></div>
                        </div>
                    </div>
                @empty
                    <div class="c-empty">No counselling sessions recorded yet.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- UPCOMING SESSIONS --}}
    <div class="c-card" style="margin-bottom:18px;">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
            <div>
                <h3 class="c-section-title">Upcoming Sessions</h3>
                <div class="c-section-subtitle">
                    Your next scheduled counselling sessions
                </div>
            </div>

            <a href="{{ route('counselling.index') }}"
               class="c-action-link"
               style="font-size:12px;color:#176B87;text-decoration:none;font-weight:700;">
                View all
            </a>
        </div>

        <div class="c-upcoming-list">

            @forelse($upcomingSessions as $session)

                @php
                    $personName = $session->student?->full_name
                        ?? $session->admissionApplication?->applicant_name
                        ?? 'Unknown applicant';
                @endphp

                <div class="c-upcoming-item">

                    <div class="c-date-box">
                        <strong>{{ $session->session_at->format('d') }}</strong>
                        <span>{{ $session->session_at->format('M') }}</span>
                    </div>

                    <div class="c-upcoming-info">
                        <strong>{{ $personName }}</strong>

                        <span>
                            {{ ucwords(str_replace('_', ' ', $session->session_type)) }}
                            · {{ $session->session_at->format('h:i A') }}
                        </span>

                        <span>
                            Counsellor: {{ $session->counsellor?->name ?? 'Not assigned' }}
                        </span>
                    </div>

                    <a href="{{ route('counselling.show', $session) }}"
                       class="c-action-link"
                       style="font-size:11px;align-self:center;color:#176B87;text-decoration:none;font-weight:700;">
                        View
                    </a>

                </div>

            @empty
                <div class="c-empty">No upcoming counselling sessions.</div>
            @endforelse

        </div>
    </div>

    {{-- COUNSELLING HISTORY --}}
    <div class="c-card">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;">

            <div>
                <h3 class="c-section-title">Counselling Sessions</h3>
                <div class="c-section-subtitle">
                    View and manage all counselling records
                </div>
            </div>

            <form method="GET"
                  action="{{ route('counselling.index') }}"
                  class="c-filter">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search student or counsellor..."
                >

                <select name="status">
                    <option value="">All Statuses</option>
                    <option value="open" @selected(request('status') === 'open')>Open</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                </select>

                <select name="session_type">
                    <option value="">All Types</option>

                    @foreach($sessionTypes as $type)
                        <option value="{{ $type->session_type }}"
                            @selected(request('session_type') === $type->session_type)>
                            {{ ucwords(str_replace('_', ' ', $type->session_type)) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="c-btn">Filter</button>

                <a href="{{ route('counselling.index') }}" class="c-btn">Reset</a>

            </form>

        </div>

        <div class="c-table-wrap">
            <table class="c-table">

                <thead>
                    <tr>
                        <th>SESSION ID</th>
                        <th>STUDENT / APPLICANT</th>
                        <th>COUNSELLOR</th>
                        <th>SESSION DATE</th>
                        <th>SESSION TYPE</th>
                        <th>STATUS</th>
                        <th>ACTION</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($sessions as $session)

                        @php
                            $personName = $session->student?->full_name
                                ?? $session->admissionApplication?->applicant_name
                                ?? 'Unknown applicant';
                        @endphp

                        <tr>

                            <td>
                                <span class="c-person">
                                    {{ $session->session_number }}
                                </span>
                            </td>

                            <td>
                                <div class="c-person">{{ $personName }}</div>

                                <div class="c-muted">
                                    {{ $session->student?->admission_number
                                        ?? $session->admissionApplication?->application_number
                                        ?? '—' }}
                                </div>
                            </td>

                            <td>
                                {{ $session->counsellor?->name ?? 'Not assigned' }}
                            </td>

                            <td>
                                {{ $session->session_at->format('d M Y') }}

                                <div class="c-muted">
                                    {{ $session->session_at->format('h:i A') }}
                                </div>
                            </td>

                            <td>
                                {{ ucwords(str_replace('_', ' ', $session->session_type)) }}
                            </td>

                            <td>
                                <span class="c-badge c-status-{{ $session->status }}">
                                    {{ $session->status }}
                                </span>
                            </td>

                            {{-- FINALIZED SCHOOLNEXA ACTION BUTTONS --}}
                            <td>
                                <div class="c-table-actions">

                                    <a href="{{ route('counselling.show', $session) }}"
                                       class="c-table-action">
                                        View
                                    </a>

                                    <a href="{{ route('counselling.edit', $session) }}"
                                       class="c-table-action">
                                        Edit
                                    </a>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7">
                                <div class="c-empty">
                                    No counselling sessions found.
                                    Schedule your first session to get started.
                                </div>
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

        <div class="c-pagination">
            {{ $sessions->links() }}
        </div>

    </div>

</div>

@endsection
