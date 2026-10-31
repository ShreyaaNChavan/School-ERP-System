
@extends('layouts.schoolnexa')

@section('content')
<div class="cd-page">

    @php
        $totalTopics = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->count());
        $completedTopics = $curriculum->chapters->sum(fn($chapter) => $chapter->topics->where('status','completed')->count());
        $progress = $totalTopics > 0 ? round(($completedTopics / $totalTopics) * 100) : 0;
        $editable = $curriculum->status === 'draft';
    @endphp

    <div class="cd-header">
        <div>
            <h2>{{ $curriculum->title }}</h2>
            <p>Curriculum details, chapters and syllabus progress</p>
        </div>
        <div class="cd-header-actions">
            <a href="{{ route('curricula.index') }}" class="cd-outline">Back to Directory</a>
            <a href="{{ route('curricula.pdf',$curriculum) }}" class="cd-pdf">Download PDF</a>
            @if($editable && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                <a href="{{ route('curricula.edit',$curriculum) }}" class="cd-primary">Edit</a>
            @endif
        </div>
    </div>

    @if(session('success')) <div class="cd-alert success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="cd-alert error">{{ session('error') }}</div> @endif

    @if($errors->any())
        <div class="cd-alert error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="cd-card">
        <div class="cd-info-grid">
            <div><label>School</label><strong>{{ $curriculum->school->name ?? '—' }}</strong></div>
            <div><label>Academic Year</label><strong>{{ $curriculum->academicYear->name ?? '—' }}</strong></div>
            <div><label>Class</label><strong>{{ $curriculum->schoolClass->name ?? '—' }}</strong></div>
            <div><label>Subject</label><strong>{{ $curriculum->subject->name ?? '—' }}</strong></div>
            <div><label>Status</label><strong class="cd-status {{ $curriculum->status }}">{{ ucfirst($curriculum->status) }}</strong></div>
            <div><label>Chapters</label><strong>{{ $curriculum->chapters->count() }}</strong></div>
        </div>

        @if($curriculum->description)
            <div class="cd-description">
                <label>Curriculum Description</label>
                <p>{{ $curriculum->description }}</p>
            </div>
        @endif

        <div class="cd-progress-area">
            <div class="cd-progress-heading">
                <strong>Syllabus Completion</strong>
                <strong>{{ $progress }}%</strong>
            </div>
            <div class="cd-progress"><span style="width:{{ $progress }}%"></span></div>
            <small>{{ $completedTopics }} of {{ $totalTopics }} topics completed</small>
        </div>
    </div>

    @if($editable && auth()->user()->hasPermission('ACADEMICS.CREATE'))
        <div class="cd-card">
            <h3>Add Chapter</h3>
            <p>Create a chapter in this curriculum.</p>

            <form action="{{ route('curricula.chapters.store',$curriculum) }}" method="POST" class="cd-chapter-form">
                @csrf
                <div>
                    <label>Chapter Number</label>
                    <input type="number" name="chapter_number" min="1" required value="{{ old('chapter_number') }}">
                </div>
                <div>
                    <label>Chapter Title</label>
                    <input type="text" name="title" maxlength="200" required placeholder="Chapter title">
                </div>
                <div>
                    <label>Estimated Periods</label>
                    <input type="number" name="estimated_periods" min="0" max="500" value="5" required>
                </div>
                <div class="cd-chapter-description">
                    <label>Description</label>
                    <input type="text" name="description" placeholder="Optional chapter description">
                </div>
                <button type="submit" class="cd-primary">Add Chapter</button>
            </form>
        </div>
    @endif

    <div class="cd-card">
        <div class="cd-section-heading">
            <div>
                <h3>Chapters & Topics</h3>
                <p>Manage syllabus content and track teaching progress.</p>
            </div>

            @if($curriculum->status === 'draft' && auth()->user()->hasPermission('ACADEMICS.CREATE'))
                <form action="{{ route('curricula.publish',$curriculum) }}" method="POST"
                    onsubmit="return confirm('Publish this curriculum? Editing will be disabled.')">
                    @csrf
                    <button class="cd-publish" type="submit">Publish Curriculum</button>
                </form>
            @elseif($curriculum->status === 'published' && auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                <form action="{{ route('curricula.archive',$curriculum) }}" method="POST"
                    onsubmit="return confirm('Archive this curriculum?')">
                    @csrf
                    <button class="cd-archive" type="submit">Archive Curriculum</button>
                </form>
            @endif
        </div>

        @forelse($curriculum->chapters as $chapter)
            <div class="cd-chapter">
                <div class="cd-chapter-heading">
                    <div>
                        <span class="cd-chapter-number">CHAPTER {{ $chapter->chapter_number }}</span>
                        <h4>{{ $chapter->title }}</h4>
                        @if($chapter->description)
                            <p>{{ $chapter->description }}</p>
                        @endif
                        <small>{{ $chapter->estimated_periods }} estimated periods · {{ $chapter->topics->count() }} topics</small>
                    </div>
                    <span class="cd-status {{ $chapter->status }}">{{ ucwords(str_replace('_',' ',$chapter->status)) }}</span>
                </div>

                @if($editable && auth()->user()->hasPermission('ACADEMICS.CREATE'))
                    <form action="{{ route('curricula.topics.store',$chapter) }}" method="POST" class="cd-topic-form">
                        @csrf
                        <input type="text" name="title" placeholder="New topic title" maxlength="200" required>
                        <input type="number" name="estimated_periods" min="0" max="200" value="1" required>
                        <button type="submit" class="cd-small-primary">Add Topic</button>
                    </form>
                @endif

                @if($chapter->topics->count())
                    <div class="cd-topic-list">
                        @foreach($chapter->topics as $topic)
                            <div class="cd-topic">
                                <div>
                                    <strong>{{ $topic->title }}</strong>
                                    @if($topic->description)
                                        <small>{{ $topic->description }}</small>
                                    @endif
                                    <small>{{ $topic->estimated_periods }} periods</small>
                                </div>

                                <div class="cd-topic-actions">
                                    @if(auth()->user()->hasPermission('ACADEMICS.UPDATE'))
                                        <form action="{{ route('curricula.topics.progress',$topic) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()">
                                                @foreach(['planned'=>'Planned','in_progress'=>'In Progress','completed'=>'Completed'] as $value=>$label)
                                                    <option value="{{ $value }}" @selected($topic->status === $value)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    @else
                                        <span class="cd-status {{ $topic->status }}">{{ ucwords(str_replace('_',' ',$topic->status)) }}</span>
                                    @endif

                                    @if($editable && auth()->user()->hasPermission('ACADEMICS.DELETE'))
                                        <form action="{{ route('curricula.topics.destroy',$topic) }}" method="POST"
                                            onsubmit="return confirm('Delete this topic?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="cd-delete" type="submit">Remove</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="cd-no-topics">No topics added to this chapter yet.</p>
                @endif

                @if($editable && auth()->user()->hasPermission('ACADEMICS.DELETE'))
                    <form action="{{ route('curricula.chapters.destroy',$chapter) }}" method="POST"
                        onsubmit="return confirm('Delete this chapter and all its topics?')">
                        @csrf
                        @method('DELETE')
                        <button class="cd-delete cd-delete-chapter" type="submit">Delete Chapter</button>
                    </form>
                @endif
            </div>
        @empty
            <div class="cd-empty">No chapters have been added yet.</div>
        @endforelse
    </div>
</div>

<style>
.cd-page{max-width:1250px;margin:auto}
.cd-header{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:22px}
.cd-header h2{font-size:22px;color:#123B5D;margin:0;font-weight:700}
.cd-header p{font-size:13px;color:#64748B;margin:6px 0 0}
.cd-header-actions{display:flex;gap:9px;flex-wrap:wrap}
.cd-outline,.cd-primary,.cd-pdf{height:38px;padding:0 15px;display:inline-flex;align-items:center;justify-content:center;border-radius:9px;text-decoration:none;font-size:12px;font-weight:600;border:1px solid}
.cd-outline{background:#fff;border-color:#CBD5E1;color:#123B5D}
.cd-primary{background:#123B5D;border-color:#123B5D;color:#fff;cursor:pointer}
.cd-pdf{background:#176B87;border-color:#176B87;color:#fff}
.cd-card{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:22px;margin-bottom:19px}
.cd-info-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:21px}
.cd-info-grid label,.cd-description label{display:block;color:#64748B;font-size:11px;margin-bottom:7px}
.cd-info-grid strong{font-size:13px;color:#25313C}
.cd-description{border-top:1px solid #E2E8F0;margin-top:20px;padding-top:15px}
.cd-description p{font-size:13px;color:#475569;line-height:1.6;margin:0}
.cd-progress-area{margin-top:22px;border-top:1px solid #E2E8F0;padding-top:17px}
.cd-progress-heading{display:flex;justify-content:space-between;color:#123B5D;font-size:13px;margin-bottom:9px}
.cd-progress{height:9px;background:#E2E8F0;border-radius:20px;overflow:hidden}
.cd-progress span{display:block;height:100%;background:#176B87;border-radius:20px}
.cd-progress-area small{display:block;color:#64748B;font-size:11px;margin-top:7px}
.cd-card h3{font-size:16px;color:#123B5D;margin:0 0 5px}
.cd-card>p,.cd-section-heading p{font-size:12px;color:#64748B;margin:0 0 17px}
.cd-chapter-form{display:grid;grid-template-columns:1fr 2fr 1fr;gap:12px;align-items:end;margin-top:18px}
.cd-chapter-form label{display:block;font-size:12px;font-weight:600;margin-bottom:6px;color:#25313C}
.cd-chapter-form input,.cd-topic-form input,.cd-topic-actions select{height:38px;width:100%;box-sizing:border-box;border:1px solid #CBD5E1;border-radius:8px;padding:0 10px;font-size:12px;background:#fff}
.cd-chapter-description{grid-column:1/3}
.cd-chapter-form button{grid-column:3}
.cd-publish,.cd-archive{height:36px;padding:0 13px;border:0;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer}
.cd-publish{background:#DCFCE7;color:#166534}
.cd-archive{background:#E2E8F0;color:#475569}
.cd-section-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:15px}
.cd-section-heading h3{margin:0}
.cd-section-heading p{margin-top:5px;margin-bottom:0}
.cd-chapter{border:1px solid #E2E8F0;border-radius:12px;padding:17px;margin-top:14px}
.cd-chapter-heading{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
.cd-chapter-number{font-size:10px;color:#176B87;font-weight:700;letter-spacing:.5px}
.cd-chapter-heading h4{font-size:14px;color:#123B5D;margin:5px 0}
.cd-chapter-heading p{font-size:12px;color:#64748B;margin:5px 0}
.cd-chapter-heading small{font-size:11px;color:#94A3B8}
.cd-status{font-size:10px;font-weight:600;padding:5px 9px;border-radius:20px;white-space:nowrap}
.cd-status.planned{background:#F1F5F9;color:#475569}
.cd-status.in_progress{background:#FEF3C7;color:#92400E}
.cd-status.completed{background:#DCFCE7;color:#166534}
.cd-status.draft{background:#FEF3C7;color:#92400E}
.cd-status.published{background:#DCFCE7;color:#166534}
.cd-status.archived{background:#E2E8F0;color:#475569}
.cd-topic-form{display:grid;grid-template-columns:2fr 100px auto;gap:9px;margin:16px 0}
.cd-small-primary{background:#E0F2FE;color:#075985;border:0;border-radius:8px;padding:0 12px;font-size:11px;font-weight:600;cursor:pointer}
.cd-topic-list{border-top:1px solid #E2E8F0;margin-top:15px}
.cd-topic{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid #F1F5F9}
.cd-topic strong{font-size:12px;color:#25313C}
.cd-topic small{display:block;font-size:10px;color:#94A3B8;margin-top:4px}
.cd-topic-actions{display:flex;align-items:center;gap:10px}
.cd-topic-actions select{width:120px;height:32px}
.cd-delete{background:none;border:0;color:#DC2626;font-size:11px;font-weight:600;cursor:pointer}
.cd-delete-chapter{margin-top:14px}
.cd-no-topics,.cd-empty{text-align:center;color:#94A3B8;font-size:12px;padding:17px}
.cd-alert{padding:12px 15px;border-radius:9px;margin-bottom:17px;font-size:13px}
.cd-alert.success{background:#DCFCE7;color:#166534}
.cd-alert.error{background:#FEE2E2;color:#991B1B}
@media(max-width:750px){.cd-info-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.cd-chapter-form{grid-template-columns:1fr 1fr}.cd-chapter-description{grid-column:1/-1}.cd-chapter-form button{grid-column:auto}}
@media(max-width:520px){.cd-info-grid{grid-template-columns:1fr}.cd-topic{align-items:flex-start;flex-direction:column}.cd-topic-form{grid-template-columns:1fr 80px}.cd-topic-form button{grid-column:1/-1;height:35px}}
</style>
@endsection
