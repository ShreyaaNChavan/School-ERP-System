@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">

    <style>
        /* =====================================
           SCHOOLNEXA - EVENTS DIRECTORY
        ===================================== */

        .event-wrap {
            max-width: 1250px;
            margin: 0 auto;
        }

        .event-wrap > * + * {
            margin-top: 22px;
        }

        /* HEADER */
        .event-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .event-heading {
            display: flex;
            align-items: center;
            gap: 14px;
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
            border-radius: 13px;
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
            letter-spacing: -.5px;
        }

        .event-head .sn-sub {
            margin-top: 5px;
            color: #64748B;
            font-size: 13px;
        }

        .event-header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .event-header-actions .sn-btn {
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            transition: background-color .15s, border-color .15s;
        }

        /* Events Dashboard: white, light border, dark bold text */
        .event-header-actions .sn-btn-outline {
            background: #fff;
            border: 1px solid #CBD5E1;
            color: #25313C;
        }

        .event-header-actions .sn-btn-outline:hover {
            background: rgba(23,107,135,.06);
            border-color: #176B87;
        }

        .event-header-actions .sn-btn-outline:focus-visible {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23,107,135,.10);
        }

        /* SUCCESS ALERT */
        .event-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 16px;
            background: #ECFDF3;
            border: 1px solid #BBF7D0;
            border-radius: 10px;
            color: #166534;
            font-size: 12px;
            font-weight: 600;
        }

        .event-success svg {
            width: 17px;
            height: 17px;
            flex-shrink: 0;
        }

        /* STATISTICS */
        .event-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .event-stat {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 19px;
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(15,23,42,.025);
            min-width: 0;
        }

        .event-stat-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 11px;
            flex-shrink: 0;
        }

        .event-stat-icon svg {
            width: 21px;
            height: 21px;
        }

        .event-stat-icon.blue {
            color: #176B87;
            background: #EAF4F8;
        }

        .event-stat-icon.purple {
            color: #5B3FA3;
            background: #F0EDFF;
        }

        .event-stat-icon.green {
            color: #15803D;
            background: #ECFDF3;
        }

        .event-stat-label {
            color: #64748B;
            font-size: 11px;
            font-weight: 600;
        }

        .event-stat-value {
            margin-top: 4px;
            color: #123B5D;
            font-size: 23px;
            font-weight: 750;
            line-height: 1.2;
        }

        /* CARDS */
        .event-card {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 13px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(15,23,42,.025);
        }

        /* FILTER HEADER */
        .event-filter-head {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 19px 22px;
            border-bottom: 1px solid #E8EDF3;
            background: #FCFDFE;
        }

        .event-filter-icon {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #176B87;
            background: #EAF4F8;
            border-radius: 9px;
            flex-shrink: 0;
        }

        .event-filter-icon svg {
            width: 18px;
            height: 18px;
        }

        .event-filter-head h2 {
            margin: 0;
            color: #123B5D;
            font-size: 14px;
            font-weight: 750;
        }

        .event-filter-head p {
            margin: 4px 0 0;
            color: #64748B;
            font-size: 11px;
        }

        /* FILTER FORM */
        .event-filter-body {
            padding: 20px 22px;
        }

        .event-filter {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) minmax(150px, 190px) minmax(140px, 170px) auto;
            gap: 14px;
            align-items: end;
        }

        .event-field {
            min-width: 0;
        }

        .event-field label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }

        .event-input-wrap {
            position: relative;
        }

        .event-input-wrap svg {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            width: 15px;
            height: 15px;
            color: #94A3B8;
            pointer-events: none;
        }

        .event-field input,
        .event-field select {
            width: 100%;
            height: 43px;
            box-sizing: border-box;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            padding: 0 12px;
            font: 12px Inter, sans-serif;
            color: #25313C;
            background: #fff;
            transition: border-color .2s, box-shadow .2s;
        }

        .event-field input {
            padding-left: 38px;
        }

        .event-field select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 32px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
        }

        .event-field input:focus,
        .event-field select:focus {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23,107,135,.10);
        }

        .event-field input::placeholder {
            color: #94A3B8;
        }

        .event-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .event-filter-actions .sn-btn {
            height: 43px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0 14px;
            border-radius: 9px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
            box-sizing: border-box;
            transition: all .2s ease;
        }

        .event-filter-actions svg {
            width: 14px;
            height: 14px;
        }

        /* TABLE HEADER */
        .event-table-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 21px 22px;
        }

        .event-table-title {
            margin: 0;
            color: #123B5D;
            font-size: 15px;
            font-weight: 750;
        }

        .event-table-sub {
            margin-top: 5px;
            color: #64748B;
            font-size: 11px;
        }

        .event-count {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 11px;
            border: 1px solid #BAE6FD;
            border-radius: 20px;
            background: #F0F9FF;
            color: #0369A1;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
        }

        .event-count svg {
            width: 13px;
            height: 13px;
        }

        /* TABLE */
        .event-table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .event-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }

        .event-table thead {
            background: #F8FAFC;
        }

        .event-table th {
            padding: 13px 17px;
            border-top: 1px solid #E8EDF3;
            border-bottom: 1px solid #E8EDF3;
            color: #64748B;
            font-size: 10px;
            font-weight: 750;
            letter-spacing: .45px;
        }

        .event-table td {
            padding: 16px 17px;
            border-bottom: 1px solid #F1F5F9;
            color: #475569;
            font-size: 12px;
            vertical-align: middle;
        }

        .event-table tbody tr {
            transition: background .15s ease;
        }

        .event-table tbody tr:hover {
            background: #F8FBFD;
        }

        .event-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* EVENT DETAILS */
        .event-title {
            color: #123B5D;
            font-size: 12px;
            font-weight: 750;
        }

        .event-muted {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            color: #94A3B8;
            font-size: 10px;
        }

        /* CATEGORY */
        .event-type {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid #E0E7FF;
            border-radius: 20px;
            background: #EEF2FF;
            color: #4338A3;
            font-size: 10px;
            font-weight: 700;
        }

        /* DATE */
        .event-date {
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }

        .event-time {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            color: #94A3B8;
            font-size: 10px;
        }

        /* VENUE */
        .event-venue {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #475569;
            font-size: 11px;
        }

        .event-venue svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
            color: #94A3B8;
        }

        /* STATUS */
        .event-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .event-status::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
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

        /* =====================================
           ACTION BUTTONS
           Large outlined button style
        ===================================== */

        .event-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            white-space: nowrap;
        }

        .event-actions form {
            margin: 0;
        }

        .event-action-btn {
            min-height: 48px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            border: 1px solid #CBD5E1;
            border-radius: 11px;
            background: #fff;
            color: #25313C;
            font-family: Inter, sans-serif;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
            box-sizing: border-box;
            transition: background .2s, border-color .2s, color .2s;
        }

        .event-action-btn svg {
            display: none;
        }

        .event-action-btn:hover {
            background: #F8FAFC;
            border-color: #176B87;
            color: #176B87;
        }

        .event-action-btn:focus-visible {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23,107,135,.12);
        }

        .event-action-btn.delete {
            color: #DC2626;
            border-color: #CBD5E1;
            background: #fff;
        }

        .event-action-btn.delete:hover {
            background: #FEF2F2;
            border-color: #FECACA;
            color: #DC2626;
        }

        /* EMPTY STATE */
        .event-empty {
            padding: 55px 20px !important;
            text-align: center;
            white-space: normal;
        }

        .event-empty-icon {
            width: 56px;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            border-radius: 15px;
            background: #F1F5F9;
            color: #94A3B8;
        }

        .event-empty-icon svg {
            width: 25px;
            height: 25px;
        }

        .event-empty strong {
            display: block;
            color: #334155;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .event-empty p {
            margin: 0;
            color: #94A3B8;
            font-size: 11px;
            line-height: 1.6;
        }

        .event-empty a {
            display: inline-flex;
            margin-top: 15px;
            color: #176B87;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        /* PAGINATION */
        .event-pagination {
            padding: 16px 22px;
            border-top: 1px solid #E8EDF3;
            background: #FCFDFE;
        }

        .event-pagination nav {
            display: flex;
            justify-content: flex-end;
        }

        .event-pagination svg {
            width: 16px;
            height: 16px;
        }

        /* RESPONSIVE */
        @media(max-width: 1100px) {
            .event-filter {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .event-filter-actions {
                grid-column: 1 / -1;
            }
        }

        @media(max-width: 650px) {
            .event-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .event-header-actions {
                width: 100%;
            }

            .event-header-actions .sn-btn {
                flex: 1;
            }

            .event-stats {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .event-stat {
                padding: 14px 16px;
            }

            .event-stat-value {
                font-size: 20px;
            }

            .event-filter {
                grid-template-columns: 1fr;
            }

            .event-filter-actions {
                grid-column: auto;
            }

            .event-filter-actions .sn-btn {
                flex: 1;
            }

            .event-filter-body {
                padding: 17px;
            }

            .event-filter-head,
            .event-table-head {
                padding: 17px;
            }

            .event-table th,
            .event-table td {
                padding: 13px 14px;
            }

            .event-actions {
                gap: 7px;
            }

            .event-action-btn {
                min-height: 42px;
                padding: 0 12px;
                font-size: 12px;
            }
        }
    </style>

    <div class="event-wrap">

        {{-- SUCCESS MESSAGE --}}
        @if(session('success'))
            <div class="event-success">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <path d="m9 11 3 3L22 4"/>
                </svg>

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
                        <path d="m9 16 2 2 4-4"/>
                    </svg>
                </div>

                <div>
                    <h1 class="sn-title">Events</h1>
                    <div class="sn-sub">
                        View, search and manage all school events and activities.
                    </div>
                </div>
            </div>

            <div class="event-header-actions">

                <a href="{{ route('events.dashboard') }}"
                   class="sn-btn sn-btn-outline">
                    Events Dashboard
                </a>

                <a href="{{ route('events.create') }}"
                   class="sn-btn sn-btn-primary">
                    Create Event
                </a>

            </div>
        </div>

        {{-- SUMMARY CARDS --}}
        <div class="event-stats">

            <div class="event-stat">
                <div class="event-stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="17" rx="2"/>
                        <path d="M16 2v4M8 2v4M3 10h18"/>
                    </svg>
                </div>

                <div>
                    <div class="event-stat-label">Total Events</div>
                    <div class="event-stat-value">
                        {{ number_format($events->total()) }}
                    </div>
                </div>
            </div>

            <div class="event-stat">
                <div class="event-stat-icon purple">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 4h16v16H4z"/>
                        <path d="M8 9h8M8 13h8M8 17h5"/>
                    </svg>
                </div>

                <div>
                    <div class="event-stat-label">Displayed Events</div>
                    <div class="event-stat-value">
                        {{ number_format($events->count()) }}
                    </div>
                </div>
            </div>

            <div class="event-stat">
                <div class="event-stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/>
                        <path d="M2 14h4M10 8h4M18 16h4"/>
                    </svg>
                </div>

                <div>
                    <div class="event-stat-label">Active Filters</div>
                    <div class="event-stat-value">
                        {{ (request()->filled('search') ? 1 : 0)
                            + (request()->filled('type') ? 1 : 0)
                            + (request()->filled('status') ? 1 : 0) }}
                    </div>
                </div>
            </div>

        </div>

        {{-- SEARCH AND FILTERS --}}
        <div class="event-card">

            <div class="event-filter-head">

                <div class="event-filter-icon">
                    <svg viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3"/>
                        <path d="M2 14h4M10 8h4M18 16h4"/>
                    </svg>
                </div>

                <div>
                    <h2>Search & Filters</h2>
                    <p>Find events using title, category or event status.</p>
                </div>

            </div>

            <div class="event-filter-body">

                <form method="GET"
                      action="{{ route('events.index') }}"
                      class="event-filter">

                    <div class="event-field">
                        <label for="search">Search Events</label>

                        <div class="event-input-wrap">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>

                            <input
                                type="text"
                                id="search"
                                name="search"
                                value="{{ request('search') }}"
                                placeholder="Search title, venue or description..."
                            >
                        </div>
                    </div>

                    <div class="event-field">
                        <label for="type">Event Category</label>

                        <select name="type" id="type">
                            <option value="">All Categories</option>

                            @foreach($types as $type)
                                <option value="{{ $type }}"
                                    @selected(request('type') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="event-field">
                        <label for="status">Event Status</label>

                        <select name="status" id="status">
                            <option value="">All Statuses</option>

                            @foreach($statuses as $status)
                                <option value="{{ $status }}"
                                    @selected(request('status') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="event-filter-actions">

                        <button type="submit" class="sn-btn sn-btn-primary">
                            <svg viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round">
                                <circle cx="11" cy="11" r="8"/>
                                <path d="m21 21-4.35-4.35"/>
                            </svg>
                            Apply
                        </button>

                        <a href="{{ route('events.index') }}"
                           class="sn-btn sn-btn-outline">
                            Reset
                        </a>

                    </div>

                </form>

            </div>
        </div>

        {{-- EVENT DIRECTORY --}}
        <div class="event-card">

            <div class="event-table-head">

                <div>
                    <h2 class="event-table-title">Event Directory</h2>
                    <div class="event-table-sub">
                        Manage scheduled and completed school events.
                    </div>
                </div>

                <div class="event-count">
                    {{ number_format($events->total()) }} Events
                </div>

            </div>

            <div class="event-table-wrap">

                <table class="event-table">

                    <thead>
                        <tr>
                            <th>EVENT DETAILS</th>
                            <th>CATEGORY</th>
                            <th>DATE & TIME</th>
                            <th>VENUE</th>
                            <th>CAMPUS</th>
                            <th>STATUS</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($events as $event)

                            <tr>

                                {{-- EVENT DETAILS --}}
                                <td>
                                    <div class="event-title">
                                        {{ $event->title }}
                                    </div>

                                    <div class="event-muted">
                                        Event ID: #{{ $event->id }}
                                    </div>
                                </td>

                                {{-- CATEGORY --}}
                                <td>
                                    <span class="event-type">
                                        {{ $event->event_type ?: 'General' }}
                                    </span>
                                </td>

                                {{-- DATE AND TIME --}}
                                <td>
                                    <div class="event-date">
                                        {{ \Carbon\Carbon::parse($event->starts_at)->format('d M Y') }}
                                    </div>

                                    <div class="event-time">
                                        {{ \Carbon\Carbon::parse($event->starts_at)->format('h:i A') }}

                                        @if($event->ends_at)
                                            – {{ \Carbon\Carbon::parse($event->ends_at)->format('h:i A') }}
                                        @endif
                                    </div>
                                </td>

                                {{-- VENUE --}}
                                <td>
                                    <div class="event-venue">
                                        <svg viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.8"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
                                            <circle cx="12" cy="10" r="2.5"/>
                                        </svg>

                                        {{ $event->venue ?: '—' }}
                                    </div>
                                </td>

                                {{-- CAMPUS --}}
                                <td>
                                    {{ $event->campus_name ?: 'All Campuses' }}
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    <span class="event-status {{ strtolower($event->status) }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>

                                {{-- ACTIONS --}}
                                <td>
                                    <div class="event-actions">

                                        <a href="{{ route('events.show', $event->id) }}"
                                           class="event-action-btn">
                                            View
                                        </a>

                                        <a href="{{ route('events.edit', $event->id) }}"
                                           class="event-action-btn">
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('events.destroy', $event->id) }}"
                                            onsubmit="return confirm('Are you sure you want to delete this event? Its registrations will also be deleted.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="event-action-btn delete">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="event-empty">

                                    <div class="event-empty-icon">
                                        <svg viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="1.8"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="4" width="18" height="17" rx="2"/>
                                            <path d="M16 2v4M8 2v4M3 10h18"/>
                                            <path d="m9 15 2 2 4-4"/>
                                        </svg>
                                    </div>

                                    <strong>No events found</strong>

                                    <p>
                                        No matching events are available.
                                        Adjust your filters or create your first school event.
                                    </p>

                                    <a href="{{ route('events.create') }}">
                                        + Create your first event
                                    </a>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- PAGINATION --}}
            @if($events->hasPages())
                <div class="event-pagination">
                    {{ $events->appends(request()->query())->links() }}
                </div>
            @endif

        </div>

    </div>
</div>
@endsection