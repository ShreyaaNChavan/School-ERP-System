@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">

<style>
/* =========================================
   SCHOOLNEXA - CREATE EVENT
========================================= */

.event-wrap {
    max-width: 1100px;
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

.event-heading-icon,
.event-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: #EAF4F8;
    color: #176B87;
    border-radius: 11px;
}

.event-heading-icon {
    width: 48px;
    height: 48px;
}

.event-heading-icon svg {
    width: 23px;
    height: 23px;
}

.event-head .sn-title {
    margin: 0;
    color: #123B5D;
    font-size: 24px;
    font-weight: 750;
    letter-spacing: -.5px;
}

.event-head .sn-sub {
    margin-top: 5px;
    color: #64748B;
    font-size: 13px;
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
    border: 1px solid #CBD5E1;
    background: #fff;
    color: #123B5D;
}

.sn-btn-custom:hover {
    background: #F0F9FF;
    border-color: #176B87;
    color: #176B87;
}

.sn-btn-custom-primary {
    border: 1px solid #123B5D;
    background: #123B5D;
    color: #fff;
}

.sn-btn-custom-primary:hover {
    background: #0E304C;
    border-color: #0E304C;
    color: #fff;
}

.sn-btn-custom:focus-visible,
.sn-btn-custom-primary:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px rgba(23,107,135,.15);
}

/* INFORMATION PANEL */

.event-info {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 17px 19px;
    background: #F0F9FF;
    border: 1px solid #BAE6FD;
    border-radius: 11px;
}

.event-info-icon {
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 9px;
    background: #DFF3FC;
    color: #0369A1;
}

.event-info-icon svg {
    width: 18px;
    height: 18px;
}

.event-info strong {
    display: block;
    margin-bottom: 5px;
    color: #075985;
    font-size: 13px;
}

.event-info p {
    margin: 0;
    color: #0369A1;
    font-size: 12px;
    line-height: 1.6;
}

/* FORM CARD */

.event-card {
    background: #fff;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 3px 12px rgba(15,23,42,.035);
}

.event-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 22px 26px;
    border-bottom: 1px solid #E8EDF3;
    background: #FCFDFE;
}

.event-section-icon {
    width: 40px;
    height: 40px;
}

.event-section-icon svg {
    width: 20px;
    height: 20px;
}

.event-card-head h2 {
    margin: 0;
    color: #123B5D;
    font-size: 15px;
    font-weight: 750;
}

.event-card-head p {
    margin: 4px 0 0;
    color: #64748B;
    font-size: 11px;
}

.event-required-note {
    margin-left: auto;
    color: #64748B;
    font-size: 11px;
    white-space: nowrap;
}

.event-required-note span,
.event-field label span {
    color: #DC2626;
}

/* FORM BODY */

.event-form-body {
    padding: 26px;
}

.event-section {
    margin-bottom: 23px;
    padding-bottom: 16px;
    border-bottom: 1px solid #E8EDF3;
}

.event-section-title {
    color: #123B5D;
    font-size: 13px;
    font-weight: 750;
}

.event-section-sub {
    margin-top: 5px;
    color: #64748B;
    font-size: 11px;
}

.event-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0,1fr));
    column-gap: 24px;
}

.event-field {
    min-width: 0;
    margin-bottom: 21px;
}

.event-field.full {
    grid-column: 1/-1;
}

.event-field label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}

.event-field input,
.event-field select,
.event-field textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background: #fff;
    color: #1E293B;
    font-family: Inter, sans-serif;
    font-size: 13px;
    transition: border-color .2s, box-shadow .2s;
}

.event-field input,
.event-field select {
    height: 44px;
    padding: 0 13px;
}

.event-field select {
    appearance: none;
    -webkit-appearance: none;
    padding-right: 36px;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 13px center;
}

.event-field textarea {
    min-height: 125px;
    padding: 13px;
    resize: vertical;
    line-height: 1.6;
}

.event-field input:focus,
.event-field select:focus,
.event-field textarea:focus {
    outline: none;
    border-color: #176B87;
    box-shadow: 0 0 0 3px rgba(23,107,135,.1);
}

.event-field input.is-invalid,
.event-field select.is-invalid,
.event-field textarea.is-invalid {
    border-color: #DC2626;
}

.event-field input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    cursor: pointer;
}

.event-hint {
    margin-top: 7px;
    color: #94A3B8;
    font-size: 11px;
    line-height: 1.5;
}

.event-field .error {
    margin-top: 6px;
    color: #DC2626;
    font-size: 11px;
}

.event-textarea-footer {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-top: 7px;
}

.event-textarea-footer .event-hint {
    margin: 0;
}

.event-char-count {
    color: #94A3B8;
    font-size: 10px;
}

/* =========================================
   INTERACTIVE CALENDAR
========================================= */

.event-calendar-section {
    margin-top: 10px;
    padding-top: 23px;
    border-top: 1px solid #E8EDF3;
}

.event-calendar-layout {
    display: grid;
    grid-template-columns: minmax(0,1fr) 320px;
    gap: 22px;
    align-items: stretch;
}

.event-calendar-intro h3 {
    margin: 0;
    color: #123B5D;
    font-size: 15px;
    font-weight: 750;
}

.event-calendar-intro p {
    margin: 6px 0 17px;
    color: #64748B;
    font-size: 11px;
    line-height: 1.6;
}

/* CALENDAR CARD */

.calendar-card {
    padding: 19px;
    border: 1px solid #E2E8F0;
    border-radius: 13px;
    background: #FFFFFF;
    box-shadow: 0 3px 12px rgba(15,23,42,.025);
}

/* CALENDAR HEADER */

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    margin-bottom: 19px;
}

.calendar-month {
    color: #123B5D;
    font-size: 15px;
    font-weight: 750;
}

.calendar-nav {
    display: flex;
    gap: 7px;
}

.calendar-nav button {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: #fff;
    color: #123B5D;
    font-size: 18px;
    cursor: pointer;
    transition: all .2s;
}

.calendar-nav button:hover {
    background: #EAF4F8;
    border-color: #176B87;
    color: #176B87;
}

/* WEEKDAYS */

.calendar-weekdays,
.calendar-days {
    display: grid;
    grid-template-columns: repeat(7,minmax(0,1fr));
    gap: 5px;
}

.calendar-weekdays {
    margin-bottom: 9px;
}

.calendar-weekdays div {
    text-align: center;
    color: #94A3B8;
    font-size: 10px;
    font-weight: 750;
    padding: 5px 0;
}

/* CALENDAR DATE CELLS */

.calendar-day {
    aspect-ratio: 1/1;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid transparent;
    border-radius: 9px;
    background: transparent;
    color: #334155;
    font-family: Inter, sans-serif;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all .15s;
}

.calendar-day:hover:not(:disabled) {
    background: #EAF4F8;
    border-color: #B9DCE9;
    color: #176B87;
}

.calendar-day.other-month {
    color: #CBD5E1;
}

.calendar-day.today {
    border-color: #176B87;
    color: #176B87;
    font-weight: 800;
}

.calendar-day.selected {
    background: #123B5D;
    border-color: #123B5D;
    color: #FFFFFF;
    font-weight: 800;
}

.calendar-day.selected:hover {
    background: #0E304C;
    color: #fff;
}

.calendar-day:focus-visible {
    outline: 2px solid #176B87;
    outline-offset: 2px;
}

/* CALENDAR LEGEND */

.calendar-legend {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    margin-top: 17px;
    padding-top: 14px;
    border-top: 1px solid #F1F5F9;
}

.calendar-legend-item {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #64748B;
    font-size: 10px;
}

.legend-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
}

.legend-dot.today {
    background: #fff;
    border: 2px solid #176B87;
}

.legend-dot.selected {
    background: #123B5D;
}

/* SELECTED DATE PANEL */

.calendar-selected-panel {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 22px;
    border-radius: 13px;
    background: linear-gradient(145deg,#123B5D,#176B87);
    color: #fff;
}

.calendar-selected-label {
    color: #C9E5F0;
    font-size: 11px;
    font-weight: 650;
    text-transform: uppercase;
    letter-spacing: .6px;
}

.calendar-selected-day {
    margin-top: 13px;
    font-size: 48px;
    line-height: 1;
    font-weight: 800;
}

.calendar-selected-month {
    margin-top: 9px;
    font-size: 17px;
    font-weight: 700;
}

.calendar-selected-year {
    margin-top: 5px;
    color: #C9E5F0;
    font-size: 12px;
}

.calendar-selected-divider {
    height: 1px;
    margin: 20px 0;
    background: rgba(255,255,255,.2);
}

.calendar-selected-message {
    color: #E0F2FE;
    font-size: 11px;
    line-height: 1.7;
}

.calendar-today-btn {
    align-self: flex-start;
    margin-top: 16px;
    padding: 8px 13px;
    border: 1px solid rgba(255,255,255,.4);
    border-radius: 8px;
    background: rgba(255,255,255,.1);
    color: #fff;
    font-family: Inter,sans-serif;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: background .2s;
}

.calendar-today-btn:hover {
    background: rgba(255,255,255,.2);
}

/* VALIDATION ALERT */

.event-alert {
    padding: 13px 16px;
    background: #FEF2F2;
    border: 1px solid #FECACA;
    border-radius: 10px;
    color: #B91C1C;
    font-size: 12px;
}

/* FORM FOOTER */

.event-form-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 19px 26px;
    background: #FAFBFC;
    border-top: 1px solid #E8EDF3;
}

.event-footer-note {
    color: #94A3B8;
    font-size: 11px;
}

.event-footer-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

/* RESPONSIVE */

@media(max-width:850px) {
    .event-calendar-layout {
        grid-template-columns: 1fr;
    }

    .calendar-selected-panel {
        min-height: 190px;
    }
}

@media(max-width:650px) {
    .event-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .event-form-grid {
        grid-template-columns: 1fr;
    }

    .event-field.full {
        grid-column: auto;
    }

    .event-form-body,
    .event-card-head {
        padding: 19px;
    }

    .event-required-note {
        display: none;
    }

    .event-calendar-layout {
        gap: 15px;
    }

    .calendar-card {
        padding: 13px;
    }

    .calendar-day {
        font-size: 10px;
    }

    .event-form-footer {
        align-items: stretch;
        flex-direction: column;
        padding: 17px 19px;
    }

    .event-footer-actions {
        flex-direction: column-reverse;
        width: 100%;
    }

    .event-footer-actions a,
    .event-footer-actions button {
        width: 100%;
    }
}
</style>

<div class="event-wrap">

    {{-- PAGE HEADER --}}

    <div class="sn-head event-head">

        <div class="event-heading">

            <div class="event-heading-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                    <path d="M12 14v4M10 16h4"/>
                </svg>
            </div>

            <div>
                <h1 class="sn-title">Create Event</h1>
                <div class="sn-sub">
                    Schedule and organize a new school event.
                </div>
            </div>

        </div>

        <a href="{{ route('events.dashboard') }}"
           class="sn-btn-custom">
            Back to Events Dashboard
        </a>

    </div>

    {{-- VALIDATION ALERT --}}

    @if($errors->any())
        <div class="event-alert">
            Please correct the errors below before submitting.
        </div>
    @endif

    {{-- INFORMATION PANEL --}}

    <div class="event-info">

        <div class="event-info-icon">
            <svg viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="10"/>
                <path d="M12 11v5M12 8h.01"/>
            </svg>
        </div>

        <div>
            <strong>Before you create an event</strong>
            <p>
                Enter the event details accurately. The event will be
                associated with the currently selected school.
                You can manage its details after creation.
            </p>
        </div>

    </div>

    {{-- MAIN FORM CARD --}}

    <div class="event-card">

        <div class="event-card-head">

            <div class="event-section-icon">
                <svg viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="17" rx="2"/>
                    <path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
            </div>

            <div>
                <h2>Event Information</h2>
                <p>Enter the basic details, schedule and location of the event.</p>
            </div>

            <div class="event-required-note">
                <span>*</span> Required fields
            </div>

        </div>

        <form method="POST" action="{{ route('events.store') }}">
            @csrf

            <div class="event-form-body">

                <div class="event-section">
                    <div class="event-section-title">
                        Basic Information
                    </div>

                    <div class="event-section-sub">
                        Provide the name and category of the event.
                    </div>
                </div>

                <div class="event-form-grid">

                    {{-- EVENT TITLE --}}

                    <div class="event-field full">
                        <label for="title">Event Title <span>*</span></label>

                        <input
                            type="text"
                            name="title"
                            id="title"
                            value="{{ old('title') }}"
                            maxlength="255"
                            placeholder="Enter event title"
                            class="@error('title') is-invalid @enderror"
                            required
                        >

                        @error('title')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CATEGORY --}}

                    <div class="event-field">
                        <label for="event_type">Event Category</label>

                        <select name="event_type" id="event_type">
                            <option value="">Select category</option>

                            @foreach($types as $type)
                                <option value="{{ $type }}"
                                    @selected(old('event_type') === $type)>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>

                        <div class="event-hint">
                            Select the category that best describes the event.
                        </div>

                        @error('event_type')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- CAMPUS --}}

                    <div class="event-field">
                        <label for="campus_id">Campus</label>

                        <select name="campus_id" id="campus_id">
                            <option value="">All Campuses</option>

                            @foreach($campuses as $campus)
                                <option value="{{ $campus->id }}"
                                    @selected(old('campus_id') == $campus->id)>
                                    {{ $campus->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="event-hint">
                            Leave empty if the event applies to all campuses.
                        </div>

                        @error('campus_id')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- START DATE --}}

                    <div class="event-field">
                        <label for="starts_at">
                            Start Date & Time <span>*</span>
                        </label>

                        <input
                            type="datetime-local"
                            name="starts_at"
                            id="starts_at"
                            value="{{ old('starts_at') }}"
                            class="@error('starts_at') is-invalid @enderror"
                            required
                        >

                        <div class="event-hint">
                            Select the event date using the calendar below.
                        </div>

                        @error('starts_at')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- END DATE --}}

                    <div class="event-field">
                        <label for="ends_at">End Date & Time</label>

                        <input
                            type="datetime-local"
                            name="ends_at"
                            id="ends_at"
                            value="{{ old('ends_at') }}"
                            class="@error('ends_at') is-invalid @enderror"
                        >

                        <div class="event-hint">
                            Must be on or after the start date and time.
                        </div>

                        @error('ends_at')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- VENUE --}}

                    <div class="event-field">
                        <label for="venue">Venue</label>

                        <input
                            type="text"
                            name="venue"
                            id="venue"
                            value="{{ old('venue') }}"
                            maxlength="255"
                            placeholder="e.g. School Auditorium"
                        >

                        <div class="event-hint">
                            Enter the location where the event will take place.
                        </div>

                        @error('venue')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- STATUS --}}

                    <div class="event-field">
                        <label for="status">
                            Event Status <span>*</span>
                        </label>

                        <select name="status" id="status" required>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}"
                                    @selected(old('status', 'planned') === $status)>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>

                        <div class="event-hint">
                            New events are generally created with Planned status.
                        </div>

                        @error('status')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- DESCRIPTION --}}

                    <div class="event-field full">
                        <label for="description">Event Description</label>

                        <textarea
                            name="description"
                            id="description"
                            placeholder="Describe the event, its objectives and other important information..."
                        >{{ old('description') }}</textarea>

                        <div class="event-textarea-footer">
                            <div class="event-hint">
                                Provide additional information about the event.
                            </div>

                            <div class="event-char-count">
                                <span id="descriptionCount">0</span> characters
                            </div>
                        </div>

                        @error('description')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                {{-- =================================
                     INTERACTIVE CALENDAR
                ================================== --}}

                <div class="event-calendar-section">

                    <div class="event-calendar-intro">
                        <h3>Event Calendar</h3>
                        <p>
                            Select a date from the calendar to automatically
                            fill in the event's start date.
                        </p>
                    </div>

                    <div class="event-calendar-layout">

                        {{-- CALENDAR --}}

                        <div class="calendar-card">

                            <div class="calendar-header">

                                <div class="calendar-month" id="calendarMonth">
                                    Loading...
                                </div>

                                <div class="calendar-nav">

                                    <button type="button"
                                            id="prevMonth"
                                            aria-label="Previous month">
                                        &#8249;
                                    </button>

                                    <button type="button"
                                            id="nextMonth"
                                            aria-label="Next month">
                                        &#8250;
                                    </button>

                                </div>

                            </div>

                            <div class="calendar-weekdays">
                                <div>Sun</div>
                                <div>Mon</div>
                                <div>Tue</div>
                                <div>Wed</div>
                                <div>Thu</div>
                                <div>Fri</div>
                                <div>Sat</div>
                            </div>

                            <div class="calendar-days" id="calendarDays">
                            </div>

                            <div class="calendar-legend">

                                <div class="calendar-legend-item">
                                    <span class="legend-dot today"></span>
                                    Today
                                </div>

                                <div class="calendar-legend-item">
                                    <span class="legend-dot selected"></span>
                                    Selected date
                                </div>

                            </div>

                        </div>

                        {{-- SELECTED DATE PREVIEW --}}

                        <div class="calendar-selected-panel">

                            <div class="calendar-selected-label">
                                Selected Event Date
                            </div>

                            <div class="calendar-selected-day" id="selectedDay">
                                --
                            </div>

                            <div class="calendar-selected-month" id="selectedMonth">
                                No date selected
                            </div>

                            <div class="calendar-selected-year" id="selectedYear">
                                Choose a date from the calendar
                            </div>

                            <div class="calendar-selected-divider"></div>

                            <div class="calendar-selected-message" id="selectedMessage">
                                Your selected event date will appear here.
                            </div>

                            <button type="button"
                                    class="calendar-today-btn"
                                    id="selectToday">
                                Select Today
                            </button>

                        </div>

                    </div>

                </div>

            </div>

            {{-- FORM FOOTER --}}

            <div class="event-form-footer">

                <div class="event-footer-note">
                    Event details will be saved securely.
                </div>

                <div class="event-footer-actions">

                    <a href="{{ route('events.dashboard') }}"
                       class="sn-btn-custom">
                        Cancel
                    </a>

                    <button type="submit"
                            class="sn-btn-custom-primary">
                        Create Event
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =====================================
       DESCRIPTION CHARACTER COUNTER
    ===================================== */

    const description = document.getElementById('description');
    const descriptionCount = document.getElementById('descriptionCount');

    if (description && descriptionCount) {

        function updateDescriptionCount() {
            descriptionCount.textContent = description.value.length;
        }

        description.addEventListener('input', updateDescriptionCount);
        updateDescriptionCount();
    }


    /* =====================================
       CALENDAR ELEMENTS
    ===================================== */

    const startInput = document.getElementById('starts_at');
    const endInput = document.getElementById('ends_at');

    const calendarMonth = document.getElementById('calendarMonth');
    const calendarDays = document.getElementById('calendarDays');

    const selectedDay = document.getElementById('selectedDay');
    const selectedMonth = document.getElementById('selectedMonth');
    const selectedYear = document.getElementById('selectedYear');
    const selectedMessage = document.getElementById('selectedMessage');

    const prevMonth = document.getElementById('prevMonth');
    const nextMonth = document.getElementById('nextMonth');
    const selectToday = document.getElementById('selectToday');

    const today = new Date();

    today.setHours(0, 0, 0, 0);

    let selectedDate = null;

    let viewYear = today.getFullYear();
    let viewMonth = today.getMonth();


    /* =====================================
       DATE HELPERS
    ===================================== */

    function formatDateKey(date) {

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');

        return `${year}-${month}-${day}`;
    }


    function parseDateKey(value) {

        if (!value) return null;

        const parts = value.split('-').map(Number);

        if (parts.length !== 3) return null;

        const date = new Date(parts[0], parts[1] - 1, parts[2]);

        if (
            date.getFullYear() !== parts[0] ||
            date.getMonth() !== parts[1] - 1 ||
            date.getDate() !== parts[2]
        ) {
            return null;
        }

        return date;
    }


    function formatFullDate(date) {

        return date.toLocaleDateString('en-IN', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

    }


    /* =====================================
       SELECT DATE
    ===================================== */

    function selectDate(date, updateInput = true) {

        selectedDate = new Date(
            date.getFullYear(),
            date.getMonth(),
            date.getDate()
        );

        if (updateInput && startInput) {

            let time = '09:00';

            if (startInput.value) {
                time = startInput.value.substring(11, 16) || time;
            }

            startInput.value = formatDateKey(selectedDate) + 'T' + time;

            startInput.dispatchEvent(new Event('change', {
                bubbles: true
            }));
        }

        viewYear = selectedDate.getFullYear();
        viewMonth = selectedDate.getMonth();

        updateSelectedDate();
        renderCalendar();
    }


    /* =====================================
       SELECTED DATE PREVIEW
    ===================================== */

    function updateSelectedDate() {

        if (!selectedDate) {

            selectedDay.textContent = '--';
            selectedMonth.textContent = 'No date selected';
            selectedYear.textContent = 'Choose a date from the calendar';

            selectedMessage.textContent =
                'Your selected event date will appear here.';

            return;
        }

        selectedDay.textContent = selectedDate.getDate();

        selectedMonth.textContent =
            selectedDate.toLocaleDateString('en-IN', {
                month: 'long'
            });

        selectedYear.textContent = selectedDate.getFullYear();

        if (formatDateKey(selectedDate) === formatDateKey(today)) {

            selectedMessage.textContent =
                'The event is scheduled for today.';

        } else {

            selectedMessage.textContent =
                formatFullDate(selectedDate) + ' is selected as the event date.';
        }

    }


    /* =====================================
       RENDER CALENDAR
    ===================================== */

    function renderCalendar() {

        calendarMonth.textContent = new Date(
            viewYear,
            viewMonth,
            1
        ).toLocaleDateString('en-IN', {
            month: 'long',
            year: 'numeric'
        });

        calendarDays.innerHTML = '';

        const firstDay = new Date(
            viewYear,
            viewMonth,
            1
        ).getDay();

        const daysInMonth = new Date(
            viewYear,
            viewMonth + 1,
            0
        ).getDate();

        const previousMonthDays = new Date(
            viewYear,
            viewMonth,
            0
        ).getDate();

        const totalCells = Math.ceil(
            (firstDay + daysInMonth) / 7
        ) * 7;

        for (let i = 0; i < totalCells; i++) {

            let dayNumber;
            let cellDate;
            let otherMonth = false;

            if (i < firstDay) {

                dayNumber = previousMonthDays - firstDay + i + 1;

                cellDate = new Date(
                    viewYear,
                    viewMonth - 1,
                    dayNumber
                );

                otherMonth = true;

            } else if (i >= firstDay + daysInMonth) {

                dayNumber = i - firstDay - daysInMonth + 1;

                cellDate = new Date(
                    viewYear,
                    viewMonth + 1,
                    dayNumber
                );

                otherMonth = true;

            } else {

                dayNumber = i - firstDay + 1;

                cellDate = new Date(
                    viewYear,
                    viewMonth,
                    dayNumber
                );

            }

            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'calendar-day';
            button.textContent = dayNumber;

            const dateKey = formatDateKey(cellDate);

            const todayKey = formatDateKey(today);

            if (otherMonth) {
                button.classList.add('other-month');
            }

            if (dateKey === todayKey) {
                button.classList.add('today');
            }

            if (
                selectedDate &&
                dateKey === formatDateKey(selectedDate)
            ) {
                button.classList.add('selected');
            }

            button.setAttribute(
                'aria-label',
                formatFullDate(cellDate)
            );

            button.setAttribute(
                'aria-pressed',
                selectedDate &&
                dateKey === formatDateKey(selectedDate)
                    ? 'true'
                    : 'false'
            );

            button.addEventListener('click', function () {

                selectDate(cellDate);

            });

            calendarDays.appendChild(button);
        }

    }


    /* =====================================
       MONTH NAVIGATION
    ===================================== */

    prevMonth.addEventListener('click', function () {

        viewMonth--;

        if (viewMonth < 0) {
            viewMonth = 11;
            viewYear--;
        }

        renderCalendar();

    });


    nextMonth.addEventListener('click', function () {

        viewMonth++;

        if (viewMonth > 11) {
            viewMonth = 0;
            viewYear++;
        }

        renderCalendar();

    });


    /* =====================================
       SELECT TODAY
    ===================================== */

    selectToday.addEventListener('click', function () {

        selectDate(today);

    });


    /* =====================================
       SYNC MANUAL DATE INPUT
    ===================================== */

    function syncCalendarFromInput() {

        if (!startInput.value) {

            selectedDate = null;

            updateSelectedDate();
            renderCalendar();

            return;
        }

        const datePart = startInput.value.substring(0, 10);

        const parsedDate = parseDateKey(datePart);

        if (parsedDate) {

            selectDate(parsedDate, false);

        }

    }

    startInput.addEventListener('change', syncCalendarFromInput);


    /* =====================================
       END DATE VALIDATION
    ===================================== */

    function updateEndDateMinimum() {

        if (!endInput || !startInput) return;

        const startValue = startInput.value;

        endInput.min = startValue || '';

        if (
            endInput.value &&
            startValue &&
            endInput.value < startValue
        ) {
            endInput.setCustomValidity(
                'End date and time must be on or after the start date and time.'
            );
        } else {
            endInput.setCustomValidity('');
        }

    }

    startInput.addEventListener('change', updateEndDateMinimum);
    endInput.addEventListener('change', updateEndDateMinimum);


    /* =====================================
       INITIALIZE
    ===================================== */

    if (startInput.value) {

        syncCalendarFromInput();

    } else {

        renderCalendar();
        updateSelectedDate();

    }

    updateEndDateMinimum();

});
</script>

@endsection
