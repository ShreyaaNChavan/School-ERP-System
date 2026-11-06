@extends('layouts.schoolnexa')

@section('content')
@include('transport._style')

<div class="tr-wrap">
    <div class="tr-head">
        <div>
            <div class="tr-title">{{ $vehicle->registration_number }}</div>
            <div class="tr-sub">Vehicle profile and driver assignment</div>
        </div>
        <a class="tr-btn" href="{{ route('transport.vehicles') }}">Back to vehicles</a>
    </div>

    @if(session('success'))
        <div class="tr-card" style="border-color:#86efac;color:#166534;background:#f0fdf4">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="tr-card" style="border-color:#fca5a5;color:#991b1b;background:#fef2f2">
            <b>Please correct the following:</b>
            <ul style="margin:8px 0 0 18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="tr-card">
        <b>Update vehicle details</b>
        <form method="POST" action="{{ route('transport.vehicles.update', $vehicle) }}">
            @csrf
            @method('PUT')
            <div class="tr-form">
                @foreach(['vehicle_type' => 'Vehicle type', 'make_model' => 'Make / model', 'capacity' => 'Capacity', 'insurance_expiry' => 'Insurance expiry', 'permit_expiry' => 'Permit expiry'] as $field => $label)
                    <div class="tr-field">
                        <label>{{ $label }}</label>
                        <input
                            class="tr-input"
                            name="{{ $field }}"
                            value="{{ old($field, $vehicle->$field) }}"
                            {{ str_contains($field, 'expiry') ? 'type=date' : ($field === 'capacity' ? 'type=number' : '') }}
                        >
                    </div>
                @endforeach

                <div class="tr-field">
                    <label>Status</label>
                    <select class="tr-input" name="status">
                        @foreach(['active', 'inactive', 'maintenance'] as $status)
                            <option value="{{ $status }}" @selected(old('status', $vehicle->status) === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button class="tr-btn tr-primary" style="margin-top:12px">Save changes</button>
        </form>
    </div>

    <div class="tr-card">
        <b>Assign driver</b>
        <p class="tr-sub" style="margin:6px 0 14px">
            Only active staff whose designation contains “Driver” are listed.
        </p>

        @if($staff->isEmpty())
            <div class="tr-muted">
                No eligible drivers found. Add or update a staff profile with the designation “Driver” before assigning a driver.
            </div>
        @else
            <form method="POST" action="{{ route('transport.drivers.assign', $vehicle) }}">
                @csrf
                <div class="tr-form">
                    <div class="tr-field">
                        <label>Staff member</label>
                        <select class="tr-input" name="staff_profile_id" required>
                            <option value="">Select driver</option>
                            @foreach($staff as $person)
                                <option value="{{ $person->id }}" @selected(old('staff_profile_id') == $person->id)>
                                    {{ $person->employee_number }} · {{ $person->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="tr-field">
                        <label>Start date</label>
                        <input
                            class="tr-input"
                            type="date"
                            name="start_date"
                            value="{{ old('start_date', today()->toDateString()) }}"
                            required
                        >
                    </div>
                </div>
                <button class="tr-btn tr-primary" style="margin-top:12px">Assign driver</button>
            </form>
        @endif
    </div>

    <div class="tr-card">
        <b>Driver assignment history</b>
        <table class="tr-table">
            <thead>
                <tr>
                    <th>Driver</th>
                    <th>Employee ID</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicle->driverAssignments as $assignment)
                    <tr>
                        <td>{{ $assignment->staff->full_name ?? '—' }}</td>
                        <td>{{ $assignment->staff->employee_number ?? '—' }}</td>
                        <td>{{ $assignment->start_date }}</td>
                        <td>{{ $assignment->end_date ?? '—' }}</td>
                        <td>
                            <span class="tr-pill">
                                {{ $assignment->end_date ? 'Previous' : 'Current' }}
                            </span>
                        </td>
                        <td>
                            @if($assignment->end_date === null)
                                <form
                                    method="POST"
                                    action="{{ route('transport.drivers.unassign', $assignment) }}"
                                    onsubmit="return confirm('Unassign this driver from the vehicle? The assignment will remain in history.');"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button class="tr-btn" type="submit">Unassign</button>
                                </form>
                            @else
                                <span class="tr-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="tr-muted">No driver assigned.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
