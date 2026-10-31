
@php
    $editing = isset($section) && $section;
@endphp

<form method="POST"
      action="{{ $editing ? route('class-sections.sections.update', $section) : route('class-sections.sections.store') }}">
    @csrf
    @if($editing)
        @method('PUT')
    @endif

    <div class="cs-form-grid">
        <div>
            <label>Class <span>*</span></label>
            <select name="school_class_id" required>
                <option value="">Select Class</option>
                @foreach($allClasses as $class)
                    <option value="{{ $class->id }}"
                        @selected(old('school_class_id', $section->school_class_id ?? '') == $class->id)>
                        {{ $class->name }} ({{ $class->school?->name ?? 'School' }})
                    </option>
                @endforeach
            </select>
            @error('school_class_id') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Campus <span>*</span></label>
            <select name="campus_id" required>
                <option value="">Select Campus</option>
                @foreach($campuses as $campus)
                    <option value="{{ $campus->id }}"
                        @selected(old('campus_id', $section->campus_id ?? '') == $campus->id)>
                        {{ $campus->name }}
                    </option>
                @endforeach
            </select>
            @error('campus_id') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Section Name <span>*</span></label>
            <input type="text" name="name" required maxlength="50"
                   placeholder="e.g. C"
                   value="{{ old('name', $section->name ?? '') }}">
            @error('name') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Room Number</label>
            <input type="text" name="room_number" maxlength="50"
                   placeholder="e.g. Room 204"
                   value="{{ old('room_number', $section->room_number ?? '') }}">
            @error('room_number') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        <div>
            <label>Student Capacity</label>
            <input type="number" name="capacity" min="1" max="1000"
                   placeholder="e.g. 40"
                   value="{{ old('capacity', $section->capacity ?? '') }}">
            @error('capacity') <small class="cs-error">{{ $message }}</small> @enderror
        </div>

        @if($editing)
            <div>
                <label>Status</label>
                <select name="is_active" required>
                    <option value="1" @selected(old('is_active', $section->is_active ? '1' : '0') == '1')>Active</option>
                    <option value="0" @selected(old('is_active', $section->is_active ? '1' : '0') == '0')>Inactive</option>
                </select>
            </div>
        @endif
    </div>

    <div class="cs-form-actions">
        <button type="submit" class="cs-primary-btn">
            {{ $editing ? 'Save Changes' : 'Create Section' }}
        </button>
    </div>
</form>
