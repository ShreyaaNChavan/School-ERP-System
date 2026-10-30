
@once
<style>
    .school-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .school-page * { box-sizing: border-box; }

    .school-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .school-section-label {
        grid-column: 1 / -1;
        color: #176B87;
        font-size: 12px;
        font-weight: 700;
        padding-bottom: 9px;
        border-bottom: 1px solid #E2E8F0;
        margin-top: 5px;
    }

    .school-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .school-form-field.full-width {
        grid-column: 1 / -1;
    }

    .school-form-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .school-form-field label span {
        color: #DC2626;
    }

    .school-control {
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

    .school-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .school-control::placeholder {
        color: #94A3B8;
    }

    .school-error {
        color: #DC2626;
        font-size: 11px;
    }

    .school-required-note {
        color: #64748B;
        font-size: 11px;
        margin-bottom: 18px;
    }

    .school-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #E2E8F0;
    }

    .school-btn {
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

    .school-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .school-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .school-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    @media (max-width: 650px) {
        .school-form-grid { grid-template-columns: 1fr; }

        .school-form-field.full-width,
        .school-section-label {
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

<p class="school-required-note">
    Fields marked with <span style="color:#DC2626;">*</span> are required.
</p>

<div class="school-form-grid">

    {{-- Basic Details --}}
    <div class="school-section-label">Basic Details</div>

    <div class="school-form-field full-width">
        <label for="organization_id">Parent Organization <span>*</span></label>

        <select name="organization_id" id="organization_id" class="school-control" required>
            <option value="">Select Organization</option>

            @foreach($organizations as $organization)
                <option
                    value="{{ $organization->id }}"
                    @selected((string) old('organization_id', $school->organization_id ?? '') === (string) $organization->id)
                >
                    {{ $organization->name }}
                </option>
            @endforeach
        </select>

        @error('organization_id')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="name">School Name <span>*</span></label>

        <input
            type="text"
            name="name"
            id="name"
            class="school-control"
            placeholder="Enter school name"
            value="{{ old('name', $school->name ?? '') }}"
            required
        >

        @error('name')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="code">School Code <span>*</span></label>

        <input
            type="text"
            name="code"
            id="code"
            class="school-control"
            placeholder="Example: SCH001"
            value="{{ old('code', $school->code ?? '') }}"
            required
        >

        @error('code')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="school_type">School Type</label>

        <select name="school_type" id="school_type" class="school-control">
            <option value="">Select School Type</option>

            @foreach(['Primary', 'Secondary', 'Higher Secondary', 'Pre-Primary', 'Combined', 'Other'] as $type)
                <option
                    value="{{ $type }}"
                    @selected(old('school_type', $school->school_type ?? '') === $type)
                >
                    {{ $type }}
                </option>
            @endforeach
        </select>

        @error('school_type')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="affiliation_number">Affiliation Number</label>

        <input
            type="text"
            name="affiliation_number"
            id="affiliation_number"
            class="school-control"
            placeholder="Enter affiliation number"
            value="{{ old('affiliation_number', $school->affiliation_number ?? '') }}"
        >

        @error('affiliation_number')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Contact Details --}}
    <div class="school-section-label">Contact Information</div>

    <div class="school-form-field">
        <label for="email">School Email</label>

        <input
            type="email"
            name="email"
            id="email"
            class="school-control"
            placeholder="school@example.com"
            value="{{ old('email', $school->email ?? '') }}"
        >

        @error('email')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="phone">School Phone</label>

        <input
            type="text"
            name="phone"
            id="phone"
            class="school-control"
            placeholder="Enter contact number"
            value="{{ old('phone', $school->phone ?? '') }}"
        >

        @error('phone')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    {{-- Regional Settings --}}
    <div class="school-section-label">Regional Settings</div>

    <div class="school-form-field">
        <label for="timezone">Timezone <span>*</span></label>

        <select name="timezone" id="timezone" class="school-control" required>
            @foreach([
                'Asia/Kolkata' => 'Asia/Kolkata (IST)',
                'UTC' => 'UTC',
                'Asia/Dubai' => 'Asia/Dubai',
                'Europe/London' => 'Europe/London',
                'America/New_York' => 'America/New_York'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('timezone', $school->timezone ?? 'Asia/Kolkata') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('timezone')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="school-form-field">
        <label for="default_currency">Default Currency <span>*</span></label>

        <select name="default_currency" id="default_currency" class="school-control" required>
            @foreach([
                'INR' => 'INR - Indian Rupee',
                'USD' => 'USD - US Dollar',
                'GBP' => 'GBP - British Pound',
                'AED' => 'AED - UAE Dirham',
                'EUR' => 'EUR - Euro'
            ] as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(old('default_currency', $school->default_currency ?? 'INR') === $value)
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>

        @error('default_currency')
            <span class="school-error">{{ $message }}</span>
        @enderror
    </div>

</div>

{{-- Form Actions --}}
<div class="school-form-actions">

    <a href="{{ route('schools.index') }}" class="school-btn school-btn-light">
        Cancel
    </a>

    <button type="submit" class="school-btn school-btn-primary">
        {{ isset($school) ? 'Update School' : 'Save School' }}
    </button>

</div>
