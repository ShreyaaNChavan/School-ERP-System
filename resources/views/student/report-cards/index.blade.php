
@extends('layouts.schoolnexa')

@section('title', 'My Report Cards')

@section('content')
@php
    $studentName = $student->full_name
        ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

    $stats = $stats ?? [
        'total' => 0,
        'published' => 0,
        'pending' => 0,
        'average' => 0,
    ];
@endphp

<div class="rc-page">

    <div class="rc-heading">
        <div>
            <div class="rc-breadcrumb">Dashboard / My Academics / Report Cards</div>
            <h1>My Report Cards</h1>
            <p>View your published examination results and academic performance.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rc-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rc-alert danger">{{ session('error') }}</div>
    @endif

    <div class="rc-student-banner">
        <div class="rc-avatar">
            {{ strtoupper(substr($studentName ?: 'S', 0, 1)) }}
        </div>
        <div>
            <span>Student</span>
            <strong>{{ $studentName }}</strong>
            <small>Admission No: {{ $student->admission_number ?? '—' }}</small>
        </div>
    </div>

    <div class="rc-stats">
        <div class="rc-stat">
            <span>Published Report Cards</span>
            <strong>{{ $stats['published'] ?? $reportCards->total() }}</strong>
            <small>Available results</small>
        </div>

        <div class="rc-stat">
            <span>Average Percentage</span>
            <strong>{{ number_format((float)($stats['average'] ?? 0), 2) }}%</strong>
            <small>Your academic average</small>
        </div>

        <div class="rc-stat">
            <span>Latest Grade</span>
            <strong class="purple">
                {{ $reportCards->first()->overall_grade ?? '—' }}
            </strong>
            <small>Most recent available result</small>
        </div>

        <div class="rc-stat">
            <span>Available Results</span>
            <strong class="green">{{ $reportCards->total() }}</strong>
            <small>Published examinations</small>
        </div>
    </div>

    <div class="rc-panel">
        <div class="rc-panel-heading">
            <div>
                <h2>My Academic Results</h2>
                <p>Your published examination report cards.</p>
            </div>
            <span class="rc-count">{{ $reportCards->total() }} records</span>
        </div>

        <form method="GET"
              action="{{ route('report-cards.index') }}"
              class="rc-filter">

            <div class="rc-field search-field">
                <label>Search examination</label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Enter examination name">
            </div>

            <div class="rc-filter-actions">
                <button type="submit" class="rc-btn primary">
                    Search
                </button>

                <a href="{{ route('report-cards.index') }}" class="rc-btn">
                    Reset
                </a>
            </div>
        </form>

        <div class="rc-table-wrap">
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>Examination</th>
                        <th>Academic Year</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Rank</th>
                        <th>Result Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reportCards as $card)
                        <tr>
                            <td>
                                <div class="rc-exam-name">
                                    {{ $card->examination_name ?? 'Examination' }}
                                </div>
                                <small class="rc-muted">
                                    {{ $card->published_at
                                        ? \Carbon\Carbon::parse($card->published_at)->format('d M Y')
                                        : 'Published' }}
                                </small>
                            </td>

                            <td>{{ $card->academic_year_name ?? '—' }}</td>

                            <td>
                                {{ number_format((float)($card->total_marks ?? 0), 2) }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format((float)($card->percentage ?? 0), 2) }}%
                                </strong>
                            </td>

                            <td>
                                <span class="rc-grade">
                                    {{ $card->overall_grade ?? '—' }}
                                </span>
                            </td>

                            <td>
                                {{ $card->rank
                                    ? str_pad($card->rank, 2, '0', STR_PAD_LEFT)
                                    : '—' }}
                            </td>

                            <td>
                                <span class="rc-status published">Published</span>
                            </td>

                            <td>
                                <a href="{{ route('report-cards.show', $card->id) }}"
                                   class="rc-btn small">
                                    View Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="rc-empty">
                                <div class="rc-empty-icon">📄</div>
                                <strong>No published report cards yet</strong>
                                <span>
                                    Your examination results will appear here
                                    once your school publishes them.
                                </span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rc-pagination">
            {{ $reportCards->links() }}
        </div>
    </div>

</div>

<style>
.rc-page{font-family:Inter,sans-serif;color:#25313C}
.rc-heading{margin-bottom:22px}
.rc-breadcrumb{font-size:12px;color:#64748B;margin-bottom:8px}
.rc-heading h1{font-size:22px;font-weight:700;margin:0;color:#123B5D}
.rc-heading p{font-size:13px;color:#64748B;margin:6px 0 0}

.rc-student-banner{display:flex;align-items:center;gap:14px;background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:17px 20px;margin-bottom:18px}
.rc-avatar{width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:#E0F2FE;color:#123B5D;font-size:20px;font-weight:800}
.rc-student-banner span,.rc-student-banner small{display:block;font-size:11px;color:#64748B}
.rc-student-banner strong{display:block;font-size:15px;color:#123B5D;margin:3px 0}
.rc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:20px}
.rc-stat,.rc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px}
.rc-stat{padding:17px 19px}
.rc-stat span{display:block;font-size:12px;font-weight:600;color:#64748B}
.rc-stat strong{display:block;font-size:25px;font-weight:700;color:#123B5D;margin:9px 0 4px}
.rc-stat strong.green{color:#16A34A}
.rc-stat strong.purple{color:#5B3FA3}
.rc-stat small{font-size:11px;color:#64748B}
.rc-panel{padding:19px;margin-bottom:20px}
.rc-panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:17px;gap:10px}
.rc-panel-heading h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.rc-panel-heading p{font-size:12px;color:#64748B;margin:5px 0 0}
.rc-count{font-size:12px;color:#64748B}
.rc-filter{display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.rc-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.rc-field label{font-size:12px;font-weight:600;color:#475569}
.rc-filter .search-field{flex:1;max-width:450px}
.rc-field input{height:40px;border:1px solid #CBD5E1;border-radius:8px;padding:0 11px;background:#fff;color:#25313C;font:400 13px Inter,sans-serif;width:100%}
.rc-field input:focus{outline:none;border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.1)}
.rc-filter-actions{display:flex;gap:8px}
.rc-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font:600 13px Inter,sans-serif;white-space:nowrap;cursor:pointer}
.rc-btn:hover{background:#F8FAFC;color:#123B5D}
.rc-btn.primary{background:#123B5D;border-color:#123B5D;color:#fff}
.rc-btn.primary:hover{background:#0E304B}
.rc-btn.small{height:32px;font-size:12px;padding:0 10px}
.rc-table-wrap{overflow-x:auto}
.rc-table{width:100%;border-collapse:collapse;white-space:nowrap}
.rc-table th{background:#F8FAFC;color:#64748B;font-size:11px;font-weight:650;text-align:left;padding:12px 10px;border-bottom:1px solid #CBD5E1}
.rc-table td{font-size:12px;padding:13px 10px;border-bottom:1px solid #E2E8F0;color:#475569}
.rc-exam-name{font-weight:650;color:#25313C}
.rc-muted{display:block;color:#94A3B8;font-size:10px;margin-top:4px}
.rc-grade{display:inline-flex;padding:4px 8px;border-radius:6px;background:#EEF2FF;color:#5B3FA3;font-size:11px;font-weight:650}
.rc-status{display:inline-flex;padding:5px 8px;border-radius:20px;font-size:11px;font-weight:650}
.rc-status.published{background:#DCFCE7;color:#166534}
.rc-empty{text-align:center!important;padding:40px 20px!important}
.rc-empty-icon{font-size:27px;margin-bottom:10px}
.rc-empty strong,.rc-empty span{display:block}
.rc-empty strong{font-size:13px;color:#25313C}
.rc-empty span{font-size:12px;color:#64748B;margin-top:5px;white-space:normal}
.rc-pagination{margin-top:17px}
.rc-alert{padding:12px 15px;border-radius:9px;margin-bottom:16px;font-size:13px}
.rc-alert.success{background:#DCFCE7;color:#166534}
.rc-alert.danger{background:#FEE2E2;color:#991B1B}

@media(max-width:1000px){.rc-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.rc-stats{grid-template-columns:1fr}.rc-panel{padding:14px}.rc-filter .search-field{max-width:none;width:100%}.rc-filter-actions{width:100%}}
</style>
@endsection
