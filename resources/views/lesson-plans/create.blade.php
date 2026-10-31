
@extends('layouts.schoolnexa')

@section('title', 'Create Lesson Plan')

@section('content')
<style>
    .lp-create { font-family: Inter, sans-serif; color: #25313C; }
    .lp-create-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:12px; }
    .lp-create-title { font-size:23px; font-weight:700; color:#123B5D; margin:0 0 5px; }
    .lp-muted { color:#64748B; font-size:13px; }
    .lp-card { background:#fff; border:1px solid #CBD5E1; border-radius:14px; padding:20px; margin-bottom:16px; }
    .lp-card-title { font-size:15px; font-weight:700; color:#123B5D; margin:0 0 17px; }
    .lp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:16px; }
    .lp-field label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:7px; }
    .lp-field input,.lp-field select,.lp-field textarea { width:100%; border:1px solid #CBD5E1; border-radius:8px; padding:10px 11px; font-size:13px; color:#25313C; background:white; outline:none; }
    .lp-field input,.lp-field select { height:41px; }
    .lp-field textarea { min-height:85px; resize:vertical; }
    .lp-field input:focus,.lp-field select:focus,.lp-field textarea:focus { border-color:#176B87; box-shadow:0 0 0 3px rgba(23,107,135,.1); }
    .lp-full { grid-column:1/-1; }
    .lp-required { color:#DC2626; }
    .lp-hint { font-size:11px; color:#64748B; margin-top:5px; }
    .lp-repeat-row { border:1px solid #E2E8F0; border-radius:10px; padding:14px; margin-bottom:10px; }
    .lp-repeat-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .lp-repeat-head strong { font-size:12px; color:#123B5D; }
    .lp-add-btn { background:#EFF6FF; color:#176B87; border:1px solid #BFDBFE; border-radius:8px; padding:8px 12px; font-size:12px; font-weight:600; cursor:pointer; }
    .lp-remove-btn { background:#FEF2F2; color:#DC2626; border:1px solid #FECACA; border-radius:7px; padding:6px 9px; font-size:11px; cursor:pointer; }
    .lp-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; }
    .lp-save { background:#123B5D; color:white; border:1px solid #123B5D; border-radius:9px; padding:11px 19px; font-size:13px; font-weight:600; cursor:pointer; }
    .lp-back { display:inline-flex; align-items:center; text-decoration:none; background:white; border:1px solid #CBD5E1; border-radius:9px; color:#123B5D; height:38px; padding:0 16px; font-size:13px; font-weight:600; }
    .lp-errors { background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; padding:12px 16px; border-radius:9px; margin-bottom:16px; font-size:12px; }
    @media(max-width:700px) { .lp-grid { grid-template-columns:1fr; } .lp-full { grid-column:auto; } .lp-create-header { align-items:flex-start; flex-direction:column; } }
</style>

<div class="lp-create">

    <div class="lp-create-header">
        <div>
            <h1 class="lp-create-title">Create Lesson Plan</h1>
            <p class="lp-muted">Prepare a structured teaching plan for your classroom.</p>
        </div>
        <a href="{{ route('lesson-plans.index') }}" class="lp-back">← Back to Lesson Plans</a>
    </div>

    @if($errors->any())
        <div class="lp-errors">
            <strong>Please correct the following:</strong>
            <ul style="margin:7px 0 0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lesson-plans.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- BASIC INFORMATION --}}
        <div class="lp-card">
            <h3 class="lp-card-title">01. Basic Information</h3>

            <div class="lp-grid">
                <div class="lp-field lp-full">
                    <label>Lesson Plan Title <span class="lp-required">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Introduction to English Grammar" required>
                </div>

                <div class="lp-field lp-full">
                    <label>Description</label>
                    <textarea name="description" placeholder="Briefly describe what this lesson covers...">{{ old('description') }}</textarea>
                </div>

                <div class="lp-field">
                    <label>Academic Year <span class="lp-required">*</span></label>
                    <select name="academic_year_id" required>
                        <option value="">Select academic year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(old('academic_year_id') == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Term</label>
                    <select name="term_id">
                        <option value="">Select term (optional)</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" @selected(old('term_id') == $term->id)>
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="lp-hint">Terms are optional. No terms have been configured yet.</div>
                </div>
            </div>
        </div>

        {{-- CLASSROOM ASSIGNMENT --}}
        <div class="lp-card">
            <h3 class="lp-card-title">02. Classroom Assignment</h3>

            <div class="lp-grid">
                <div class="lp-field">
                    <label>Class & Subject <span class="lp-required">*</span></label>
                    <select name="class_subject_id" id="class_subject_id" required>
                        <option value="">Select class and subject</option>
                        @foreach($classSubjects as $item)
                            <option
                                value="{{ $item->id }}"
                                data-class-id="{{ $item->school_class_id }}"
                                @selected(old('class_subject_id') == $item->id)
                            >
                                {{ $item->schoolClass?->name }} — {{ $item->subject?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Section <span class="lp-required">*</span></label>
                    <select name="section_id" id="section_id" required>
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option
                                value="{{ $section->id }}"
                                data-class-id="{{ $section->school_class_id }}"
                                @selected(old('section_id') == $section->id)
                            >
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Teacher <span class="lp-required">*</span></label>
                    <select name="staff_profile_id" required>
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('staff_profile_id') == $teacher->id)>
                                {{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Curriculum Topic</label>
                    <select name="curriculum_topic_id">
                        <option value="">Select curriculum topic (optional)</option>
                        @foreach($curriculumTopics as $topic)
                            <option value="{{ $topic->id }}" @selected(old('curriculum_topic_id') == $topic->id)>
                                {{ $topic->chapter?->curriculum?->title }} —
                                {{ $topic->chapter?->title }} —
                                {{ $topic->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- SCHEDULE --}}
        <div class="lp-card">
            <h3 class="lp-card-title">03. Schedule & Teaching Method</h3>

            <div class="lp-grid">
                <div class="lp-field">
                    <label>Scheduled Date <span class="lp-required">*</span></label>
                    <input type="date" name="scheduled_date" value="{{ old('scheduled_date') }}" required>
                </div>

                <div class="lp-field">
                    <label>Duration (Minutes) <span class="lp-required">*</span></label>
                    <input type="number" name="duration_minutes" min="1" max="600" value="{{ old('duration_minutes', 40) }}" required>
                </div>

                <div class="lp-field lp-full">
                    <label>Teaching Method</label>
                    <select name="teaching_method">
                        <option value="">Select teaching method</option>
                        @foreach(['Lecture','Demonstration','Discussion','Activity Based Learning','Group Work','Project Based Learning','Practical','Blended Learning'] as $method)
                            <option value="{{ $method }}" @selected(old('teaching_method') == $method)>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field lp-full">
                    <label>Teacher Notes</label>
                    <textarea name="teacher_notes" placeholder="Additional preparation notes or instructions...">{{ old('teacher_notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- OBJECTIVES --}}
        <div class="lp-card">
            <div class="lp-repeat-head">
                <h3 class="lp-card-title" style="margin:0;">04. Learning Objectives</h3>
                <button type="button" class="lp-add-btn" onclick="addObjective()">+ Add Objective</button>
            </div>

            <div id="objectives-container"></div>
            <p class="lp-hint">Add what students should know or be able to do after this lesson.</p>
        </div>

        {{-- ACTIVITIES --}}
        <div class="lp-card">
            <div class="lp-repeat-head">
                <h3 class="lp-card-title" style="margin:0;">05. Teaching Activities</h3>
                <button type="button" class="lp-add-btn" onclick="addActivity()">+ Add Activity</button>
            </div>

            <div id="activities-container"></div>
            <p class="lp-hint">Break the lesson into activities and allocate time to each.</p>
        </div>

        {{-- RESOURCES --}}
        <div class="lp-card">
            <div class="lp-repeat-head">
                <h3 class="lp-card-title" style="margin:0;">06. Learning Resources</h3>
                <button type="button" class="lp-add-btn" onclick="addResource()">+ Add Resource</button>
            </div>

            <div id="resources-container"></div>
            <p class="lp-hint">Attach learning material or provide an external resource link.</p>
        </div>

        <div class="lp-actions">
            <a href="{{ route('lesson-plans.index') }}" class="lp-back">Cancel</a>
            <button type="submit" class="lp-save">Save as Draft</button>
        </div>
    </form>
</div>

<script>
    let objectiveCount = 0;
    let activityCount = 0;
    let resourceCount = 0;

    function addObjective(title = '', description = '') {
        const id = objectiveCount++;
        document.getElementById('objectives-container').insertAdjacentHTML('beforeend', `
            <div class="lp-repeat-row">
                <div class="lp-repeat-head">
                    <strong>Objective ${id + 1}</strong>
                    <button type="button" class="lp-remove-btn" onclick="this.closest('.lp-repeat-row').remove()">Remove</button>
                </div>
                <div class="lp-grid">
                    <div class="lp-field lp-full">
                        <label>Objective Title</label>
                        <input name="objectives[${id}][title]" value="${escapeHtml(title)}" placeholder="e.g. Identify different types of nouns">
                    </div>
                    <div class="lp-field lp-full">
                        <label>Description</label>
                        <textarea name="objectives[${id}][description]" placeholder="Explain the expected learning outcome...">${escapeHtml(description)}</textarea>
                    </div>
                </div>
            </div>
        `);
    }

    function addActivity() {
        const id = activityCount++;
        document.getElementById('activities-container').insertAdjacentHTML('beforeend', `
            <div class="lp-repeat-row">
                <div class="lp-repeat-head">
                    <strong>Activity ${id + 1}</strong>
                    <button type="button" class="lp-remove-btn" onclick="this.closest('.lp-repeat-row').remove()">Remove</button>
                </div>
                <div class="lp-grid">
                    <div class="lp-field">
                        <label>Activity Title</label>
                        <input name="activities[${id}][title]" placeholder="e.g. Introduction and explanation">
                    </div>
                    <div class="lp-field">
                        <label>Activity Type</label>
                        <select name="activities[${id}][activity_type]">
                            <option value="">Select type</option>
                            <option value="Introduction">Introduction</option>
                            <option value="Explanation">Explanation</option>
                            <option value="Discussion">Discussion</option>
                            <option value="Practice">Practice</option>
                            <option value="Assessment">Assessment</option>
                            <option value="Group Activity">Group Activity</option>
                        </select>
                    </div>
                    <div class="lp-field">
                        <label>Duration (Minutes)</label>
                        <input type="number" name="activities[${id}][duration_minutes]" min="0" value="10">
                    </div>
                    <div class="lp-field">
                        <label>Description</label>
                        <textarea name="activities[${id}][description]" placeholder="Describe the activity..."></textarea>
                    </div>
                </div>
            </div>
        `);
    }

    function addResource() {
        const id = resourceCount++;
        document.getElementById('resources-container').insertAdjacentHTML('beforeend', `
            <div class="lp-repeat-row">
                <div class="lp-repeat-head">
                    <strong>Resource ${id + 1}</strong>
                    <button type="button" class="lp-remove-btn" onclick="this.closest('.lp-repeat-row').remove()">Remove</button>
                </div>
                <div class="lp-grid">
                    <div class="lp-field">
                        <label>Resource Title</label>
                        <input name="resources[${id}][title]" placeholder="e.g. English Grammar PDF">
                    </div>
                    <div class="lp-field">
                        <label>Resource Type</label>
                        <select name="resources[${id}][resource_type]">
                            <option value="">Select type</option>
                            <option value="PDF">PDF</option>
                            <option value="Document">Document</option>
                            <option value="Presentation">Presentation</option>
                            <option value="Video">Video</option>
                            <option value="Image">Image</option>
                            <option value="Link">External Link</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="lp-field lp-full">
                        <label>Upload File (Optional)</label>
                        <input type="file" name="resources[${id}][file]">
                    </div>
                    <div class="lp-field lp-full">
                        <label>External URL (Optional)</label>
                        <input type="url" name="resources[${id}][external_url]" placeholder="https://...">
                    </div>
                    <div class="lp-field lp-full">
                        <label>Description</label>
                        <textarea name="resources[${id}][description]" placeholder="Resource details..."></textarea>
                    </div>
                </div>
            </div>
        `);
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    // Show only sections belonging to the selected class.
    const classSubjectSelect = document.getElementById('class_subject_id');
    const sectionSelect = document.getElementById('section_id');

    function filterSections() {
        const selectedOption = classSubjectSelect.options[classSubjectSelect.selectedIndex];
        const classId = selectedOption?.dataset.classId;

        Array.from(sectionSelect.options).forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            option.hidden = !classId || option.dataset.classId !== classId;

            if (option.hidden && option.selected) {
                sectionSelect.value = '';
            }
        });
    }

    classSubjectSelect.addEventListener('change', filterSections);
    filterSections();

    // Restore old input after validation errors.
    @if(old('objectives'))
        @foreach(old('objectives') as $objective)
            addObjective(@json($objective['title'] ?? ''), @json($objective['description'] ?? ''));
        @endforeach
    @else
        addObjective();
    @endif

    @if(old('activities'))
        @foreach(old('activities') as $activity)
            addActivity();
        @endforeach
    @else
        addActivity();
    @endif

    @if(old('resources'))
        @foreach(old('resources') as $resource)
            addResource();
        @endforeach
    @endif
</script>
@endsection
