@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">
    <style>
        /* ===============================
           CERTIFICATE GENERATOR
        =============================== */

        .cert-wrap {
            max-width: 1050px;
            margin: 0 auto;
        }

        .cert-wrap > * + * {
            margin-top: 22px;
        }

        /* Header */
        .cert-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .cert-heading {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .cert-heading-icon {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #EAF4F8;
            color: #176B87;
            font-size: 22px;
        }

        .cert-head .sn-title {
            margin: 0;
            font-size: 24px;
            font-weight: 750;
            color: #123B5D;
            letter-spacing: -0.5px;
        }

        .cert-head .sn-sub {
            margin-top: 5px;
            font-size: 13px;
            color: #64748B;
        }

        .cert-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 42px;
            padding: 10px 16px;
            background: #fff;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            color: #334155;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
            transition: all .2s ease;
        }

        .cert-back:hover {
            background: #F0F9FF;
            border-color: #176B87;
            color: #176B87;
        }

        /* Information panel */
        .cert-info {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            padding: 17px 19px;
            background: #F0F9FF;
            border: 1px solid #BAE6FD;
            border-radius: 12px;
        }

        .cert-info-icon {
            width: 34px;
            height: 34px;
            display: flex;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            background: #DFF3FC;
            border-radius: 9px;
            color: #0369A1;
            font-size: 17px;
        }

        .cert-info-content strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #075985;
            margin-bottom: 5px;
        }

        .cert-info-content p {
            margin: 0;
            color: #0369A1;
            font-size: 12px;
            line-height: 1.7;
        }

        /* Main card */
        .cert-card {
            background: #fff;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
        }

        .cert-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 23px 26px;
            border-bottom: 1px solid #E8EDF3;
            background: #FCFDFE;
        }

        .cert-section-icon {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F0F4FF;
            color: #5B3FA3;
            border-radius: 10px;
            font-size: 17px;
        }

        .cert-card-head h2 {
            margin: 0;
            font-size: 15px;
            font-weight: 750;
            color: #123B5D;
        }

        .cert-card-head p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #64748B;
        }

        .cert-required {
            margin-left: auto;
            font-size: 11px;
            color: #64748B;
            white-space: nowrap;
        }

        .cert-required span {
            color: #DC2626;
        }

        /* Form */
        .cert-form-body {
            padding: 26px;
        }

        .cert-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 24px;
            row-gap: 22px;
        }

        .cert-field {
            min-width: 0;
        }

        .cert-field.full {
            grid-column: 1 / -1;
        }

        .cert-field label {
            display: flex;
            align-items: center;
            gap: 4px;
            margin-bottom: 8px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }

        .cert-field label span {
            color: #DC2626;
        }

        .cert-field input,
        .cert-field select,
        .cert-field textarea {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #CBD5E1;
            border-radius: 9px;
            background-color: #fff;
            color: #1E293B;
            font-family: Inter, sans-serif;
            font-size: 13px;
            transition: border-color .2s, box-shadow .2s;
        }

        .cert-field input,
        .cert-field select {
            height: 44px;
            padding: 0 13px;
        }

        .cert-field select {
            appearance: none;
            -webkit-appearance: none;
            padding-right: 35px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2364748B' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 13px center;
        }

        .cert-field textarea {
            min-height: 125px;
            padding: 13px;
            resize: vertical;
            line-height: 1.6;
        }

        .cert-field input::placeholder,
        .cert-field textarea::placeholder {
            color: #94A3B8;
        }

        .cert-field input:focus,
        .cert-field select:focus,
        .cert-field textarea:focus {
            outline: none;
            border-color: #176B87;
            box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
        }

        .cert-hint {
            margin-top: 7px;
            color: #94A3B8;
            font-size: 11px;
        }

        .cert-field .error {
            margin-top: 6px;
            font-size: 11px;
            color: #DC2626;
        }

        /* Student selection */
        .cert-field select:has(option:checked[value=""]) {
            color: #94A3B8;
        }

        /* Footer */
        .cert-form-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            padding: 19px 26px;
            background: #FAFBFC;
            border-top: 1px solid #E8EDF3;
        }

        .cert-footer-note {
            color: #94A3B8;
            font-size: 11px;
        }

        .cert-footer-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cert-form-footer .sn-btn {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            padding: 10px 19px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 9px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .cert-form-footer .sn-btn-primary:hover {
            filter: brightness(.93);
            transform: translateY(-1px);
        }

        .cert-form-footer button:disabled {
            opacity: .5;
            cursor: not-allowed;
            transform: none;
        }

        /* Validation */
        .cert-alert {
            padding: 13px 16px;
            background: #FEF2F2;
            border: 1px solid #FECACA;
            border-radius: 10px;
            color: #B91C1C;
            font-size: 12px;
        }

        /* Responsive */
        @media(max-width: 650px) {
            .cert-heading {
                align-items: center;
            }

            .cert-head {
                align-items: flex-start;
                flex-direction: column;
            }

            .cert-form-grid {
                grid-template-columns: 1fr;
                row-gap: 19px;
            }

            .cert-field.full {
                grid-column: auto;
            }

            .cert-card-head,
            .cert-form-body {
                padding: 19px;
            }

            .cert-required {
                display: none;
            }

            .cert-form-footer {
                padding: 17px 19px;
                align-items: stretch;
                flex-direction: column;
            }

            .cert-footer-actions {
                flex-direction: column-reverse;
                width: 100%;
            }

            .cert-footer-actions .sn-btn {
                width: 100%;
            }

            .cert-footer-note {
                text-align: center;
            }
        }
    </style>

    <div class="cert-wrap">

        {{-- Header --}}
        <div class="sn-head cert-head">
            <div class="cert-heading">
                <div class="cert-heading-icon">
                    <i class="fas fa-certificate"></i>
                </div>

                <div>
                    <h1 class="sn-title">Generate Certificate</h1>
                    <div class="sn-sub">
                        Create and issue official student certificates.
                    </div>
                </div>
            </div>

            <a href="{{ route('certificates.dashboard') }}"
               class="cert-back">
                <i class="fas fa-arrow-left"></i>
                Back to Certificates
            </a>
        </div>

        {{-- Validation --}}
        @if($errors->any())
            <div class="cert-alert">
                <i class="fas fa-exclamation-circle"></i>
                Please correct the errors below before submitting the form.
            </div>
        @endif

        {{-- Information --}}
        <div class="cert-info">
            <div class="cert-info-icon">
                <i class="fas fa-info-circle"></i>
            </div>

            <div class="cert-info-content">
                <strong>Before you generate</strong>
                <p>
                    A unique certificate number will be generated automatically.
                    After successful submission, the certificate will be saved as a PDF.
                </p>
            </div>
        </div>

        {{-- Certificate form --}}
        <div class="cert-card">

            <div class="cert-card-head">
                <div class="cert-section-icon">
                    <i class="fas fa-file-alt"></i>
                </div>

                <div>
                    <h2>Certificate Information</h2>
                    <p>Enter the student and certificate details below.</p>
                </div>

                <div class="cert-required">
                    <span>*</span> Required fields
                </div>
            </div>

            <form method="POST" action="{{ route('certificates.store') }}">
                @csrf

                <div class="cert-form-body">
                    <div class="cert-form-grid">

                        {{-- Student --}}
                        <div class="cert-field">
                            <label for="student_id">
                                Student <span>*</span>
                            </label>

                            <select name="student_id" id="student_id" required>
                                <option value="">Select a student</option>

                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                        @selected(old('student_id') == $student->id)>
                                        {{ trim($student->first_name.' '.($student->middle_name ?? '').' '.($student->last_name ?? '')) }}
                                        — {{ $student->admission_number }}
                                    </option>
                                @endforeach
                            </select>

                            @error('student_id')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            @if($students->isEmpty())
                                <div class="cert-hint">
                                    No active students are available in the selected school.
                                </div>
                            @else
                                <div class="cert-hint">
                                    Search and select the student receiving this certificate.
                                </div>
                            @endif
                        </div>

                        {{-- Certificate type --}}
                        <div class="cert-field">
                            <label for="certificate_type">
                                Certificate Type <span>*</span>
                            </label>

                            <select name="certificate_type" id="certificate_type" required>
                                <option value="">Select certificate type</option>

                                @foreach($types as $type)
                                    <option value="{{ $type }}"
                                        @selected(old('certificate_type') === $type)>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>

                            @error('certificate_type')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            <div class="cert-hint">
                                Choose the appropriate certificate category.
                            </div>
                        </div>

                        {{-- Issue date --}}
                        <div class="cert-field">
                            <label for="issued_date">
                                Issue Date <span>*</span>
                            </label>

                            <input
                                type="date"
                                name="issued_date"
                                id="issued_date"
                                value="{{ old('issued_date', date('Y-m-d')) }}"
                                max="{{ date('Y-m-d') }}"
                                required
                            >

                            @error('issued_date')
                                <div class="error">{{ $message }}</div>
                            @enderror

                            <div class="cert-hint">
                                Select the official date of certificate issuance.
                            </div>
                        </div>

                        {{-- Remarks --}}
                        <div class="cert-field full">
                            <label for="remarks">
                                Remarks / Additional Details
                            </label>

                            <textarea
                                name="remarks"
                                id="remarks"
                                maxlength="1000"
                                placeholder="Enter any additional information to appear on the certificate..."
                            >{{ old('remarks') }}</textarea>

                            <div class="cert-hint">
                                Optional · Maximum 1000 characters.
                            </div>

                            @error('remarks')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Footer --}}
                <div class="cert-form-footer">
                    <div class="cert-footer-note">
                        <i class="fas fa-lock"></i>
                        Certificate details will be saved securely.
                    </div>

                    <div class="cert-footer-actions">
                        <a href="{{ route('certificates.dashboard') }}"
                           class="sn-btn sn-btn-outline">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="sn-btn sn-btn-primary"
                            @disabled($students->isEmpty())
                        >
                            <i class="fas fa-file-pdf"></i>
                            &nbsp; Generate Certificate
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
