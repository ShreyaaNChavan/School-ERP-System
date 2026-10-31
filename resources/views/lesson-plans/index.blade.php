
@extends('layouts.schoolnexa')

@section('title', 'Lesson Planning')

@section('content')
<style>
    .lp-page {
        color: #25313C;
        font-family: 'Inter', sans-serif;
    }

    .lp-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 22px;
    }

    .lp-title {
        font-size: 23px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 5px;
    }

    .lp-subtitle {
        color: #64748B;
        font-size: 13px;
        margin: 0;
    }

    .lp-primary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #123B5D;
        color: white;
        border: 1px solid #123B5D;
        border-radius: 9px;
        padding: 10px 16px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .lp-primary-btn:hover {
        background: #176B87;
        color: white;
    }

    .lp-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .lp-stat {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 17px;
    }

    .lp-stat-label {
        color: #64748B;
        font-size: 12px;
        font-weight: 500;
    }

    .lp-stat-value {
        font-size: 25px;
        font-weight: 700;
        color: #123B5D;
        margin-top: 7px;
    }

    .lp-workspace {
        display: grid;
        grid-template-columns: minmax(190px, 0.75fr) minmax(270px, 1.15fr) minmax(300px, 1.3fr);
        gap: 15px;
        align-items: start;
    }

    .lp-panel {
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 14px;
        padding: 17px;
        min-width: 0;
    }

    .lp-panel-title {
        font-size: 15px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 15px;
    }

    .lp-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 6px;
    }

    .lp-input {
        width: 100%;
        height: 39px;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        padding: 0 10px;
        font-size: 12px;
        color: #25313C;
        background: #fff;
        outline: none;
        margin-bottom: 14px;
    }

    .lp-input:focus {
        border-color: #176B87;
        box-shadow: 0 0 0 3px rgba(23,107,135,.10);
    }

    .lp-filter-btn {
        width: 100%;
        height: 38px;
        border: 0;
        border-radius: 8px;
        background: #123B5D;
        color: white;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .lp-reset {
        display: block;
        text-align: center;
        margin-top: 12px;
        color: #176B87;
        font-size: 12px;
        text-decoration: none;
    }

    .lp-plan-list {
        display: flex;
        flex-direction: column;
        gap: 11px;
    }

    .lp-plan-card {
        display: block;
        border: 1px solid #CBD5E1;
        border-radius: 11px;
        padding: 13px;
        text-decoration: none;
        color: inherit;
        transition: .15s ease;
    }

    .lp-plan-card:hover {
        border-color: #176B87;
        background: #F8FAFC;
    }

    .lp-plan-card.active {
        border: 1.5px solid #176B87;
        background: #F0F9FC;
    }

    .lp-plan-name {
        color: #123B5D;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .lp-plan-meta {
        color: #64748B;
        font-size: 11px;
        line-height: 1.8;
    }

    .lp-status {
        display: inline-block;
        border-radius: 20px;
        padding: 4px 9px;
        font-size: 10px;
        font-weight: 700;
        text-transform: capitalize;
    }

    .lp-status.draft { background: #F1F5F9; color: #475569; }
    .lp-status.published { background: #DBEAFE; color: #1D4ED8; }
    .lp-status.in_progress { background: #F3E8FF; color: #7E22CE; }
    .lp-status.completed { background: #DCFCE7; color: #15803D; }
    .lp-status.cancelled { background: #FEE2E2; color: #B91C1C; }

    .lp-detail-heading {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: flex-start;
        margin-bottom: 17px;
    }

    .lp-detail-title {
        font-size: 17px;
        font-weight: 700;
        color: #123B5D;
        margin: 0 0 7px;
    }

    .lp-section {
        border-top: 1px solid #E2E8F0;
        padding-top: 14px;
        margin-top: 14px;
    }

    .lp-section-title {
        color: #123B5D;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .lp-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
    }

    .lp-info-label {
        color: #64748B;
        font-size: 10px;
        margin-bottom: 4px;
    }

    .lp-info-value {
        color: #25313C;
        font-size: 12px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .lp-item {
        padding: 9px 0;
        border-bottom: 1px solid #F1F5F9;
        font-size: 12px;
    }

    .lp-item:last-child {
        border-bottom: 0;
    }

    .lp-item-title {
        font-weight: 600;
        color: #25313C;
    }

    .lp-item-description {
        color: #64748B;
        font-size: 11px;
        margin-top: 4px;
    }

    .lp-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 17px;
    }

    .lp-outline-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #CBD5E1;
        border-radius: 8px;
        color: #123B5D;
        text-decoration: none;
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .lp-outline-btn:hover {
        background: #F8FAFC;
        color: #176B87;
    }

    .lp-empty {
        text-align: center;
        color: #64748B;
        padding: 28px 12px;
        font-size: 12px;
    }

    .lp-pagination {
        margin-top: 14px;
    }

    @media(max-width: 1100px) {
        .lp-workspace {
            grid-template-columns: 220px minmax(0, 1fr);
        }

        .lp-detail-panel {
            grid-column: 1 / -1;
        }
    }

    @media(max-width: 700px) {
        .lp-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .lp-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .lp-workspace {
            grid-template-columns: 1fr;
        }

        .lp-detail-panel {
            grid-column: auto;
        }
    }
</style>

<div class="lp-page">

    <div class="lp-header">
        <div>
            <h1 class="lp-title">Lesson Planning</h1>
            <p class="lp-subtitle">
                Plan, organize and monitor classroom teaching activities.
            </p>
        </div>

        @if(auth()->user()->hasPermission('ACADEMICS.CREATE'))
            <a href="{{ route('lesson-plans.create') }}" class="lp-primary-btn">
                <span>＋</span> Create Lesson Plan
            </a>
        @endif
    </div>

    {{-- Statistics --}}
    <div class="lp-stats">
        <div class="lp-stat">
            <div class="lp-stat-label">Total Lesson Plans</div>
            <div class="lp-stat-value">{{ $statistics['total'] }}</div>
        </div>

        <div class="lp-stat">
            <div class="lp-stat-label">Draft</div>
            <div class="lp-stat-value">{{ $statistics['draft'] }}</div>
        </div>

        <div class="lp-stat">
            <div class="lp-stat-label">Published</div>
            <div class="lp-stat-value">{{ $statistics['published'] }}</div>
        </div>

        <div class="lp-stat">
            <div class="lp-stat-label">Completed</div>
            <div class="lp-stat-value">{{ $statistics['completed'] }}</div>
        </div>
    </div>

    <div class="lp-workspace">

        {{-- LEFT: FILTERS --}}
        <aside class="lp-panel">
            <h3 class="lp-panel-title">Filters</h3>

            <form method="GET" action="{{ route('lesson-plans.index') }}">
                <label class="lp-label">Search lesson plan</label>
                <input
                    type="text"
                    name="search"
                    class="lp-input"
                    placeholder="Search by title..."
                    value="{{ request('search') }}"
                >

                <label class="lp-label">Status</label>
                <select name="status" class="lp-input">
                    <option value="">All statuses</option>
                    @foreach(['draft', 'published', 'in_progress', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucwords(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>

                <label class="lp-label">Scheduled date</label>
                <input
                    type="date"
                    name="scheduled_date"
                    class="lp-input"
                    value="{{ request('scheduled_date') }}"
                >

                <button type="submit" class="lp-filter-btn">Apply Filters</button>

                <a href="{{ route('lesson-plans.index') }}" class="lp-reset">
                    Clear all filters
                </a>
            </form>
        </aside>

        {{-- CENTER: LESSON PLAN LIST --}}
        <section class="lp-panel">
            <h3 class="lp-panel-title">
                Lesson Plans
                <span style="color:#64748B;font-size:11px;font-weight:500;">
                    ({{ $lessonPlans->total() }})
                </span>
            </h3>

            @forelse($lessonPlans as $plan)
                <a
                    href="{{ route('lesson-plans.index', array_merge(request()->query(), ['selected' => $plan->id])) }}"
                    class="lp-plan-card {{ $selectedLesson && $selectedLesson->id === $plan->id ? 'active' : '' }}"
                >
                    <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">
                        <div class="lp-plan-name">{{ $plan->title }}</div>
                        <span class="lp-status {{ $plan->status }}">
                            {{ str_replace('_', ' ', $plan->status) }}
                        </span>
                    </div>

                    <div class="lp-plan-meta">
                        {{ $plan->classSubject?->schoolClass?->name ?? 'Class not available' }}
                        · {{ $plan->section?->name ?? 'Section not available' }}
                        <br>

                        {{ $plan->classSubject?->subject?->name ?? 'Subject not available' }}
                        <br>

                        {{ $plan->teacher?->first_name ?? '' }}
                        {{ $plan->teacher?->last_name ?? '' }}
                        <br>

                        {{ $plan->scheduled_date?->format('d M Y') ?? 'Date not set' }}
                        · {{ $plan->duration_minutes }} minutes
                    </div>
                </a>
            @empty
                <div class="lp-empty">
                    <div style="font-size:25px;margin-bottom:8px;">—</div>
                    <strong>No lesson plans found</strong>
                    <p style="margin:6px 0 0;">
                        Create a lesson plan or change your filters.
                    </p>
                </div>
            @endforelse

            @if($lessonPlans->hasPages())
                <div class="lp-pagination">
                    {{ $lessonPlans->links() }}
                </div>
            @endif
        </section>

        {{-- RIGHT: SELECTED LESSON DETAILS --}}
        <section class="lp-panel lp-detail-panel">
            @if($selectedLesson)
                <div class="lp-detail-heading">
                    <div>
                        <h3 class="lp-detail-title">{{ $selectedLesson->title }}</h3>
                        <span class="lp-status {{ $selectedLesson->status }}">
                            {{ str_replace('_', ' ', $selectedLesson->status) }}
                        </span>
                    </div>
                </div>

                @if($selectedLesson->description)
                    <p style="font-size:12px;color:#64748B;line-height:1.6;">
                        {{ $selectedLesson->description }}
                    </p>
                @endif

                <div class="lp-section">
                    <div class="lp-section-title">Lesson Information</div>

                    <div class="lp-info-grid">
                        <div>
                            <div class="lp-info-label">Class</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->classSubject?->schoolClass?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Section</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->section?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Subject</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->classSubject?->subject?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Academic Year</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->academicYear?->name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Teacher</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->teacher?->first_name }}
                                {{ $selectedLesson->teacher?->last_name }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Scheduled Date</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->scheduled_date?->format('d M Y') ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Duration</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->duration_minutes }} minutes
                            </div>
                        </div>

                        <div>
                            <div class="lp-info-label">Teaching Method</div>
                            <div class="lp-info-value">
                                {{ $selectedLesson->teaching_method ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lp-section">
                    <div class="lp-section-title">
                        Learning Objectives ({{ $selectedLesson->objectives->count() }})
                    </div>

                    @forelse($selectedLesson->objectives as $objective)
                        <div class="lp-item">
                            <div class="lp-item-title">
                                {{ $objective->is_completed ? '✓ ' : '' }}
                                {{ $objective->title }}
                            </div>

                            @if($objective->description)
                                <div class="lp-item-description">
                                    {{ $objective->description }}
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="lp-plan-meta">No objectives added yet.</div>
                    @endforelse
                </div>

                <div class="lp-section">
                    <div class="lp-section-title">
                        Teaching Activities ({{ $selectedLesson->activities->count() }})
                    </div>

                    @forelse($selectedLesson->activities as $activity)
                        <div class="lp-item">
                            <div class="lp-item-title">{{ $activity->title }}</div>
                            <div class="lp-item-description">
                                {{ $activity->activity_type ?? 'Activity' }}
                                · {{ $activity->duration_minutes }} minutes
                            </div>
                        </div>
                    @empty
                        <div class="lp-plan-meta">No activities added yet.</div>
                    @endforelse
                </div>

                <div class="lp-section">
                    <div class="lp-section-title">
                        Resources ({{ $selectedLesson->resources->count() }})
                    </div>

                    @forelse($selectedLesson->resources as $resource)
                        <div class="lp-item">
                            <div class="lp-item-title">{{ $resource->title }}</div>
                            <div class="lp-item-description">
                                {{ ucfirst($resource->resource_type) }}
                            </div>
                        </div>
                    @empty
                        <div class="lp-plan-meta">No resources added yet.</div>
                    @endforelse
                </div>

                <div class="lp-actions">
                    <a
                        href="{{ route('lesson-plans.show', $selectedLesson) }}"
                        class="lp-outline-btn"
                    >
                        View Full Plan
                    </a>

                    @if(auth()->user()->hasPermission('ACADEMICS.UPDATE') &&
                        !in_array($selectedLesson->status, ['completed', 'cancelled']))
                        <a
                            href="{{ route('lesson-plans.edit', $selectedLesson) }}"
                            class="lp-outline-btn"
                        >
                            Edit Plan
                        </a>
                    @endif

                    <a
                        href="{{ route('lesson-plans.pdf', $selectedLesson) }}"
                        class="lp-outline-btn"
                    >
                        Download PDF
                    </a>
                </div>
            @else
                <div class="lp-empty">
                    <strong>Select a lesson plan</strong>
                    <p style="margin-top:6px;">
                        Choose a lesson from the list to view its details here.
                    </p>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
