
@extends('layouts.schoolnexa')

@section('content')
@php
    $studentName = $student->full_name
        ?? trim(($student->first_name ?? '') . ' ' . ($student->last_name ?? ''));

    $examName = $examination->name ?? 'Examination';
    $schoolName = $school->name ?? 'School Name';

    $subjectRows = $subjects ?? collect();

    $totalObtained = (float) ($reportCard->total_marks ?? 0);
    $totalMaximum = $subjectRows->sum(function ($item) {
        return (float) ($item->maximum_marks ?? 0);
    });

    $percentage = (float) ($reportCard->percentage ?? 0);
    $grade = $reportCard->overall_grade ?? '-';
    $rank = $reportCard->rank ?? '-';

    $published = !empty($reportCard->published_at);
    $resultStatus = $percentage >= 33 ? 'PASS' : 'FAIL';

    $guardianName = $guardian->full_name ?? 'Not available';

    $teacherName = $teacher
        ? trim(($teacher->first_name ?? '') . ' ' . ($teacher->last_name ?? ''))
        : 'Class Teacher';
@endphp

<style>
    .report-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .report-page * {
        box-sizing: border-box;
    }

    .report-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 20px;
    }

    .report-toolbar h2 {
        font-size: 22px;
        font-weight: 700;
        margin: 0;
        color: #123B5D;
    }

    .report-toolbar p {
        margin: 5px 0 0;
        font-size: 13px;
        color: #64748B;
    }

    .report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .report-btn {
        height: 39px;
        padding: 0 13px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: white;
        color: #123B5D;
        font-size: 13px;
        font-weight: 600;
        font-family: 'Inter', sans-serif;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        text-decoration: none;
    }

    .report-btn:hover {
        background: #F8FAFC;
    }

    .report-btn-primary {
        background: #123B5D;
        color: white;
        border-color: #123B5D;
    }

    .report-btn-primary:hover {
        background: #0D2F4A;
        color: white;
    }

    .report-btn-success {
        background: #16A34A;
        border-color: #16A34A;
        color: white;
    }

    .report-btn-success:hover {
        background: #15803D;
        color: white;
    }

    .report-status {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        background: {{ $published ? '#DCFCE7' : '#FEF3C7' }};
        color: {{ $published ? '#166534' : '#92400E' }};
    }

    .report-paper {
        background: white;
        border: 1px solid #CBD5E1;
        border-radius: 12px;
        padding: 34px;
        max-width: 950px;
        margin: 0 auto;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .06);
    }

    .report-school-header {
        text-align: center;
        padding-bottom: 20px;
        border-bottom: 2px solid #123B5D;
        margin-bottom: 20px;
    }

    .report-school-logo {
        width: 65px;
        height: 65px;
        border: 1px solid #CBD5E1;
        border-radius: 50%;
        display: inline-flex;
        justify-content: center;
        align-items: center;
        color: #123B5D;
        font-size: 25px;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .report-school-header h1 {
        font-size: 23px;
        font-weight: 800;
        color: #123B5D;
        margin: 0;
        text-transform: uppercase;
    }

    .report-school-header p {
        font-size: 12px;
        color: #64748B;
        margin: 5px 0 0;
    }

    .report-heading {
        text-align: center;
        margin: 20px 0;
    }

    .report-heading h2 {
        font-size: 18px;
        font-weight: 800;
        color: #123B5D;
        margin: 0;
        text-transform: uppercase;
    }

    .report-heading p {
        font-size: 13px;
        color: #64748B;
        margin-top: 5px;
    }

    .report-section-title {
        font-size: 14px;
        font-weight: 700;
        color: #123B5D;
        padding-bottom: 8px;
        border-bottom: 1px solid #CBD5E1;
        margin: 22px 0 12px;
    }

    .report-student-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 13px 20px;
    }

    .report-field label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        color: #64748B;
        margin-bottom: 4px;
    }

    .report-field strong {
        font-size: 12px;
        font-weight: 600;
        color: #25313C;
        overflow-wrap: anywhere;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .report-table th {
        background: #F1F5F9;
        color: #123B5D;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        padding: 11px 10px;
        border: 1px solid #CBD5E1;
    }

    .report-table td {
        padding: 11px 10px;
        border: 1px solid #CBD5E1;
        color: #25313C;
    }

    .report-table .center {
        text-align: center;
    }

    .report-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 18px;
    }

    .report-summary-card {
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 13px;
        text-align: center;
        background: #F8FAFC;
    }

    .report-summary-card label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #64748B;
        text-transform: uppercase;
    }

    .report-summary-card strong {
        display: block;
        font-size: 20px;
        font-weight: 800;
        color: #123B5D;
        margin-top: 6px;
    }

    .report-result {
        display: inline-block;
        padding: 5px 13px;
        border-radius: 20px;
        font-weight: 800;
        font-size: 12px;
        background: {{ $resultStatus === 'PASS' ? '#DCFCE7' : '#FEE2E2' }};
        color: {{ $resultStatus === 'PASS' ? '#166534' : '#991B1B' }};
    }

    .report-remarks {
        min-height: 55px;
        border: 1px solid #CBD5E1;
        border-radius: 7px;
        padding: 12px;
        font-size: 12px;
        line-height: 1.6;
    }

    .report-signatures {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-top: 55px;
        text-align: center;
    }

    .report-signature {
        border-top: 1px solid #64748B;
        padding-top: 9px;
        font-size: 11px;
        font-weight: 700;
        color: #25313C;
    }

    .report-footer {
        text-align: center;
        border-top: 1px solid #CBD5E1;
        padding-top: 12px;
        margin-top: 25px;
        font-size: 10px;
        color: #64748B;
    }

    .report-muted {
        color: #64748B;
    }

    @media(max-width: 700px) {
        .report-paper {
            padding: 18px;
        }

        .report-student-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .report-summary {
            grid-template-columns: repeat(2, 1fr);
        }

        .report-table {
            min-width: 550px;
        }

        .report-table-wrap {
            overflow-x: auto;
        }

        .report-signatures {
            gap: 12px;
        }
    }

    @media print {
        @page {
            size: A4;
            margin: 12mm;
        }

        body * {
            visibility: hidden !important;
        }

        #report-card-print-area,
        #report-card-print-area * {
            visibility: visible !important;
        }

        #report-card-print-area {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0 !important;
            box-shadow: none !important;
        }

        .no-print {
            display: none !important;
        }

        .report-paper {
            border: none;
            box-shadow: none;
            padding: 0;
        }

        .report-table th {
            background: #F1F5F9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .report-summary-card,
        .report-result,
        .report-status {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .report-section-title,
        .report-summary,
        .report-signatures {
            break-inside: avoid;
        }
    }
</style>

<div class="report-page">

    {{-- Toolbar --}}
    <div class="report-toolbar no-print">
        <div>
            <h2>Report Card Preview</h2>
            <p>
                Academic / Report Cards /
                {{ $studentName }}
            </p>
            <div style="margin-top:8px;">
                <span class="report-status">
                    {{ $published ? 'Published' : 'Generated · Awaiting Publish' }}
                </span>
            </div>
        </div>

        <div class="report-actions">

            <a href="{{ route('report-cards.index') }}" class="report-btn">
                ← Back
            </a>

            <button type="button" class="report-btn" onclick="window.print()">
                Print
            </button>

            <button type="button" class="report-btn report-btn-primary"
                    onclick="saveReportPDF()">
                Save as PDF
            </button>

            @if(!$published)
                @if(auth()->user()->hasPermission('EXAMINATIONS.PUBLISH'))
                    <form method="POST"
                          action="{{ route('report-cards.publish', $reportCard->id) }}"
                          onsubmit="return confirm('Publish this report card? It will be released to the parent portal.');">
                        @csrf
                        <button type="submit" class="report-btn report-btn-success">
                            Publish Report
                        </button>
                    </form>
                @endif
            @endif

        </div>
    </div>

    {{-- Report Card Document --}}
    <div class="report-paper" id="report-card-print-area">

        {{-- School Header --}}
        <div class="report-school-header">

            <div class="report-school-logo">
                {{ strtoupper(substr($schoolName, 0, 1)) }}
            </div>

            <h1>{{ $schoolName }}</h1>

            <p>
                {{ $school->address ?? 'Academic Excellence and Student Development' }}
            </p>

            <p>
                Official Academic Report
            </p>

        </div>

        {{-- Examination Heading --}}
        <div class="report-heading">
            <h2>Student Progress Report</h2>
            <p>
                {{ $examName }}
            </p>
        </div>

        {{-- Student Details --}}
        <div class="report-section-title">
            STUDENT INFORMATION
        </div>

        <div class="report-student-grid">

            <div class="report-field">
                <label>Student Name</label>
                <strong>{{ $studentName }}</strong>
            </div>

            <div class="report-field">
                <label>Admission Number</label>
                <strong>{{ $student->admission_number ?? '-' }}</strong>
            </div>

            <div class="report-field">
                <label>Academic Year</label>
                <strong>{{ $examination->academicYear->name ?? '2026-2027' }}</strong>
            </div>

            <div class="report-field">
                <label>Class</label>
                <strong>{{ $enrollment->schoolClass->name ?? '-' }}</strong>
            </div>

            <div class="report-field">
                <label>Section</label>
                <strong>{{ $enrollment->section->name ?? '-' }}</strong>
            </div>

            <div class="report-field">
                <label>Parent / Guardian</label>
                <strong>{{ $guardianName }}</strong>
            </div>

        </div>

        {{-- Subject Performance --}}
        <div class="report-section-title">
            SUBJECT-WISE PERFORMANCE
        </div>

        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width:7%;">Sr. No.</th>
                        <th>Subject</th>
                        <th class="center">Maximum Marks</th>
                        <th class="center">Marks Obtained</th>
                        <th class="center">Grade</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($subjectRows as $index => $item)
                        <tr>
                            <td class="center">{{ $index + 1 }}</td>

                            <td>
                                {{ $item->subject_name ?? $item->name ?? 'Subject' }}
                            </td>

                            <td class="center">
                                {{ number_format((float)($item->maximum_marks ?? 0), 2) }}
                            </td>

                            <td class="center">
                                @if(($item->grade ?? '') === 'AB')
                                    AB
                                @elseif($item->marks_obtained !== null)
                                    {{ number_format((float)$item->marks_obtained, 2) }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="center">
                                {{ $item->grade ?? '-' }}
                            </td>

                            <td>
                                {{ $item->remarks ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="center">
                                No subject results available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Summary --}}
        <div class="report-section-title">
            ACADEMIC SUMMARY
        </div>

        <div class="report-summary">

            <div class="report-summary-card">
                <label>Total Marks</label>
                <strong>{{ number_format($totalObtained, 2) }}</strong>
            </div>

            <div class="report-summary-card">
                <label>Percentage</label>
                <strong>{{ number_format($percentage, 2) }}%</strong>
            </div>

            <div class="report-summary-card">
                <label>Overall Grade</label>
                <strong>{{ $grade }}</strong>
            </div>

            <div class="report-summary-card">
                <label>Class Rank</label>
                <strong>{{ $rank }}</strong>
            </div>

        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-top:18px;">

            <div class="report-field">
                <label>Attendance</label>
                <strong>
                    {{ number_format((float)($attendancePercentage ?? 0), 1) }}%
                </strong>
            </div>

            <div class="report-field">
                <label>Final Result</label>
                <span class="report-result">{{ $resultStatus }}</span>
            </div>

        </div>

        {{-- Teacher Remarks --}}
        <div class="report-section-title">
            CLASS TEACHER REMARKS
        </div>

        <div class="report-remarks">
            {{ $reportCard->teacher_remarks ?: 'No teacher remarks added.' }}
        </div>

        {{-- Principal Remarks --}}
        <div class="report-section-title">
            PRINCIPAL REMARKS
        </div>

        <div class="report-remarks">
            {{ $reportCard->principal_remarks ?: 'No principal remarks added.' }}
        </div>

        {{-- Signatures --}}
        <div class="report-signatures">

            <div class="report-signature">
                {{ $teacherName }}<br>
                <span class="report-muted">Class Teacher</span>
            </div>

            <div class="report-signature">
                Parent / Guardian<br>
                <span class="report-muted">Signature</span>
            </div>

            <div class="report-signature">
                Principal<br>
                <span class="report-muted">School Authority</span>
            </div>

        </div>

        {{-- Footer --}}
        <div class="report-footer">
            This is a computer-generated academic report.
            @if($published)
                Published on {{ $reportCard->published_at->format('d M Y, h:i A') }}.
            @else
                Generated on {{ $reportCard->created_at->format('d M Y, h:i A') }}.
            @endif
        </div>

    </div>
</div>

<script>
    function saveReportPDF() {
        window.print();
    }
</script>

@endsection
