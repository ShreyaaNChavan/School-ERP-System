
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $lessonPlan->title }}</title>
    <style>
        @page { margin: 32px 38px; }
        body { font-family: DejaVu Sans, sans-serif; color:#25313C; font-size:10px; line-height:1.5; }
        .header { background:#123B5D; color:white; padding:18px; margin-bottom:18px; }
        .brand { font-size:19px; font-weight:bold; letter-spacing:.3px; }
        .subtitle { font-size:9px; margin-top:4px; color:#DBEAFE; }
        h1 { font-size:17px; color:#123B5D; margin:0 0 7px; }
        h2 { font-size:12px; color:#123B5D; border-bottom:1px solid #CBD5E1; padding-bottom:6px; margin:19px 0 10px; }
        .status { display:inline-block; background:#E2E8F0; color:#334155; padding:3px 8px; border-radius:10px; font-size:8px; }
        .description { color:#64748B; margin:8px 0 14px; }
        table { width:100%; border-collapse:collapse; margin-bottom:10px; }
        td, th { padding:7px 8px; border:1px solid #E2E8F0; vertical-align:top; }
        th { background:#F1F5F9; text-align:left; color:#123B5D; font-weight:bold; }
        .label { color:#64748B; font-size:8px; }
        .value { font-weight:bold; }
        .item { padding:7px 0; border-bottom:1px solid #E2E8F0; }
        .item-title { font-weight:bold; color:#25313C; }
        .muted { color:#64748B; font-size:9px; }
        .footer { margin-top:25px; border-top:1px solid #CBD5E1; padding-top:8px; color:#64748B; font-size:8px; text-align:center; }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand">SchoolNexa</div>
        <div class="subtitle">School Management Platform · Lesson Planning</div>
    </div>

    <h1>{{ $lessonPlan->title }}</h1>
    <span class="status">{{ ucwords(str_replace('_', ' ', $lessonPlan->status)) }}</span>

    @if($lessonPlan->description)
        <p class="description">{{ $lessonPlan->description }}</p>
    @endif

    <h2>Lesson Information</h2>
    <table>
        <tr>
            <td width="25%"><div class="label">Academic Year</div><div class="value">{{ $lessonPlan->academicYear?->name ?? '—' }}</div></td>
            <td width="25%"><div class="label">Term</div><div class="value">{{ $lessonPlan->term?->name ?? '—' }}</div></td>
            <td width="25%"><div class="label">Scheduled Date</div><div class="value">{{ $lessonPlan->scheduled_date?->format('d M Y') ?? '—' }}</div></td>
            <td width="25%"><div class="label">Duration</div><div class="value">{{ $lessonPlan->duration_minutes }} minutes</div></td>
        </tr>
        <tr>
            <td><div class="label">Class</div><div class="value">{{ $lessonPlan->classSubject?->schoolClass?->name ?? '—' }}</div></td>
            <td><div class="label">Section</div><div class="value">{{ $lessonPlan->section?->name ?? '—' }}</div></td>
            <td><div class="label">Subject</div><div class="value">{{ $lessonPlan->classSubject?->subject?->name ?? '—' }}</div></td>
            <td><div class="label">Teacher</div><div class="value">{{ $lessonPlan->teacher?->first_name }} {{ $lessonPlan->teacher?->last_name }}</div></td>
        </tr>
        <tr>
            <td colspan="2"><div class="label">Teaching Method</div><div class="value">{{ $lessonPlan->teaching_method ?? '—' }}</div></td>
            <td colspan="2"><div class="label">Curriculum Topic</div><div class="value">{{ $lessonPlan->curriculumTopic?->title ?? '—' }}</div></td>
        </tr>
    </table>

    <h2>Learning Objectives</h2>
    @forelse($lessonPlan->objectives as $objective)
        <div class="item">
            <div class="item-title">{{ $loop->iteration }}. {{ $objective->title }}</div>
            @if($objective->description)
                <div class="muted">{{ $objective->description }}</div>
            @endif
            <div class="muted">Status: {{ $objective->is_completed ? 'Completed' : 'Pending' }}</div>
        </div>
    @empty
        <p class="muted">No learning objectives added.</p>
    @endforelse

    <h2>Teaching Activities</h2>
    @forelse($lessonPlan->activities as $activity)
        <div class="item">
            <div class="item-title">{{ $loop->iteration }}. {{ $activity->title }}</div>
            <div class="muted">
                {{ $activity->activity_type ?? 'Activity' }} · {{ $activity->duration_minutes }} minutes
            </div>
            @if($activity->description)
                <div class="muted">{{ $activity->description }}</div>
            @endif
        </div>
    @empty
        <p class="muted">No teaching activities added.</p>
    @endforelse

    <h2>Learning Resources</h2>
    @forelse($lessonPlan->resources as $resource)
        <div class="item">
            <div class="item-title">{{ $resource->title }}</div>
            <div class="muted">Type: {{ $resource->resource_type }}</div>
            @if($resource->description)
                <div class="muted">{{ $resource->description }}</div>
            @endif
            @if($resource->external_url)
                <div class="muted">Link: {{ $resource->external_url }}</div>
            @endif
            @if($resource->file_path)
                <div class="muted">Attachment: {{ basename($resource->file_path) }}</div>
            @endif
        </div>
    @empty
        <p class="muted">No learning resources added.</p>
    @endforelse

    @if($lessonPlan->teacher_notes)
        <h2>Teacher Notes</h2>
        <p>{{ $lessonPlan->teacher_notes }}</p>
    @endif

    <div class="footer">
        Generated by SchoolNexa · {{ now()->format('d M Y, h:i A') }}
    </div>
</body>
</html>
