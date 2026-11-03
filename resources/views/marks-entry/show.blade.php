
@extends('layouts.schoolnexa')

@section('content')
<div class="marks-page">

    <div class="marks-header">
        <div>
            <div class="marks-breadcrumb">Dashboard / Academic / Marks Entry</div>
            <h1>Marks Entry</h1>
            <p>Enter, review and publish student examination results.</p>
        </div>

        <a href="{{ route('marks-entry.index') }}" class="marks-btn">← Back to Marks Entry</a>
    </div>

    <div class="marks-exam-card">
        <div class="marks-exam-top">
            <div>
                <span class="marks-label">EXAMINATION</span>
                <h2>{{ $examSubject->examination->name }}</h2>
                <p>{{ $examSubject->schoolClass->name }} · Section {{ $examSubject->section->name }} · {{ $examSubject->subject->name }}</p>
            </div>
            <span class="marks-status {{ $isPublished ? 'published' : 'draft' }}">
                {{ $isPublished ? 'Published' : 'Draft' }}
            </span>
        </div>

        <div class="marks-exam-details">
            <div>
                <span>Exam Date</span>
                <strong>{{ $examSubject->exam_date?->format('d M Y') ?? '—' }}</strong>
            </div>
            <div>
                <span>Maximum Marks</span>
                <strong>{{ number_format($examSubject->maximum_marks, 0) }}</strong>
            </div>
            <div>
                <span>Passing Marks</span>
                <strong>{{ number_format($examSubject->passing_marks, 0) }}</strong>
            </div>
            <div>
                <span>Grading Method</span>
                <strong>CBSE 8-Point</strong>
            </div>
        </div>
    </div>

    <div class="marks-stats">
        <div class="marks-stat">
            <span>Total Students</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="marks-stat">
            <span>Marks Entered</span>
            <strong>{{ $stats['entered'] }}</strong>
        </div>
        <div class="marks-stat">
            <span>Pending</span>
            <strong class="warning-text">{{ $stats['pending'] }}</strong>
        </div>
        <div class="marks-stat">
            <span>Absent</span>
            <strong>{{ $stats['absent'] }}</strong>
        </div>
    </div>

    @if($errors->any())
        <div class="marks-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(session('success'))
        <div class="marks-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="marks-alert error">{{ session('error') }}</div>
    @endif

    <div class="marks-panel">
        <div class="marks-panel-heading">
            <div>
                <h3>Student Marks Sheet</h3>
                <p>Enter marks for each student or mark them absent.</p>
            </div>
            <span class="marks-count">{{ $students->count() }} Students</span>
        </div>

        @if($students->isEmpty())
            <div class="marks-empty">
                No active students are enrolled in this class and section for the examination's academic year.
            </div>
        @else
            <form method="POST" action="{{ route('marks-entry.save', $examSubject->id) }}" id="marksForm">
                @csrf

                <div class="marks-table-wrap">
                    <table class="marks-table">
                        <thead>
                            <tr>
                                <th>Roll / Admission No.</th>
                                <th>Student Name</th>
                                <th>Attendance</th>
                                <th>Marks</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($students as $index => $student)
                                <tr class="student-row"
                                    data-index="{{ $index }}"
                                    data-existing-status="{{ $student->entry_status }}">

                                    <td>
                                        <strong>{{ $student->admission_number }}</strong>
                                    </td>

                                    <td>
                                        <strong>{{ $student->full_name }}</strong>
                                    </td>

                                    <td>
                                        <select
                                            class="marks-input attendance-select"
                                            name="entries[{{ $index }}][status]"
                                            data-index="{{ $index }}"
                                            {{ $isPublished ? 'disabled' : '' }}>
                                            <option value="present"
                                                @selected(old("entries.$index.status", $student->entry_status === 'absent' ? 'absent' : 'present') === 'present')>
                                                Present
                                            </option>
                                            <option value="absent"
                                                @selected(old("entries.$index.status", $student->entry_status === 'absent' ? 'absent' : 'present') === 'absent')>
                                                Absent
                                            </option>
                                        </select>

                                        <input type="hidden"
                                            name="entries[{{ $index }}][student_id]"
                                            value="{{ $student->student_id }}">
                                    </td>

                                    <td>
                                        <input
                                            type="number"
                                            class="marks-input marks-value"
                                            name="entries[{{ $index }}][marks]"
                                            data-index="{{ $index }}"
                                            min="0"
                                            max="{{ $examSubject->maximum_marks }}"
                                            step="0.01"
                                            value="{{ old("entries.$index.marks", $student->marks_obtained) }}"
                                            placeholder="—"
                                            {{ $isPublished ? 'disabled' : '' }}>
                                    </td>

                                    <td>
                                        <span class="grade-display" id="grade-{{ $index }}">
                                            {{ $student->grade ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        <input
                                            type="text"
                                            class="marks-input remarks-input"
                                            name="entries[{{ $index }}][remarks]"
                                            value="{{ old("entries.$index.remarks", $student->remarks) }}"
                                            placeholder="Optional"
                                            {{ $isPublished ? 'disabled' : '' }}>
                                    </td>

                                    <td>
                                        <span class="entry-badge {{ $student->entry_status }}">
                                            {{ ucfirst($student->entry_status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="marks-footer">
                    <div class="marks-summary">
                        <span>Total: <strong>{{ $stats['total'] }}</strong></span>
                        <span>Entered: <strong>{{ $stats['entered'] }}</strong></span>
                        <span>Pending: <strong>{{ $stats['pending'] }}</strong></span>
                        <span>Absent: <strong>{{ $stats['absent'] }}</strong></span>
                    </div>

                    <div class="marks-actions">
                        @if(!$isPublished)
                            <button type="submit" class="marks-btn marks-primary">
                                Save Draft
                            </button>
                        @else
                            <span class="locked-message">This marks sheet is published and locked.</span>
                        @endif
                    </div>
                </div>
            </form>

            @if(!$isPublished)
                <div class="publish-area">
                    <div>
                        <strong>Ready to publish?</strong>
                        <p>All students must have marks entered or be marked absent.</p>
                    </div>

                    <form method="POST"
                        action="{{ route('marks-entry.publish', $examSubject->id) }}"
                        onsubmit="return confirm('Publish this marks sheet? It will be locked against further editing.');">
                        @csrf
                        <button type="submit" class="marks-btn marks-publish">
                            Publish Marks
                        </button>
                    </form>
                </div>
            @endif
        @endif
    </div>
</div>

<style>
.marks-page{font-family:'Inter',sans-serif;color:#25313C;padding:24px;max-width:1500px;margin:auto}
.marks-header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:22px}
.marks-breadcrumb{font-size:12px;color:#64748B;margin-bottom:8px}
.marks-header h1{font-size:22px;font-weight:700;color:#123B5D;margin:0}
.marks-header p{font-size:13px;color:#64748B;margin:6px 0 0}
.marks-exam-card,.marks-stat,.marks-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:20px;margin-bottom:18px}
.marks-exam-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
.marks-label{font-size:11px;font-weight:650;color:#64748B;letter-spacing:.5px}
.marks-exam-top h2{font-size:18px;font-weight:700;color:#123B5D;margin:7px 0}
.marks-exam-top p{font-size:13px;color:#64748B;margin:0}
.marks-status,.entry-badge{display:inline-flex;padding:5px 10px;border-radius:20px;font-size:11px;font-weight:650}
.marks-status.draft,.entry-badge.pending{background:#FEF3C7;color:#92400E}
.marks-status.published{background:#DCFCE7;color:#166534}
.marks-exam-details{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-top:22px;padding-top:17px;border-top:1px solid #E2E8F0}
.marks-exam-details span{display:block;font-size:11px;color:#64748B;margin-bottom:6px}
.marks-exam-details strong{font-size:13px;font-weight:600;color:#25313C}
.marks-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:18px}
.marks-stat{margin:0}
.marks-stat span{display:block;font-size:12px;font-weight:600;color:#64748B}
.marks-stat strong{display:block;font-size:26px;font-weight:700;color:#123B5D;margin-top:8px}
.warning-text{color:#D97706!important}
.marks-panel{padding:20px}
.marks-panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.marks-panel-heading h3{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.marks-panel-heading p{font-size:13px;color:#64748B;margin:5px 0 0}
.marks-count{font-size:12px;color:#64748B}
.marks-table-wrap{overflow-x:auto}
.marks-table{width:100%;border-collapse:collapse;min-width:950px}
.marks-table th{font-size:11px;font-weight:650;color:#64748B;background:#F8FAFC;text-align:left;padding:13px 12px;border-bottom:1px solid #CBD5E1}
.marks-table td{font-size:13px;padding:12px;border-bottom:1px solid #E2E8F0;vertical-align:middle}
.marks-table td strong{font-weight:550}
.marks-input{height:37px;border:1px solid #CBD5E1;border-radius:8px;padding:0 9px;font:400 12px 'Inter',sans-serif;color:#25313C;background:#fff;max-width:145px;width:100%}
.marks-input:disabled{background:#F1F5F9;cursor:not-allowed}
.grade-display{font-size:12px;font-weight:700;color:#123B5D}
.entry-badge.present{background:#DCFCE7;color:#166534}
.entry-badge.absent{background:#FEE2E2;color:#991B1B}
.entry-badge.pending{background:#FEF3C7;color:#92400E}
.marks-footer{display:flex;justify-content:space-between;align-items:center;gap:15px;padding-top:18px;flex-wrap:wrap}
.marks-summary{display:flex;gap:18px;flex-wrap:wrap;font-size:12px;color:#64748B}
.marks-summary strong{color:#25313C}
.marks-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 13px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px 'Inter',sans-serif;text-decoration:none;white-space:nowrap;cursor:pointer}
.marks-btn:hover{background:#F8FAFC;color:#123B5D}
.marks-primary{background:#123B5D;color:#fff;border-color:#123B5D}
.marks-primary:hover{background:#176B87;color:#fff}
.marks-publish{background:#16A34A;color:#fff;border-color:#16A34A}
.marks-publish:hover{background:#15803D;color:#fff}
.publish-area{display:flex;justify-content:space-between;align-items:center;gap:15px;margin-top:20px;padding-top:18px;border-top:1px solid #E2E8F0}
.publish-area strong{font-size:13px;color:#123B5D}
.publish-area p{font-size:12px;color:#64748B;margin:5px 0 0}
.marks-alert{padding:13px 15px;border-radius:9px;margin-bottom:16px;font-size:13px}
.marks-alert ul{margin:8px 0 0;padding-left:20px}
.marks-alert.error{background:#FEE2E2;color:#991B1B}
.marks-alert.success{background:#DCFCE7;color:#166534}
.marks-empty{text-align:center;padding:40px 15px;color:#64748B;font-size:13px}
.locked-message{font-size:12px;color:#166534;font-weight:600}
@media(max-width:850px){.marks-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.marks-exam-details{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.marks-page{padding:14px}.marks-header{align-items:flex-start;flex-direction:column}.marks-stats{grid-template-columns:1fr 1fr}.publish-area{align-items:flex-start;flex-direction:column}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const maximumMarks = {{ (float) $examSubject->maximum_marks }};
    const rows = document.querySelectorAll('.student-row');

    function calculateGrade(marks) {
        const percentage = (marks / maximumMarks) * 100;

        if (percentage >= 91) return 'A1';
        if (percentage >= 81) return 'A2';
        if (percentage >= 71) return 'B1';
        if (percentage >= 61) return 'B2';
        if (percentage >= 51) return 'C1';
        if (percentage >= 41) return 'C2';
        if (percentage >= 33) return 'D';

        return 'E';
    }

    function updateRow(row) {
        const index = row.dataset.index;
        const attendance = row.querySelector('.attendance-select');
        const marksInput = row.querySelector('.marks-value');
        const gradeDisplay = document.getElementById('grade-' + index);

        if (!attendance || !marksInput || !gradeDisplay) return;

        if (attendance.value === 'absent') {
            marksInput.value = '';
            marksInput.disabled = true;
            gradeDisplay.textContent = 'AB';
        } else {
            if (!marksInput.disabled) {
                const marks = parseFloat(marksInput.value);
                gradeDisplay.textContent = marksInput.value !== '' && !isNaN(marks)
                    ? calculateGrade(marks)
                    : '—';
            }
            marksInput.disabled = false;
        }
    }

    rows.forEach(function (row) {
        const attendance = row.querySelector('.attendance-select');
        const marksInput = row.querySelector('.marks-value');

        if (attendance && !attendance.disabled) {
            attendance.addEventListener('change', function () {
                updateRow(row);
            });
        }

        if (marksInput && !marksInput.disabled) {
            marksInput.addEventListener('input', function () {
                updateRow(row);
            });
        }

        updateRow(row);
    });
});
</script>
@endsection
