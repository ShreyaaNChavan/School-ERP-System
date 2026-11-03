
@extends('layouts.schoolnexa')

@section('content')
<div class="rc-page">
    <div class="rc-heading">
        <div>
            <div class="rc-breadcrumb">Dashboard / Academic / Report Cards</div>
            <h1>Report Cards</h1>
            <p>Generate, review and publish student examination results.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rc-alert success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rc-alert danger">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="rc-alert danger">
            <strong>Please check the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="rc-stats">
        <div class="rc-stat">
            <span>Total Report Cards</span>
            <strong>{{ $stats['total'] }}</strong>
            <small>Generated records</small>
        </div>
        <div class="rc-stat">
            <span>Published</span>
            <strong class="green">{{ $stats['published'] }}</strong>
            <small>Released report cards</small>
        </div>
        <div class="rc-stat">
            <span>Awaiting Publish</span>
            <strong class="orange">{{ $stats['pending'] }}</strong>
            <small>Pending report cards</small>
        </div>
        <div class="rc-stat">
            <span>Average Percentage</span>
            <strong>{{ number_format($stats['average'], 2) }}%</strong>
            <small>Across generated cards</small>
        </div>
    </div>

    <div class="rc-panel">
        <div class="rc-panel-heading">
            <div>
                <h2>Generate Report Cards</h2>
                <p>Select an examination and class to generate student report cards.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('report-cards.generate') }}">
            @csrf

            <div class="rc-form-grid">
                <div class="rc-field">
                    <label>Examination <span>*</span></label>
                    <select name="examination_id" required>
                        <option value="">Select examination</option>
                        @foreach($examinations as $exam)
                            <option value="{{ $exam->id }}"
                                @selected(old('examination_id', request('examination_id')) == $exam->id)>
                                {{ $exam->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rc-field">
                    <label>Class <span>*</span></label>
                    <select name="school_class_id" id="generateClass" required>
                        <option value="">Select class</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}"
                                @selected(old('school_class_id', request('school_class_id')) == $class->id)>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rc-field">
                    <label>Section <span>*</span></label>
                    <select name="section_id" id="generateSection" required>
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}"
                                data-class="{{ $section->school_class_id }}"
                                @selected(old('section_id', request('section_id')) == $section->id)>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="rc-field rc-generate-action">
                    <label>&nbsp;</label>
                    <button type="submit" class="rc-btn primary">
                        Generate Report Cards
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="rc-panel">
        <div class="rc-panel-heading">
            <div>
                <h2>Generated Report Cards</h2>
                <p>View individual report cards and manage publication.</p>
            </div>
            <span class="rc-count">{{ $reportCards->total() }} records</span>
        </div>

        <form method="GET" action="{{ route('report-cards.index') }}" class="rc-filter">
            <div class="rc-field">
                <label>Examination</label>
                <select name="examination_id">
                    <option value="">All examinations</option>
                    @foreach($examinations as $exam)
                        <option value="{{ $exam->id }}"
                            @selected(request('examination_id') == $exam->id)>
                            {{ $exam->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rc-field">
                <label>Class</label>
                <select name="school_class_id">
                    <option value="">All classes</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}"
                            @selected(request('school_class_id') == $class->id)>
                            {{ $class->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rc-field">
                <label>Section</label>
                <select name="section_id">
                    <option value="">All sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}"
                            @selected(request('section_id') == $section->id)>
                            {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rc-field search-field">
                <label>Search student</label>
                <input type="text" name="search"
                    value="{{ request('search') }}"
                    placeholder="Name or admission number">
            </div>

            <div class="rc-filter-actions">
                <button type="submit" class="rc-btn primary">Apply Filters</button>
                <a href="{{ route('report-cards.index') }}" class="rc-btn">Reset</a>
            </div>
        </form>

        <div class="rc-table-wrap">
            <table class="rc-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Admission No.</th>
                        <th>Class / Section</th>
                        <th>Examination</th>
                        <th>Total Marks</th>
                        <th>Percentage</th>
                        <th>Grade</th>
                        <th>Rank</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reportCards as $card)
                        <tr>
                            <td>
                                <div class="rc-student-name">
                                    {{ trim($card->first_name.' '.($card->middle_name ?? '').' '.$card->last_name) }}
                                </div>
                            </td>
                            <td>{{ $card->admission_number }}</td>
                            <td>
                                {{ $card->class_name ?? '—' }}
                                @if($card->section_name)
                                    / {{ $card->section_name }}
                                @endif
                            </td>
                            <td>{{ $card->examination_name }}</td>
                            <td>{{ number_format((float)$card->total_marks, 2) }}</td>
                            <td>{{ number_format((float)$card->percentage, 2) }}%</td>
                            <td><span class="rc-grade">{{ $card->overall_grade }}</span></td>
                            <td>{{ $card->rank ? str_pad($card->rank, 2, '0', STR_PAD_LEFT) : '—' }}</td>
                            <td>
                                @if($card->published_at)
                                    <span class="rc-status published">Published</span>
                                @else
                                    <span class="rc-status pending">Awaiting publish</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('report-cards.show', $card->id) }}"
                                   class="rc-btn small">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="rc-empty">
                                <strong>No report cards found</strong>
                                <span>Generate report cards or change your filters.</span>
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
.rc-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:22px}
.rc-breadcrumb{font-size:12px;color:#64748B;margin-bottom:8px}
.rc-heading h1{font-size:22px;font-weight:700;margin:0;color:#123B5D}
.rc-heading p{font-size:13px;color:#64748B;margin:6px 0 0}
.rc-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:20px}
.rc-stat,.rc-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px}
.rc-stat{padding:17px 19px}
.rc-stat span{display:block;font-size:12px;font-weight:600;color:#64748B}
.rc-stat strong{display:block;font-size:26px;font-weight:700;color:#123B5D;margin:9px 0 4px}
.rc-stat strong.green{color:#16A34A}
.rc-stat strong.orange{color:#D97706}
.rc-stat small{font-size:11px;color:#64748B}
.rc-panel{padding:19px;margin-bottom:20px}
.rc-panel-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:17px}
.rc-panel-heading h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.rc-panel-heading p{font-size:12px;color:#64748B;margin:5px 0 0}
.rc-count{font-size:12px;color:#64748B}
.rc-form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;align-items:end}
.rc-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.rc-field label{font-size:12px;font-weight:600;color:#475569}
.rc-field label span{color:#DC2626}
.rc-field input,.rc-field select{height:40px;border:1px solid #CBD5E1;border-radius:8px;padding:0 11px;background:#fff;color:#25313C;font:400 13px Inter,sans-serif;width:100%;min-width:0}
.rc-field input:focus,.rc-field select:focus{outline:none;border-color:#176B87;box-shadow:0 0 0 3px rgba(23,107,135,.1)}
.rc-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;text-decoration:none;font:600 13px Inter,sans-serif;white-space:nowrap;cursor:pointer}
.rc-btn:hover{background:#F8FAFC;color:#123B5D}
.rc-btn.primary{background:#123B5D;border-color:#123B5D;color:#fff}
.rc-btn.primary:hover{background:#0E304B}
.rc-btn.small{height:32px;font-size:12px;padding:0 10px}
.rc-filter{display:flex;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:18px}
.rc-filter .rc-field{flex:1;min-width:140px}
.rc-filter .search-field{flex:1.5}
.rc-filter-actions{display:flex;gap:8px}
.rc-table-wrap{overflow-x:auto}
.rc-table{width:100%;border-collapse:collapse;white-space:nowrap}
.rc-table th{background:#F8FAFC;color:#64748B;font-size:11px;font-weight:650;text-align:left;padding:12px 10px;border-bottom:1px solid #CBD5E1}
.rc-table td{font-size:12px;padding:13px 10px;border-bottom:1px solid #E2E8F0;color:#475569}
.rc-student-name{font-weight:600;color:#25313C}
.rc-grade{display:inline-flex;padding:4px 8px;border-radius:6px;background:#EEF2FF;color:#5B3FA3;font-size:11px;font-weight:650}
.rc-status{display:inline-flex;padding:5px 8px;border-radius:20px;font-size:11px;font-weight:650}
.rc-status.published{background:#DCFCE7;color:#166534}
.rc-status.pending{background:#FEF3C7;color:#92400E}
.rc-empty{text-align:center!important;padding:35px!important}
.rc-empty strong,.rc-empty span{display:block}
.rc-empty strong{font-size:13px;color:#25313C}
.rc-empty span{font-size:12px;color:#64748B;margin-top:5px}
.rc-pagination{margin-top:17px}
.rc-alert{padding:12px 15px;border-radius:9px;margin-bottom:16px;font-size:13px}
.rc-alert ul{margin:7px 0 0;padding-left:20px}
.rc-alert.success{background:#DCFCE7;color:#166534}
.rc-alert.danger{background:#FEE2E2;color:#991B1B}
@media(max-width:1000px){.rc-stats{grid-template-columns:repeat(2,1fr)}.rc-form-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:650px){.rc-stats,.rc-form-grid{grid-template-columns:1fr}.rc-panel{padding:14px}.rc-filter .rc-field{flex-basis:100%}.rc-filter-actions{width:100%}}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const classSelect = document.getElementById('generateClass');
    const sectionSelect = document.getElementById('generateSection');

    if (!classSelect || !sectionSelect) return;

    function filterSections() {
        const classId = classSelect.value;

        Array.from(sectionSelect.options).forEach(function (option) {
            if (!option.value) return;
            option.hidden = option.dataset.class !== classId;
        });

        const selected = sectionSelect.options[sectionSelect.selectedIndex];

        if (selected && selected.value && selected.dataset.class !== classId) {
            sectionSelect.value = '';
        }
    }

    classSelect.addEventListener('change', filterSections);
    filterSections();
});
</script>
@endsection
