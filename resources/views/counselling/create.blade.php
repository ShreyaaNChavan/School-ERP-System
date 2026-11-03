
@extends('layouts.schoolnexa')

@section('content')

<style>
    .counselling-form-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
        max-width: 1050px;
    }

    .cf-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 22px;
    }

    .cf-header h1 {
        color: #123B5D;
        font-size: 22px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .cf-header p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .cf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
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
    }

    .cf-btn-primary {
        background: #123B5D;
        border-color: #123B5D;
        color: #fff;
    }

    .cf-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .cf-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 17px;
    }

    .cf-section-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .cf-section-subtitle {
        color: #64748B;
        font-size: 12px;
        margin: 0 0 20px;
    }

    .cf-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .cf-field {
        min-width: 0;
    }

    .cf-field.full {
        grid-column: 1 / -1;
    }

    .cf-field label {
        display: block;
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 7px;
    }

    .cf-required {
        color: #DC2626;
    }

    .cf-field input,
    .cf-field select,
    .cf-field textarea {
        width: 100%;
        min-height: 41px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 9px 11px;
        background: #fff;
        color: #25313C;
        font-family: inherit;
        font-size: 13px;
        outline: none;
    }

    .cf-field textarea {
        min-height: 100px;
        resize: vertical;
    }

    .cf-field input:focus,
    .cf-field select:focus,
    .cf-field textarea:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.09);
    }

    .cf-hint {
        color: #94A3B8;
        font-size: 11px;
        margin-top: 6px;
    }

    .cf-error {
        color: #DC2626;
        font-size: 11px;
        margin-top: 5px;
    }

    .cf-info {
        background: #F8FAFC;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-top: 17px;
    }

    .cf-info-title {
        color: #123B5D;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .cf-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }

    .cf-info-label {
        color: #94A3B8;
        font-size: 10px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .cf-info-value {
        color: #334155;
        font-size: 12px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .cf-alert {
        border: 1px solid #FECACA;
        background: #FEF2F2;
        color: #991B1B;
        border-radius: 9px;
        padding: 12px 14px;
        font-size: 12px;
        margin-bottom: 17px;
    }

    .cf-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 18px;
    }

    @media(max-width: 650px) {
        .cf-grid,
        .cf-info-grid {
            grid-template-columns: 1fr;
        }

        .cf-card {
            padding: 16px;
        }

        .cf-header {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="counselling-form-page">

    <div class="cf-header">
        <div>
            <h1>Schedule Counselling</h1>
            <p>Create and assign a new counselling session.</p>
        </div>

        <a href="{{ route('counselling.index') }}" class="cf-btn">
            ← Back to Counselling
        </a>
    </div>

    @if($errors->any())
        <div class="cf-alert">
            <strong>Please correct the following errors:</strong>
            <ul style="margin:7px 0 0 18px;padding:0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('counselling.store') }}">
        @csrf

        {{-- STUDENT / APPLICANT --}}
        <div class="cf-card">
            <h3 class="cf-section-title">Student / Applicant Information</h3>
            <p class="cf-section-subtitle">
                Select an existing student or an admission applicant.
            </p>

            <div class="cf-grid">

                <div class="cf-field full">
                    <label for="person_type">Counselling For <span class="cf-required">*</span></label>
                    <select id="person_type">
                        <option value="student" @selected(old('student_id') || !old('admission_application_id'))>
                            Existing Student
                        </option>
                        <option value="applicant" @selected(old('admission_application_id'))>
                            Admission Applicant
                        </option>
                    </select>
                </div>

                <div class="cf-field full" id="student_field">
                    <label for="student_id">Select Student <span class="cf-required">*</span></label>
                    <select name="student_id" id="student_id">
                        <option value="">Select a student</option>
                        @foreach($students as $student)
                            @php
                                $primaryGuardian = $student->guardians->firstWhere('pivot.is_primary', 1)
                                    ?? $student->guardians->first();
                            @endphp

                            <option
                                value="{{ $student->id }}"
                                data-parent="{{ $primaryGuardian?->full_name ?? $primaryGuardian?->name ?? '' }}"
                                data-phone="{{ $primaryGuardian?->phone ?? '' }}"
                                data-email="{{ $primaryGuardian?->email ?? '' }}"
                                @selected(old('student_id') == $student->id)
                            >
                                {{ $student->full_name }}
                                — {{ $student->admission_number ?? 'No admission number' }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field full" id="applicant_field" style="display:none;">
                    <label for="admission_application_id">
                        Select Admission Applicant <span class="cf-required">*</span>
                    </label>

                    <select name="admission_application_id" id="admission_application_id" disabled>
                        <option value="">Select an applicant</option>
                        @foreach($applications as $application)
                            <option
                                value="{{ $application->id }}"
                                data-parent="{{ $application->parent_name ?? '' }}"
                                data-phone="{{ $application->parent_phone ?? '' }}"
                                data-email="{{ $application->parent_email ?? '' }}"
                                @selected(old('admission_application_id') == $application->id)
                            >
                                {{ $application->applicant_name }}
                                — {{ $application->application_number }}
                            </option>
                        @endforeach
                    </select>

                    @error('admission_application_id')
                        <div class="cf-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>

            <div class="cf-info" id="parent_info">
                <div class="cf-info-title">Parent / Guardian Information</div>

                <div class="cf-info-grid">
                    <div>
                        <div class="cf-info-label">Parent / Guardian</div>
                        <div class="cf-info-value" id="parent_name">Select a student or applicant</div>
                    </div>

                    <div>
                        <div class="cf-info-label">Phone Number</div>
                        <div class="cf-info-value" id="parent_phone">—</div>
                    </div>

                    <div>
                        <div class="cf-info-label">Email Address</div>
                        <div class="cf-info-value" id="parent_email">—</div>
                    </div>
                </div>

                <div class="cf-hint">
                    Details are retrieved from the selected student's guardian or admission application.
                </div>
            </div>
        </div>

        {{-- SESSION DETAILS --}}
        <div class="cf-card">
            <h3 class="cf-section-title">Session Details</h3>
            <p class="cf-section-subtitle">
                Set the appointment schedule and counselling category.
            </p>

            <div class="cf-grid">

                <div class="cf-field">
                    <label for="counsellor_id">Assign Counsellor <span class="cf-required">*</span></label>

                    <select name="counsellor_id" id="counsellor_id" required>
                        <option value="">Select counsellor</option>
                        @foreach($counsellors as $counsellor)
                            <option value="{{ $counsellor->id }}"
                                @selected(old('counsellor_id') == $counsellor->id)>
                                {{ $counsellor->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('counsellor_id') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field">
                    <label for="session_type">Session Type <span class="cf-required">*</span></label>

                    <select name="session_type" id="session_type" required>
                        <option value="">Select session type</option>
                        @foreach([
                            'admission' => 'Admission Counselling',
                            'academic' => 'Academic Counselling',
                            'career' => 'Career Counselling',
                            'behavioural' => 'Behavioural Counselling',
                            'personal' => 'Personal Counselling',
                            'parent_meeting' => 'Parent Meeting',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('session_type') == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    @error('session_type') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field">
                    <label for="session_at">Session Date & Time <span class="cf-required">*</span></label>

                    <input
                        type="datetime-local"
                        name="session_at"
                        id="session_at"
                        value="{{ old('session_at', now()->format('Y-m-d\TH:i')) }}"
                        required
                    >

                    @error('session_at') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field">
                    <label for="next_session_at">Next Follow-up Date & Time</label>

                    <input
                        type="datetime-local"
                        name="next_session_at"
                        id="next_session_at"
                        value="{{ old('next_session_at') }}"
                    >

                    <div class="cf-hint">Optional. Must be later than the session date.</div>
                    @error('next_session_at') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field">
                    <label for="status">Session Status <span class="cf-required">*</span></label>

                    <select name="status" id="status" required>
                        <option value="open" @selected(old('status', 'open') == 'open')>Scheduled / Open</option>
                        <option value="completed" @selected(old('status') == 'completed')>Completed</option>
                        <option value="cancelled" @selected(old('status') == 'cancelled')>Cancelled</option>
                    </select>

                    @error('status') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        {{-- COUNSELLING NOTES --}}
        <div class="cf-card">
            <h3 class="cf-section-title">Counselling Notes & Action Plan</h3>
            <p class="cf-section-subtitle">
                Record the purpose of the session and the expected next steps.
            </p>

            <div class="cf-grid">

                <div class="cf-field full">
                    <label for="summary">Session Summary <span class="cf-required">*</span></label>

                    <textarea
                        name="summary"
                        id="summary"
                        placeholder="Describe the purpose, discussion points or concerns for this counselling session..."
                        required
                    >{{ old('summary') }}</textarea>

                    @error('summary') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

                <div class="cf-field full">
                    <label for="action_plan">Action Plan</label>

                    <textarea
                        name="action_plan"
                        id="action_plan"
                        placeholder="Enter recommendations, actions to be taken and responsibilities..."
                    >{{ old('action_plan') }}</textarea>

                    @error('action_plan') <div class="cf-error">{{ $message }}</div> @enderror
                </div>

            </div>
        </div>

        <div class="cf-actions">
            <a href="{{ route('counselling.index') }}" class="cf-btn">Cancel</a>
            <button type="submit" class="cf-btn cf-btn-primary">
                Schedule Session
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const personType = document.getElementById('person_type');
    const studentField = document.getElementById('student_field');
    const applicantField = document.getElementById('applicant_field');

    const studentSelect = document.getElementById('student_id');
    const applicantSelect = document.getElementById('admission_application_id');

    const parentName = document.getElementById('parent_name');
    const parentPhone = document.getElementById('parent_phone');
    const parentEmail = document.getElementById('parent_email');

    function updatePersonType() {
        const isStudent = personType.value === 'student';

        studentField.style.display = isStudent ? '' : 'none';
        applicantField.style.display = isStudent ? 'none' : '';

        studentSelect.disabled = !isStudent;
        applicantSelect.disabled = isStudent;

        studentSelect.required = isStudent;
        applicantSelect.required = !isStudent;

        updateParentInfo();
    }

    function updateParentInfo() {
        const selected = personType.value === 'student'
            ? studentSelect.options[studentSelect.selectedIndex]
            : applicantSelect.options[applicantSelect.selectedIndex];

        if (!selected || !selected.value) {
            parentName.textContent = 'Select a student or applicant';
            parentPhone.textContent = '—';
            parentEmail.textContent = '—';
            return;
        }

        parentName.textContent = selected.dataset.parent || 'Not provided';
        parentPhone.textContent = selected.dataset.phone || 'Not provided';
        parentEmail.textContent = selected.dataset.email || 'Not provided';
    }

    personType.addEventListener('change', updatePersonType);
    studentSelect.addEventListener('change', updateParentInfo);
    applicantSelect.addEventListener('change', updateParentInfo);

    updatePersonType();
});
</script>

@endsection
