@once
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    .org-page {
        font-family: 'Inter', system-ui, sans-serif;
        color: #25313C;
    }

    .org-page * { box-sizing: border-box; }

    .org-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .org-heading h1 {
        color: #123B5D;
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .org-heading p {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .org-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 16px;
        padding: 22px;
        margin-bottom: 20px;
    }

    .org-panel-title {
        color: #123B5D;
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 20px;
    }

    .org-btn {
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

    .org-btn-primary {
        background: #123B5D;
        color: #fff;
    }

    .org-btn-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .org-btn-light {
        background: #fff;
        border-color: #CBD5E1;
        color: #25313C;
    }

    .org-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .org-form-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .org-form-field.full-width {
        grid-column: 1 / -1;
    }

    .org-form-field label {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
    }

    .org-form-field label span {
        color: #DC2626;
    }

    .org-control {
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

    .org-control:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .org-control::placeholder { color: #94A3B8; }

    .org-error {
        color: #DC2626;
        font-size: 11px;
    }

    .org-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #E2E8F0;
    }

    .org-section-label {
        grid-column: 1 / -1;
        color: #176B87;
        font-size: 12px;
        font-weight: 700;
        padding-bottom: 9px;
        border-bottom: 1px solid #E2E8F0;
        margin-top: 5px;
    }

    .org-required-note {
        color: #64748B;
        font-size: 11px;
        margin-bottom: 18px;
    }

    @media (max-width: 650px) {
        .org-form-grid { grid-template-columns: 1fr; }
        .org-form-field.full-width,
        .org-section-label { grid-column: auto; }
        .org-panel { padding: 16px; }
    }
</style>
@endonce

@if($errors->any())
    <div style="background:#FEE2E2;color:#991B1B;padding:13px 15px;border-radius:9px;margin-bottom:18px;font-size:13px;">
        Please correct the errors below before submitting the form.
    </div>
@endif

<p class="org-required-note">
    Fields marked with <span style="color:#DC2626;">*</span> are required.
</p>

<div class="org-form-grid">

    <div class="org-section-label">Basic Details</div>

    <div class="org-form-field">
        <label for="name">Organization Name <span>*</span></label>
        <input
            type="text"
            id="name"
            name="name"
            class="org-control"
            placeholder="Enter organization name"
            value="{{ old('name', $organization->name ?? '') }}"
            required
        >
        @error('name') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="legal_name">Legal Name</label>
        <input
            type="text"
            id="legal_name"
            name="legal_name"
            class="org-control"
            placeholder="Enter registered legal name"
            value="{{ old('legal_name', $organization->legal_name ?? '') }}"
        >
        @error('legal_name') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="registration_number">Registration Number</label>
        <input
            type="text"
            id="registration_number"
            name="registration_number"
            class="org-control"
            placeholder="Enter registration number"
            value="{{ old('registration_number', $organization->registration_number ?? '') }}"
        >
        @error('registration_number') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-section-label">Contact Information</div>

    <div class="org-form-field">
        <label for="primary_email">Primary Email</label>
        <input
            type="email"
            id="primary_email"
            name="primary_email"
            class="org-control"
            placeholder="example@organization.com"
            value="{{ old('primary_email', $organization->primary_email ?? '') }}"
        >
        @error('primary_email') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="primary_phone">Primary Phone</label>
        <input
            type="text"
            id="primary_phone"
            name="primary_phone"
            class="org-control"
            placeholder="Enter contact number"
            value="{{ old('primary_phone', $organization->primary_phone ?? '') }}"
        >
        @error('primary_phone') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-section-label">Address Details</div>

    <div class="org-form-field full-width">
        <label for="address_line_1">Address Line 1</label>
        <input
            type="text"
            id="address_line_1"
            name="address_line_1"
            class="org-control"
            placeholder="Building, street or area"
            value="{{ old('address_line_1', $organization->address_line_1 ?? '') }}"
        >
        @error('address_line_1') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field full-width">
        <label for="address_line_2">Address Line 2</label>
        <input
            type="text"
            id="address_line_2"
            name="address_line_2"
            class="org-control"
            placeholder="Additional address information"
            value="{{ old('address_line_2', $organization->address_line_2 ?? '') }}"
        >
        @error('address_line_2') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="city">City</label>
        <input
            type="text"
            id="city"
            name="city"
            class="org-control"
            placeholder="Enter city"
            value="{{ old('city', $organization->city ?? '') }}"
        >
        @error('city') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="state">State</label>
        <input
            type="text"
            id="state"
            name="state"
            class="org-control"
            placeholder="Enter state"
            value="{{ old('state', $organization->state ?? '') }}"
        >
        @error('state') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="postal_code">Postal Code</label>
        <input
            type="text"
            id="postal_code"
            name="postal_code"
            class="org-control"
            placeholder="Enter postal code"
            value="{{ old('postal_code', $organization->postal_code ?? '') }}"
        >
        @error('postal_code') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-form-field">
        <label for="country_code">Country Code <span>*</span></label>
        <input
            type="text"
            id="country_code"
            name="country_code"
            class="org-control"
            placeholder="IN"
            maxlength="2"
            value="{{ old('country_code', $organization->country_code ?? 'IN') }}"
            required
        >
        @error('country_code') <span class="org-error">{{ $message }}</span> @enderror
    </div>

    <div class="org-section-label">Regional Settings</div>

    <div class="org-form-field">
        <label for="timezone">Timezone <span>*</span></label>
        <select id="timezone" name="timezone" class="org-control" required>
            <option value="Asia/Kolkata" @selected(old('timezone', $organization->timezone ?? 'Asia/Kolkata') === 'Asia/Kolkata')>
                Asia/Kolkata (IST)
            </option>
            <option value="UTC" @selected(old('timezone', $organization->timezone ?? '') === 'UTC')>
                UTC
            </option>
            <option value="Asia/Dubai" @selected(old('timezone', $organization->timezone ?? '') === 'Asia/Dubai')>
                Asia/Dubai
            </option>
            <option value="Europe/London" @selected(old('timezone', $organization->timezone ?? '') === 'Europe/London')>
                Europe/London
            </option>
            <option value="America/New_York" @selected(old('timezone', $organization->timezone ?? '') === 'America/New_York')>
                America/New_York
            </option>
        </select>
        @error('timezone') <span class="org-error">{{ $message }}</span> @enderror
    </div>

</div>

<div class="org-form-actions">
    <a href="{{ route('organizations.index') }}" class="org-btn org-btn-light">
        Cancel
    </a>

    <button type="submit" class="org-btn org-btn-primary">
        {{ isset($organization) ? 'Update Organization' : 'Save Organization' }}
    </button>
</div>