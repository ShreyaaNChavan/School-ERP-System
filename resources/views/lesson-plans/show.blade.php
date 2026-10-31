
@extends('layouts.schoolnexa')

@section('title', 'Lesson Plan Details')

@section('content')
<style>
    .lp-page { font-family:Inter,sans-serif; color:#25313C; }
    .lp-header { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
    .lp-title { font-size:23px; font-weight:700; color:#123B5D; margin:0 0 5px; }
    .lp-muted { color:#64748B; font-size:12px; }
    .lp-card { background:#fff; border:1px solid #CBD5E1; border-radius:14px; padding:19px; margin-bottom:15px; }
    .lp-card-title { font-size:14px; font-weight:700; color:#123B5D; margin:0 0 15px; }
    .lp-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:18px; }
    .lp-label { font-size:11px; color:#64748B; margin-bottom:5px; }
    .lp-value { font-size:13px; font-weight:600; color:#25313C; overflow-wrap:anywhere; }
    .lp-section { border-top:1px solid #E2E8F0; padding-top:15px; margin-top:15px; }
    .lp-item { padding:12px 0; border-bottom:1px solid #F1F5F9; }
    .lp-item:last-child { border-bottom:0; }
    .lp-item-title { font-size:13px; font-weight:600; color:#25313C; }
    .lp-item-desc { font-size:12px; color:#64748B; margin-top:5px; line-height:1.6; }
    .lp-status { display:inline-block; padding:5px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:capitalize; }
    .draft { background:#F1F5F9; color:#475569; }
    .published { background:#DBEAFE; color:#1D4ED8; }
    .in_progress { background:#F3E8FF; color:#7E22CE; }
    .completed { background:#DCFCE7; color:#15803D; }
    .cancelled { background:#FEE2E2; color:#B91C1C; }
    .lp-btn { display:inline-flex; align-items:center; justify-content:center; padding:9px 14px; border:1px solid #CBD5E1; border-radius:9px; background:white; color:#123B5D; font-size:12px; font-weight:600; text-decoration:none; }
    .lp-btn-primary { background:#123B5D; border-color:#123B5D; color:white; }
    .lp-actions { display:flex; gap:9px; flex-wrap:wrap; }
    .lp-empty { font-size:12px; color:#64748B; }
    @media(max-width:700px) { .lp-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .lp-header { align-items:flex-start; flex-direction:column; } }
</style>

<div class="lp-page">
    <div class="lp-header">
        <div>
            <h1 class="lp-title">{{ $lessonPlan->title }}</h1>
            <p class="lp-muted">Complete lesson plan details and teaching resources.</p>
        </div>
        <div class="lp-actions">
            <a href="{{ route('lesson-plans.index') }}" class="lp-btn">← Back</a>
            @if(auth()->user()->hasPermission('ACADEMICS.UPDATE') && !in_array($lessonPlan->status, ['completed','cancelled']))
                <a href="{{ route('lesson-plans.edit', $lessonPlan) }}" class="lp-btn">Edit Plan</a>
            @endif
            <a href="{{ route('lesson-plans.pdf', $lessonPlan) }}" class="lp-btn lp-btn-primary">Download PDF</a>
        </div>
    </div>

    <div class="lp-card">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:15px;">
            <h3 class="lp-card-title" style="margin:0;">Overview</h3>
            <span class="lp-status {{ $lessonPlan->status }}">{{ str_replace('_',' ',$lessonPlan->status) }}</span>
        </div>

        @if($lessonPlan->description)
            <p class="lp-muted" style="line-height:1.7;margin-bottom:18px;">{{ $lessonPlan->description }}</p>
        @endif

        <div class="lp-grid">
            <div><div class="lp-label">Academic Year</div><div class="lp-value">{{ $lessonPlan->academicYear?->name ?? '—' }}</div></div>
            <div><div class="lp-label">Term</div><div class="lp-value">{{ $lessonPlan->term?->name ?? 'Not assigned' }}</div></div>
            <div><div class="lp-label">Scheduled Date</div><div class="lp-value">{{ $lessonPlan->scheduled_date?->format('d M Y') ?? '—' }}</div></div>
            <div><div class="lp-label">Class</div><div class="lp-value">{{ $lessonPlan->classSubject?->schoolClass?->name ?? '—' }}</div></div>
            <div><div class="lp-label">Section</div><div class="lp-value">{{ $lessonPlan->section?->name ?? '—' }}</div></div>
            <div><div class="lp-label">Subject</div><div class="lp-value">{{ $lessonPlan->classSubject?->subject?->name ?? '—' }}</div></div>
            <div><div class="lp-label">Teacher</div><div class="lp-value">{{ $lessonPlan->teacher?->first_name }} {{ $lessonPlan->teacher?->last_name }}</div></div>
            <div><div class="lp-label">Duration</div><div class="lp-value">{{ $lessonPlan->duration_minutes }} minutes</div></div>
            <div><div class="lp-label">Teaching Method</div><div class="lp-value">{{ $lessonPlan->teaching_method ?? '—' }}</div></div>
            <div><div class="lp-label">Curriculum Topic</div><div class="lp-value">{{ $lessonPlan->curriculumTopic?->title ?? 'Not linked' }}</div></div>
            <div><div class="lp-label">Published At</div><div class="lp-value">{{ $lessonPlan->published_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
            <div><div class="lp-label">Completed At</div><div class="lp-value">{{ $lessonPlan->completed_at?->format('d M Y, h:i A') ?? '—' }}</div></div>
        </div>
    </div>

    <div class="lp-card">
        <h3 class="lp-card-title">Learning Objectives ({{ $lessonPlan->objectives->count() }})</h3>
        @forelse($lessonPlan->objectives as $objective)
            <div class="lp-item">
                <div class="lp-item-title">
                    {{ $objective->is_completed ? '✓ ' : '' }}{{ $objective->title }}
                    <span class="lp-status {{ $objective->is_completed ? 'completed' : 'draft' }}">
                        {{ $objective->is_completed ? 'Completed' : 'Pending' }}
                    </span>
                </div>
                @if($objective->description)
                    <div class="lp-item-desc">{{ $objective->description }}</div>
                @endif
            </div>
        @empty
            <p class="lp-empty">No learning objectives have been added.</p>
        @endforelse
    </div>

    <div class="lp-card">
        <h3 class="lp-card-title">Teaching Activities ({{ $lessonPlan->activities->count() }})</h3>
        @forelse($lessonPlan->activities as $activity)
            <div class="lp-item">
                <div class="lp-item-title">{{ $loop->iteration }}. {{ $activity->title }}</div>
                <div class="lp-item-desc">
                    {{ $activity->activity_type ?? 'Activity' }} · {{ $activity->duration_minutes }} minutes
                    @if($activity->description)
                        <br>{{ $activity->description }}
                    @endif
                </div>
            </div>
        @empty
            <p class="lp-empty">No teaching activities have been added.</p>
        @endforelse
    </div>

    <div class="lp-card">
        <h3 class="lp-card-title">Learning Resources ({{ $lessonPlan->resources->count() }})</h3>
        @forelse($lessonPlan->resources as $resource)
            <div class="lp-item">
                <div class="lp-item-title">{{ $resource->title }}</div>
                <div class="lp-item-desc">
                    Type: {{ $resource->resource_type }}
                    @if($resource->description)
                        <br>{{ $resource->description }}
                    @endif
                </div>
                <div class="lp-actions" style="margin-top:8px;">
                    @if($resource->file_path)
                        <a class="lp-btn" href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($resource->file_path) }}" target="_blank">Open Attachment</a>
                    @endif
                    @if($resource->external_url)
                        <a class="lp-btn" href="{{ $resource->external_url }}" target="_blank" rel="noopener">Open Link</a>
                    @endif
                </div>
            </div>
        @empty
            <p class="lp-empty">No learning resources have been added.</p>
        @endforelse
    </div>

    @if($lessonPlan->teacher_notes)
        <div class="lp-card">
            <h3 class="lp-card-title">Teacher Notes</h3>
            <p class="lp-muted" style="line-height:1.7;">{{ $lessonPlan->teacher_notes }}</p>
        </div>
    @endif
</div>
@endsection
