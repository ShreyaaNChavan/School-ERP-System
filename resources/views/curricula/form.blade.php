
@php
    $editing = isset($curriculum) && $curriculum;
@endphp

<div class="cv-form-grid">

    <div class="cv-field">
        <label>School <span>*</span></label>
        <select name="school_id" required>
            <option value="">Select School</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}"
                    @selected(old('school_id', $curriculum->school_id ?? '') == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>
        @error('school_id') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

    <div class="cv-field">
        <label>Academic Year <span>*</span></label>
        <select name="academic_year_id" required>
            <option value="">Select Academic Year</option>
            @foreach($academicYears as $year)
                <option value="{{ $year->id }}"
                    @selected(old('academic_year_id', $curriculum->academic_year_id ?? '') == $year->id)>
                    {{ $year->name }} — {{ $year->school->name ?? 'School' }}
                </option>
            @endforeach
        </select>
        @error('academic_year_id') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

    <div class="cv-field">
        <label>Class <span>*</span></label>
        <select name="school_class_id" required>
            <option value="">Select Class</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}"
                    @selected(old('school_class_id', $curriculum->school_class_id ?? '') == $class->id)>
                    {{ $class->name }} — {{ $class->school->name ?? 'School' }}
                </option>
            @endforeach
        </select>
        @error('school_class_id') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

    <div class="cv-field">
        <label>Subject <span>*</span></label>
        <select name="subject_id" required>
            <option value="">Select Subject</option>
            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}"
                    @selected(old('subject_id', $curriculum->subject_id ?? '') == $subject->id)>
                    {{ $subject->name }} — {{ $subject->school->name ?? 'School' }}
                </option>
            @endforeach
        </select>
        @error('subject_id') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

    <div class="cv-field cv-full">
        <label>Curriculum Title <span>*</span></label>
        <input type="text" name="title" maxlength="200" required
            value="{{ old('title', $curriculum->title ?? '') }}"
            placeholder="e.g. Class 9 Mathematics Curriculum">
        @error('title') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

    <div class="cv-field cv-full">
        <label>Description</label>
        <textarea name="description" rows="4"
            placeholder="Enter curriculum objectives and overview...">{{ old('description', $curriculum->description ?? '') }}</textarea>
        @error('description') <small class="cv-error">{{ $message }}</small> @enderror
    </div>

</div>

<style>
.cv-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:19px}
.cv-field{display:flex;flex-direction:column;gap:7px}
.cv-full{grid-column:1/-1}
.cv-field label{font-size:13px;font-weight:600;color:#25313C}
.cv-field label span{color:#DC2626}
.cv-field input,.cv-field select,.cv-field textarea{
    width:100%;box-sizing:border-box;border:1px solid #CBD5E1;
    border-radius:9px;padding:10px 12px;font-size:13px;color:#25313C;
    background:#fff;font-family:inherit
}
.cv-field input:focus,.cv-field select:focus,.cv-field textarea:focus{
    outline:none;border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.1)
}
.cv-error{font-size:12px;color:#DC2626}
@media(max-width:650px){.cv-form-grid{grid-template-columns:1fr}.cv-full{grid-column:auto}}
</style>
