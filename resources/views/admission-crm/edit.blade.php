
@extends('layouts.schoolnexa')

@section('content')
<style>
    .edit-page {font-family:Inter,sans-serif;color:#25313C;}
    .edit-title {font-size:22px;font-weight:700;color:#123B5D;}
    .edit-subtitle {font-size:13px;color:#64748B;margin-top:5px;}
    .edit-card {background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px;margin-bottom:18px;}
    .edit-section {font-size:15px;font-weight:700;color:#123B5D;margin-bottom:5px;}
    .edit-hint {font-size:12px;color:#64748B;margin-bottom:20px;}
    .edit-grid {display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px;}
    .edit-field {margin-bottom:2px;min-width:0;}
    .edit-label {display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:7px;}
    .edit-input {width:100%;height:42px;border:1px solid #CBD5E1;border-radius:9px;padding:0 12px;font-size:13px;color:#25313C;background:#fff;outline:none;}
    .edit-input:focus {border-color:#176B87;box-shadow:0 0 0 3px #176B871A;}
    .edit-textarea {width:100%;min-height:115px;border:1px solid #CBD5E1;border-radius:9px;padding:12px;font-size:13px;outline:none;resize:vertical;}
    .edit-textarea:focus {border-color:#176B87;box-shadow:0 0 0 3px #176B871A;}
    .edit-btn {display:inline-flex;align-items:center;justify-content:center;height:40px;padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;}
    .edit-btn-primary {background:#123B5D;color:#fff;border-color:#123B5D;}
    .edit-btn-primary:hover {background:#194D73;color:#fff;}
    .edit-error {font-size:11px;color:#DC2626;margin-top:5px;}
    @media(max-width:650px) {.edit-grid {grid-template-columns:1fr;gap:16px;}}
</style>

<div class="edit-page">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:22px;">
        <div>
            <div class="edit-title">Edit Admission Lead</div>
            <div class="edit-subtitle">
                Update prospective student and enquiry information.
                · {{ $lead->lead_number }}
            </div>
        </div>

        <a href="{{ route('admission-crm.show', $lead) }}" class="edit-btn">
            ← Back to Profile
        </a>
    </div>

    @if($errors->any())
        <div style="padding:13px 16px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;margin-bottom:18px;color:#B91C1C;font-size:13px;">
            Please correct the highlighted fields.
        </div>
    @endif

    <form method="POST" action="{{ route('admission-crm.update', $lead) }}">
        @csrf
        @method('PUT')

        {{-- Student details --}}
        <div class="edit-card">
            <div class="edit-section">Student Information</div>
            <div class="edit-hint">Update the prospective student's details.</div>

            <div class="edit-grid">
                <div class="edit-field">
                    <label class="edit-label">Student Name *</label>
                    <input type="text" name="student_name" class="edit-input"
                           value="{{ old('student_name', $lead->student_name) }}" required>
                    @error('student_name')<div class="edit-error">{{ $message }}</div>@enderror
                </div>

                <div class="edit-field">
                    <label class="edit-label">Class / Grade Interested In</label>
                    <select name="prospective_grade" class="edit-input">
                        <option value="">Select class</option>
                        @foreach(['Nursery','Jr. KG','Sr. KG','Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12'] as $grade)
                            <option value="{{ $grade }}"
                                @selected(old('prospective_grade', $lead->prospective_grade) === $grade)>
                                {{ $grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('prospective_grade')<div class="edit-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Parent details --}}
        <div class="edit-card">
            <div class="edit-section">Parent / Guardian Information</div>
            <div class="edit-hint">Update contact information for admission communication.</div>

            <div class="edit-grid">
                <div class="edit-field">
                    <label class="edit-label">Parent / Guardian Name</label>
                    <input type="text" name="parent_name" class="edit-input"
                           value="{{ old('parent_name', $lead->parent_name) }}">
                    @error('parent_name')<div class="edit-error">{{ $message }}</div>@enderror
                </div>

                <div class="edit-field">
                    <label class="edit-label">Mobile Number</label>
                    <input type="tel" name="phone" class="edit-input"
                           value="{{ old('phone', $lead->phone) }}">
                    @error('phone')<div class="edit-error">{{ $message }}</div>@enderror
                </div>

                <div class="edit-field">
                    <label class="edit-label">Email Address</label>
                    <input type="email" name="email" class="edit-input"
                           value="{{ old('email', $lead->email) }}">
                    @error('email')<div class="edit-error">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- Enquiry details --}}
        <div class="edit-card">
            <div class="edit-section">Enquiry Details</div>
            <div class="edit-hint">Manage enquiry source, counsellor assignment and notes.</div>

            <div class="edit-grid">
                <div class="edit-field">
                    <label class="edit-label">Enquiry Source</label>
                    <select name="source" class="edit-input">
                        <option value="">Select source</option>
                        @foreach(['Website','Walk-in','Phone Call','Social Media','Referral','Advertisement','School Event','Other'] as $source)
                            <option value="{{ $source }}"
                                @selected(old('source', $lead->source) === $source)>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                    @error('source')<div class="edit-error">{{ $message }}</div>@enderror
                </div>

                <div class="edit-field">
                    <label class="edit-label">Assigned To</label>
                    <select name="assigned_to" class="edit-input">
                        <option value="">Unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}"
                                @selected((string)old('assigned_to', $lead->assigned_to) === (string)$user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to')<div class="edit-error">{{ $message }}</div>@enderror
                </div>
            </div>

            <div style="margin-top:18px;">
                <label class="edit-label">Enquiry Notes</label>
                <textarea name="notes" class="edit-textarea"
                          placeholder="Enter enquiry details and additional information...">{{ old('notes', $lead->notes) }}</textarea>
                @error('notes')<div class="edit-error">{{ $message }}</div>@enderror
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:25px;">
            <a href="{{ route('admission-crm.show', $lead) }}" class="edit-btn">Cancel</a>
            <button type="submit" class="edit-btn edit-btn-primary">
                Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
