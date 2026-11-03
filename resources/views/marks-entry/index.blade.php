
@extends('layouts.schoolnexa')

@section('content')
<div class="me-page">
    <div class="me-header">
        <div>
            <h1>Marks Entry</h1>
            <p>Manage examination marks, student results and publication.</p>
        </div>
    </div>

    <div class="me-stats">
        <div class="me-stat">
            <span>Total Exam Subjects</span>
            <strong>{{ $stats['total'] }}</strong>
        </div>
        <div class="me-stat">
            <span>Published Sheets</span>
            <strong>{{ $stats['published'] }}</strong>
        </div>
        <div class="me-stat">
            <span>Marks Records</span>
            <strong>{{ $stats['entered'] }}</strong>
        </div>
    </div>

    <div class="me-panel">
        <h3>Filter Examination Subjects</h3>

        <form method="GET" action="{{ route('marks-entry.index') }}" class="me-filters">
            <select name="academic_year_id">
                <option value="">All Academic Years</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>
                        {{ $year->name }}
                    </option>
                @endforeach
            </select>

            <select name="examination_id">
                <option value="">All Examinations</option>
                @foreach($examinations as $exam)
                    <option value="{{ $exam->id }}" @selected(request('examination_id') == $exam->id)>
                        {{ $exam->name }}
                    </option>
                @endforeach
            </select>

            <select name="school_class_id">
                <option value="">All Classes</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" @selected(request('school_class_id') == $class->id)>
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>

            <select name="section_id">
                <option value="">All Sections</option>
                @foreach($sections as $section)
                    <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                        {{ $section->name }}
                    </option>
                @endforeach
            </select>

            <select name="subject_id">
                <option value="">All Subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="me-btn me-primary">Apply Filters</button>
            <a href="{{ route('marks-entry.index') }}" class="me-btn">Reset</a>
        </form>
    </div>

    <div class="me-panel">
        <div class="me-panel-heading">
            <div>
                <h3>Examination Subjects</h3>
                <p>Select a subject to open its marks sheet.</p>
            </div>
            <span class="me-count">{{ $examSubjects->total() }} records</span>
        </div>

        <div class="me-table-wrap">
            <table class="me-table">
                <thead>
                    <tr>
                        <th>Examination</th>
                        <th>Academic Year</th>
                        <th>Class / Section</th>
                        <th>Subject</th>
                        <th>Exam Date</th>
                        <th>Maximum Marks</th>
                        <th>Records Entered</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($examSubjects as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->examination_name }}</strong>
                                <small>{{ ucfirst($item->examination_status) }}</small>
                            </td>
                            <td>{{ $item->academic_year_name }}</td>
                            <td>{{ $item->class_name }} / {{ $item->section_name }}</td>
                            <td>{{ $item->subject_name }}</td>
                            <td>{{ $item->exam_date ? \Carbon\Carbon::parse($item->exam_date)->format('d M Y') : '—' }}</td>
                            <td>{{ number_format($item->maximum_marks, 0) }}</td>
                            <td>{{ $item->entered_count }}</td>
                            <td>
                                <a href="{{ route('marks-entry.show', $item->id) }}" class="me-btn me-primary">
                                    Enter Marks
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="me-empty">
                                No examination subjects found. Create an examination and add its subject schedule first.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="me-pagination">
            {{ $examSubjects->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<style>
.me-page{font-family:'Inter',sans-serif;color:#25313C;padding:24px;max-width:1500px;margin:auto}
.me-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
.me-header h1{font-size:22px;font-weight:700;margin:0;color:#123B5D}
.me-header p,.me-panel-heading p{font-size:13px;color:#64748B;margin:6px 0 0}
.me-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}
.me-stat,.me-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:20px}
.me-stat span{display:block;font-size:12px;font-weight:600;color:#64748B}
.me-stat strong{display:block;font-size:26px;margin-top:8px;color:#123B5D}
.me-panel{margin-bottom:20px}
.me-panel h3,.me-panel-heading h3{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.me-filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:16px}
.me-filters select{height:40px;border:1px solid #CBD5E1;border-radius:9px;padding:0 10px;font:400 13px 'Inter',sans-serif;background:#fff;color:#25313C;min-width:0}
.me-btn{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px 'Inter',sans-serif;text-decoration:none;white-space:nowrap;cursor:pointer}
.me-btn:hover{background:#F8FAFC;color:#123B5D}
.me-primary{background:#123B5D;color:#fff;border-color:#123B5D}
.me-primary:hover{background:#176B87;color:#fff}
.me-panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}
.me-count{font-size:12px;color:#64748B}
.me-table-wrap{overflow-x:auto}
.me-table{width:100%;border-collapse:collapse;white-space:nowrap}
.me-table th{background:#F8FAFC;color:#64748B;font-size:11px;font-weight:650;text-align:left;padding:13px 12px;border-bottom:1px solid #CBD5E1}
.me-table td{font-size:13px;padding:14px 12px;border-bottom:1px solid #E2E8F0;vertical-align:middle}
.me-table td strong{display:block;color:#25313C;font-weight:600}
.me-table td small{display:block;color:#64748B;font-size:11px;margin-top:4px}
.me-empty{text-align:center;color:#64748B;padding:30px!important;white-space:normal!important}
.me-pagination{margin-top:18px}
@media(max-width:850px){.me-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.me-stats{grid-template-columns:1fr}}
@media(max-width:520px){.me-page{padding:14px}.me-filters{grid-template-columns:1fr}}
</style>
@endsection
