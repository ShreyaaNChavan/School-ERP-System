
@extends('layouts.schoolnexa')

@section('content')
<div class="cu-page">

    <div class="cu-header">
        <div>
            <h2>Curriculum / Syllabus</h2>
            <p>Manage class-wise academic curricula and syllabus progress.</p>
        </div>

        @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
            <a href="{{ route('curricula.create') }}" class="cu-primary">+ Create Curriculum</a>
        @endif
    </div>

    @if(session('success'))
        <div class="cu-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="cu-alert error">{{ session('error') }}</div>
    @endif

    <div class="cu-stats">
        <div class="cu-stat"><span>Total Curricula</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="cu-stat"><span>Published</span><strong>{{ $stats['published'] }}</strong></div>
        <div class="cu-stat"><span>Draft</span><strong>{{ $stats['draft'] }}</strong></div>
        <div class="cu-stat"><span>Archived</span><strong>{{ $stats['archived'] }}</strong></div>
    </div>

    <div class="cu-panel">

        <form method="GET" action="{{ route('curricula.index') }}" class="cu-filters">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search curriculum, subject or class...">

            <select name="school_id">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>

            <select name="academic_year_id">
                <option value="">All Academic Years</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>
                        {{ $year->name }}
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

            <select name="status">
                <option value="">All Status</option>
                @foreach(['draft','published','archived'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <button class="cu-filter-btn" type="submit">Apply</button>
            <a href="{{ route('curricula.index') }}" class="cu-reset">Reset</a>
        </form>

        <div class="cu-table-wrap">
            <table class="cu-table">
                <thead>
                    <tr>
                        <th>Curriculum</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Academic Year</th>
                        <th>Chapters</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($curricula as $curriculum)
                        @php
                            $topicCount = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->count());
                            $completedCount = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->where('status','completed')->count());
                            $progress = $topicCount > 0 ? round(($completedCount / $topicCount) * 100) : 0;
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $curriculum->title }}</strong>
                                <small>{{ $curriculum->school->name ?? '—' }}</small>
                            </td>
                            <td>{{ $curriculum->schoolClass->name ?? '—' }}</td>
                            <td>{{ $curriculum->subject->name ?? '—' }}</td>
                            <td>{{ $curriculum->academicYear->name ?? '—' }}</td>
                            <td>{{ $curriculum->chapters->count() }}</td>
                            <td>
                                <div class="cu-progress"><span style="width:{{ $progress }}%"></span></div>
                                <small>{{ $progress }}%</small>
                            </td>
                            <td>
                                <span class="cu-status {{ $curriculum->status }}">
                                    {{ ucfirst($curriculum->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="cu-actions">
                                    <a href="{{ route('curricula.show',$curriculum) }}">View</a>
                                    @if($curriculum->status === 'draft' && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                        <a href="{{ route('curricula.edit',$curriculum) }}">Edit</a>
                                    @endif
                                    <a href="{{ route('curricula.pdf',$curriculum) }}">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="cu-empty">No curricula found. Create your first curriculum to get started.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="cu-pagination">{{ $curricula->links() }}</div>
    </div>
</div>

<style>
.cu-page{max-width:1450px;margin:auto}
.cu-header{display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;margin-bottom:23px}
.cu-header h2{font-size:22px;color:#123B5D;font-weight:700;margin:0}
.cu-header p{font-size:13px;color:#64748B;margin:6px 0 0}
.cu-primary,.cu-filter-btn,.cu-reset{display:inline-flex;align-items:center;justify-content:center;height:39px;padding:0 16px;border-radius:9px;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer}
.cu-primary,.cu-filter-btn{background:#123B5D;color:#fff;border:1px solid #123B5D}
.cu-reset{background:#fff;color:#123B5D;border:1px solid #CBD5E1}
.cu-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:15px;margin-bottom:21px}
.cu-stat{background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:19px}
.cu-stat span{display:block;font-size:12px;color:#64748B}
.cu-stat strong{display:block;font-size:25px;color:#123B5D;margin-top:8px}
.cu-panel{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:20px}
.cu-filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px}
.cu-filters input,.cu-filters select{height:39px;border:1px solid #CBD5E1;border-radius:9px;padding:0 11px;font-size:12px;color:#25313C;background:#fff}
.cu-filters input{flex:1;min-width:200px}
.cu-filters select{min-width:140px}
.cu-table-wrap{overflow-x:auto}
.cu-table{width:100%;border-collapse:collapse;min-width:1050px}
.cu-table th{background:#F8FAFC;color:#64748B;text-align:left;font-size:10px;letter-spacing:.4px;text-transform:uppercase;padding:13px 11px;border-bottom:1px solid #CBD5E1}
.cu-table td{padding:14px 11px;border-bottom:1px solid #E2E8F0;font-size:12px;color:#25313C}
.cu-table td strong{display:block;color:#123B5D;font-size:12px}
.cu-table td small{display:block;color:#94A3B8;font-size:11px;margin-top:4px}
.cu-progress{height:6px;width:90px;background:#E2E8F0;border-radius:10px;overflow:hidden;margin-bottom:5px}
.cu-progress span{display:block;height:100%;background:#176B87;border-radius:10px}
.cu-status{padding:5px 9px;border-radius:20px;font-size:10px;font-weight:600}
.cu-status.draft{background:#FEF3C7;color:#92400E}
.cu-status.published{background:#DCFCE7;color:#166534}
.cu-status.archived{background:#E2E8F0;color:#475569}
.cu-actions{display:flex;gap:10px;white-space:nowrap}
.cu-actions a{font-size:12px;font-weight:600;color:#176B87;text-decoration:none}
.cu-empty{text-align:center;padding:35px!important;color:#64748B!important}
.cu-pagination{margin-top:18px}
.cu-alert{padding:12px 15px;border-radius:9px;margin-bottom:17px;font-size:13px}
.cu-alert.success{background:#DCFCE7;color:#166534}
.cu-alert.error{background:#FEE2E2;color:#991B1B}
@media(max-width:850px){.cu-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:480px){.cu-stats{grid-template-columns:1fr}}
</style>
@endsection
