
@extends('layouts.schoolnexa')

@section('content')
<style>
    .app-form-page {font-family:Inter,sans-serif;color:#25313C;}
    .form-title {font-size:22px;font-weight:700;color:#123B5D;}
    .form-subtitle {font-size:13px;color:#64748B;margin-top:5px;}
    .form-card {background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:18px;}
    .form-section {font-size:15px;font-weight:700;color:#123B5D;margin-bottom:5px;}
    .form-hint {font-size:12px;color:#64748B;margin-bottom:20px;}
    .form-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;}
    .form-field {min-width:0;}
    .form-label {display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:7px;}
    .form-control {width:100%;height:42px;border:1px solid #CBD5E1;border-radius:9px;padding:0 12px;font-size:13px;color:#25313C;background:#fff;outline:none;}
    .form-control:focus {border-color:#176B87;box-shadow:0 0 0 3px #176B871A;}
    .form-textarea {width:100%;min-height:90px;border:1px solid #CBD5E1;border-radius:9px;padding:12px;font-size:13px;outline:none;}
    .form-btn {display:inline-flex;align-items:center;justify-content:center;height:40px;padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;}
    .form-btn-primary {background:#123B5D;color:#fff;border-color:#123B5D;}
    .form-error {font-size:11px;color:#DC2626;margin-top:5px;}
    .required {color:#DC2626;}
    @media(max-width:650px) {.form-grid {grid-template-columns:1fr;gap:16px;}}
</style>

<div class="app-form-page">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;">
        <div>
            <div class="form-title">New Admission Application</div>
            <div class="form-subtitle">Register a prospective student for admission.</div>
        </div>
        <a href="{{ route('admission-applications.index') }}" class="form-btn">← Back to Applications</a>
    </div>

    @if($errors->any())
        <div style="padding:13px 16px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;margin-bottom:18px;color:#B91C1C;font-size:13px;">
            Please correct the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ route('admission-applications.store') }}"
          enctype="multipart/form-data">
        @csrf

        {{-- CRM Integration --}}
        <div class="form-card">
            <div class="form-section">CRM Lead Integration</div>
            <div class="form-hint">Link this application to an existing admission enquiry, if applicable.</div>

            <div class="form-field">
                <label class="form-label">Select CRM Lead</label>
                <select name="admission_lead_id" id="leadSelect" class="form-control">
                    <option value="">Direct Application (No CRM Lead)</option>
                    @foreach($leads as $lead)
                        <option value="{{ $lead->id }}"
                            data-student="{{ $lead->student_name }}"
                            data-parent="{{ $lead->parent_name }}"
                            data-phone="{{ $lead->phone }}"
                            data-email="{{ $lead->email }}"
                            data-grade="{{ $lead->prospective_grade }}"
                            @selected(old('admission_lead_id', $selectedLead?->id) == $lead->id)>
                            {{ $lead->lead_number }} — {{ $lead->student_name }}
                        </option>
                    @endforeach
                </select>
                @error('admission_lead_id')<div class="form-error">{{ $message }}</div>@enderror
                <div style="font-size:11px;color:#64748B;margin-top:7px;">
                    Selecting a lead will automatically populate the applicant and parent details.
                </div>
            </div>
        </div>

        {{-- Academic Details --}}
        <div class="form-card">
            <div class="form-section">Academic Information</div>
            <div class="form-hint">Select the academic year and class for which admission is requested.</div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">Academic Year <span class="required">*</span></label>
                    <select name="academic_year_id" class="form-control" required>
                        <option value="">Select academic year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Applying For Class <span class="required">*</span></label>
                    <select name="applying_grade" id="applyingGrade" class="form-control" required>
                        <option value="">Select class</option>
                        @foreach(['Nursery','Jr. KG','Sr. KG','Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12'] as $grade)
                            <option value="{{ $grade }}" @selected(old('applying_grade', $selectedLead?->prospective_grade) === $grade)>
                                {{ $grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('applying_grade')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Student Details --}}
        <div class="form-card">
            <div class="form-section">Student Information</div>
            <div class="form-hint">Enter the applicant's personal details.</div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">Applicant Full Name <span class="required">*</span></label>
                    <input type="text" name="applicant_name" id="applicantName"
                           class="form-control" value="{{ old('applicant_name', $selectedLead?->student_name) }}"
                           placeholder="Enter full name" required>
                    @error('applicant_name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control"
                           value="{{ old('date_of_birth') }}" max="{{ now()->toDateString() }}">
                    @error('date_of_birth')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Parent Details --}}
        <div class="form-card">
            <div class="form-section">Parent / Guardian Information</div>
            <div class="form-hint">Provide contact information for admission communication.</div>

            <div class="form-grid">
                <div class="form-field">
                    <label class="form-label">Parent / Guardian Name</label>
                    <input type="text" name="parent_name" id="parentName"
                           class="form-control" value="{{ old('parent_name', $selectedLead?->parent_name) }}"
                           placeholder="Enter parent name">
                    @error('parent_name')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Contact Number</label>
                    <input type="tel" name="parent_phone" id="parentPhone"
                           class="form-control" value="{{ old('parent_phone', $selectedLead?->phone) }}"
                           placeholder="Enter mobile number">
                    @error('parent_phone')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-field">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="parent_email" id="parentEmail"
                           class="form-control" value="{{ old('parent_email', $selectedLead?->email) }}"
                           placeholder="parent@example.com">
                    @error('parent_email')<div class="form-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="form-card">
            <div class="form-section">Supporting Documents</div>
            <div class="form-hint">Document upload will be enabled after the secure storage and validation endpoint is implemented.</div>

            <div style="padding:15px;background:#F8FAFC;border:1px dashed #CBD5E1;border-radius:10px;font-size:12px;color:#64748B;">
                Document upload is not active yet. You can submit the application details now; documents can be attached in the next implementation step.
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:25px;">
            <a href="{{ route('admission-applications.index') }}" class="form-btn">Cancel</a>
            <button type="submit" class="form-btn form-btn-primary">Submit Application</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const leadSelect = document.getElementById('leadSelect');

    leadSelect.addEventListener('change', function () {
        const option = this.options[this.selectedIndex];

        if (!this.value) return;

        document.getElementById('applicantName').value = option.dataset.student || '';
        document.getElementById('parentName').value = option.dataset.parent || '';
        document.getElementById('parentPhone').value = option.dataset.phone || '';
        document.getElementById('parentEmail').value = option.dataset.email || '';

        const grade = option.dataset.grade || '';
        if (grade) {
            document.getElementById('applyingGrade').value = grade;
        }
    });
});
</script>
@endsection
