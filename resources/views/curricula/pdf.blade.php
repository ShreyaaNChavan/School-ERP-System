
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $curriculum->title }}</title>
    <style>
        @page { margin: 35px 42px; }
        body { font-family: DejaVu Sans, sans-serif; color:#25313C; font-size:11px; }
        .school { text-align:center; border-bottom:2px solid #123B5D; padding-bottom:16px; }
        .school h1 { color:#123B5D; font-size:21px; margin:0 0 7px; }
        .school p { color:#64748B; margin:3px 0; font-size:10px; }
        .document-title { text-align:center; margin:22px 0; }
        .document-title h2 { color:#123B5D; font-size:18px; margin:0 0 7px; }
        .document-title p { color:#64748B; margin:0; }
        .details { width:100%; border-collapse:collapse; margin-bottom:20px; }
        .details td { width:50%; padding:8px; border:1px solid #CBD5E1; }
        .label { color:#64748B; font-size:9px; display:block; margin-bottom:4px; }
        .value { color:#123B5D; font-weight:bold; }
        .description { background:#F8FAFC; border:1px solid #E2E8F0; padding:12px; margin-bottom:20px; line-height:1.6; }
        .chapter { margin-top:18px; page-break-inside:avoid; }
        .chapter-heading { background:#123B5D; color:#fff; padding:10px 12px; }
        .chapter-heading strong { font-size:12px; }
        .chapter-heading span { float:right; font-size:9px; }
        .chapter-description { color:#64748B; font-size:10px; padding:8px 10px; }
        table.topics { width:100%; border-collapse:collapse; margin-top:5px; }
        .topics th { background:#F1F5F9; text-align:left; padding:8px; font-size:9px; color:#475569; }
        .topics td { border-bottom:1px solid #E2E8F0; padding:8px; font-size:9px; }
        .status { font-size:9px; font-weight:bold; }
        .progress { margin-top:20px; border:1px solid #CBD5E1; padding:12px; }
        .footer { margin-top:30px; border-top:1px solid #CBD5E1; padding-top:9px; text-align:center; color:#94A3B8; font-size:9px; }
        .muted { color:#64748B; }
    </style>
</head>
<body>

    <div class="school">
        <h1>{{ $curriculum->school->name ?? 'School' }}</h1>
        <p>Academic Curriculum & Syllabus</p>
    </div>

    <div class="document-title">
        <h2>{{ $curriculum->title }}</h2>
        <p>{{ $curriculum->subject->name ?? 'Subject' }} Curriculum</p>
    </div>

    <table class="details">
        <tr>
            <td><span class="label">Academic Year</span><span class="value">{{ $curriculum->academicYear->name ?? '—' }}</span></td>
            <td><span class="label">Class</span><span class="value">{{ $curriculum->schoolClass->name ?? '—' }}</span></td>
        </tr>
        <tr>
            <td><span class="label">Subject</span><span class="value">{{ $curriculum->subject->name ?? '—' }}</span></td>
            <td><span class="label">Status</span><span class="value">{{ ucfirst($curriculum->status) }}</span></td>
        </tr>
    </table>

    @if($curriculum->description)
        <div class="description">
            <strong>Curriculum Overview</strong><br>
            {{ $curriculum->description }}
        </div>
    @endif

    @php
        $totalTopics = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->count());
        $completedTopics = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->where('status','completed')->count());
        $progress = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;
    @endphp

    @foreach($curriculum->chapters as $chapter)
        <div class="chapter">
            <div class="chapter-heading">
                <strong>Chapter {{ $chapter->chapter_number }}: {{ $chapter->title }}</strong>
                <span>{{ $chapter->estimated_periods }} estimated periods</span>
            </div>

            @if($chapter->description)
                <div class="chapter-description">{{ $chapter->description }}</div>
            @endif

            @if($chapter->topics->count())
                <table class="topics">
                    <thead>
                        <tr>
                            <th style="width:7%">No.</th>
                            <th style="width:53%">Topic</th>
                            <th style="width:20%">Periods</th>
                            <th style="width:20%">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($chapter->topics as $index => $topic)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $topic->title }}
                                    @if($topic->description)
                                        <br><span class="muted">{{ $topic->description }}</span>
                                    @endif
                                </td>
                                <td>{{ $topic->estimated_periods }}</td>
                                <td>{{ ucwords(str_replace('_',' ',$topic->status)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="muted">No topics added.</p>
            @endif
        </div>
    @endforeach

    <div class="progress">
        <strong>Syllabus Progress: {{ $progress }}%</strong><br>
        <span class="muted">{{ $completedTopics }} of {{ $totalTopics }} topics completed</span>
    </div>

    <div class="footer">
        Generated on {{ now()->format('d M Y, h:i A') }} · {{ $curriculum->school->name ?? 'School' }}
    </div>

</body>
</html>
