
@php
    $editing = isset($subject) && $subject;
@endphp

<div class="subject-form-grid">

    <div class="sf-field">
        <label>School <span>*</span></label>
        <select name="school_id" required>
            <option value="">Select School</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}"
                    @selected(old('school_id', $subject->school_id ?? '') == $school->id)>
                    {{ $school->name }}
                </option>
            @endforeach
        </select>
        @error('school_id') <small class="sf-error">{{ $message }}</small> @enderror
    </div>

    <div class="sf-field">
        <label>Subject Name <span>*</span></label>
        <input type="text" name="name"
            value="{{ old('name', $subject->name ?? '') }}"
            placeholder="e.g. Mathematics" required maxlength="150">
        @error('name') <small class="sf-error">{{ $message }}</small> @enderror
    </div>

    <div class="sf-field">
        <label>Subject Code</label>
        <input type="text" name="code"
            value="{{ old('code', $subject->code ?? '') }}"
            placeholder="e.g. MAT-09" maxlength="50">
        @error('code') <small class="sf-error">{{ $message }}</small> @enderror
    </div>

    <div class="sf-field">
        <label>Subject Type <span>*</span></label>
        <select name="subject_type" required>
            @foreach([
                'theory' => 'Theory',
                'practical' => 'Practical',
                'both' => 'Theory + Practical',
                'activity' => 'Activity',
                'elective' => 'Elective',
                'language' => 'Language'
            ] as $value => $label)
                <option value="{{ $value }}"
                    @selected(old('subject_type', $subject->subject_type ?? 'theory') == $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('subject_type') <small class="sf-error">{{ $message }}</small> @enderror
    </div>

    <div class="sf-field sf-full">
        <label>Description</label>
        <textarea name="description" rows="4"
            placeholder="Enter subject description">{{ old('description', $subject->description ?? '') }}</textarea>
        @error('description') <small class="sf-error">{{ $message }}</small> @enderror
    </div>

    @if($editing)
    <div class="sf-field">
        <label>Status</label>
        <select name="is_active">
            <option value="1" @selected(old('is_active', $subject->is_active ? '1' : '0') == '1')>Active</option>
            <option value="0" @selected(old('is_active', $subject->is_active ? '1' : '0') == '0')>Inactive</option>
        </select>
    </div>
@else
    <input type="hidden" name="is_active" value="1">
@endif

</div>

<style>
.subject-form-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:20px;
}
.sf-field { display:flex; flex-direction:column; gap:7px; }
.sf-full { grid-column:1/-1; }
.sf-field label { font-size:13px; font-weight:600; color:#25313C; }
.sf-field label span { color:#DC2626; }
.sf-field input,.sf-field select,.sf-field textarea {
    width:100%; box-sizing:border-box; border:1px solid #CBD5E1;
    border-radius:9px; padding:10px 12px; font-size:13px;
    color:#25313C; background:#fff; font-family:inherit;
}
.sf-field input:focus,.sf-field select:focus,.sf-field textarea:focus {
    outline:none; border-color:#176B87;
    box-shadow:0 0 0 3px rgba(23,107,135,.10);
}
.sf-error { color:#DC2626; font-size:12px; }
@media(max-width:650px) {
    .subject-form-grid { grid-template-columns:1fr; }
    .sf-full { grid-column:auto; }
}
</style>
