
@php
    $isEdit = isset($staff);

    $selectedSchool = old('school_id', $staff->school_id ?? '');
    $selectedCampus = old('campus_id', $staff->campus_id ?? '');
@endphp

<style>
    .stf-form {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .stf-form-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .stf-form-heading {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .stf-form-description {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 20px;
    }

    .stf-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .stf-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 0;
    }

    .stf-field-full {
        grid-column: 1 / -1;
    }

    .stf-label {
        font-size: 12px;
        font-weight: 650;
        color: #334155;
    }

    .stf-required {
        color: #DC2626;
    }

    .stf-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        font-size: 13px;
        color: #25313C;
        background: #fff;
        outline: none;
        box-sizing: border-box;
    }

    .stf-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .stf-help {
        font-size: 11px;
        color: #64748B;
    }

    .stf-error {
        color: #DC2626;
        font-size: 11px;
    }

    .stf-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .stf-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 17px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .stf-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .stf-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .stf-btn-outline {
        background: #fff;
        color: #25313C;
        border-color: #CBD5E1;
    }

    @media(max-width: 700px) {
        .stf-form-grid {
            grid-template-columns: 1fr;
        }

        .stf-form-panel {
            padding: 15px;
        }
    }
</style>

<div class="stf-form">

    <form
        method="POST"
        action="{{ $isEdit
            ? route('staff.update', $staff)
            : route('staff.store') }}"
    >

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        {{-- PERSONAL INFORMATION --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">Personal Information</h3>

            <p class="stf-form-description">
                Enter the employee's basic identification details.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field">
                    <label class="stf-label">
                        Employee Number <span class="stf-required">*</span>
                    </label>

                    <input
                        type="text"
                        name="employee_number"
                        class="stf-control"
                        value="{{ old('employee_number', $staff->employee_number ?? '') }}"
                        placeholder="e.g. EMP-2026-001"
                        required
                    >

                    @error('employee_number')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">
                        First Name <span class="stf-required">*</span>
                    </label>

                    <input
                        type="text"
                        name="first_name"
                        class="stf-control"
                        value="{{ old('first_name', $staff->first_name ?? '') }}"
                        placeholder="Enter first name"
                        required
                    >

                    @error('first_name')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">Middle Name</label>

                    <input
                        type="text"
                        name="middle_name"
                        class="stf-control"
                        value="{{ old('middle_name', $staff->middle_name ?? '') }}"
                        placeholder="Enter middle name"
                    >

                    @error('middle_name')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">
                        Last Name <span class="stf-required">*</span>
                    </label>

                    <input
                        type="text"
                        name="last_name"
                        class="stf-control"
                        value="{{ old('last_name', $staff->last_name ?? '') }}"
                        placeholder="Enter last name"
                        required
                    >

                    @error('last_name')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

        </div>

        {{-- EMPLOYMENT INFORMATION --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">Employment Information</h3>

            <p class="stf-form-description">
                Specify the employee's role and employment details.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field">
                    <label class="stf-label">Designation</label>

                    <select name="designation" class="stf-control">
                        <option value="">Select designation</option>

                        @foreach($designations as $designation)

                            <option
                                value="{{ $designation->name }}"
                                @selected(old(
                                    'designation',
                                    $staff->designation ?? ''
                                ) == $designation->name)
                            >
                                {{ $designation->name }}
                            </option>

                        @endforeach

                        @if($isEdit && $staff->designation &&
                            !$designations->contains('name', $staff->designation))
                            <option value="{{ $staff->designation }}" selected>
                                {{ $staff->designation }}
                            </option>
                        @endif

                    </select>

                    @error('designation')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">Department</label>

                    <select name="department" class="stf-control">
                        <option value="">Select department</option>

                        @foreach($departments as $department)

                            <option
                                value="{{ $department->name }}"
                                @selected(old(
                                    'department',
                                    $staff->department ?? ''
                                ) == $department->name)
                            >
                                {{ $department->name }}
                            </option>

                        @endforeach

                        @if($isEdit && $staff->department &&
                            !$departments->contains('name', $staff->department))
                            <option value="{{ $staff->department }}" selected>
                                {{ $staff->department }}
                            </option>
                        @endif

                    </select>

                    @error('department')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">Employment Type</label>

                    @php
                        $employmentTypes = [
                            'permanent' => 'Permanent',
                            'contract' => 'Contract',
                            'temporary' => 'Temporary',
                            'part-time' => 'Part-time',
                            'visiting' => 'Visiting',
                        ];
                    @endphp

                    <select name="employment_type" class="stf-control">
                        <option value="">Select employment type</option>

                        @foreach($employmentTypes as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old(
                                    'employment_type',
                                    $staff->employment_type ?? ''
                                ) == $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach

                    </select>

                    @error('employment_type')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">Joining Date</label>

                    <input
                        type="date"
                        name="joining_date"
                        class="stf-control"
                        value="{{ old(
                            'joining_date',
                            isset($staff) && $staff->joining_date
                                ? $staff->joining_date->format('Y-m-d')
                                : ''
                        ) }}"
                    >

                    @error('joining_date')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

        </div>

        {{-- SCHOOL AND CAMPUS --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">School & Campus Assignment</h3>

            <p class="stf-form-description">
                Select the school and campus where this employee works.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field">
                    <label class="stf-label">
                        School <span class="stf-required">*</span>
                    </label>

                    <select
                        name="school_id"
                        id="stfSchool"
                        class="stf-control"
                        required
                    >
                        <option value="">Select school</option>

                        @foreach($schools as $school)
                            <option
                                value="{{ $school->id }}"
                                @selected($selectedSchool == $school->id)
                            >
                                {{ $school->name }}
                            </option>
                        @endforeach

                    </select>

                    @error('school_id')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="stf-field">
                    <label class="stf-label">Campus</label>

                    <select
                        name="campus_id"
                        id="stfCampus"
                        class="stf-control"
                    >
                        <option value="">Select campus</option>

                        @foreach($campuses as $campus)
                            <option
                                value="{{ $campus->id }}"
                                data-school="{{ $campus->school_id }}"
                                @selected($selectedCampus == $campus->id)
                            >
                                {{ $campus->name }}
                            </option>
                        @endforeach

                    </select>

                    <span class="stf-help">
                        Select a school first to filter its campuses.
                    </span>

                    @error('campus_id')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

        </div>

        {{-- CONTACT INFORMATION --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">Contact Information</h3>

            <p class="stf-form-description">
                Add the employee's contact details.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field">
                    <label class="stf-label">Phone Number</label>

                    <input
                        type="tel"
                        name="phone"
                        class="stf-control"
                        value="{{ old('phone', $staff->phone ?? '') }}"
                        placeholder="Enter phone number"
                    >

                    @error('phone')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

        </div>

        {{-- USER ACCOUNT --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">User Account Linking</h3>

            <p class="stf-form-description">
                Optionally connect this employee with an existing login account.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field stf-field-full">

                    <label class="stf-label">Existing User Account</label>

                    <select name="user_id" class="stf-control">
                        <option value="">No account linked</option>

                        @foreach($users as $user)
                            <option
                                value="{{ $user->id }}"
                                @selected(old(
                                    'user_id',
                                    $staff->user_id ?? ''
                                ) == $user->id)
                            >
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach

                    </select>

                    <span class="stf-help">
                        You can link an existing account now or leave this empty
                        until the employee receives login access.
                    </span>

                    @error('user_id')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror

                </div>

            </div>

        </div>

        {{-- STATUS --}}
        <div class="stf-form-panel">

            <h3 class="stf-form-heading">Employment Status</h3>

            <p class="stf-form-description">
                Set the current employment status.
            </p>

            <div class="stf-form-grid">

                <div class="stf-field">
                    <label class="stf-label">
                        Status <span class="stf-required">*</span>
                    </label>

                    <select name="status" class="stf-control" required>

                        <option
                            value="active"
                            @selected(old('status', $staff->status ?? 'active') == 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected(old('status', $staff->status ?? 'active') == 'inactive')
                        >
                            Inactive
                        </option>

                    </select>

                    @error('status')
                        <span class="stf-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>

        </div>

        {{-- ACTIONS --}}
        <div class="stf-form-actions">

            <a href="{{ route('staff.index') }}"
               class="stf-btn stf-btn-outline">
                Cancel
            </a>

            <button type="submit" class="stf-btn stf-btn-primary">
                {{ $isEdit ? 'Update Staff' : 'Save Staff' }}
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const schoolSelect = document.getElementById('stfSchool');
    const campusSelect = document.getElementById('stfCampus');

    if (!schoolSelect || !campusSelect) return;

    function filterCampuses() {
        const schoolId = schoolSelect.value;

        Array.from(campusSelect.options).forEach(function (option) {
            if (!option.value) return;

            const matches = option.dataset.school === schoolId;

            option.hidden = !matches;
            option.disabled = !matches;
        });

        const selectedOption = campusSelect.selectedOptions[0];

        if (selectedOption && selectedOption.value &&
            selectedOption.dataset.school !== schoolId) {
            campusSelect.value = '';
        }
    }

    schoolSelect.addEventListener('change', filterCampuses);

    filterCampuses();
});
</script>
