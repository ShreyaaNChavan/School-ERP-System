
@php
    $editing = isset($schoolClass) && $schoolClass;
@endphp

<form method="POST"
      action="{{ $editing ? route('class-sections.classes.update', $schoolClass) : route('class-sections.classes.store') }}">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="cs-form-grid">
        <div>
            <label>School <span>*</span></label>
            <select name="school_id" required>
                <option value="">Select School</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}"
                        @selected(old('school_id', $schoolClass->school_id ?? '') == $school->id)>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
            @error('school_id') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Class Name <span>*</span></label>
            <input type="text" name="name" required maxlength="100"
                   placeholder="e.g. Class 11"
                   value="{{ old('name', $schoolClass->name ?? '') }}">
            @error('name') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Class Code</label>
            <input type="text" name="code" maxlength="30"
                   placeholder="e.g. C11"
                   value="{{ old('code', $schoolClass->code ?? '') }}">
            @error('code') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Grade Level</label>
            <input type="number" name="grade_level" min="0" max="20"
                   placeholder="e.g. 11"
                   value="{{ old('grade_level', $schoolClass->grade_level ?? '') }}">
            @error('grade_level') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Education Stage</label>
            <select name="education_stage">
                <option value="">Select Stage</option>
                @foreach(['Primary', 'Middle School', 'Secondary', 'Higher Secondary', 'Pre-Primary'] as $stage)
                    <option value="{{ $stage }}"
                        @selected(old('education_stage', $schoolClass->education_stage ?? '') === $stage)>
                        {{ $stage }}
                    </option>
                @endforeach
            </select>
            @error('education_stage') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        @if($editing)
            <div>
                <label>Status</label>
                <select name="is_active" required>
                    <option value="1" @selected(old('is_active', $schoolClass->is_active ? '1' : '0') == '1')>Active</option>
                    <option value="0" @selected(old('is_active', $schoolClass->is_active ? '1' : '0') == '0')>Inactive</option>
                </select>
            </div>
        @endif
    </div>

    <div class="cs-form-actions">
        <button type="submit" class="cs-primary-btn">
            {{ $editing ? 'Save Changes' : 'Create Class' }}
        </button>
    </div>
</form>
