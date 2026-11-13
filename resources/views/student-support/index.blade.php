@extends('layouts.schoolnexa')
@section('content')
<div class="sn-page ss-page">@include('student-support._style')
    <div class="ss-head"><div><h1>{{ $config['title'] }}</h1><div class="ss-sub">{{ $config['description'] }}</div></div><div class="ss-actions"><a class="ss-btn primary" href="{{ route('student-support.create',$module) }}">+ Add Record</a><a class="ss-btn" href="{{ route('student-support.dashboard') }}">← Back to Support Center</a></div></div>
    <div class="ss-panel"><form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px"><input name="search" value="{{ request('search') }}" placeholder="Search records..." style="flex:1;min-width:220px;border:1px solid #CBD5E1;border-radius:9px;padding:10px 12px"><select name="status" style="border:1px solid #CBD5E1;border-radius:9px;padding:10px"><option value="">All statuses</option>@foreach(['submitted','open','under_review','investigating','in_progress','scheduled','completed','resolved','closed','cancelled','active','responding'] as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>@endforeach</select><button class="ss-btn primary">Apply</button><a class="ss-btn" href="{{ route('student-support.index',$module) }}">Reset</a></form>
        <table class="ss-table"><thead><tr><th>REFERENCE</th><th>RECORD</th><th>PRIORITY / SEVERITY</th><th>STATUS</th><th>DATE</th><th>ACTIONS</th></tr></thead><tbody>
        @forelse($items as $item)
            <tr><td>{{ $item->reference_number ?? $item->case_reference ?? ('#'.$item->id) }}</td><td>{{ $item->subject ?? $item->title ?? $item->session_type ?? $item->summary ?? 'Support record' }}</td><td>{{ ucfirst($item->priority ?? $item->severity ?? '—') }}</td><td><span class="ss-badge">{{ ucwords(str_replace('_',' ',$item->status ?? '—')) }}</span></td><td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d M Y') : '—' }}</td><td><div class="ss-actions" style="justify-content:flex-start"><a class="ss-btn" href="{{ route('student-support.show',[$module,$item->id]) }}">View</a></div></td></tr>
        @empty<tr><td colspan="6" style="text-align:center;padding:28px;color:#64748B">No records found.</td></tr>@endforelse
        </tbody></table><div style="margin-top:16px">{{ $items->links() }}</div>
    </div>
</div>
@endsection
