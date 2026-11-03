
@extends('layouts.schoolnexa')

@section('content')

@php
    $selectedType = $counselling->student_id ? 'student' : 'applicant';
    $selectedStudent = $counselling->student;
    $selectedApplication = $counselling->admissionApplication;
    $selectedGuardian = $selectedStudent?->guardians?->firstWhere('pivot.is_primary', 1)
        ?? $selectedStudent?->guardians?->first();
@endphp

<style>
    .ce-page{font-family:'Inter',sans-serif;color:#25313C;max-width:1050px}
    .ce-header{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:22px;flex-wrap:wrap}
    .ce-header h1{font-size:22px;font-weight:700;color:#123B5D;margin:0 0 5px}
    .ce-header p{font-size:13px;color:#64748B;margin:0}
    .ce-card{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:17px}
    .ce-title{font-size:15px;font-weight:700;color:#123B5D;margin:0 0 5px}
    .ce-subtitle{font-size:12px;color:#64748B;margin:0 0 20px}
    .ce-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}
    .ce-field{min-width:0}
    .ce-full{grid-column:1/-1}
    .ce-field label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:7px}
    .ce-field input,.ce-field select,.ce-field textarea{width:100%;min-height:41px;border:1px solid #CBD5E1;border-radius:8px;padding:9px 11px;font:13px 'Inter',sans-serif;color:#25313C;background:#fff;outline:none}
    .ce-field textarea{min-height:105px;resize:vertical}
    .ce-field input:focus,.ce-field select:focus,.ce-field textarea:focus{border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.09)}
    .ce-required{color:#DC2626}
    .ce-error{font-size:11px;color:#DC2626;margin-top:5px}
    .ce-hint{font-size:11px;color:#94A3B8;margin-top:6px}
    .ce-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid #CBD5E1;border-radius:9px;background:white;color:#123B5D;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer}
    .ce-primary{background:#123B5D;color:white;border-color:#123B5D}
    .ce-primary:hover{background:#176B87;color:white}
    .ce-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}
    .ce-info{background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px;padding:14px 16px;margin-top:17px}
    .ce-info-title{font-size:12px;font-weight:700;color:#123B5D;margin-bottom:10px}
    .ce-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
    .ce-info-label{font-size:10px;font-weight:600;color:#94A3B8;margin-bottom:4px}
    .ce-info-value{font-size:12px;font-weight:600;color:#334155;overflow-wrap:anywhere}
    .ce-alert{background:#FEF2F2;border:1px solid #FECACA;border-radius:9px;padding:12px 14px;color:#991B1B;font-size:12px;margin-bottom:17px}
    @media(max-width:650px){.ce-grid,.ce-info-grid{grid-template-columns:1fr}.ce-card{padding:16px}.ce-header{align-items:flex-start;flex-direction:column}}
</style>

<div class="ce-page">

    <div class="ce-header">
        <div>
            <h1>Edit Counselling Session</h1>
            <p>Update {{ $counselling->session_number }} · {{ $selectedStudent?->full_name ?? $selectedApplication?->applicant_name }}</p>
        </div>

        <a href="{{ route('counselling.show', $counselling) }}" class="ce-btn">
            ← Back to Session
        </a>
    </div>

    @if($errors->any())
        <div class="ce-alert">
            <strong>Please correct the following errors:</strong>
            <ul style="margin:7px 0 0 18px;padding:0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('counselling.update', $counselling) }}">
        @csrf
        @method('PUT')

        {{-- PERSON INFORMATION --}}
        <div class="ce-card">
            <h3 class="ce-title">Student / Applicant Information</h3>
            <p class="ce-subtitle">Update the person linked to this counselling session.</p>

            <div class="ce-grid">

                <div class="ce-field ce-full">
                    <label for="person_type">Counselling For <span class="ce-required">*</span></label>
                    <select id="person_type">
                        <option value="student" @selected(old('student_id', $counselling->student_id) !== null)>Existing Student</option>
                        <option value="applicant" @selected(old('admission_application_id', $counselling->admission_application_id) !== null)>Admission Applicant</option>
                    </select>
                </div>

                <div class="ce-field ce-full" id="student_field">
                    <label for="student_id">Select Student <span class="ce-required">*</span></label>
                    <select name="student_id" id="student_id">
                        <option value="">Select student</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}"
                                data-parent="{{ ($student->guardians->firstWhere('pivot.is_primary', 1) ?? $student->guardians->first())?->full_name ?? '' }}"
                                data-phone="{{ ($student->guardians->firstWhere('pivot.is_primary', 1) ?? $student->guardians->first())?->phone ?? '' }}"
                                data-email="{{ ($student->guardians->firstWhere('pivot.is_primary', 1) ?? $student->guardians->first())?->email ?? '' }}"
                                @selected(old('student_id', $counselling->student_id) == $student->id)>
                                {{ $student->full_name }} — {{ $student->admission_number ?? 'No admission number' }}
                            </option>
                        @endforeach
                    </select>
                    @error('student_id')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field ce-full" id="applicant_field">
                    <label for="admission_application_id">Select Admission Applicant <span class="ce-required">*</span></label>
                    <select name="admission_application_id" id="admission_application_id">
                        <option value="">Select applicant</option>
                        @foreach($applications as $application)
                            <option value="{{ $application->id }}"
                                data-parent="{{ $application->parent_name ?? '' }}"
                                data-phone="{{ $application->parent_phone ?? '' }}"
                                data-email="{{ $application->parent_email ?? '' }}"
                                @selected(old('admission_application_id', $counselling->admission_application_id) == $application->id)>
                                {{ $application->applicant_name }} — {{ $application->application_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('admission_application_id')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

            </div>

            <div class="ce-info">
                <div class="ce-info-title">Parent / Guardian Information</div>
                <div class="ce-info-grid">
                    <div>
                        <div class="ce-info-label">Name</div>
                        <div class="ce-info-value" id="parent_name">—</div>
                    </div>
                    <div>
                        <div class="ce-info-label">Phone</div>
                        <div class="ce-info-value" id="parent_phone">—</div>
                    </div>
                    <div>
                        <div class="ce-info-label">Email</div>
                        <div class="ce-info-value" id="parent_email">—</div>
                    </div>
                </div>
                <div class="ce-hint">Parent details are retrieved from the selected student or admission application.</div>
            </div>
        </div>

        {{-- SESSION DETAILS --}}
        <div class="ce-card">
            <h3 class="ce-title">Session Details</h3>
            <p class="ce-subtitle">Update the appointment and assigned counsellor.</p>

            <div class="ce-grid">

                <div class="ce-field">
                    <label for="counsellor_id">Assigned Counsellor <span class="ce-required">*</span></label>
                    <select name="counsellor_id" id="counsellor_id" required>
                        <option value="">Select counsellor</option>
                        @foreach($counsellors as $counsellor)
                            <option value="{{ $counsellor->id }}" @selected(old('counsellor_id', $counselling->counsellor_id) == $counsellor->id)>
                                {{ $counsellor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('counsellor_id')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field">
                    <label for="session_type">Session Type <span class="ce-required">*</span></label>
                    <select name="session_type" id="session_type" required>
                        @foreach([
                            'admission' => 'Admission Counselling',
                            'academic' => 'Academic Counselling',
                            'career' => 'Career Counselling',
                            'behavioural' => 'Behavioural Counselling',
                            'personal' => 'Personal Counselling',
                            'parent_meeting' => 'Parent Meeting',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}" @selected(old('session_type', $counselling->session_type) == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('session_type')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field">
                    <label for="session_at">Session Date & Time <span class="ce-required">*</span></label>
                    <input type="datetime-local" name="session_at" id="session_at"
                        value="{{ old('session_at', $counselling->session_at?->format('Y-m-d\TH:i')) }}" required>
                    @error('session_at')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field">
                    <label for="next_session_at">Next Follow-up Date & Time</label>
                    <input type="datetime-local" name="next_session_at" id="next_session_at"
                        value="{{ old('next_session_at', $counselling->next_session_at?->format('Y-m-d\TH:i')) }}">
                    <div class="ce-hint">Optional. Must be later than the session date.</div>
                    @error('next_session_at')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field">
                    <label for="status">Session Status <span class="ce-required">*</span></label>
                    <select name="status" id="status" required>
                        <option value="open" @selected(old('status', $counselling->status) === 'open')>Open</option>
                        <option value="completed" @selected(old('status', $counselling->status) === 'completed')>Completed</option>
                        <option value="cancelled" @selected(old('status', $counselling->status) === 'cancelled')>Cancelled</option>
                    </select>
                    @error('status')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

        {{-- NOTES --}}
        <div class="ce-card">
            <h3 class="ce-title">Counselling Notes & Action Plan</h3>
            <p class="ce-subtitle">Update discussion details and next steps.</p>

            <div class="ce-grid">
                <div class="ce-field ce-full">
                    <label for="summary">Session Summary <span class="ce-required">*</span></label>
                    <textarea name="summary" id="summary" required>{{ old('summary', $counselling->summary) }}</textarea>
                    @error('summary')<div class="ce-error">{{ $message }}</div>@enderror
                </div>

                <div class="ce-field ce-full">
                    <label for="action_plan">Action Plan</label>
                    <textarea name="action_plan" id="action_plan">{{ old('action_plan', $counselling->action_plan) }}</textarea>
                    @error('action_plan')<div class="ce-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        <div class="ce-actions">
            <a href="{{ route('counselling.show', $counselling) }}" class="ce-btn">Cancel</a>
            <button type="submit" class="ce-btn ce-primary">Save Changes</button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.getElementById('person_type');
    const studentField = document.getElementById('student_field');
    const applicantField = document.getElementById('applicant_field');
    const student = document.getElementById('student_id');
    const applicant = document.getElementById('admission_application_id');

    function updatePerson() {
        const isStudent = type.value === 'student';

        studentField.style.display = isStudent ? '' : 'none';
        applicantField.style.display = isStudent ? 'none' : '';

        student.disabled = !isStudent;
        applicant.disabled = isStudent;

        student.required = isStudent;
        applicant.required = !isStudent;

        updateParent();
    }

    function updateParent() {
        const select = type.value === 'student' ? student : applicant;
        const option = select.options[select.selectedIndex];

        document.getElementById('parent_name').textContent =
            option?.dataset.parent || 'Not provided';
        document.getElementById('parent_phone').textContent =
            option?.dataset.phone || 'Not provided';
        document.getElementById('parent_email').textContent =
            option?.dataset.email || 'Not provided';
    }

    type.addEventListener('change', updatePerson);
    student.addEventListener('change', updateParent);
    applicant.addEventListener('change', updateParent);

    updatePerson();
});
</script>

@endsection
