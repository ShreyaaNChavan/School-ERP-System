@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<style>
.cc-head{display:flex;justify-content:space-between;margin-bottom:18px}.cc-head h1{font-size:23px;color:#123B5D;margin:0 0 5px}.cc-head p{font-size:13px;color:#64748B;margin:0}.cc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font-size:13px;font-weight:650}.cc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:20px}.cc-table{width:100%;border-collapse:collapse}.cc-table th{background:#F8FAFC;color:#64748B;font-size:11px;text-align:left;padding:12px}.cc-table td{padding:13px;border-top:1px solid #E2E8F0;font-size:13px}
</style>
<div class="cc-head"><div><h1>Delivery Reports</h1><p>Review recorded delivery attempts and channel status</p></div><a class="cc-btn" href="{{ route('communication.dashboard') }}">← Back to Dashboard</a></div>
<div class="cc-panel"><p style="font-size:12px;color:#64748B">Only in-app delivery records are supported until email, SMS, push or WhatsApp providers are configured.</p><div style="overflow-x:auto"><table class="cc-table"><thead><tr><th>ANNOUNCEMENT</th><th>CHANNEL</th><th>STATUS</th><th>RECIPIENTS</th><th>DELIVERED</th><th>FAILED</th><th>DATE</th></tr></thead><tbody>@forelse($items as $row)<tr><td>{{ $row->title }}</td><td>{{ strtoupper($row->channel) }}</td><td>{{ ucfirst($row->status) }}</td><td>{{ $row->recipient_count }}</td><td>{{ $row->delivered_count }}</td><td>{{ $row->failed_count }}</td><td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d M Y') : '—' }}</td></tr>@empty<tr><td colspan="7">No delivery records yet.</td></tr>@endforelse</tbody></table></div><div style="margin-top:14px">{{ $items->links() }}</div></div>
</div></div>
@endsection
