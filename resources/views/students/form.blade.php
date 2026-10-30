
@php
    $isEdit = isset($student);

    $enrollment = $isEdit
        ? $student->enrollments->first()
        : null;

    $selectedSchool = old('school_id', $student->school_id ?? '');

    $selectedCampus = old('campus_id', $student->campus_id ?? '');

    $selectedAcademicYear = old(
        'academic_year_id',
        $enrollment->academic_year_id ?? ''
    );

    $selectedClass = old(
        'school_class_id',
        $enrollment->school_class_id ?? ''
    );

    $selectedSection = old(
        'section_id',
        $enrollment->section_id ?? ''
    );
@endphp

<style>
    .student-form {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .student-form-card {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 20px;
    }

    .student-form-heading {
        font-size: 16px;
        font-weight: 700;
        color: #123B5D;
        margin-bottom: 5px;
    }

    .student-form-subtitle {
        color: #64748B;
        font-size: 13px;
        margin-bottom: 20px;
    }

    .student-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .student-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .student-field.full {
        grid-column: 1 / -1;
    }

    .student-field label {
        font-size: 13px;
        font-weight: 600;
        color: #25313C;
    }

    .student-field label span {
        color: #DC2626;
    }

    .student-field input,
    .student-field select {
        width: 100%;
        min-height: 43px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        padding: 9px 12px;
        font-size: 13px;
        color: #25313C;
        background: #fff;
        outline: none;
    }

    .student-field input:focus,
    .student-field select:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
    }

    .student-field input.is-invalid,
    .student-field select.is-invalid {
        border-color: #DC2626;
    }

    .student-error {
        font-size: 12px;
        color: #DC2626;
    }

    .student-form-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
    }

    .student-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 18px;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: none;
    }

    .student-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .student-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .student-btn-secondary {
        background: #F8FAFC;
        border: 1px solid #CBD5E1;
        color: #25313C;
    }

    .student-help-text {
        font-size: 11px;
        color: #64748B;
    }

    @media(max-width: 700px) {
        .student-form-grid {
            grid-template-columns: 1fr;
        }

        .student-field.full {
            grid-column: auto;
        }

        .student-form-actions {
            flex-direction: column-reverse;
            align-items: stretch;
        }
    }
</style>

<div class="student-form">

    <form
        action="{{ $isEdit ? route('students.update', $student->id) : route('students.store') }}"
        method="POST"
    >
        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        {{-- PERSONAL INFORMATION --}}

        <div class="student-form-card">

            <div class="student-form-heading">
                Personal Information
            </div>

            <div class="student-form-subtitle">
                Enter the student's basic personal details.
            </div>

            <div class="student-form-grid">

                <div class="student-field">
                    <label>First Name <span>*</span></label>

                    <input
                        type="text"
                        name="first_name"
                        value="{{ old('first_name', $student->first_name ?? '') }}"
                        class="@error('first_name') is-invalid @enderror"
                        placeholder="Enter first name"
                        required
                    >

                    @error('first_name')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Middle Name</label>

                    <input
                        type="text"
                        name="middle_name"
                        value="{{ old('middle_name', $student->middle_name ?? '') }}"
                        placeholder="Enter middle name"
                    >

                    @error('middle_name')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Last Name <span>*</span></label>

                    <input
                        type="text"
                        name="last_name"
                        value="{{ old('last_name', $student->last_name ?? '') }}"
                        class="@error('last_name') is-invalid @enderror"
                        placeholder="Enter last name"
                        required
                    >

                    @error('last_name')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Date of Birth <span>*</span></label>

                    <input
                        type="date"
                        name="date_of_birth"
                        value="{{ old('date_of_birth', isset($student) ? optional($student->date_of_birth)->format('Y-m-d') : '') }}"
                        class="@error('date_of_birth') is-invalid @enderror"
                        required
                    >

                    @error('date_of_birth')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Gender <span>*</span></label>

                    <select
                        name="gender"
                        required
                        class="@error('gender') is-invalid @enderror"
                    >
                        <option value="">Select Gender</option>

                        <option value="male"
                            @selected(old('gender', $student->gender ?? '') == 'male')>
                            Male
                        </option>

                        <option value="female"
                            @selected(old('gender', $student->gender ?? '') == 'female')>
                            Female
                        </option>

                        <option value="other"
                            @selected(old('gender', $student->gender ?? '') == 'other')>
                            Other
                        </option>
                    </select>

                    @error('gender')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Blood Group</label>

                    <select name="blood_group">
                        <option value="">Select Blood Group</option>

                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $group)
                            <option
                                value="{{ $group }}"
                                @selected(old('blood_group', $student->blood_group ?? '') == $group)
                            >
                                {{ $group }}
                            </option>
                        @endforeach
                    </select>

                    @error('blood_group')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>


        {{-- ADMISSION INFORMATION --}}

        <div class="student-form-card">

            <div class="student-form-heading">
                Admission Information
            </div>

            <div class="student-form-subtitle">
                Select the school and campus where the student is admitted.
            </div>

            <div class="student-form-grid">

                <div class="student-field">
                    <label>School <span>*</span></label>

                    <select
                        name="school_id"
                        required
                        class="@error('school_id') is-invalid @enderror"
                    >
                        <option value="">Select School</option>

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
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Campus <span>*</span></label>

                    <select
                        name="campus_id"
                        required
                        class="@error('campus_id') is-invalid @enderror"
                    >
                        <option value="">Select Campus</option>

                        @foreach($campuses as $campus)
                            <option
                                value="{{ $campus->id }}"
                                @selected($selectedCampus == $campus->id)
                            >
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('campus_id')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Admission Number <span>*</span></label>

                    <input
                        type="text"
                        name="admission_number"
                        value="{{ old('admission_number', $student->admission_number ?? '') }}"
                        class="@error('admission_number') is-invalid @enderror"
                        placeholder="e.g. ADM-2026-001"
                        required
                    >

                    @error('admission_number')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="student-field">
                    <label>Admission Date <span>*</span></label>

                    <input
                        type="date"
                        name="admission_date"
                        value="{{ old('admission_date', isset($student) ? optional($student->admission_date)->format('Y-m-d') : date('Y-m-d')) }}"
                        class="@error('admission_date') is-invalid @enderror"
                        required
                    >

                    @error('admission_date')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>


        {{-- ACADEMIC INFORMATION --}}

        <div class="student-form-card">

            <div class="student-form-heading">
                Academic Information
            </div>

            <div class="student-form-subtitle">
                Assign the student to an academic year, class and section.
            </div>

            <div class="student-form-grid">

                {{-- ACADEMIC YEAR --}}

                <div class="student-field">
                    <label>Academic Year <span>*</span></label>

                    <select
                        name="academic_year_id"
                        required
                        class="@error('academic_year_id') is-invalid @enderror"
                    >
                        <option value="">Select Academic Year</option>

                        @foreach($academicYears as $year)
                            <option
                                value="{{ $year->id }}"
                                @selected($selectedAcademicYear == $year->id)
                            >
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('academic_year_id')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>


                {{-- CLASS --}}

                <div class="student-field">
                    <label>Class <span>*</span></label>

                    <select
                        name="school_class_id"
                        id="studentClassSelect"
                        required
                        class="@error('school_class_id') is-invalid @enderror"
                    >
                        <option value="">Select Class</option>

                        @foreach($classes as $class)
                            <option
                                value="{{ $class->id }}"
                                @selected($selectedClass == $class->id)
                            >
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('school_class_id')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>


                {{-- SECTION --}}

                <div class="student-field">
                    <label>Section <span>*</span></label>

                    <select
                        name="section_id"
                        id="studentSectionSelect"
                        required
                        class="@error('section_id') is-invalid @enderror"
                    >
                        <option value="">Select Section</option>

                        @foreach($sections as $section)
                            <option
                                value="{{ $section->id }}"
                                data-class-id="{{ $section->school_class_id }}"
                                @selected($selectedSection == $section->id)
                            >
                                {{ $section->schoolClass->name }} - Section {{ $section->name }}
                            </option>
                        @endforeach
                    </select>

                    <div class="student-help-text">
                        Sections are automatically filtered according to the selected class.
                    </div>

                    @error('section_id')
                        <div class="student-error">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>


        {{-- FORM ACTIONS --}}

        <div class="student-form-actions">

            <a
                href="{{ route('students.index') }}"
                class="student-btn student-btn-secondary"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="student-btn student-btn-primary"
            >
                {{ $isEdit ? 'Update Student' : 'Create Student' }}
            </button>

        </div>

    </form>
</div>


{{-- SECTION FILTERING SCRIPT --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    const classSelect = document.getElementById('studentClassSelect');
    const sectionSelect = document.getElementById('studentSectionSelect');

    if (!classSelect || !sectionSelect) {
        return;
    }

    function filterSections() {

        const selectedClass = classSelect.value;

        Array.from(sectionSelect.options).forEach(function(option) {

            if (option.value === '') {
                option.hidden = false;
                return;
            }

            const belongsToClass =
                option.dataset.classId === selectedClass;

            option.hidden = !belongsToClass;
        });

        const selectedOption = sectionSelect.selectedOptions[0];

        if (
            selectedOption &&
            selectedOption.value !== '' &&
            selectedOption.dataset.classId !== selectedClass
        ) {
            sectionSelect.value = '';
        }

        if (!selectedClass) {
            sectionSelect.value = '';
        }
    }

    classSelect.addEventListener('change', filterSections);

    // Preserve and filter the selected section during edit.
    filterSections();

});
</script>
