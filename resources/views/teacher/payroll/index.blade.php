@extends('layouts.schoolnexa')

@section('content')
<div class="tp-wrap">
    <div class="tp-heading"><div><span class="tp-eyebrow">MY EMPLOYMENT</span><h1>My Payroll</h1><p>View only your own salary records and payslips.</p></div></div>
    <div class="tp-stats">
        <div><span>Payroll records</span><strong>{{ $summary['records'] }}</strong></div>
        <div><span>Paid</span><strong>{{ $summary['paid'] }}</strong></div>
        <div><span>Pending</span><strong>{{ $summary['pending'] }}</strong></div>
    </div>
    <div class="tp-table-wrap"><table class="tp-table"><thead><tr><th>Payroll period</th><th>Period dates</th><th>Gross salary</th><th>Deductions</th><th>Net salary</th><th>Payment status</th><th>Action</th></tr></thead><tbody>
    @forelse($records as $record)
        <tr>
            <td><strong>{{ $record->period_name }}</strong><small>{{ $record->payment_date ? 'Payment date: '.\Carbon\Carbon::parse($record->payment_date)->format('d M Y') : 'Payment date not set' }}</small></td>
            <td>{{ \Carbon\Carbon::parse($record->start_date)->format('d M Y') }} – {{ \Carbon\Carbon::parse($record->end_date)->format('d M Y') }}</td>
            <td>₹{{ number_format((float)$record->gross_salary, 2) }}</td>
            <td>₹{{ number_format((float)$record->deductions, 2) }}</td>
            <td><strong>₹{{ number_format((float)$record->net_salary, 2) }}</strong></td>
            <td><span class="tp-pill {{ $record->payment_status === 'paid' ? 'paid' : '' }}">{{ ucfirst($record->payment_status) }}</span></td>
            <td><a class="tp-link" href="{{ route('teacher.payroll.payslip', $record->id) }}">View payslip</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="tp-empty">No payroll records are available for your account yet.</td></tr>
    @endforelse
    </tbody></table></div>
    <div class="tp-pagination">{{ $records->links() }}</div>
</div>
<style>
.tp-wrap{max-width:1200px;margin:0 auto;padding:24px;color:#172b4d}.tp-heading{margin-bottom:20px}.tp-heading h1{font-size:28px;margin:4px 0}.tp-heading p{margin:0;color:#64748b}.tp-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.3px;color:#176b87}.tp-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:13px;margin-bottom:18px}.tp-stats div{background:#fff;border:1px solid #e5edf4;border-radius:14px;padding:17px}.tp-stats span{display:block;color:#64748b;font-size:12px}.tp-stats strong{display:block;font-size:25px;margin-top:6px}.tp-table-wrap{overflow:auto;background:#fff;border:1px solid #e5edf4;border-radius:14px}.tp-table{width:100%;border-collapse:collapse;min-width:900px}.tp-table th,.tp-table td{padding:14px 15px;border-bottom:1px solid #edf1f5;text-align:left;font-size:13px}.tp-table th{background:#f7fafc;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.5px}.tp-table td small{display:block;color:#64748b;margin-top:5px}.tp-pill{display:inline-block;border-radius:20px;background:#fff7ed;color:#b45309;padding:5px 10px;font-weight:700;font-size:11px}.tp-pill.paid{background:#ecfdf3;color:#15803d}.tp-link{color:#176b87;font-weight:700;text-decoration:none}.tp-empty{text-align:center!important;padding:38px!important;color:#64748b}.tp-pagination{margin-top:16px}.tp-pagination nav{display:flex;justify-content:flex-end}.tp-pagination svg{width:16px}@media(max-width:650px){.tp-stats{grid-template-columns:1fr}}
</style>
@endsection
