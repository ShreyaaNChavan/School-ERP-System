@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page event-edit-page">

    <style>
        /* =========================================
           EVENT EDIT - SCHOOLNEXA DESIGN
        ========================================= */

        .event-edit-page {
            --event-navy: #123B5D;
            --event-teal: #176B87;
            --event-purple: #5B3FA3;
            --event-text: #1E293B;
            --event-muted: #64748B;
            --event-border: #E2E8F0;
            --event-bg: #F8FAFC;
        }

        /* Page Header */
        .event-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .event-heading-left {
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .event-heading-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #EAF3F8;
            color: var(--event-navy);
            font-size: 21px;
        }

        .event-page-title {
            font-size: 23px;
            font-weight: 750;
            color: var(--event-navy);
            margin: 0 0 6px;
            letter-spacing: -.5px;
        }

        .event-page-subtitle {
            color: var(--event-muted);
            font-size: 12px;
            line-height: 1.6;
            margin: 0;
        }

        .event-page-subtitle strong {
            color: var(--event-text);
            font-weight: 650;
        }

        /* Event Metadata */
        .event-meta-strip {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            margin-top: 9px;
        }

        .event-meta-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            color: #475569;
            padding: 5px 10px;
            border-radius: 7px;
            font-size: 10px;
            font-weight: 700;
        }

        .event-meta-chip i {
            font-size: 10px;
        }

        /* Buttons */
        .event-edit-page .event-btn {
            min-height: 48px;
            padding: 0 20px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-family: Inter, sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all .2s ease;
            box-sizing: border-box;
            white-space: nowrap;
        }

        .event-edit-page .event-btn-outline {
            color: var(--event-navy);
            background: #FFFFFF;
            border: 1px solid #CBD5E1;
        }

        .event-edit-page .event-btn-outline:hover {
            background: #F0F9FF;
            border-color: var(--event-teal);
        }

        .event-edit-page .event-btn-primary {
            color: #FFFFFF;
            background: var(--event-navy);
            border: 1px solid var(--event-navy);
            box-shadow: 0 3px 8px rgba(18, 59, 93, .12);
        }

        .event-edit-page .event-btn-primary:hover {
            background: #0E304C;
            border-color: #0E304C;
            box-shadow: 0 5px 12px rgba(18, 59, 93, .18);
            transform: translateY(-1px);
        }

        .event-edit-page .event-btn:focus-visible {
            outline: 3px solid rgba(23, 107, 135, .2);
            outline-offset: 2px;
        }

        /* Main Card */
        .event-edit-card {
            background: #FFFFFF;
            border: 1px solid var(--event-border);
            border-radius: 15px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
            overflow: hidden;
        }

        .event-card-header {
            padding: 22px 25px;
            border-bottom: 1px solid #E8EDF3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
        }

        .event-card-heading {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .event-card-heading-icon {
            width: 39px;
            height: 39px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #EEF5FA;
            color: var(--event-navy);
            font-size: 16px;
        }

        .event-card-heading h2 {
            font-size: 15px;
            font-weight: 750;
            color: var(--event-navy);
            margin: 0 0 4px;
        }

        .event-card-heading p {
            font-size: 11px;
            color: var(--event-muted);
            margin: 0;
        }

        .event-required-note {
            font-size: 11px;
            color: var(--event-muted);
        }

        .event-required-note span {
            color: #DC2626;
            font-weight: 700;
        }

        /* Card Body */
        .event-card-body {
            padding: 25px;
        }

        /* Information Banner */
        .event-info-banner {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 15px 17px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 11px;
            margin-bottom: 27px;
        }

        .event-info-icon {
            width: 31px;
            height: 31px;
            flex-shrink: 0;
            border-radius: 8px;
            background: #DDF3FF;
            color: #0369A1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .event-info-content {
            flex: 1;
        }

        .event-info-content strong {
            display: block;
            color: #075985;
            font-size: 12px;
            font-weight: 750;
            margin-bottom: 3px;
        }

        .event-info-content p {
            font-size: 11px;
            line-height: 1.6;
            color: #0369A1;
            margin: 0;
        }

        /* Form Section */
        .event-form-section {
            margin-bottom: 25px;
        }

        .event-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 19px;
        }

        .event-section-number {
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #EAF3F8;
            color: var(--event-navy);
            border-radius: 7px;
            font-size: 11px;
            font-weight: 800;
        }

        .event-section-heading h3 {
            color: var(--event-text);
            font-size: 13px;
            font-weight: 750;
            margin: 0;
        }

        .event-section-heading p {
            font-size: 10px;
            color: var(--event-muted);
            margin: 3px 0 0;
        }

        .event-section-divider {
            height: 1px;
            background: #EEF2F6;
            flex: 1;
            margin-left: 8px;
        }

        /* Form Grid */
        .event-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 22px;
            row-gap: 0;
        }

        .event-field {
            min-width: 0;
            margin-bottom: 19px;
        }

        .event-field-full {
            grid-column: 1 / -1;
        }

        .event-field label {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 8px;
        }

        .event-field label .required {
            color: #DC2626;
        }

        .event-field input,
        .event-field select,
        .event-field textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #D7E0E9;
            border-radius: 9px;
            padding: 11px 13px;
            background: #FFFFFF;
            color: #25313C;
            font-family: Inter, sans-serif;
            font-size: 12px;
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .event-field input,
        .event-field select {
            height: 44px;
        }

        .event-field textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        .event-field input:hover,
        .event-field select:hover,
        .event-field textarea:hover {
            border-color: #94A3B8;
        }

        .event-field input:focus,
        .event-field select:focus,
        .event-field textarea:focus {
            outline: none;
            border-color: var(--event-teal);
            box-shadow: 0 0 0 3px rgba(23, 107, 135, .09);
            background: #FCFEFF;
        }

        .event-field input::placeholder,
        .event-field textarea::placeholder {
            color: #A0AAB8;
        }

        .event-field .error {
            display: block;
            color: #DC2626;
            font-size: 10px;
            font-weight: 600;
            margin-top: 6px;
        }

        .event-field input.is-invalid,
        .event-field select.is-invalid,
        .event-field textarea.is-invalid {
            border-color: #DC2626;
        }

        .event-field-hint {
            color: #94A3B8;
            font-size: 10px;
            margin-top: 6px;
        }

        /* Status Select */
        .event-status-select {
            font-weight: 650 !important;
        }

        /* Validation Alert */
        .event-validation-alert {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 17px;
            margin-bottom: 20px;
            border: 1px solid #FECACA;
            border-radius: 10px;
            background: #FEF2F2;
            color: #991B1B;
            font-size: 12px;
        }

        .event-validation-alert i {
            margin-top: 2px;
        }

        .event-validation-alert strong {
            display: block;
            margin-bottom: 3px;
            font-size: 12px;
        }

        .event-validation-alert p {
            margin: 0;
            font-size: 11px;
            line-height: 1.5;
        }

        /* Footer */
        .event-form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 19px 25px;
            border-top: 1px solid #E8EDF3;
            background: #FAFCFE;
        }

        .event-footer-note {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #64748B;
            font-size: 10px;
        }

        .event-footer-note i {
            color: #16A34A;
        }

        .event-footer-actions {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        /* Responsive */
        @media(max-width: 768px) {
            .event-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .event-page-header .event-btn {
                width: 100%;
            }

            .event-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .event-card-body {
                padding: 19px;
            }

            .event-form-footer {
                padding: 17px 19px;
            }
        }

        @media(max-width: 600px) {
            .event-form-grid {
                grid-template-columns: 1fr;
            }

            .event-field-full {
                grid-column: auto;
            }

            .event-form-footer {
                align-items: stretch;
                flex-direction: column;
            }

            .event-footer-actions {
                width: 100%;
            }

            .event-footer-actions .event-btn {
                flex: 1;
            }

            .event-footer-note {
                justify-content: center;
            }

            .event-page-title {
                font-size: 20px;
            }
        }
    </style>


    {{-- =========================================
         PAGE HEADER
    ========================================== --}}
    <div class="event-page-header">

        <div class="event-heading-left">

           

            <div>
                <h1 class="event-page-title">Edit Event</h1>

                <p class="event-page-subtitle">
                    Update and manage the information for
                    <strong>{{ $record->title }}</strong>
                </p>

                <div class="event-meta-strip">
                    <span class="event-meta-chip">
                        <i class="fas fa-hashtag"></i>
                        Event ID: {{ $record->id }}
                    </span>

                    <span class="event-meta-chip">
                        <i class="fas fa-circle" style="font-size:7px"></i>
                        {{ ucfirst($record->status) }}
                    </span>
                </div>
            </div>

        </div>

        <a href="{{ route('events.show', $record->id) }}"
           class="event-btn event-btn-outline">
            <i class="fas fa-arrow-left"></i>
            Back to Event Details
        </a>

    </div>


    {{-- =========================================
         VALIDATION ALERT
    ========================================== --}}
    @if($errors->any())
        <div class="event-validation-alert">
            <i class="fas fa-exclamation-circle"></i>

            <div>
                <strong>Unable to save event changes</strong>
                <p>Please review the highlighted fields and correct the errors before submitting.</p>
            </div>
        </div>
    @endif


    {{-- =========================================
         MAIN FORM CARD
    ========================================== --}}
    <div class="event-edit-card">

        {{-- Card Header --}}
        <div class="event-card-header">

            <div class="event-card-heading">

                <div class="event-card-heading-icon">
                    <i class="fas fa-sliders-h"></i>
                </div>

                <div>
                    <h2>Event Information</h2>
                    <p>Update the event details using the form below.</p>
                </div>

            </div>

            <div class="event-required-note">
                Fields marked <span>*</span> are required
            </div>

        </div>


        {{-- Form Body --}}
        <form method="POST"
              action="{{ route('events.update', $record->id) }}">

            @csrf
            @method('PUT')

            <div class="event-card-body">

                {{-- Information Banner --}}
                <div class="event-info-banner">

                    <div class="event-info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>

                    <div class="event-info-content">
                        <strong>Editing Existing Event</strong>
                        <p>
                            You are modifying event #{{ $record->id }}.
                            Changes will be saved to the existing event record.
                        </p>
                    </div>

                </div>


                {{-- SECTION 1: BASIC DETAILS --}}
                <div class="event-form-section">

                    <div class="event-section-heading">
                        <div class="event-section-number">01</div>

                        <div>
                            <h3>Basic Details</h3>
                            <p>Event name and classification</p>
                        </div>

                        <div class="event-section-divider"></div>
                    </div>


                    <div class="event-form-grid">

                        {{-- Event Title --}}
                        <div class="event-field event-field-full">

                            <label for="title">
                                Event Title <span class="required">*</span>
                            </label>

                            <input
                                type="text"
                                name="title"
                                id="title"
                                value="{{ old('title', $record->title) }}"
                                maxlength="255"
                                placeholder="Enter a descriptive event title"
                                class="@error('title') is-invalid @enderror"
                                required
                            >

                            @error('title')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>


                        {{-- Event Category --}}
                        <div class="event-field">

                            <label for="event_type">
                                Event Category
                            </label>

                            <select
                                name="event_type"
                                id="event_type"
                                class="@error('event_type') is-invalid @enderror"
                            >
                                <option value="">Select event category</option>

                                @foreach($types as $type)
                                    <option
                                        value="{{ $type }}"
                                        @selected(old('event_type', $record->event_type) === $type)
                                    >
                                        {{ $type }}
                                    </option>
                                @endforeach

                            </select>

                            @error('event_type')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>


                        {{-- Campus --}}
                        <div class="event-field">

                            <label for="campus_id">
                                Campus
                            </label>

                            <select
                                name="campus_id"
                                id="campus_id"
                                class="@error('campus_id') is-invalid @enderror"
                            >
                                <option value="">All Campuses</option>

                                @foreach($campuses as $campus)
                                    <option
                                        value="{{ $campus->id }}"
                                        @selected(old('campus_id', $record->campus_id) == $campus->id)
                                    >
                                        {{ $campus->name }}
                                    </option>
                                @endforeach

                            </select>

                            @error('campus_id')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- SECTION 2: SCHEDULE AND LOCATION --}}
                <div class="event-form-section">

                    <div class="event-section-heading">
                        <div class="event-section-number">02</div>

                        <div>
                            <h3>Schedule & Location</h3>
                            <p>Set the event timing and venue</p>
                        </div>

                        <div class="event-section-divider"></div>
                    </div>


                    <div class="event-form-grid">

                        {{-- Start Date --}}
                        <div class="event-field">

                            <label for="starts_at">
                                Start Date & Time <span class="required">*</span>
                            </label>

                            <input
                                type="datetime-local"
                                name="starts_at"
                                id="starts_at"
                                value="{{ old('starts_at', \Carbon\Carbon::parse($record->starts_at)->format('Y-m-d\TH:i')) }}"
                                class="@error('starts_at') is-invalid @enderror"
                                required
                            >

                            @error('starts_at')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>


                        {{-- End Date --}}
                        <div class="event-field">

                            <label for="ends_at">
                                End Date & Time
                            </label>

                            <input
                                type="datetime-local"
                                name="ends_at"
                                id="ends_at"
                                value="{{ old('ends_at', $record->ends_at ? \Carbon\Carbon::parse($record->ends_at)->format('Y-m-d\TH:i') : '') }}"
                                class="@error('ends_at') is-invalid @enderror"
                            >

                            @error('ends_at')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>


                        {{-- Venue --}}
                        <div class="event-field event-field-full">

                            <label for="venue">
                                Event Venue
                            </label>

                            <input
                                type="text"
                                name="venue"
                                id="venue"
                                value="{{ old('venue', $record->venue) }}"
                                maxlength="255"
                                placeholder="Enter the event location or venue"
                                class="@error('venue') is-invalid @enderror"
                            >

                            @error('venue')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                </div>


                {{-- SECTION 3: STATUS AND DESCRIPTION --}}
                <div class="event-form-section" style="margin-bottom:0">

                    <div class="event-section-heading">
                        <div class="event-section-number">03</div>

                        <div>
                            <h3>Additional Information</h3>
                            <p>Event status and description</p>
                        </div>

                        <div class="event-section-divider"></div>
                    </div>


                    <div class="event-form-grid">

                        {{-- Status --}}
                        <div class="event-field">

                            <label for="status">
                                Event Status <span class="required">*</span>
                            </label>

                            <select
                                name="status"
                                id="status"
                                class="event-status-select @error('status') is-invalid @enderror"
                                required
                            >

                                @foreach($statuses as $status)
                                    <option
                                        value="{{ $status }}"
                                        @selected(old('status', $record->status) === $status)
                                    >
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach

                            </select>

                            @error('status')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>


                        {{-- Description --}}
                        <div class="event-field event-field-full">

                            <label for="description">
                                Event Description
                            </label>

                            <textarea
                                name="description"
                                id="description"
                                placeholder="Provide details about the event, its purpose, activities and other important information..."
                                class="@error('description') is-invalid @enderror"
                            >{{ old('description', $record->description) }}</textarea>

                            <div class="event-field-hint">
                                Provide a clear description to help students, parents and staff understand the event.
                            </div>

                            @error('description')
                                <span class="error">{{ $message }}</span>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================
                 FORM FOOTER
            ========================================== --}}
            <div class="event-form-footer">

                <div class="event-footer-note">
                    <i class="fas fa-shield-alt"></i>
                    Your changes will be saved to this event.
                </div>

                <div class="event-footer-actions">

                    <a href="{{ route('events.show', $record->id) }}"
                       class="event-btn event-btn-outline">
                        Cancel
                    </a>

                    <button type="submit"
                            class="event-btn event-btn-primary">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
@endsection