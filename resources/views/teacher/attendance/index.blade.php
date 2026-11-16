@extends('layouts.schoolnexa')

@section('content')
<div class="tp-wrap">
    <div class="tp-heading"><div><span class="tp-eyebrow">MY EMPLOYMENT</span><h1>My Attendance</h1><p>Your personal staff attendance records.</p></div></div>
    <div class="tp-stats">
        <div><span>Total records</span><strong>{{ $stats['total'] }}</strong></div>
        <div><span>Present</span><strong>{{ $stats['present'] }}</strong></div>
        <div><span>Absent</span><strong>{{ $stats['absent'] }}</strong></div>
        <div><span>Late</span><strong>{{ $stats['late'] }}</strong></div>
    </div>
    <form class="tp-filter" method="GET">
        <label>From<input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}"></label>
        <label>To<input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}"></label>
        <label>Status<select name="status"><option value="">All statuses</option>@foreach(['present','absent','late','half_day','leave'] as $status)<option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></label>
        <button class="tp-btn tp-primary">Apply filters</button>
        <a class="tp-btn tp-light" href="{{ route('teacher.attendance.index') }}">Reset</a>
    </form>
    <div class="tp-table-wrap"><table class="tp-table"><thead><tr><th>Date</th><th>Day</th><th>Check in</th><th>Check out</th><th>Status</th><th>Remarks</th></tr></thead><tbody>
    @forelse($records as $record)
        <tr><td><strong>{{ \Carbon\Carbon::parse($record->attendance_date)->format('d M Y') }}</strong></td><td>{{ \Carbon\Carbon::parse($record->attendance_date)->format('l') }}</td><td>{{ $record->check_in ?: '—' }}</td><td>{{ $record->check_out ?: '—' }}</td><td><span class="tp-pill">{{ ucfirst(str_replace('_',' ',$record->status)) }}</span></td><td>{{ $record->remarks ?: '—' }}</td></tr>
    @empty
        <tr><td colspan="6" class="tp-empty">No staff attendance records found for this period.</td></tr>
    @endforelse
    </tbody></table></div>
    <div class="tp-pagination">{{ $records->links() }}</div>
</div>
<style>
.tp-wrap{max-width:1200px;margin:0 auto;padding:24px;color:#172b4d}.tp-heading{margin-bottom:20px}.tp-heading h1{font-size:28px;margin:4px 0}.tp-heading p{margin:0;color:#64748b}.tp-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.3px;color:#176b87}.tp-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:13px;margin-bottom:18px}.tp-stats div{background:#fff;border:1px solid #e5edf4;border-radius:14px;padding:17px}.tp-stats span{display:block;color:#64748b;font-size:12px}.tp-stats strong{display:block;font-size:25px;margin-top:6px}.tp-filter{display:flex;flex-wrap:wrap;align-items:end;gap:12px;background:#fff;border:1px solid #e5edf4;padding:16px;border-radius:14px;margin-bottom:16px}.tp-filter label{display:grid;gap:6px;font-size:12px;font-weight:700}.tp-filter input,.tp-filter select{padding:10px;border:1px solid #dbe5ee;border-radius:9px;min-width:145px}.tp-btn{border:0;border-radius:9px;padding:10px 14px;text-decoration:none;font-weight:700;cursor:pointer}.tp-primary{background:#123b5d;color:#fff}.tp-light{background:#edf3f7;color:#123b5d}.tp-table-wrap{overflow:auto;background:#fff;border:1px solid #e5edf4;border-radius:14px}.tp-table{width:100%;border-collapse:collapse;min-width:760px}.tp-table th,.tp-table td{padding:14px 15px;border-bottom:1px solid #edf1f5;text-align:left;font-size:13px}.tp-table th{background:#f7fafc;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.5px}.tp-pill{display:inline-block;border-radius:20px;background:#edf5f8;color:#176b87;padding:5px 10px;font-weight:700;font-size:11px}.tp-empty{text-align:center!important;padding:38px!important;color:#64748b}.tp-pagination{margin-top:16px}.tp-pagination nav{display:flex;justify-content:flex-end}.tp-pagination svg{width:16px}@media(max-width:700px){.tp-stats{grid-template-columns:repeat(2,1fr)}}
</style>
@endsection
