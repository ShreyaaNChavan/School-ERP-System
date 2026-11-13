@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page">@include('shared.module-style')
<style>
.cc-head{display:flex;justify-content:space-between;gap:12px;margin-bottom:18px}.cc-head h1{font-size:23px;color:#123B5D;margin:0 0 5px}.cc-head p{font-size:13px;color:#64748B;margin:0}.cc-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font-size:13px;font-weight:650}.cc-primary{background:#123B5D;color:#fff;border-color:#123B5D}.cc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:15px;padding:20px}.cc-table{width:100%;border-collapse:collapse}.cc-table th{background:#F8FAFC;color:#64748B;font-size:11px;text-align:left;padding:13px}.cc-table td{font-size:13px;padding:14px;border-top:1px solid #E2E8F0}
</style>
<div class="cc-head"><div><h1>Message Center</h1><p>Authorized direct and group conversations</p></div><div style="display:flex;gap:8px"><a class="cc-btn" href="{{ route('communication.dashboard') }}">← Back to Dashboard</a><a class="cc-btn cc-primary" href="{{ route('communication.compose') }}">+ New Message</a></div></div>
<div class="cc-panel"><div style="overflow-x:auto"><table class="cc-table"><thead><tr><th>SUBJECT</th><th>TYPE</th><th>CREATED</th><th>LAST ACTIVITY</th><th>ACTION</th></tr></thead><tbody>@forelse($items as $row)<tr><td><strong>{{ $row->subject }}</strong></td><td>{{ ucfirst($row->conversation_type) }}</td><td>{{ \Carbon\Carbon::parse($row->created_at)->format('d M Y') }}</td><td>{{ $row->last_message_at ? \Carbon\Carbon::parse($row->last_message_at)->format('d M Y, h:i A') : '—' }}</td><td><a class="cc-btn" href="{{ route('communication.message.show',$row->id) }}">Open</a></td></tr>@empty<tr><td colspan="5">No conversations yet.</td></tr>@endforelse</tbody></table></div><div style="margin-top:15px">{{ $items->links() }}</div></div>
</div></div>
@endsection
