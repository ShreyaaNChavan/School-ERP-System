
@extends('layouts.schoolnexa')

@section('content')
<style>
    .exam-page {
        font-family: 'Inter', sans-serif;
        color: #25313C;
        max-width: 1440px;
        width: 100%;
        margin: 0 auto;
        padding: 26px 28px 36px;
        box-sizing: border-box;
    }

    .exam-heading {
        font-size: 22px;
        font-weight: 700;
        color: #123B5D;
        margin: 0;
    }

    .exam-subtitle {
        color: #64748B;
        font-size: 13px;
        margin-top: 5px;
    }

    .exam-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-bottom: 18px;
    }

    .exam-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin: 20px 0;
    }

    .exam-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 18px;
    }

    .exam-stat-label {
        font-size: 12px;
        font-weight: 600;
        color: #64748B;
    }

    .exam-stat-value {
        font-size: 26px;
        font-weight: 700;
        color: #123B5D;
        margin-top: 8px;
    }

    .exam-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .exam-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }

    .exam-input {
        height: 40px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        padding: 0 12px;
        font: 400 13px 'Inter', sans-serif;
        color: #25313C;
        background: #fff;
        min-width: 150px;
    }

    .exam-input:focus {
        outline: none;
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23, 107, 135, .10);
    }

    .exam-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .exam-actions form {
        display: inline-flex;
        margin: 0;
    }

    .exam-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #fff;
        color: #123B5D;
        font: 600 13px 'Inter', sans-serif;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        transition: .2s;
    }

    .exam-action:hover {
        background: #F8FAFC;
        color: #123B5D;
    }

    .exam-primary {
        background: #123B5D;
        border-color: #123B5D;
        color: #fff;
    }

    .exam-primary:hover {
        background: #176B87;
        border-color: #176B87;
        color: #fff;
    }

    .exam-danger {
        color: #DC2626;
    }

    .exam-danger:hover {
        color: #DC2626;
        background: #FEF2F2;
    }

    .exam-table-wrap {
        overflow-x: auto;
    }

    .exam-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .exam-table th {
        text-align: left;
        padding: 13px 12px;
        background: #F8FAFC;
        color: #64748B;
        font-size: 12px;
        font-weight: 600;
        border-bottom: 1px solid #CBD5E1;
        white-space: nowrap;
    }

    .exam-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #E2E8F0;
        font-size: 13px;
        vertical-align: middle;
    }

    .exam-table tr:last-child td {
        border-bottom: none;
    }

    .exam-name {
        color: #123B5D;
        font-weight: 650;
        text-decoration: none;
    }

    .exam-name:hover {
        color: #176B87;
    }

    .exam-muted {
        color: #64748B;
        font-size: 12px;
        margin-top: 4px;
    }

    .exam-badge {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 650;
        text-transform: capitalize;
    }

    .exam-draft {
        color: #92400E;
        background: #FEF3C7;
    }

    .exam-published {
        color: #166534;
        background: #DCFCE7;
    }

    .exam-completed {
        color: #1D4ED8;
        background: #DBEAFE;
    }

    .exam-archived {
        color: #475569;
        background: #E2E8F0;
    }

    .exam-empty {
        text-align: center;
        padding: 45px 20px !important;
        color: #64748B;
    }

    .exam-section-title {
        font-size: 16px;
        font-weight: 650;
        color: #123B5D;
        margin: 0;
    }

    @media(max-width: 850px) {
        .exam-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .exam-page {
            padding: 20px 16px 30px;
        }
    }

    @media(max-width: 520px) {
        .exam-stats {
            grid-template-columns: 1fr 1fr;
        }

        .exam-toolbar {
            align-items: stretch;
        }

        .exam-filters {
            width: 100%;
        }

        .exam-filters .exam-input {
            width: 100%;
        }

        .exam-page {
            padding: 16px 12px 24px;
        }
    }
</style>

<div class="exam-page">

    {{-- Page Header --}}
    <div class="exam-toolbar">
        <div>
            <h1 class="exam-heading">Examination Management</h1>
            <p class="exam-subtitle">
                Create examinations, manage schedules and publish exam details.
            </p>
        </div>

        @if(auth()->user()->hasPermission('EXAMINATIONS.CREATE'))
            <a href="{{ route('examinations.create') }}"
               class="exam-action exam-primary">
                + Create Examination
            </a>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="exam-stats">

        <div class="exam-stat">
            <div class="exam-stat-label">Total Examinations</div>
            <div class="exam-stat-value">{{ $stats['total'] }}</div>
        </div>

        <div class="exam-stat">
            <div class="exam-stat-label">Draft</div>
            <div class="exam-stat-value">{{ $stats['draft'] }}</div>
        </div>

        <div class="exam-stat">
            <div class="exam-stat-label">Published</div>
            <div class="exam-stat-value">{{ $stats['published'] }}</div>
        </div>

        <div class="exam-stat">
            <div class="exam-stat-label">Completed</div>
            <div class="exam-stat-value">{{ $stats['completed'] }}</div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="exam-panel">

        <form method="GET"
              action="{{ route('examinations.index') }}"
              class="exam-toolbar">

            <div class="exam-filters">

                <input type="text"
                       name="search"
                       class="exam-input"
                       placeholder="Search examination..."
                       value="{{ request('search') }}">

                <select name="school_id" class="exam-input">
                    <option value="">All Schools</option>

                    @foreach($schools as $school)
                        <option value="{{ $school->id }}"
                            @selected(request('school_id') == $school->id)>
                            {{ $school->name }}
                        </option>
                    @endforeach
                </select>

                <select name="academic_year_id" class="exam-input">
                    <option value="">All Academic Years</option>

                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}"
                            @selected(request('academic_year_id') == $year->id)>
                            {{ $year->name }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="exam-input">
                    <option value="">All Statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="published" @selected(request('status') === 'published')>Published</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                    <option value="archived" @selected(request('status') === 'archived')>Archived</option>
                </select>

                <button type="submit" class="exam-action">
                    Apply Filters
                </button>

                <a href="{{ route('examinations.index') }}"
                   class="exam-action">
                    Reset
                </a>

            </div>
        </form>

    </div>

    {{-- Examination Directory --}}
    <div class="exam-panel">

        <div style="margin-bottom:18px;">
            <h2 class="exam-section-title">
                Examination Directory
            </h2>

            <p class="exam-subtitle">
                {{ $examinations->total() }} examination records found
            </p>
        </div>

        <div class="exam-table-wrap">

            <table class="exam-table">

                <thead>
                    <tr>
                        <th>EXAMINATION</th>
                        <th>SCHOOL</th>
                        <th>ACADEMIC YEAR</th>
                        <th>EXAM TYPE</th>
                        <th>EXAM PERIOD</th>
                        <th>SUBJECTS</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($examinations as $examination)

                        <tr>

                            <td>
                                <a href="{{ route('examinations.show', $examination) }}"
                                   class="exam-name">
                                    {{ $examination->name }}
                                </a>

                                <div class="exam-muted">
                                    EXM-{{ str_pad($examination->id, 4, '0', STR_PAD_LEFT) }}
                                </div>
                            </td>

                            <td>
                                {{ $examination->school->name ?? '—' }}
                            </td>

                            <td>
                                {{ $examination->academicYear->name ?? '—' }}
                            </td>

                            <td>
                                {{ $examination->exam_type }}
                            </td>

                            <td>
                                {{ $examination->start_date?->format('d M Y') }}

                                <div class="exam-muted">
                                    to {{ $examination->end_date?->format('d M Y') }}
                                </div>
                            </td>

                            <td>
                                {{ $examination->exam_subjects_count }}
                            </td>

                            <td>
                                <span class="exam-badge exam-{{ $examination->status }}">
                                    {{ ucfirst($examination->status) }}
                                </span>
                            </td>

                            <td>
                                <div class="exam-actions">

                                    <a href="{{ route('examinations.show', $examination) }}"
                                       class="exam-action">
                                        View
                                    </a>

                                    @if(auth()->user()->hasPermission('EXAMINATIONS.UPDATE')
                                        && $examination->status !== 'completed')

                                        <a href="{{ route('examinations.edit', $examination) }}"
                                           class="exam-action">
                                            Edit
                                        </a>

                                    @endif

                                    @if(auth()->user()->hasPermission('EXAMINATIONS.PUBLISH')
                                        && $examination->status === 'draft')

                                        <form method="POST"
                                              action="{{ route('examinations.publish', $examination) }}">
                                            @csrf

                                            <button type="submit"
                                                    class="exam-action">
                                                Publish
                                            </button>
                                        </form>

                                    @endif

                                    @if(auth()->user()->hasPermission('EXAMINATIONS.DELETE'))

                                        <form method="POST"
                                              action="{{ route('examinations.destroy', $examination) }}"
                                              onsubmit="return confirm('Delete this examination?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="exam-action exam-danger">
                                                Delete
                                            </button>

                                        </form>

                                    @endif

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="exam-empty">

                                <div style="font-weight:650;color:#123B5D;">
                                    No examinations found
                                </div>

                                <div class="exam-muted">
                                    Create an examination to start managing exam schedules.
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- Pagination --}}
        <div style="margin-top:18px;">
            {{ $examinations->links() }}
        </div>

    </div>

</div>
@endsection
