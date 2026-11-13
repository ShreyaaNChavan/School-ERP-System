
@extends('layouts.schoolnexa')

@section('content')
<div class="sn-page">
    <style>
        .cert-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
        .cert-detail{padding:15px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:10px}
        .cert-detail-label{font-size:11px;font-weight:700;color:#64748B;text-transform:uppercase;letter-spacing:.4px;margin-bottom:7px}
        .cert-detail-value{font-size:14px;font-weight:650;color:#25313C;overflow-wrap:anywhere}
        .cert-number{color:#176B87;font-weight:750;font-size:16px}
        .cert-status{display:inline-flex;align-items:center;gap:7px;background:#DCFCE7;color:#166534;padding:6px 11px;border-radius:20px;font-size:12px;font-weight:700}
        .cert-status-dot{height:7px;width:7px;background:#16A34A;border-radius:50%}
        .cert-footer{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;margin-top:22px;padding-top:18px;border-top:1px solid #E2E8F0}
        @media(max-width:600px){.cert-detail-grid{grid-template-columns:1fr}}
    </style>

    <div class="sn-head">
        <div>
            <h1 class="sn-title">Certificate Details</h1>
            <div class="sn-sub">View issued certificate information and download its PDF</div>
        </div>
        <div class="sn-actions">
            <a href="{{ route('certificates.index') }}" class="sn-btn sn-btn-outline">Back to Certificate History</a>
        </div>
    </div>

    @if(session('success'))
        <div class="sn-alert">{{ session('success') }}</div>
    @endif

    <div class="sn-card">
        <div class="sn-head">
            <div>
                <h2 class="sn-section-title">Certificate Information</h2>
                <div class="sn-sub">Official certificate record</div>
            </div>
            <span class="cert-status">
                <span class="cert-status-dot"></span>
                Issued
            </span>
        </div>

        <div class="cert-detail-grid">
            <div class="cert-detail">
                <div class="cert-detail-label">Certificate Number</div>
                <div class="cert-detail-value cert-number">{{ $record->certificate_number }}</div>
            </div>

            <div class="cert-detail">
                <div class="cert-detail-label">Certificate Type</div>
                <div class="cert-detail-value">{{ $record->certificate_type }}</div>
            </div>

            <div class="cert-detail">
                <div class="cert-detail-label">Student Name</div>
                <div class="cert-detail-value">
                    @if($student)
                        {{ trim($student->first_name.' '.($student->middle_name ?? '').' '.($student->last_name ?? '')) }}
                    @else
                        Student record unavailable
                    @endif
                </div>
            </div>

            <div class="cert-detail">
                <div class="cert-detail-label">Admission Number</div>
                <div class="cert-detail-value">{{ $student->admission_number ?? '—' }}</div>
            </div>

            <div class="cert-detail">
                <div class="cert-detail-label">Issue Date</div>
                <div class="cert-detail-value">
                    {{ \Carbon\Carbon::parse($record->issued_date)->format('d F Y') }}
                </div>
            </div>

            <div class="cert-detail">
                <div class="cert-detail-label">PDF Status</div>
                <div class="cert-detail-value">
                    {{ $record->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($record->file_path) ? 'PDF Available' : 'PDF Not Available' }}
                </div>
            </div>
        </div>

        <div class="cert-footer">
            <a href="{{ route('certificates.index') }}" class="sn-btn sn-btn-outline">Close</a>

            @if($record->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($record->file_path))
                <a href="{{ route('certificates.download', $record->id) }}" class="sn-btn sn-btn-primary">
                    Download Certificate PDF
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
