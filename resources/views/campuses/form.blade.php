
@once
<style>
    .campus-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .campus-page * { box-sizing: border-box; }

    .campus-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .campus-section-label {
        grid-column: 1 / -1;
        color: #176B87;
        font-size: 12px;
        font-weight: 700;
        padding-bottom: 9px;
        border-bottom: 1px solid #E2E8F0;
        margin-top: 5px;
    }

    .campus-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .campus-form-field.full-width {
        grid-column: 1 / -1;
    }

    .campus-form-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .campus-form-field label span {
        color: #DC2626;
    }

    .campus-control {
        width: 100%;
        min-height: 42px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        padding: 10px 12px;
        color: #25313C;
        background: #fff;
        font: 400 13px 'Inter', sans-serif;
        outline: none;
    }

    .campus-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .campus-control::placeholder {
        color: #94A3B8;
    }

    .campus-error {
        color: #DC2626;
        font-size: 11px;
    }

    .campus-required-note {
        color: #64748B;
        font-size: 11px;
        margin-bottom: 18px;
    }

    .campus-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #E2E8F0;
    }

    .campus-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 9px;
        font: 600 13px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
    }

    .campus-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .campus-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .campus-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    @media (max-width: 650px) {
        .campus-form-grid { grid-template-columns: 1fr; }

        .campus-form-field.full-width,
        .campus-section-label {
            grid-column: auto;
        }
    }
</style>
@endonce

@if($errors->any())
    <div style="background:#FEE2E2;color:#991B1B;padding:13px 15px;border-radius:9px;margin-bottom:18px;font-size:13px;">
        Please correct the errors below before submitting the form.
    </div>
@endif

<p class="campus-required-note">
    Fields marked with <span style="color:#DC2626;">*</span> are required.
</p>

<div class="campus-form-grid">

    {{-- Parent School --}}
    <div class="campus-section-label">School Assignment</div>

    <div class="campus-form-field full-width">
        <label for="school_id">Parent School <span>*</span></label>

        <select name="school_id" id="school_id" class="campus-control" required>
            <option value="">Select School</option>

            @foreach($schools as $school)
                <option
                    value="{{ $school->id }}"
                    @selected((string) old('school_id', $campus->school_id ?? '') === (string) $school->id)
                >
                    {{ $school->name }}
                    @if($school->organization)
                        — {{ $school->organization->name }}
                    @endif
                </option>
            @endforeach
        </select>

        @error('school_id')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Basic Details --}}
    <div class="campus-section-label">Basic Details</div>

    <div class="campus-form-field">
        <label for="name">Campus Name <span>*</span></label>

        <input
            type="text"
            name="name"
            id="name"
            class="campus-control"
            placeholder="Enter campus name"
            value="{{ old('name', $campus->name ?? '') }}"
            required
        >

        @error('name')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="code">Campus Code <span>*</span></label>

        <input
            type="text"
            name="code"
            id="code"
            class="campus-control"
            placeholder="Example: CAMP001"
            value="{{ old('code', $campus->code ?? '') }}"
            required
        >

        @error('code')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Address --}}
    <div class="campus-section-label">Address Information</div>

    <div class="campus-form-field full-width">
        <label for="address_line_1">Address Line 1</label>

        <input
            type="text"
            name="address_line_1"
            id="address_line_1"
            class="campus-control"
            placeholder="Building, street or area"
            value="{{ old('address_line_1', $campus->address_line_1 ?? '') }}"
        >

        @error('address_line_1')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field full-width">
        <label for="address_line_2">Address Line 2</label>

        <input
            type="text"
            name="address_line_2"
            id="address_line_2"
            class="campus-control"
            placeholder="Additional address information"
            value="{{ old('address_line_2', $campus->address_line_2 ?? '') }}"
        >

        @error('address_line_2')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="city">City</label>

        <input
            type="text"
            name="city"
            id="city"
            class="campus-control"
            placeholder="Enter city"
            value="{{ old('city', $campus->city ?? '') }}"
        >

        @error('city')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="state">State</label>

        <input
            type="text"
            name="state"
            id="state"
            class="campus-control"
            placeholder="Enter state"
            value="{{ old('state', $campus->state ?? '') }}"
        >

        @error('state')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="postal_code">Postal Code</label>

        <input
            type="text"
            name="postal_code"
            id="postal_code"
            class="campus-control"
            placeholder="Enter postal code"
            value="{{ old('postal_code', $campus->postal_code ?? '') }}"
        >

        @error('postal_code')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="phone">Contact Number</label>

        <input
            type="text"
            name="phone"
            id="phone"
            class="campus-control"
            placeholder="Enter contact number"
            value="{{ old('phone', $campus->phone ?? '') }}"
        >

        @error('phone')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Coordinates --}}
    <div class="campus-section-label">Location Coordinates</div>

    <div class="campus-form-field">
        <label for="latitude">Latitude</label>

        <input
            type="number"
            step="any"
            name="latitude"
            id="latitude"
            class="campus-control"
            placeholder="Example: 18.5204"
            value="{{ old('latitude', $campus->latitude ?? '') }}"
        >

        @error('latitude')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="campus-form-field">
        <label for="longitude">Longitude</label>

        <input
            type="number"
            step="any"
            name="longitude"
            id="longitude"
            class="campus-control"
            placeholder="Example: 73.8567"
            value="{{ old('longitude', $campus->longitude ?? '') }}"
        >

        @error('longitude')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Regional Settings --}}
    <div class="campus-section-label">Regional Settings</div>

    <div class="campus-form-field">
        <label for="timezone">Timezone <span>*</span></label>

        <select name="timezone" id="timezone" class="campus-control" required>
            @foreach([
                'Asia/Kolkata' => 'Asia/Kolkata (IST)',
                'UTC' => 'UTC',
                'Asia/Dubai' => 'Asia/Dubai',
                'Europe/London' => 'Europe/London',
                'America/New_York' => 'America/New_York'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('timezone', $campus->timezone ?? 'Asia/Kolkata') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('timezone')
            <span class="campus-error">{{ $message }}</span>
        @enderror
    </div>

</div>

{{-- Form Actions --}}
<div class="campus-form-actions">

    <a href="{{ route('campuses.index') }}" class="campus-btn campus-btn-light">
        Cancel
    </a>

    <button type="submit" class="campus-btn campus-btn-primary">
        {{ isset($campus) ? 'Update Campus' : 'Save Campus' }}
    </button>

</div>
