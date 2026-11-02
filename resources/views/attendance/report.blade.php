@extends('layouts.schoolnexa')
@section('title','Attendance Reports')
@section('content')
<div style="font-family:Inter,sans-serif;padding:22px 24px;color:#25313C;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;flex-wrap:wrap;">
        <div><h1 style="font-size:22px;font-weight:700;color:#123B5D;margin:0;">Attendance Reports</h1><p style="font-size:13px;color:#64748B;margin:5px 0 0;">Review attendance records by date, academic year and student.</p></div>
        <a href="{{ route('attendance.index') }}" style="display:inline-flex;align-items:center;height:39px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;color:#123B5D;text-decoration:none;font-size:13px;font-weight:600;">Back to Attendance</a>
    </div>
    <form method="GET" class="att-report-filter" style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;background:#fff;border:1px solid #CBD5E1;border-radius:12px;padding:16px;margin-bottom:16px;">
        <div><label>Academic Year</label><select name="academic_year_id"><option value="">All Years</option>@foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(request('academic_year_id')==$year->id)>{{ $year->name }}</option>@endforeach</select></div>
        <div><label>Student ID</label><input type="number" name="student_id" value="{{ request('student_id') }}" placeholder="Student profile ID"></div>
        <div><label>From</label><input type="date" name="from_date" value="{{ request('from_date') }}"></div>
        <div><label>To</label><input type="date" name="to_date" value="{{ request('to_date') }}"></div>
        <button type="submit">Apply</button>
    </form>
    <div style="background:#fff;border:1px solid #CBD5E1;border-radius:12px;overflow:auto;">
        <table style="width:100%;border-collapse:collapse;min-width:850px;">
            <thead><tr style="background:#F8FAFC;text-align:left;color:#64748B;font-size:11px;"><th style="padding:12px;">Date</th><th>Admission No.</th><th>Student</th><th>Class / Section</th><th>Subject / Session</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>@forelse($records as $record)
                <tr style="border-top:1px solid #E2E8F0;font-size:12px;">
                    <td style="padding:11px;">{{ $record->session?->attendance_date?->format('d M Y') }}</td>
                    <td>{{ $record->student->admission_number ?? '-' }}</td>
                    <td>{{ $record->student->full_name ?? '-' }}</td>
                    <td>{{ $record->session->section->schoolClass->name ?? '-' }} / {{ $record->session->section->name ?? '-' }}</td>
                    <td>{{ $record->session->subject->name ?? 'Full Day' }}</td>
                    <td>{{ ucfirst($record->status) }}</td><td>{{ $record->remarks ?? '—' }}</td>
                </tr>
            @empty<tr><td colspan="7" style="padding:28px;text-align:center;color:#64748B;">No attendance records match these filters.</td></tr>@endforelse</tbody>
        </table>
    </div>
    <div style="margin-top:14px;">{{ $records->links() }}</div>
</div>
<style>
.att-report-filter label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px}.att-report-filter input,.att-report-filter select{height:38px;min-width:155px;border:1px solid #CBD5E1;border-radius:8px;padding:0 9px;font:13px Inter,sans-serif}.att-report-filter button{height:39px;padding:0 14px;background:#123B5D;color:#fff;border:1px solid #123B5D;border-radius:9px;font:600 13px Inter,sans-serif;cursor:pointer}
</style>
@endsection
