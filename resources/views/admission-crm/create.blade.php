
@extends('layouts.schoolnexa')

@section('content')
<style>
    .lead-page { font-family:Inter,sans-serif;color:#25313C; }
    .lead-title { font-size:22px;font-weight:700;color:#123B5D; }
    .lead-subtitle { font-size:13px;color:#64748B;margin-top:5px; }
    .lead-card { background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:22px; }
    .lead-section { font-size:15px;font-weight:700;color:#123B5D;margin-bottom:5px; }
    .lead-hint { font-size:12px;color:#64748B;margin-bottom:18px; }
    .lead-label { display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:7px; }
    .lead-input {
        width:100%;height:42px;border:1px solid #CBD5E1;border-radius:9px;
        padding:0 12px;font-size:13px;color:#25313C;background:#fff;outline:none;
    }
    .lead-input:focus { border-color:#176B87;box-shadow:0 0 0 3px #176B871A; }
    .lead-textarea {
        width:100%;min-height:110px;border:1px solid #CBD5E1;border-radius:9px;
        padding:12px;font-size:13px;resize:vertical;outline:none;
    }
    .lead-textarea:focus { border-color:#176B87;box-shadow:0 0 0 3px #176B871A; }
    .lead-field { margin-bottom:18px; }
    .lead-grid { display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px; }
    .lead-btn {
        display:inline-flex;align-items:center;justify-content:center;height:40px;
        padding:0 15px;border:1px solid #CBD5E1;border-radius:9px;
        background:#fff;color:#123B5D;font-size:13px;font-weight:600;
        text-decoration:none;cursor:pointer;
    }
    .lead-btn-primary { background:#123B5D;color:#fff;border-color:#123B5D; }
    .lead-btn-primary:hover { background:#194D73;color:#fff; }
    .lead-error { color:#DC2626;font-size:11px;margin-top:5px; }
    @media(max-width:650px) { .lead-grid { grid-template-columns:1fr;gap:0; } }
</style>

<div class="lead-page">

    <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:22px;flex-wrap:wrap;">
        <div>
            <div class="lead-title">Add New Admission Lead</div>
            <div class="lead-subtitle">Register a prospective student and capture their admission enquiry.</div>
        </div>

        <a href="{{ route('admission-crm.index') }}" class="lead-btn">
            ← Back to CRM
        </a>
    </div>

    @if($errors->any())
        <div style="padding:13px 16px;background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;margin-bottom:18px;color:#B91C1C;font-size:13px;">
            Please correct the highlighted fields and try again.
        </div>
    @endif

    <form method="POST" action="{{ route('admission-crm.store') }}">
        @csrf

        <div class="lead-card" style="margin-bottom:18px;">
            <div class="lead-section">Student Information</div>
            <div class="lead-hint">Basic details of the prospective student.</div>

            <div class="lead-grid">
                <div class="lead-field">
                    <label class="lead-label">Student Name <span style="color:#DC2626;">*</span></label>
                    <input type="text" name="student_name" class="lead-input"
                           value="{{ old('student_name') }}" placeholder="Enter student full name" required>
                    @error('student_name') <div class="lead-error">{{ $message }}</div> @enderror
                </div>

                <div class="lead-field">
                    <label class="lead-label">Class / Grade Interested In</label>
                    <select name="prospective_grade" class="lead-input">
                        <option value="">Select class</option>
                        @foreach(['Nursery','Jr. KG','Sr. KG','Class 1','Class 2','Class 3','Class 4','Class 5','Class 6','Class 7','Class 8','Class 9','Class 10','Class 11','Class 12'] as $grade)
                            <option value="{{ $grade }}" @selected(old('prospective_grade') === $grade)>
                                {{ $grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('prospective_grade') <div class="lead-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="lead-card" style="margin-bottom:18px;">
            <div class="lead-section">Parent / Guardian Information</div>
            <div class="lead-hint">Contact information for admission communication.</div>

            <div class="lead-grid">
                <div class="lead-field">
                    <label class="lead-label">Parent / Guardian Name</label>
                    <input type="text" name="parent_name" class="lead-input"
                           value="{{ old('parent_name') }}" placeholder="Enter parent or guardian name">
                    @error('parent_name') <div class="lead-error">{{ $message }}</div> @enderror
                </div>

                <div class="lead-field">
                    <label class="lead-label">Mobile Number</label>
                    <input type="tel" name="phone" class="lead-input"
                           value="{{ old('phone') }}" placeholder="Enter contact number">
                    @error('phone') <div class="lead-error">{{ $message }}</div> @enderror
                </div>

                <div class="lead-field">
                    <label class="lead-label">Email Address</label>
                    <input type="email" name="email" class="lead-input"
                           value="{{ old('email') }}" placeholder="parent@example.com">
                    @error('email') <div class="lead-error">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="lead-card" style="margin-bottom:18px;">
            <div class="lead-section">Enquiry Details</div>
            <div class="lead-hint">Record how the enquiry was received and who will handle it.</div>

            <div class="lead-grid">
                <div class="lead-field">
                    <label class="lead-label">Enquiry Source</label>
                    <select name="source" class="lead-input">
                        <option value="">Select source</option>
                        @foreach(['Website','Walk-in','Phone Call','Social Media','Referral','Advertisement','School Event','Other'] as $source)
                            <option value="{{ $source }}" @selected(old('source') === $source)>
                                {{ $source }}
                            </option>
                        @endforeach
                    </select>
                    @error('source') <div class="lead-error">{{ $message }}</div> @enderror
                </div>

                <div class="lead-field">
                    <label class="lead-label">Assign To</label>
                    <select name="assigned_to" class="lead-input">
                        <option value="">Leave unassigned</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string)old('assigned_to') === (string)$user->id)>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <div class="lead-error">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="lead-field" style="margin-bottom:0;">
                <label class="lead-label">Initial Enquiry Notes</label>
                <textarea name="notes" class="lead-textarea"
                          placeholder="Enter enquiry details, requirements or any additional information...">{{ old('notes') }}</textarea>
                @error('notes') <div class="lead-error">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('admission-crm.index') }}" class="lead-btn">Cancel</a>
            <button type="submit" class="lead-btn lead-btn-primary">
                Save Admission Lead
            </button>
        </div>
    </form>
</div>
@endsection
