
@extends('layouts.schoolnexa')

@section('content')

<style>
    .exam-show-page {
        max-width: 1400px;
        margin: 0 auto;
        padding: 26px 28px 36px;
        font-family: 'Inter', sans-serif;
        color: #25313C;
    }

    .exam-show-heading {
        font-size: 22px;
        font-weight: 700;
        color: #123B5D;
        margin: 0;
    }

    .exam-show-subtitle {
        color: #64748B;
        font-size: 13px;
        margin-top: 5px;
    }

    .exam-show-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 20px;
        margin-top: 18px;
    }

    .exam-show-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
    }

    .exam-show-label {
        font-size: 12px;
        color: #64748B;
        font-weight: 600;
    }

    .exam-show-value {
        color: #123B5D;
        font-size: 14px;
        font-weight: 600;
        margin-top: 7px;
    }

    .exam-show-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .exam-show-actions form {
        display: inline-flex;
        margin: 0;
    }

    .exam-show-action {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 40px;
        padding: 0 12px;
        border: 1px solid #CBD5E1;
        border-radius: 9px;
        background: #fff;
        color: #123B5D;
        text-decoration: none;
        font: 600 13px 'Inter', sans-serif;
        cursor: pointer;
        white-space: nowrap;
    }

    .exam-show-action:hover {
        background: #F8FAFC;
        color: #123B5D;
    }

    .exam-show-primary {
        background: #123B5D;
        color: #fff;
        border-color: #123B5D;
    }

    .exam-show-primary:hover {
        background: #176B87;
        color: #fff;
    }

    .exam-show-danger {
        color: #DC2626;
    }

    .exam-show-table-wrap {
        overflow-x: auto;
    }

    .exam-show-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .exam-show-table th {
        padding: 13px 12px;
        background: #F8FAFC;
        color: #64748B;
        text-align: left;
        font-size: 12px;
        font-weight: 600;
        border-bottom: 1px solid #CBD5E1;
        white-space: nowrap;
    }

    .exam-show-table td {
        padding: 14px 12px;
        font-size: 13px;
        border-bottom: 1px solid #E2E8F0;
    }

    .exam-show-badge {
        display: inline-flex;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 650;
    }

    .exam-show-draft {
        background: #FEF3C7;
        color: #92400E;
    }

    .exam-show-published {
        background: #DCFCE7;
        color: #166534;
    }

    .exam-show-completed {
        background: #DBEAFE;
        color: #1D4ED8;
    }

    .exam-show-archived {
        background: #E2E8F0;
        color: #475569;
    }

    .exam-show-empty {
        padding: 35px !important;
        text-align: center;
        color: #64748B;
    }

    @media(max-width: 850px) {
        .exam-show-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 600px) {
        .exam-show-page {
            padding: 18px 12px 28px;
        }

        .exam-show-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="exam-show-page">

    {{-- Header --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:15px;flex-wrap:wrap;">

        <div>
            <h1 class="exam-show-heading">
                {{ $examination->name }}
            </h1>

            <p class="exam-show-subtitle">
                Examination ID: EXM-{{ str_pad($examination->id, 4, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <div class="exam-show-actions">

            <a href="{{ route('examinations.index') }}"
               class="exam-show-action">
                Back to Examinations
            </a>

            @if(auth()->user()->hasPermission('EXAMINATIONS.UPDATE')
                && $examination->status !== 'completed')

                <a href="{{ route('examinations.edit', $examination) }}"
                   class="exam-show-action">
                    Edit Examination
                </a>

            @endif

            @if(auth()->user()->hasPermission('EXAMINATIONS.PUBLISH')
                && $examination->status === 'draft')

                <form method="POST"
                      action="{{ route('examinations.publish', $examination) }}">
                    @csrf

                    <button type="submit"
                            class="exam-show-action exam-show-primary">
                        Publish Examination
                    </button>
                </form>

            @endif

            @if(auth()->user()->hasPermission('EXAMINATIONS.DELETE'))

                <form method="POST"
                      action="{{ route('examinations.destroy', $examination) }}"
                      onsubmit="return confirm('Are you sure you want to delete this examination?')">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="exam-show-action exam-show-danger">
                        Delete
                    </button>
                </form>

            @endif

        </div>

    </div>

    {{-- Examination Overview --}}
    <div class="exam-show-panel">

        <h2 style="font-size:16px;font-weight:650;color:#123B5D;margin:0 0 20px;">
            Examination Overview
        </h2>

        <div class="exam-show-grid">

            <div>
                <div class="exam-show-label">School</div>
                <div class="exam-show-value">
                    {{ $examination->school->name ?? '—' }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">Academic Year</div>
                <div class="exam-show-value">
                    {{ $examination->academicYear->name ?? '—' }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">Examination Type</div>
                <div class="exam-show-value">
                    {{ $examination->exam_type }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">Status</div>
                <div class="exam-show-value">
                    <span class="exam-show-badge exam-show-{{ $examination->status }}">
                        {{ ucfirst($examination->status) }}
                    </span>
                </div>
            </div>

            <div>
                <div class="exam-show-label">Start Date</div>
                <div class="exam-show-value">
                    {{ $examination->start_date?->format('d M Y') }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">End Date</div>
                <div class="exam-show-value">
                    {{ $examination->end_date?->format('d M Y') }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">Total Subjects</div>
                <div class="exam-show-value">
                    {{ $examination->examSubjects->count() }}
                </div>
            </div>

            <div>
                <div class="exam-show-label">Created On</div>
                <div class="exam-show-value">
                    {{ $examination->created_at?->format('d M Y') }}
                </div>
            </div>

        </div>

        @if($examination->instructions)
            <div style="margin-top:22px;padding-top:18px;border-top:1px solid #E2E8F0;">
                <div class="exam-show-label">Examination Instructions</div>

                <div style="font-size:13px;line-height:1.7;margin-top:8px;white-space:pre-line;">
                    {{ $examination->instructions }}
                </div>
            </div>
        @endif

    </div>

    {{-- Subject Schedule --}}
    <div class="exam-show-panel">

        <div style="margin-bottom:18px;">
            <h2 style="font-size:16px;font-weight:650;color:#123B5D;margin:0;">
                Examination Timetable
            </h2>

            <p class="exam-show-subtitle">
                Subject-wise schedule, timings and marks distribution.
            </p>
        </div>

        <div class="exam-show-table-wrap">

            <table class="exam-show-table">

                <thead>
                    <tr>
                        <th>SUBJECT</th>
                        <th>CLASS</th>
                        <th>SECTION</th>
                        <th>EXAM DATE</th>
                        <th>START TIME</th>
                        <th>END TIME</th>
                        <th>MAX MARKS</th>
                        <th>PASSING MARKS</th>
                        <th>ROOM</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($examination->examSubjects->sortBy([
                        ['exam_date', 'asc'],
                        ['start_time', 'asc']
                    ]) as $examSubject)

                        <tr>

                            <td style="font-weight:600;color:#123B5D;">
                                {{ $examSubject->subject->name ?? '—' }}
                            </td>

                            <td>
                                {{ $examSubject->schoolClass->name ?? '—' }}
                            </td>

                            <td>
                                {{ $examSubject->section->name ?? '—' }}
                            </td>

                            <td>
                                {{ $examSubject->exam_date?->format('d M Y') }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($examSubject->start_time)->format('h:i A') }}
                            </td>

                            <td>
                                {{ \Illuminate\Support\Carbon::parse($examSubject->end_time)->format('h:i A') }}
                            </td>

                            <td>{{ $examSubject->maximum_marks }}</td>

                            <td>{{ $examSubject->passing_marks }}</td>

                            <td>{{ $examSubject->room_number ?: '—' }}</td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="9" class="exam-show-empty">
                                No subject schedules have been added yet.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
