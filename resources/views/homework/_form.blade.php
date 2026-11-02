
@php($isEdit = isset($homework) && $homework)

<div class="hw-form-page">

    {{-- Page Header --}}
    <div class="hw-form-header">
        <div>
            <h1>{{ $pageTitle }}</h1>
            <p>Provide assignment details, schedule and class allocation.</p>
        </div>

        <a href="{{ route('homework.index') }}" class="hw-back-button">
            Back to Homework
        </a>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="hw-error">
            <strong>Please correct the following errors:</strong>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" enctype="multipart/form-data">
        @csrf

        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif

        <section class="hw-form-panel">

            <div class="hw-section-heading">
                <div>
                    <h2>Assignment Details</h2>
                    <p>Enter the homework information and select the class allocation.</p>
                </div>
            </div>

            <div class="hw-form-grid">

                {{-- Homework Title --}}
                <div class="hw-field hw-span-2">
                    <label for="hwTitle">Homework Title <span>*</span></label>
                    <input
                        type="text"
                        id="hwTitle"
                        name="title"
                        value="{{ old('title', $homework->title ?? '') }}"
                        placeholder="e.g. Chapter 3 - Algebra Practice"
                        required
                    >
                    @error('title') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Academic Year --}}
                <div class="hw-field">
                    <label for="hwAcademicYear">Academic Year <span>*</span></label>
                    <select name="academic_year_id" id="hwAcademicYear" required>
                        <option value="">Select Academic Year</option>
                        @foreach($academicYears as $year)
                            <option
                                value="{{ $year->id }}"
                                @selected(old('academic_year_id', $homework->academic_year_id ?? '') == $year->id)
                            >
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('academic_year_id') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Class and Section --}}
                <div class="hw-field">
                    <label for="hwSection">Class & Section <span>*</span></label>
                    <select name="section_id" id="hwSection" required>
                        <option value="">Select Section</option>
                        @foreach($sections as $section)
                            <option
                                value="{{ $section->id }}"
                                data-class="{{ $section->school_class_id }}"
                                @selected(old('section_id', $homework->section_id ?? '') == $section->id)
                            >
                                {{ $section->schoolClass->name ?? 'Class' }} - {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('section_id') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Subject --}}
                <div class="hw-field">
                    <label for="hwSubject">Subject <span>*</span></label>
                    <select name="subject_id" id="hwSubject" required>
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option
                                value="{{ $subject->id }}"
                                @selected(old('subject_id', $homework->subject_id ?? '') == $subject->id)
                            >
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Assigned Teacher --}}
                <div class="hw-field">
                    <label for="hwTeacher">Assigned Teacher <span>*</span></label>
                    <select name="staff_profile_id" id="hwTeacher" required>
                        <option value="">Select Teacher</option>
                        @foreach($teachers as $teacher)
                            <option
                                value="{{ $teacher->id }}"
                                @selected(old('staff_profile_id', $homework->staff_profile_id ?? '') == $teacher->id)
                            >
                                {{ trim($teacher->first_name . ' ' . $teacher->last_name) }} · {{ $teacher->employee_number }}
                            </option>
                        @endforeach
                    </select>
                    @error('staff_profile_id') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Assigned Date --}}
                <div class="hw-field">
                    <label for="hwAssignedDate">Assigned Date <span>*</span></label>
                    <input
                        type="date"
                        name="assigned_date"
                        id="hwAssignedDate"
                        value="{{ old('assigned_date', isset($homework->assigned_date) ? $homework->assigned_date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                        required
                    >
                    @error('assigned_date') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Due Date --}}
                <div class="hw-field">
                    <label for="hwDueDate">Due Date</label>
                    <input
                        type="date"
                        name="due_date"
                        id="hwDueDate"
                        value="{{ old('due_date', isset($homework->due_date) ? $homework->due_date->format('Y-m-d') : '') }}"
                    >
                    @error('due_date') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Status --}}
                <div class="hw-field">
                    <label for="hwStatus">Status <span>*</span></label>
                    <select name="status" id="hwStatus" required>
                        @foreach(['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label)
                            <option
                                value="{{ $value }}"
                                @selected(old('status', $homework->status ?? 'published') === $value)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Instructions --}}
                <div class="hw-field hw-span-2">
                    <label for="hwInstructions">Instructions</label>
                    <textarea
                        name="instructions"
                        id="hwInstructions"
                        rows="5"
                        placeholder="Explain the homework task, topics to cover and submission requirements..."
                    >{{ old('instructions', $homework->instructions ?? '') }}</textarea>
                    @error('instructions') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

                {{-- Attachment --}}
                <div class="hw-field hw-span-2">
                    <label for="hwAttachment">Attachment</label>
                    <div class="hw-upload-box">
                        <input
                            type="file"
                            name="attachment"
                            id="hwAttachment"
                            accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                        >
                        <small>Supported formats: PDF, DOC, DOCX, PPT, PPTX, JPG and PNG. Maximum file size: 10 MB.</small>

                        @if($isEdit && $homework->attachment_path)
                            <div class="hw-current-file">
                                Current attachment:
                                <a href="{{ asset('storage/' . $homework->attachment_path) }}" target="_blank">
                                    View existing file
                                </a>
                            </div>
                        @endif
                    </div>
                    @error('attachment') <small class="hw-field-error">{{ $message }}</small> @enderror
                </div>

            </div>
        </section>

        {{-- Form Actions --}}
        <div class="hw-form-footer">
            <a href="{{ route('homework.index') }}" class="hw-cancel-button">
                Cancel
            </a>

            <button class="hw-submit-button" type="submit">
                {{ $submitLabel }}
            </button>
        </div>

    </form>
</div>

<style>
/* =========================================
   HOMEWORK FORM - SCHOOLNEXA DESIGN SYSTEM
========================================= */

.hw-form-page {
    font-family: 'Inter', sans-serif;
    color: #25313C;
    padding: 22px 24px;
    box-sizing: border-box;
}

.hw-form-page * {
    box-sizing: border-box;
}

/* Header */

.hw-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.hw-form-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #123B5D;
    margin: 0;
}

.hw-form-header p {
    font-size: 13px;
    color: #64748B;
    margin: 5px 0 0;
}

/* Back Button */

.hw-back-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 46px;
    padding: 0 19px;
    border: 1px solid #CBD5E1;
    border-radius: 11px;
    background: #FFFFFF;
    color: #123B5D;
    font: 600 14px 'Inter', sans-serif;
    text-decoration: none;
    white-space: nowrap;
    transition: background .2s ease;
}

.hw-back-button:hover {
    background: #F8FAFC;
    color: #123B5D;
}

/* Main Panel */

.hw-form-panel {
    background: #FFFFFF;
    border: 1px solid #CBD5E1;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 16px;
}

.hw-section-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 22px;
    padding-bottom: 15px;
    border-bottom: 1px solid #E2E8F0;
}

.hw-section-heading h2 {
    font-size: 16px;
    font-weight: 650;
    color: #123B5D;
    margin: 0;
}

.hw-section-heading p {
    font-size: 12px;
    color: #64748B;
    margin: 5px 0 0;
}

/* Form Grid */

.hw-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px 22px;
}

.hw-span-2 {
    grid-column: span 2;
}

/* Labels */

.hw-field {
    min-width: 0;
}

.hw-field label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    margin-bottom: 8px;
}

.hw-field label span {
    color: #DC2626;
}

/* Inputs, Selects and Textarea */

.hw-field input:not([type="file"]),
.hw-field select,
.hw-field textarea {
    display: block;
    width: 100%;
    min-height: 43px;
    padding: 10px 12px;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background-color: #FFFFFF;
    color: #25313C;
    font-family: 'Inter', sans-serif;
    font-size: 13px;
    font-weight: 400;
    outline: none;
    box-shadow: none;
    transition: border-color .2s ease, box-shadow .2s ease;
}

.hw-field input::placeholder,
.hw-field textarea::placeholder {
    color: #94A3B8;
    opacity: 1;
}

.hw-field select {
    cursor: pointer;
}

.hw-field textarea {
    min-height: 125px;
    resize: vertical;
    line-height: 1.6;
}

.hw-field input:focus,
.hw-field select:focus,
.hw-field textarea:focus {
    border-color: #176B87;
    box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
}

/* File Upload */

.hw-upload-box {
    padding: 14px;
    background: #F8FAFC;
    border: 1px dashed #CBD5E1;
    border-radius: 10px;
}

.hw-field input[type="file"] {
    display: block;
    width: 100%;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    color: #475569;
}

.hw-field input[type="file"]::file-selector-button {
    min-height: 36px;
    padding: 0 13px;
    margin-right: 12px;
    border: 1px solid #CBD5E1;
    border-radius: 8px;
    background: #FFFFFF;
    color: #123B5D;
    font-family: 'Inter', sans-serif;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.hw-upload-box > small {
    display: block;
    margin-top: 10px;
    font-size: 11px;
    color: #64748B;
}

.hw-current-file {
    margin-top: 10px;
    font-size: 12px;
    color: #475569;
}

.hw-current-file a {
    color: #176B87;
    font-weight: 600;
    text-decoration: none;
}

.hw-current-file a:hover {
    text-decoration: underline;
}

/* Validation */

.hw-error {
    padding: 13px 16px;
    margin-bottom: 16px;
    border: 1px solid #FECACA;
    background: #FEF2F2;
    color: #B91C1C;
    border-radius: 9px;
    font-size: 13px;
}

.hw-error strong {
    display: block;
    margin-bottom: 5px;
}

.hw-field-error {
    display: block;
    margin-top: 6px;
    color: #DC2626;
    font-size: 11px;
}

/* Footer Buttons */

.hw-form-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 9px;
    margin-top: 16px;
}

.hw-cancel-button,
.hw-submit-button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 42px;
    padding: 0 17px;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background: #FFFFFF;
    color: #123B5D;
    font: 600 13px 'Inter', sans-serif;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background .2s ease;
}

.hw-cancel-button:hover {
    background: #F8FAFC;
    color: #123B5D;
}

.hw-submit-button {
    background: #123B5D;
    border-color: #123B5D;
    color: #FFFFFF;
}

.hw-submit-button:hover {
    background: #0E304C;
    border-color: #0E304C;
    color: #FFFFFF;
}

/* Responsive */

@media(max-width: 768px) {
    .hw-form-page {
        padding: 16px;
    }

    .hw-form-panel {
        padding: 17px;
    }

    .hw-form-grid {
        grid-template-columns: 1fr;
        gap: 17px;
    }

    .hw-span-2 {
        grid-column: span 1;
    }
}

@media(max-width: 480px) {
    .hw-form-header {
        align-items: flex-start;
    }

    .hw-form-footer {
        flex-wrap: wrap;
    }

    .hw-cancel-button,
    .hw-submit-button {
        flex: 1;
    }
}
</style>
