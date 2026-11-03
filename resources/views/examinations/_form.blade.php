
@php
    $isEdit = isset($examination);

    $subjectRows = old('exam_subjects', $isEdit
        ? $examination->examSubjects->map(fn($item) => [
            'school_class_id' => $item->school_class_id,
            'section_id' => $item->section_id,
            'subject_id' => $item->subject_id,
            'exam_date' => $item->exam_date?->format('Y-m-d'),
            'start_time' => $item->start_time,
            'end_time' => $item->end_time,
            'maximum_marks' => $item->maximum_marks,
            'passing_marks' => $item->passing_marks,
            'room_number' => $item->room_number,
        ])->toArray()
        : [[
            'school_class_id' => '',
            'section_id' => '',
            'subject_id' => '',
            'exam_date' => '',
            'start_time' => '',
            'end_time' => '',
            'maximum_marks' => 100,
            'passing_marks' => 35,
            'room_number' => '',
        ]]
    );
@endphp

<style>
    .exam-form-page {
        max-width: 1200px;
        margin: 0 auto;
        padding: 26px 28px 36px;
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .exam-form-heading {
        font-size: 22px;
        font-weight: 700;
        color: #123B5D;
        margin: 0;
    }

    .exam-form-subtitle {
        color: #64748B;
        font-size: 13px;
        margin-top: 5px;
    }

    .exam-form-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 22px;
        margin-top: 18px;
    }

    .exam-form-section-title {
        font-size: 16px;
        font-weight: 650;
        color: #123B5D;
        margin: 0 0 18px;
    }

    .exam-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .exam-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .exam-form-field.full {
        grid-column: 1 / -1;
    }

    .exam-form-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
    }

    .exam-form-control {
        width: 100%;
        height: 42px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        padding: 0 12px;
        font: 400 13px 'Inter', sans-serif;
        color: #25313C;
        background: #fff;
        box-sizing: border-box;
    }

    textarea.exam-form-control {
        height: 100px;
        padding: 12px;
        resize: vertical;
    }

    .exam-form-control:focus {
        outline: none;
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .exam-form-error {
        color: #DC2626;
        font-size: 11px;
    }

    .exam-subject-card {
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 14px;
        background: #fff;
    }

    .exam-subject-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
    }

    .exam-subject-title {
        font-size: 14px;
        font-weight: 650;
        color: #123B5D;
    }

    .exam-subject-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .exam-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        flex-wrap: wrap;
    }

    .exam-actions form {
        display: inline-flex;
        margin: 0;
    }

    .exam-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 14px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #fff;
        color: #123B5D;
        font: 600 13px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .exam-action:hover {
        background: #F8FAFC;
        color: #123B5D;
    }

    .exam-action-primary {
        color: #fff;
        background: #123B5D;
        border-color: #123B5D;
    }

    .exam-action-primary:hover {
        background: #176B87;
        border-color: #176B87;
        color: #fff;
    }

    .exam-action-danger {
        color: #DC2626;
    }

    .exam-add-button {
        margin-bottom: 15px;
    }

    .exam-info {
        background: #F0F9FF;
        border: 1px solid #BAE6FD;
        border-radius: 9px;
        padding: 12px 14px;
        color: #075985;
        font-size: 12px;
        margin-bottom: 18px;
    }

    @media(max-width: 800px) {
        .exam-subject-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 600px) {
        .exam-form-page {
            padding: 18px 12px 28px;
        }

        .exam-form-grid,
        .exam-subject-grid {
            grid-template-columns: 1fr;
        }

        .exam-form-panel {
            padding: 16px;
        }
    }
</style>

<div class="exam-form-page">

    <div>
        <h1 class="exam-form-heading">
            {{ $isEdit ? 'Edit Examination' : 'Create Examination' }}
        </h1>

        <p class="exam-form-subtitle">
            {{ $isEdit
                ? 'Update examination details and subject schedules.'
                : 'Configure examination details and add subject schedules.' }}
        </p>
    </div>

    @if($errors->any())
        <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:14px;margin-top:18px;color:#991B1B;font-size:13px;">
            <strong>Please correct the following errors:</strong>
            <ul style="padding-left:20px;margin-top:8px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $isEdit
                ? route('examinations.update', $examination)
                : route('examinations.store') }}">

        @csrf

        @if($isEdit)
            @method('PUT')
        @endif

        {{-- Basic Information --}}
        <div class="exam-form-panel">

            <h2 class="exam-form-section-title">
                Examination Information
            </h2>

            <div class="exam-form-grid">

                <div class="exam-form-field">
                    <label class="exam-form-label">School *</label>

                    <select name="school_id"
                            id="school_id"
                            class="exam-form-control"
                            required>
                        <option value="">Select School</option>

                        @foreach($schools as $school)
                            <option value="{{ $school->id }}"
                                @selected(old('school_id', $examination->school_id ?? '') == $school->id)>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">Academic Year *</label>

                    <select name="academic_year_id"
                            id="academic_year_id"
                            class="exam-form-control"
                            required>
                        <option value="">Select Academic Year</option>

                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}"
                                @selected(old('academic_year_id', $examination->academic_year_id ?? '') == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">Examination Name *</label>

                    <input type="text"
                           name="name"
                           class="exam-form-control"
                           placeholder="e.g. First Semester Examination"
                           value="{{ old('name', $examination->name ?? '') }}"
                           required>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">Examination Type *</label>

                    <select name="exam_type"
                            class="exam-form-control"
                            required>
                        <option value="">Select Type</option>

                        @foreach(['Unit Test', 'Mid Term', 'Semester', 'Annual', 'Preliminary', 'Practical', 'Oral', 'Other'] as $type)
                            <option value="{{ $type }}"
                                @selected(old('exam_type', $examination->exam_type ?? '') === $type)>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">Start Date *</label>

                    <input type="date"
                           name="start_date"
                           id="start_date"
                           class="exam-form-control"
                           value="{{ old('start_date', isset($examination) ? $examination->start_date?->format('Y-m-d') : '') }}"
                           required>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">End Date *</label>

                    <input type="date"
                           name="end_date"
                           id="end_date"
                           class="exam-form-control"
                           value="{{ old('end_date', isset($examination) ? $examination->end_date?->format('Y-m-d') : '') }}"
                           required>
                </div>

                <div class="exam-form-field">
                    <label class="exam-form-label">Status *</label>

                    <select name="status"
                            class="exam-form-control"
                            required>
                        <option value="draft"
                            @selected(old('status', $examination->status ?? 'draft') === 'draft')>
                            Draft
                        </option>

                        <option value="published"
                            @selected(old('status', $examination->status ?? '') === 'published')>
                            Published
                        </option>
                    </select>
                </div>

                <div class="exam-form-field full">
                    <label class="exam-form-label">Instructions</label>

                    <textarea name="instructions"
                              class="exam-form-control"
                              placeholder="Enter examination instructions for students...">{{ old('instructions', $examination->instructions ?? '') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Subject Schedule --}}
        <div class="exam-form-panel">

            <div class="exam-subject-header">
                <div>
                    <h2 class="exam-form-section-title" style="margin-bottom:4px;">
                        Subject Schedule
                    </h2>

                    <p class="exam-form-subtitle">
                        Add the subjects and their examination timings.
                    </p>
                </div>

                <button type="button"
                        class="exam-action exam-action-primary"
                        id="addSubject">
                    + Add Subject
                </button>
            </div>

            <div class="exam-info">
                Each subject requires a class, section, subject, date, time and marks configuration.
            </div>

            <div id="subjectContainer">

                @foreach($subjectRows as $index => $row)

                    <div class="exam-subject-card" data-subject-row>

                        <div class="exam-subject-header">
                            <span class="exam-subject-title">
                                Subject <span data-row-number>{{ $loop->iteration }}</span>
                            </span>

                            <button type="button"
                                    class="exam-action exam-action-danger remove-subject"
                                    {{ count($subjectRows) === 1 ? 'disabled' : '' }}>
                                Remove
                            </button>
                        </div>

                        <div class="exam-subject-grid">

                            <div class="exam-form-field">
                                <label class="exam-form-label">Class *</label>

                                <select name="exam_subjects[{{ $index }}][school_class_id]"
                                        class="exam-form-control"
                                        required>
                                    <option value="">Select Class</option>

                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}"
                                            @selected(($row['school_class_id'] ?? '') == $class->id)>
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Section *</label>

                                <select name="exam_subjects[{{ $index }}][section_id]"
                                        class="exam-form-control"
                                        required>
                                    <option value="">Select Section</option>

                                    @foreach($sections as $section)
                                        <option value="{{ $section->id }}"
                                            @selected(($row['section_id'] ?? '') == $section->id)>
                                            {{ $section->schoolClass->name ?? 'Class' }}
                                            - {{ $section->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Subject *</label>

                                <select name="exam_subjects[{{ $index }}][subject_id]"
                                        class="exam-form-control"
                                        required>
                                    <option value="">Select Subject</option>

                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                            @selected(($row['subject_id'] ?? '') == $subject->id)>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Exam Date *</label>

                                <input type="date"
                                       name="exam_subjects[{{ $index }}][exam_date]"
                                       class="exam-form-control subject-date"
                                       value="{{ $row['exam_date'] ?? '' }}"
                                       required>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Start Time *</label>

                                <input type="time"
                                       name="exam_subjects[{{ $index }}][start_time]"
                                       class="exam-form-control"
                                       value="{{ $row['start_time'] ?? '' }}"
                                       required>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">End Time *</label>

                                <input type="time"
                                       name="exam_subjects[{{ $index }}][end_time]"
                                       class="exam-form-control"
                                       value="{{ $row['end_time'] ?? '' }}"
                                       required>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Maximum Marks *</label>

                                <input type="number"
                                       name="exam_subjects[{{ $index }}][maximum_marks]"
                                       class="exam-form-control"
                                       min="1"
                                       step="0.01"
                                       value="{{ $row['maximum_marks'] ?? 100 }}"
                                       required>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Passing Marks *</label>

                                <input type="number"
                                       name="exam_subjects[{{ $index }}][passing_marks]"
                                       class="exam-form-control"
                                       min="0"
                                       step="0.01"
                                       value="{{ $row['passing_marks'] ?? 35 }}"
                                       required>
                            </div>

                            <div class="exam-form-field">
                                <label class="exam-form-label">Room Number</label>

                                <input type="text"
                                       name="exam_subjects[{{ $index }}][room_number]"
                                       class="exam-form-control"
                                       placeholder="e.g. Room 101"
                                       value="{{ $row['room_number'] ?? '' }}">
                            </div>

                        </div>
                    </div>

                @endforeach

            </div>

        </div>

        {{-- Form Actions --}}
        <div class="exam-form-panel">
            <div class="exam-actions">

                <a href="{{ $isEdit
                    ? route('examinations.show', $examination)
                    : route('examinations.index') }}"
                   class="exam-action">
                    Cancel
                </a>

                <button type="submit"
                        class="exam-action exam-action-primary">
                    {{ $isEdit ? 'Update Examination' : 'Save Examination' }}
                </button>

            </div>
        </div>

    </form>

</div>

{{-- Dynamic Subject Row Template --}}
<template id="subjectTemplate">

    <div class="exam-subject-card" data-subject-row>

        <div class="exam-subject-header">
            <span class="exam-subject-title">
                Subject <span data-row-number></span>
            </span>

            <button type="button"
                    class="exam-action exam-action-danger remove-subject">
                Remove
            </button>
        </div>

        <div class="exam-subject-grid">

            <div class="exam-form-field">
                <label class="exam-form-label">Class *</label>

                <select name="exam_subjects[INDEX][school_class_id]"
                        class="exam-form-control"
                        required>
                    <option value="">Select Class</option>

                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Section *</label>

                <select name="exam_subjects[INDEX][section_id]"
                        class="exam-form-control"
                        required>
                    <option value="">Select Section</option>

                    @foreach($sections as $section)
                        <option value="{{ $section->id }}">
                            {{ $section->schoolClass->name ?? 'Class' }} - {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Subject *</label>

                <select name="exam_subjects[INDEX][subject_id]"
                        class="exam-form-control"
                        required>
                    <option value="">Select Subject</option>

                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Exam Date *</label>

                <input type="date"
                       name="exam_subjects[INDEX][exam_date]"
                       class="exam-form-control subject-date"
                       required>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Start Time *</label>

                <input type="time"
                       name="exam_subjects[INDEX][start_time]"
                       class="exam-form-control"
                       required>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">End Time *</label>

                <input type="time"
                       name="exam_subjects[INDEX][end_time]"
                       class="exam-form-control"
                       required>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Maximum Marks *</label>

                <input type="number"
                       name="exam_subjects[INDEX][maximum_marks]"
                       class="exam-form-control"
                       min="1"
                       step="0.01"
                       value="100"
                       required>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Passing Marks *</label>

                <input type="number"
                       name="exam_subjects[INDEX][passing_marks]"
                       class="exam-form-control"
                       min="0"
                       step="0.01"
                       value="35"
                       required>
            </div>

            <div class="exam-form-field">
                <label class="exam-form-label">Room Number</label>

                <input type="text"
                       name="exam_subjects[INDEX][room_number]"
                       class="exam-form-control"
                       placeholder="e.g. Room 101">
            </div>

        </div>
    </div>

</template>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const container = document.getElementById('subjectContainer');
    const template = document.getElementById('subjectTemplate');
    const addButton = document.getElementById('addSubject');

    let nextIndex = Math.max(
        -1,
        ...Array.from(container.querySelectorAll('[data-subject-row]'))
            .map(row => {
                const input = row.querySelector('[name^="exam_subjects["]');
                const match = input?.name.match(/exam_subjects\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            })
    ) + 1;

    function updateRows() {
        const rows = container.querySelectorAll('[data-subject-row]');

        rows.forEach((row, index) => {
            row.querySelector('[data-row-number]').textContent = index + 1;

            const removeButton = row.querySelector('.remove-subject');
            removeButton.disabled = rows.length === 1;
            removeButton.style.opacity = rows.length === 1 ? '.45' : '1';
            removeButton.style.cursor = rows.length === 1 ? 'not-allowed' : 'pointer';
        });
    }

    addButton.addEventListener('click', function () {
        const html = template.innerHTML.replaceAll('INDEX', nextIndex++);
        container.insertAdjacentHTML('beforeend', html);
        updateRows();
    });

    container.addEventListener('click', function (event) {
        const button = event.target.closest('.remove-subject');

        if (!button || container.querySelectorAll('[data-subject-row]').length <= 1) {
            return;
        }

        button.closest('[data-subject-row]').remove();
        updateRows();
    });

    updateRows();

    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    function updateDateLimits() {
        endDate.min = startDate.value || '';

        document.querySelectorAll('.subject-date').forEach(input => {
            input.min = startDate.value || '';
            input.max = endDate.value || '';
        });
    }

    startDate.addEventListener('change', updateDateLimits);
    endDate.addEventListener('change', updateDateLimits);

    updateDateLimits();
});
</script>
