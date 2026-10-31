@php
    $isEdit = isset($guardian);

    $selectedStudents = old(
        'student_ids',
        $isEdit ? $guardian->students->pluck('id')->toArray() : []
    );

    $selectedStudents = array_map('strval', $selectedStudents);
@endphp

<style>
    .gdn-form {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .gdn-form-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .gdn-form-heading {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .gdn-form-description {
        font-size: 12px;
        color: #64748B;
        margin-bottom: 20px;
    }

    .gdn-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .gdn-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .gdn-field-full {
        grid-column: 1 / -1;
    }

    .gdn-label {
        font-size: 12px;
        font-weight: 650;
        color: #334155;
    }

    .gdn-required {
        color: #DC2626;
    }

    .gdn-control {
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

    .gdn-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .gdn-help {
        font-size: 11px;
        color: #64748B;
    }

    .gdn-error {
        color: #DC2626;
        font-size: 11px;
    }

    .gdn-student-list {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .gdn-student-option {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        cursor: pointer;
        background: #fff;
    }

    .gdn-student-option:hover {
        border-color: #176B87;
        background: #F8FAFC;
    }

    .gdn-student-option input {
        margin-top: 3px;
        accent-color: #176B87;
    }

    .gdn-student-name {
        font-size: 12px;
        font-weight: 650;
        color: #123B5D;
    }

    .gdn-student-details {
        font-size: 11px;
        color: #64748B;
        margin-top: 4px;
    }

    .gdn-preferences {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .gdn-preference {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 13px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #fff;
    }

    .gdn-preference input {
        margin-top: 3px;
        accent-color: #176B87;
    }

    .gdn-preference-title {
        font-size: 12px;
        font-weight: 650;
        color: #25313C;
    }

    .gdn-preference-description {
        font-size: 11px;
        color: #64748B;
        margin-top: 4px;
    }

    .gdn-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }

    .gdn-btn {
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

    .gdn-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .gdn-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .gdn-btn-outline {
        background: #fff;
        color: #25313C;
        border-color: #CBD5E1;
    }

    @media(max-width: 700px) {
        .gdn-form-grid,
        .gdn-student-list,
        .gdn-preferences {
            grid-template-columns: 1fr;
        }

        .gdn-form-panel {
            padding: 15px;
        }
    }
</style>

<div class="gdn-form">

    <form
        method="POST"
        action="{{ $isEdit
            ? route('guardians.update', $guardian)
            : route('guardians.store') }}"
    >

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        {{-- PERSONAL INFORMATION --}}
        <div class="gdn-form-panel">

            <h3 class="gdn-form-heading">Personal Information</h3>

            <p class="gdn-form-description">
                Enter the parent's basic personal details.
            </p>

            <div class="gdn-form-grid">

                <div class="gdn-field">
                    <label class="gdn-label">
                        Full Name <span class="gdn-required">*</span>
                    </label>

                    <input
                        type="text"
                        name="full_name"
                        class="gdn-control"
                        value="{{ old('full_name', $guardian->full_name ?? '') }}"
                        placeholder="Enter full name"
                        required
                    >

                    @error('full_name')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gdn-field">
                    <label class="gdn-label">
                        Relationship <span class="gdn-required">*</span>
                    </label>

                    <select name="relationship" class="gdn-control" required>
                        <option value="">Select relationship</option>

                        @foreach([
                            'Father',
                            'Mother',
                            'Guardian',
                            'Grandfather',
                            'Grandmother',
                            'Uncle',
                            'Aunt',
                            'Other'
                        ] as $relationship)

                            <option
                                value="{{ $relationship }}"
                                @selected(old(
                                    'relationship',
                                    $guardian->relationship ?? ''
                                ) == $relationship)
                            >
                                {{ $relationship }}
                            </option>

                        @endforeach
                    </select>

                    @error('relationship')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gdn-field">
                    <label class="gdn-label">Occupation</label>

                    <input
                        type="text"
                        name="occupation"
                        class="gdn-control"
                        value="{{ old('occupation', $guardian->occupation ?? '') }}"
                        placeholder="Enter occupation"
                    >

                    @error('occupation')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </div>

        {{-- CONTACT INFORMATION --}}
        <div class="gdn-form-panel">

            <h3 class="gdn-form-heading">Contact Information</h3>

            <p class="gdn-form-description">
                Provide the parent's contact details.
            </p>

            <div class="gdn-form-grid">

                <div class="gdn-field">
                    <label class="gdn-label">
                        Primary Phone <span class="gdn-required">*</span>
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        class="gdn-control"
                        value="{{ old('phone', $guardian->phone ?? '') }}"
                        placeholder="Enter phone number"
                        required
                    >

                    @error('phone')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gdn-field">
                    <label class="gdn-label">Alternate Phone</label>

                    <input
                        type="tel"
                        name="alternate_phone"
                        class="gdn-control"
                        value="{{ old('alternate_phone', $guardian->alternate_phone ?? '') }}"
                        placeholder="Enter alternate phone"
                    >

                    @error('alternate_phone')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gdn-field gdn-field-full">
                    <label class="gdn-label">Email Address</label>

                    <input
                        type="email"
                        name="email"
                        class="gdn-control"
                        value="{{ old('email', $guardian->email ?? '') }}"
                        placeholder="example@email.com"
                    >

                    @error('email')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="gdn-field gdn-field-full">
                    <label class="gdn-label">Residential Address</label>

                    <textarea
                        name="address"
                        class="gdn-control"
                        rows="3"
                        placeholder="Enter complete residential address"
                    >{{ old('address', $guardian->address ?? '') }}</textarea>

                    @error('address')
                        <span class="gdn-error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </div>

        {{-- LINK STUDENTS --}}
        <div class="gdn-form-panel">

            <h3 class="gdn-form-heading">Link Students</h3>

            <p class="gdn-form-description">
                Select the students associated with this parent or guardian.
                You can select more than one student.
            </p>

            @if($students->isEmpty())

                <p class="gdn-help">
                    No student records are available to link.
                </p>

            @else

                <div class="gdn-field">
                    <input
                        type="text"
                        id="gdnStudentSearch"
                        class="gdn-control"
                        placeholder="Search students by name or admission number..."
                    >
                </div>

                <div class="gdn-student-list" id="gdnStudentList">

                    @foreach($students as $student)

                        <label
                            class="gdn-student-option"
                            data-search="{{ strtolower(
                                $student->full_name . ' ' .
                                $student->admission_number
                            ) }}"
                        >

                            <input
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $student->id }}"
                                @checked(in_array(
                                    (string) $student->id,
                                    $selectedStudents,
                                    true
                                ))
                            >

                            <div>
                                <div class="gdn-student-name">
                                    {{ $student->full_name }}
                                </div>

                                <div class="gdn-student-details">
                                    Admission No: {{ $student->admission_number }}
                                </div>

                                <div class="gdn-student-details">
                                    {{ $student->school->name ?? 'School not assigned' }}
                                    ·
                                    {{ $student->campus->name ?? 'Campus not assigned' }}
                                </div>
                            </div>

                        </label>

                    @endforeach

                </div>

                @error('student_ids')
                    <span class="gdn-error">{{ $message }}</span>
                @enderror

                @error('student_ids.*')
                    <span class="gdn-error">{{ $message }}</span>
                @enderror

            @endif

        </div>

        {{-- GUARDIAN PREFERENCES --}}
        <div class="gdn-form-panel">

            <h3 class="gdn-form-heading">Guardian Preferences</h3>

            <p class="gdn-form-description">
                Configure the permissions and communication preferences
                for the selected students.
            </p>

            <div class="gdn-preferences">

                <label class="gdn-preference">
                    <input
                        type="checkbox"
                        name="is_primary"
                        value="1"
                        @checked(old(
                            'is_primary',
                            $isEdit && $guardian->students->contains(
                                fn($student) => $student->pivot->is_primary
                            )
                        ))
                    >

                    <div>
                        <div class="gdn-preference-title">
                            Primary Guardian
                        </div>

                        <div class="gdn-preference-description">
                            Mark this guardian as the primary contact.
                        </div>
                    </div>
                </label>

                <label class="gdn-preference">
                    <input
                        type="checkbox"
                        name="is_emergency_contact"
                        value="1"
                        @checked(old(
                            'is_emergency_contact',
                            $isEdit && $guardian->students->contains(
                                fn($student) => $student->pivot->is_emergency_contact
                            )
                        ))
                    >

                    <div>
                        <div class="gdn-preference-title">
                            Emergency Contact
                        </div>

                        <div class="gdn-preference-description">
                            Allow this guardian to be contacted during emergencies.
                        </div>
                    </div>
                </label>

                <label class="gdn-preference">
                    <input
                        type="checkbox"
                        name="can_pick_up_student"
                        value="1"
                        @checked(old(
                            'can_pick_up_student',
                            $isEdit && $guardian->students->contains(
                                fn($student) => $student->pivot->can_pick_up_student
                            )
                        ))
                    >

                    <div>
                        <div class="gdn-preference-title">
                            Student Pickup Permission
                        </div>

                        <div class="gdn-preference-description">
                            Allow this guardian to pick up the student.
                        </div>
                    </div>
                </label>

                <label class="gdn-preference">
                    <input
                        type="checkbox"
                        name="receives_communications"
                        value="1"
                        @checked(old(
                            'receives_communications',
                            $isEdit && $guardian->students->contains(
                                fn($student) => $student->pivot->receives_communications
                            )
                        ))
                    >

                    <div>
                        <div class="gdn-preference-title">
                            Receive Communications
                        </div>

                        <div class="gdn-preference-description">
                            Allow this guardian to receive school notifications.
                        </div>
                    </div>
                </label>

            </div>
        </div>

        {{-- FORM ACTIONS --}}
        <div class="gdn-form-actions">

            <a href="{{ route('guardians.index') }}"
               class="gdn-btn gdn-btn-outline">
                Cancel
            </a>

            <button type="submit" class="gdn-btn gdn-btn-primary">
                {{ $isEdit ? 'Update Parent' : 'Save Parent' }}
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('gdnStudentSearch');
    const studentOptions = document.querySelectorAll('.gdn-student-option');

    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        const search = this.value.toLowerCase().trim();

        studentOptions.forEach(function (option) {
            const text = option.dataset.search || '';
            option.style.display = text.includes(search) ? 'flex' : 'none';
        });
    });
});
</script>
