
@extends('layouts.schoolnexa')

@section('content')
<div style="max-width:1200px;margin:auto;">

    <div class="sd-header">
        <div>
            <h2>Subject Details</h2>
            <p>View subject information and class-wise assignments</p>
        </div>
        <div class="sd-header-actions">
            <a href="{{ route('subjects.index') }}" class="sd-outline">Back to Directory</a>

            @if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                <a href="{{ route('subjects.edit',$subject) }}" class="sd-primary">Edit Subject</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="sd-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="sd-alert error">{{ session('error') }}</div>
    @endif

    <div class="sd-card">
        <div class="sd-card-heading">
            <div>
                <h3>{{ $subject->name }}</h3>
                <p>{{ $subject->code ?: 'No subject code' }}</p>
            </div>
            <span class="sd-status {{ $subject->is_active ? 'active' : 'inactive' }}">
                {{ $subject->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="sd-info-grid">
            <div><label>School</label><strong>{{ $subject->school->name ?? '—' }}</strong></div>
            <div><label>Subject Type</label><strong>{{ ucfirst($subject->subject_type) }}</strong></div>
            <div><label>Subject Code</label><strong>{{ $subject->code ?: '—' }}</strong></div>
            <div><label>Assigned Classes</label><strong>{{ $subject->classSubjects->count() }}</strong></div>
        </div>

        <div class="sd-description">
            <label>Description</label>
            <p>{{ $subject->description ?: 'No description provided.' }}</p>
        </div>
    </div>

    <div class="sd-card">
        <div class="sd-section-heading">
            <div>
                <h3>Class-wise Subject Assignments</h3>
                <p>Manage the classes in which this subject is taught.</p>
            </div>
        </div>

        @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
            <form action="{{ route('subjects.assignments.store',$subject) }}" method="POST" class="sd-assign-form">
                @csrf

                <div>
                    <label>Select Class</label>
                    <select name="school_class_id" required>
                        <option value="">Choose class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" @selected(old('school_class_id') == $class->id)>
                                {{ $class->name }}{{ $class->code ? ' ('.$class->code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label>Weekly Periods</label>
                    <input type="number" name="weekly_periods" min="1" max="60"
                        value="{{ old('weekly_periods',5) }}" required>
                </div>

                <div>
                    <label>Subject Category</label>
                    <select name="is_optional" required>
                        <option value="0">Compulsory</option>
                        <option value="1">Optional</option>
                    </select>
                </div>

                <button type="submit" class="sd-primary">Assign Subject</button>
            </form>

            @error('school_class_id') <p class="sd-field-error">{{ $message }}</p> @enderror
            @error('weekly_periods') <p class="sd-field-error">{{ $message }}</p> @enderror
            @error('is_optional') <p class="sd-field-error">{{ $message }}</p> @enderror
        @endif

        <div class="sd-table-wrap">
            <table class="sd-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Weekly Periods</th>
                        <th>Category</th>
                        <th>Teaching Assignments</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subject->classSubjects as $assignment)
                        <tr>
                            <td><strong>{{ $assignment->schoolClass->name ?? '—' }}</strong></td>
                            <td>
                                @if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                    <form method="POST" action="{{ route('subjects.assignments.update',$assignment) }}" class="sd-inline-form">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="weekly_periods" min="1" max="60"
                                            value="{{ $assignment->weekly_periods }}" required>
                                        <input type="hidden" name="is_optional" value="{{ $assignment->is_optional ? 1 : 0 }}">
                                        <button type="submit" class="sd-small-save">Save</button>
                                    </form>
                                @else
                                    {{ $assignment->weekly_periods }}
                                @endif
                            </td>
                            <td>{{ $assignment->is_optional ? 'Optional' : 'Compulsory' }}</td>
                            <td>{{ $assignment->teachingAssignments->count() }}</td>
                            <td>
                                @if(auth()->user()->hasPermission('ACADEMICS.DELETE'))
                                    <form method="POST" action="{{ route('subjects.assignments.destroy',$assignment) }}"
                                        onsubmit="return confirm('Remove this subject from the class?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sd-delete">Remove</button>
                                    </form>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="sd-empty">This subject has not been assigned to any class yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<style>
.sd-header {display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:22px;}
.sd-header h2 {font-size:22px;font-weight:700;color:#123B5D;margin:0;}
.sd-header p {font-size:13px;color:#64748B;margin:6px 0 0;}
.sd-header-actions {display:flex;gap:10px;flex-wrap:wrap;}
.sd-outline,.sd-primary {
    display:inline-flex;align-items:center;justify-content:center;height:38px;
    padding:0 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;
}
.sd-outline {background:#fff;border:1px solid #CBD5E1;color:#123B5D;}
.sd-primary {background:#123B5D;border:1px solid #123B5D;color:#fff;cursor:pointer;}
.sd-card {background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:22px;margin-bottom:20px;}
.sd-card-heading,.sd-section-heading {display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;}
.sd-card-heading h3,.sd-section-heading h3 {font-size:17px;color:#123B5D;margin:0;font-weight:700;}
.sd-card-heading p,.sd-section-heading p {font-size:12px;color:#64748B;margin:5px 0 0;}
.sd-status {padding:6px 12px;border-radius:20px;font-size:11px;font-weight:600;}
.sd-status.active {background:#DCFCE7;color:#166534;}
.sd-status.inactive {background:#FEE2E2;color:#991B1B;}
.sd-info-grid {display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:20px;margin-top:25px;}
.sd-info-grid label,.sd-description label {display:block;color:#64748B;font-size:11px;margin-bottom:7px;}
.sd-info-grid strong {font-size:13px;color:#25313C;}
.sd-description {border-top:1px solid #E2E8F0;margin-top:22px;padding-top:17px;}
.sd-description p {font-size:13px;color:#25313C;margin:0;line-height:1.6;}
.sd-assign-form {display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;align-items:end;margin:22px 0;}
.sd-assign-form label {display:block;font-size:12px;font-weight:600;color:#25313C;margin-bottom:7px;}
.sd-assign-form input,.sd-assign-form select,.sd-inline-form input {
    width:100%;box-sizing:border-box;height:38px;border:1px solid #CBD5E1;
    border-radius:8px;padding:0 10px;font-size:12px;background:#fff;
}
.sd-table-wrap {overflow-x:auto;margin-top:18px;}
.sd-table {width:100%;border-collapse:collapse;min-width:750px;}
.sd-table th {background:#F8FAFC;color:#64748B;text-align:left;font-size:11px;padding:12px;border-bottom:1px solid #CBD5E1;text-transform:uppercase;}
.sd-table td {padding:13px 12px;border-bottom:1px solid #E2E8F0;font-size:12px;color:#25313C;}
.sd-table td strong {color:#123B5D;}
.sd-inline-form {display:flex;gap:6px;align-items:center;}
.sd-inline-form input {width:65px;}
.sd-small-save {border:0;background:#E0F2FE;color:#075985;padding:7px 9px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;}
.sd-delete {border:0;background:none;color:#DC2626;font-size:12px;font-weight:600;cursor:pointer;}
.sd-empty {text-align:center;color:#64748B!important;padding:28px!important;}
.sd-alert {padding:12px 15px;border-radius:9px;margin-bottom:18px;font-size:13px;}
.sd-alert.success {background:#DCFCE7;color:#166534;}
.sd-alert.error {background:#FEE2E2;color:#991B1B;}
.sd-field-error {color:#DC2626;font-size:12px;margin:7px 0;}
@media(max-width:850px) {
    .sd-info-grid {grid-template-columns:repeat(2,minmax(0,1fr));}
    .sd-assign-form {grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media(max-width:520px) {
    .sd-info-grid,.sd-assign-form {grid-template-columns:1fr;}
}
</style>
@endsection
