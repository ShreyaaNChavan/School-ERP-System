@extends('layouts.schoolnexa')
@section('title', 'Homework Management')
@section('content')
<div class="hw-page">
    <div class="hw-header">
        <div>
            <h1>Homework Management</h1>
            <p>Create assignments, track submissions and review student work.</p>
        </div>
        @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
            <a href="{{ route('homework.create') }}" class="hw-primary">+ Create Homework</a>
        @endif
    </div>

    @if(session('success'))
        <div class="hw-alert-success">{{ session('success') }}</div>
    @endif

    <div class="hw-stats">
        <div class="hw-stat"><span>Total Homework</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="hw-stat"><span>Published</span><strong>{{ $stats['published'] }}</strong></div>
        <div class="hw-stat"><span>Drafts</span><strong>{{ $stats['draft'] }}</strong></div>
        <div class="hw-stat"><span>Overdue</span><strong>{{ $stats['overdue'] }}</strong></div>
        <div class="hw-stat"><span>Submissions</span><strong>{{ $stats['submissions'] }}</strong></div>
    </div>

    <form method="GET" class="hw-filter">
        <div class="hw-filter-grid">
            <div>
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search homework title">
            </div>
            <div>
                <label>Academic Year</label>
                <select name="academic_year_id">
                    <option value="">All Years</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}" @selected(request('academic_year_id') == $year->id)>{{ $year->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Section</label>
                <select name="section_id">
                    <option value="">All Sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected(request('section_id') == $section->id)>
                            {{ $section->schoolClass->name ?? 'Class' }} - {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Subject</label>
                <select name="subject_id">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Status</label>
                <select name="status">
                    <option value="">All Statuses</option>
                    @foreach(['published' => 'Published', 'draft' => 'Draft', 'archived' => 'Archived'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="hw-filter-actions">
            <button class="hw-primary" type="submit">Apply Filters</button>
            <a class="hw-action" href="{{ route('homework.index') }}">Reset</a>
        </div>
    </form>

    <section class="hw-panel">
        <div class="hw-panel-head">
            <div>
                <h2>Homework Directory</h2>
                <p>Manage assignments and monitor student submissions.</p>
            </div>
            <span>{{ $assignments->total() }} records</span>
        </div>

        <div class="hw-table-wrap">
            <table class="hw-table">
                <thead>
                    <tr>
                        <th>Homework</th>
                        <th>Class / Section</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Assigned</th>
                        <th>Due Date</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->title }}</strong>
                                <small>{{ \Illuminate\Support\Str::limit(strip_tags($item->instructions), 55) }}</small>
                            </td>
                            <td>{{ $item->section->schoolClass->name ?? '-' }} - {{ $item->section->name ?? '-' }}</td>
                            <td>{{ $item->subject->name ?? '-' }}</td>
                            <td>{{ trim(($item->teacher->first_name ?? '') . ' ' . ($item->teacher->last_name ?? '')) }}</td>
                            <td>{{ $item->assigned_date?->format('d M Y') }}</td>
                            <td>{{ $item->due_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <strong>{{ $item->submissions_count }}</strong>
                                <small>{{ $item->reviewed_count }} reviewed</small>
                            </td>
                            <td><span class="hw-badge {{ $item->status }}">{{ ucfirst($item->status) }}</span></td>
                            <td>
                                <div class="hw-actions">
                                    <a class="hw-action" href="{{ route('homework.show', $item) }}">View</a>
                                    @if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                        <a class="hw-action" href="{{ route('homework.edit', $item) }}">Edit</a>
                                    @endif
                                    @if(auth()->user()->hasPermission('ACADEMICS.DELETE'))
                                        <form method="POST" action="{{ route('homework.destroy', $item) }}" onsubmit="return confirm('Delete this homework and its submissions?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="hw-action hw-danger" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="hw-empty">No homework assignments found. Create your first assignment to get started.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="hw-pagination">
            <div class="hw-pagination-summary">
                Showing <strong>{{ $assignments->firstItem() ?? 0 }}–{{ $assignments->lastItem() ?? 0 }}</strong>
                of <strong>{{ $assignments->total() }}</strong> entries
            </div>

            @if($assignments->hasPages())
                <nav class="hw-page-links" aria-label="Homework pagination">
                    {{-- Previous page --}}
                    @if($assignments->onFirstPage())
                        <span class="hw-page-link disabled">Previous</span>
                    @else
                        <a class="hw-page-link" href="{{ $assignments->previousPageUrl() }}">Previous</a>
                    @endif

                    {{-- Page numbers and ellipses --}}
                    @foreach($assignments->onEachSide(1)->getUrlRange(1, $assignments->lastPage()) as $page => $url)
                        @if($page == 1 || $page == $assignments->lastPage() || abs($page - $assignments->currentPage()) <= 1)
                            <a
                                class="hw-page-link {{ $assignments->currentPage() == $page ? 'active' : '' }}"
                                href="{{ $url }}"
                                @if($assignments->currentPage() == $page) aria-current="page" @endif
                            >{{ $page }}</a>
                        @elseif($page == 2 && $assignments->currentPage() > 3)
                            <span class="hw-page-ellipsis">…</span>
                        @elseif($page == $assignments->lastPage() - 1 && $assignments->currentPage() < $assignments->lastPage() - 2)
                            <span class="hw-page-ellipsis">…</span>
                        @endif
                    @endforeach

                    {{-- Next page --}}
                    @if($assignments->hasMorePages())
                        <a class="hw-page-link" href="{{ $assignments->nextPageUrl() }}">Next</a>
                    @else
                        <span class="hw-page-link disabled">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </section>
</div>

<style>
.hw-page{font-family:Inter,sans-serif;color:#25313C;padding:22px 24px}
.hw-header{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
.hw-header h1{font-size:22px;font-weight:700;color:#123B5D;margin:0}
.hw-header p{font-size:13px;color:#64748B;margin:5px 0 0}
.hw-primary,.hw-action{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 12px;border:1px solid #CBD5E1;border-radius:9px;background:#fff;color:#123B5D;font:600 13px Inter,sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap;transition:background .2s ease}
.hw-primary{background:#123B5D;border-color:#123B5D;color:#fff}
.hw-primary:hover{background:#0E304C;color:#fff}
.hw-action:hover{background:#F8FAFC;color:#123B5D}
.hw-danger{color:#DC2626}
.hw-danger:hover{background:#FEF2F2;color:#DC2626}
.hw-actions{display:flex;align-items:center;gap:7px;flex-wrap:nowrap}
.hw-actions form{display:inline-flex;margin:0}
.hw-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
.hw-stat{background:#fff;border:1px solid #CBD5E1;border-radius:12px;padding:14px 16px}
.hw-stat span{display:block;font-size:11px;font-weight:600;color:#64748B;text-transform:uppercase}
.hw-stat strong{display:block;font-size:26px;font-weight:700;color:#123B5D;margin-top:5px}
.hw-filter,.hw-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;margin-bottom:17px}
.hw-filter{padding:16px}
.hw-filter-grid{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
.hw-filter label{display:block;font-size:12px;font-weight:600;color:#475569;margin-bottom:6px}
.hw-filter input,.hw-filter select{width:100%;box-sizing:border-box;min-height:39px;border:1px solid #CBD5E1;border-radius:8px;padding:8px 10px;background:#fff;color:#25313C;font:400 13px Inter,sans-serif}
.hw-filter-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:12px}
.hw-panel{overflow:hidden}
.hw-panel-head{display:flex;justify-content:space-between;align-items:center;padding:16px 18px;border-bottom:1px solid #E2E8F0}
.hw-panel-head h2{font-size:16px;font-weight:650;color:#123B5D;margin:0}
.hw-panel-head p{font-size:12px;color:#64748B;margin:4px 0 0}
.hw-panel-head>span{font-size:12px;color:#64748B}
.hw-table-wrap{overflow-x:auto}
.hw-table{width:100%;min-width:1100px;border-collapse:collapse}
.hw-table th{background:#F8FAFC;text-align:left;padding:12px 13px;font-size:11px;font-weight:650;color:#64748B;border-bottom:1px solid #E2E8F0;white-space:nowrap}
.hw-table td{padding:12px 13px;border-bottom:1px solid #E2E8F0;font-size:12px;vertical-align:middle}
.hw-table td strong{color:#123B5D}
.hw-table td small{display:block;color:#64748B;font-size:11px;margin-top:3px}
.hw-badge{display:inline-flex;padding:5px 9px;border-radius:20px;font-size:10px;font-weight:650}
.hw-badge.published{background:#DCFCE7;color:#166534}
.hw-badge.draft{background:#FEF3C7;color:#92400E}
.hw-badge.archived{background:#F1F5F9;color:#475569}
.hw-empty{text-align:center!important;padding:35px!important;color:#64748B!important}
.hw-pagination{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;padding:14px 18px;border-top:1px solid #E2E8F0}
.hw-pagination-summary{font-size:12px;color:#64748B}
.hw-pagination-summary strong{color:#25313C;font-weight:600}
.hw-page-links{display:flex;align-items:center;justify-content:flex-end;gap:5px;flex-wrap:wrap}
.hw-page-link{display:inline-flex;align-items:center;justify-content:center;min-width:34px;height:34px;padding:0 10px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#123B5D;font:600 12px Inter,sans-serif;text-decoration:none;white-space:nowrap;transition:background .2s ease,border-color .2s ease}
.hw-page-link:hover{background:#F8FAFC;border-color:#94A3B8;color:#123B5D}
.hw-page-link.active{background:#123B5D;border-color:#123B5D;color:#fff}
.hw-page-link.disabled{background:#F8FAFC;color:#94A3B8;border-color:#E2E8F0;cursor:not-allowed}
.hw-page-ellipsis{padding:0 4px;color:#64748B;font-size:13px}
.hw-alert-success{padding:12px 15px;border-radius:9px;margin-bottom:15px;background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;font-size:13px}
@media(max-width:1050px){.hw-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.hw-stats{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:650px){.hw-page{padding:14px}.hw-filter-grid,.hw-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.hw-pagination{align-items:flex-start;flex-direction:column}.hw-page-links{justify-content:flex-start}}
</style>
@endsection
