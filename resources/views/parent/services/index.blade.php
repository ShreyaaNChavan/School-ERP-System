@extends('layouts.schoolnexa')

@section('title', $title)

@section('content')

@php
    $formatValue = function ($value, $key) {
        if (is_null($value) || $value === '') {
            return '—';
        }

        if (str_contains($key, 'attachment_path')) {
            return $value;
        }

        if (str_contains($key, 'status')) {
            return ucfirst(str_replace('_', ' ', $value));
        }

        if (str_contains($key, 'time')) {
            return $value;
        }

        return $value;
    };

    $studentName = function ($child) {
        return trim($child->first_name.' '.($child->middle_name ?? '').' '.$child->last_name);
    };
@endphp

<style>
    .parent-service { color:#243b53; }
    .parent-service-header {
        display:flex; justify-content:space-between; align-items:center;
        gap:15px; flex-wrap:wrap; margin-bottom:18px;
    }
    .parent-service-header h1 { margin:0; font-size:23px; color:#173b60; }
    .parent-service-header p { margin:6px 0 0; color:#8090a3; font-size:12px; }
    .parent-child-strip {
        display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
        gap:12px; margin-bottom:20px;
    }
    .parent-child-card {
        background:#fff; border:1px solid #e4eaf1; border-radius:10px;
        padding:15px; display:flex; align-items:center; gap:12px;
    }
    .parent-child-avatar {
        width:42px;height:42px;border-radius:50%;display:grid;place-items:center;
        background:#e8f2fc;color:#176ca7;font-weight:800;font-size:15px;
    }
    .parent-child-card strong { display:block;font-size:12px;color:#294761; }
    .parent-child-card small { display:block;margin-top:5px;color:#8794a4;font-size:10px; }
    .parent-service-panel {
        background:#fff;border:1px solid #e3eaf1;border-radius:11px;
        padding:17px;margin-bottom:17px;min-width:0;
    }
    .parent-service-panel h2 { font-size:15px;color:#244665;margin:0 0 14px; }
    .parent-service-table { width:100%;border-collapse:collapse;white-space:nowrap; }
    .parent-service-table th {
        background:#f6f9fc;color:#77899d;font-size:10px;text-transform:uppercase;
        text-align:left;padding:12px;
    }
    .parent-service-table td {
        padding:13px 12px;border-bottom:1px solid #edf1f5;
        font-size:11px;color:#40556b;
    }
    .parent-service-table tr:last-child td { border-bottom:0; }
    .parent-service-empty { text-align:center;padding:38px 15px;color:#8291a2;font-size:12px; }
    .parent-service-count {
        display:inline-block;background:#eaf4fb;color:#176ca7;
        border-radius:20px;padding:5px 10px;font-size:10px;font-weight:700;
    }
    @media(max-width:650px) {
        .parent-service-panel { padding:12px; }
        .parent-service-header h1 { font-size:19px; }
    }
</style>

<div class="parent-service">

    <div class="parent-service-header">
        <div>
            <h1>{{ $title }}</h1>
            <p>View information and records for your linked children.</p>
        </div>

        <span class="parent-service-count">
            {{ $children->count() }} Linked {{ $children->count() === 1 ? 'Child' : 'Children' }}
        </span>
    </div>

    {{-- Linked Children --}}
    <div class="parent-child-strip">
        @forelse($children as $child)
            <div class="parent-child-card">
                <div class="parent-child-avatar">
                    {{ strtoupper(substr($child->first_name, 0, 1)) }}
                </div>

                <div>
                    <strong>{{ $studentName($child) }}</strong>
                    <small>Admission No: {{ $child->admission_number }}</small>
                    <small>Status: {{ ucfirst($child->status) }}</small>
                </div>
            </div>
        @empty
            <div class="parent-service-panel">
                No children are linked to this parent account.
                Please contact the school administrator.
            </div>
        @endforelse
    </div>

    {{-- Main Records --}}
    <div class="parent-service-panel">
        <h2>{{ $title }}</h2>

        @if($records->isEmpty())
            <div class="parent-service-empty">
                <div style="font-size:28px;margin-bottom:9px;">
                    <i class="fa-regular fa-folder-open"></i>
                </div>
                <strong>No records available</strong>
                <p>There are currently no {{ strtolower($title) }} records for your linked children.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="parent-service-table">
                    <thead>
                        <tr>
                            @foreach(array_keys((array)$records->first()) as $column)
                                <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($records as $record)
                            <tr>
                                @foreach((array)$record as $key => $value)
                                    <td>
                                        @if($key === 'attachment_path' && $value)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($value) }}"
                                               target="_blank" rel="noopener"
                                               style="color:#176ca7;font-weight:600;">
                                                View Attachment
                                            </a>
                                        @else
                                            {{ $formatValue($value, $key) }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Secondary Records: Hostel Attendance --}}
    @if(isset($secondaryRecords))
        <div class="parent-service-panel">
            <h2>{{ $secondaryTitle ?? 'Additional Records' }}</h2>

            @if($secondaryRecords->isEmpty())
                <div class="parent-service-empty">No additional records available.</div>
            @else
                <div style="overflow-x:auto;">
                    <table class="parent-service-table">
                        <thead>
                            <tr>
                                @foreach(array_keys((array)$secondaryRecords->first()) as $column)
                                    <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($secondaryRecords as $record)
                                <tr>
                                    @foreach((array)$record as $key => $value)
                                        <td>{{ $formatValue($value, $key) }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

</div>
@endsection