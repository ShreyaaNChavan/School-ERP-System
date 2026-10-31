
@extends('layouts.schoolnexa')

@section('title', 'Edit Lesson Plan')

@section('content')
<style>
    .lp-edit { font-family:Inter,sans-serif; color:#25313C; }
    .lp-head { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:20px; }
    .lp-title { font-size:23px; font-weight:700; color:#123B5D; margin:0 0 5px; }
    .lp-muted { color:#64748B; font-size:12px; }
    .lp-card { background:white; border:1px solid #CBD5E1; border-radius:14px; padding:20px; margin-bottom:15px; }
    .lp-card h3 { font-size:14px; color:#123B5D; margin:0 0 16px; font-weight:700; }
    .lp-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px; }
    .lp-field label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; }
    .lp-field input,.lp-field select,.lp-field textarea { width:100%; border:1px solid #CBD5E1; border-radius:8px; padding:10px; font-size:13px; background:white; color:#25313C; }
    .lp-field input,.lp-field select { height:40px; }
    .lp-field textarea { min-height:75px; }
    .lp-full { grid-column:1/-1; }
    .lp-row { border:1px solid #E2E8F0; border-radius:10px; padding:14px; margin-bottom:10px; }
    .lp-row-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; }
    .lp-row-head strong { font-size:12px; color:#123B5D; }
    .lp-add { background:#EFF6FF; border:1px solid #BFDBFE; color:#176B87; padding:8px 12px; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; }
    .lp-remove { background:#FEF2F2; border:1px solid #FECACA; color:#DC2626; padding:6px 9px; border-radius:7px; font-size:11px; cursor:pointer; }
    .lp-btn { display:inline-flex; align-items:center; justify-content:center; background:white; border:1px solid #CBD5E1; border-radius:9px; color:#123B5D; padding:9px 15px; text-decoration:none; font-size:12px; font-weight:600; }
    .lp-save { background:#123B5D; border:1px solid #123B5D; border-radius:9px; color:white; padding:10px 18px; font-size:12px; font-weight:600; cursor:pointer; }
    .lp-actions { display:flex; justify-content:flex-end; gap:9px; margin-top:18px; }
    .lp-errors { background:#FEF2F2; border:1px solid #FECACA; color:#B91C1C; border-radius:9px; padding:12px; font-size:12px; margin-bottom:15px; }
    .lp-hint { color:#64748B; font-size:11px; margin-top:5px; }
    @media(max-width:700px) { .lp-grid { grid-template-columns:1fr; } .lp-full { grid-column:auto; } .lp-head { align-items:flex-start; flex-direction:column; } }
</style>

<div class="lp-edit">
    <div class="lp-head">
        <div>
            <h1 class="lp-title">Edit Lesson Plan</h1>
            <p class="lp-muted">Update lesson details, objectives, activities and resources.</p>
        </div>
        <a href="{{ route('lesson-plans.show', $lessonPlan) }}" class="lp-btn">← Back to Lesson</a>
    </div>

    @if($errors->any())
        <div class="lp-errors">
            <strong>Please correct the following errors:</strong>
            <ul style="margin:7px 0 0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lesson-plans.update', $lessonPlan) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="lp-card">
            <h3>01. Basic Information</h3>
            <div class="lp-grid">
                <div class="lp-field lp-full">
                    <label>Lesson Plan Title *</label>
                    <input name="title" required maxlength="200" value="{{ old('title', $lessonPlan->title) }}">
                </div>

                <div class="lp-field lp-full">
                    <label>Description</label>
                    <textarea name="description">{{ old('description', $lessonPlan->description) }}</textarea>
                </div>

                <div class="lp-field">
                    <label>Academic Year *</label>
                    <select name="academic_year_id" required>
                        <option value="">Select academic year</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" @selected(old('academic_year_id', $lessonPlan->academic_year_id) == $year->id)>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Term</label>
                    <select name="term_id">
                        <option value="">No term</option>
                        @foreach($terms as $term)
                            <option value="{{ $term->id }}" @selected(old('term_id', $lessonPlan->term_id) == $term->id)>
                                {{ $term->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="lp-card">
            <h3>02. Classroom Assignment</h3>
            <div class="lp-grid">
                <div class="lp-field">
                    <label>Class & Subject *</label>
                    <select name="class_subject_id" id="class_subject_id" required>
                        <option value="">Select class and subject</option>
                        @foreach($classSubjects as $item)
                            <option value="{{ $item->id }}"
                                data-class-id="{{ $item->school_class_id }}"
                                @selected(old('class_subject_id', $lessonPlan->class_subject_id) == $item->id)>
                                {{ $item->schoolClass?->name }} — {{ $item->subject?->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Section *</label>
                    <select name="section_id" id="section_id" required>
                        <option value="">Select section</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}"
                                data-class-id="{{ $section->school_class_id }}"
                                @selected(old('section_id', $lessonPlan->section_id) == $section->id)>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Teacher *</label>
                    <select name="staff_profile_id" required>
                        <option value="">Select teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" @selected(old('staff_profile_id', $lessonPlan->staff_profile_id) == $teacher->id)>
                                {{ $teacher->first_name }} {{ $teacher->middle_name }} {{ $teacher->last_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field">
                    <label>Curriculum Topic</label>
                    <select name="curriculum_topic_id">
                        <option value="">No curriculum topic</option>
                        @foreach($curriculumTopics as $topic)
                            <option value="{{ $topic->id }}" @selected(old('curriculum_topic_id', $lessonPlan->curriculum_topic_id) == $topic->id)>
                                {{ $topic->chapter?->curriculum?->title }} — {{ $topic->chapter?->title }} — {{ $topic->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="lp-card">
            <h3>03. Schedule & Teaching Method</h3>
            <div class="lp-grid">
                <div class="lp-field">
                    <label>Scheduled Date *</label>
                    <input type="date" name="scheduled_date" required value="{{ old('scheduled_date', $lessonPlan->scheduled_date?->format('Y-m-d')) }}">
                </div>

                <div class="lp-field">
                    <label>Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" min="1" max="600" required value="{{ old('duration_minutes', $lessonPlan->duration_minutes) }}">
                </div>

                <div class="lp-field lp-full">
                    <label>Teaching Method</label>
                    <select name="teaching_method">
                        <option value="">Select teaching method</option>
                        @foreach(['Lecture','Demonstration','Discussion','Activity Based Learning','Group Work','Project Based Learning','Practical','Blended Learning'] as $method)
                            <option value="{{ $method }}" @selected(old('teaching_method', $lessonPlan->teaching_method) == $method)>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="lp-field lp-full">
                    <label>Teacher Notes</label>
                    <textarea name="teacher_notes">{{ old('teacher_notes', $lessonPlan->teacher_notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="lp-card">
            <div class="lp-row-head">
                <h3 style="margin:0;">04. Learning Objectives</h3>
                <button type="button" class="lp-add" onclick="addObjective()">+ Add Objective</button>
            </div>
            <div id="objectives-container">
                @foreach($lessonPlan->objectives as $i => $objective)
                    <div class="lp-row">
                        <div class="lp-row-head">
                            <strong>Objective {{ $i + 1 }}</strong>
                            <button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button>
                        </div>
                        <div class="lp-grid">
                            <div class="lp-field lp-full">
                                <label>Title</label>
                                <input name="objectives[{{ $i }}][title]" value="{{ $objective->title }}">
                            </div>
                            <div class="lp-field lp-full">
                                <label>Description</label>
                                <textarea name="objectives[{{ $i }}][description]">{{ $objective->description }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lp-card">
            <div class="lp-row-head">
                <h3 style="margin:0;">05. Teaching Activities</h3>
                <button type="button" class="lp-add" onclick="addActivity()">+ Add Activity</button>
            </div>
            <div id="activities-container">
                @foreach($lessonPlan->activities as $i => $activity)
                    <div class="lp-row">
                        <div class="lp-row-head">
                            <strong>Activity {{ $i + 1 }}</strong>
                            <button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button>
                        </div>
                        <div class="lp-grid">
                            <div class="lp-field">
                                <label>Title</label>
                                <input name="activities[{{ $i }}][title]" value="{{ $activity->title }}">
                            </div>
                            <div class="lp-field">
                                <label>Activity Type</label>
                                <input name="activities[{{ $i }}][activity_type]" value="{{ $activity->activity_type }}">
                            </div>
                            <div class="lp-field">
                                <label>Duration (Minutes)</label>
                                <input type="number" min="0" name="activities[{{ $i }}][duration_minutes]" value="{{ $activity->duration_minutes }}">
                            </div>
                            <div class="lp-field">
                                <label>Description</label>
                                <textarea name="activities[{{ $i }}][description]">{{ $activity->description }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lp-card">
            <div class="lp-row-head">
                <h3 style="margin:0;">06. Learning Resources</h3>
                <button type="button" class="lp-add" onclick="addResource()">+ Add Resource</button>
            </div>
            <div id="resources-container">
                @foreach($lessonPlan->resources as $i => $resource)
                    <div class="lp-row">
                        <div class="lp-row-head">
                            <strong>Resource {{ $i + 1 }}</strong>
                            <button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button>
                        </div>
                        <div class="lp-grid">
                            <div class="lp-field">
                                <label>Title</label>
                                <input name="resources[{{ $i }}][title]" value="{{ $resource->title }}">
                            </div>
                            <div class="lp-field">
                                <label>Resource Type</label>
                                <input name="resources[{{ $i }}][resource_type]" value="{{ $resource->resource_type }}">
                            </div>
                            <div class="lp-field lp-full">
                                <label>Existing Attachment</label>
                                <div class="lp-hint">
                                    {{ $resource->file_path ? basename($resource->file_path) : 'No uploaded file' }}
                                    — upload a replacement only if needed.
                                </div>
                                <input type="file" name="resources[{{ $i }}][file]">
                            </div>
                            <div class="lp-field lp-full">
                                <label>External URL</label>
                                <input type="url" name="resources[{{ $i }}][external_url]" value="{{ $resource->external_url }}">
                            </div>
                            <div class="lp-field lp-full">
                                <label>Description</label>
                                <textarea name="resources[{{ $i }}][description]">{{ $resource->description }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="lp-hint">Existing uploaded files may need to be uploaded again if you replace the resource list. We'll preserve them in the next controller adjustment before testing edits.</p>
        </div>

        <div class="lp-actions">
            <a href="{{ route('lesson-plans.show', $lessonPlan) }}" class="lp-btn">Cancel</a>
            <button type="submit" class="lp-save">Save Changes</button>
        </div>
    </form>
</div>

<script>
let objectiveIndex = {{ $lessonPlan->objectives->count() }};
let activityIndex = {{ $lessonPlan->activities->count() }};
let resourceIndex = {{ $lessonPlan->resources->count() }};

function addObjective() {
    const i = objectiveIndex++;
    document.getElementById('objectives-container').insertAdjacentHTML('beforeend', `
        <div class="lp-row">
            <div class="lp-row-head"><strong>New Objective</strong><button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button></div>
            <div class="lp-grid">
                <div class="lp-field lp-full"><label>Title</label><input name="objectives[${i}][title]"></div>
                <div class="lp-field lp-full"><label>Description</label><textarea name="objectives[${i}][description]"></textarea></div>
            </div>
        </div>`);
}

function addActivity() {
    const i = activityIndex++;
    document.getElementById('activities-container').insertAdjacentHTML('beforeend', `
        <div class="lp-row">
            <div class="lp-row-head"><strong>New Activity</strong><button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button></div>
            <div class="lp-grid">
                <div class="lp-field"><label>Title</label><input name="activities[${i}][title]"></div>
                <div class="lp-field"><label>Activity Type</label><input name="activities[${i}][activity_type]"></div>
                <div class="lp-field"><label>Duration (Minutes)</label><input type="number" min="0" name="activities[${i}][duration_minutes]" value="10"></div>
                <div class="lp-field"><label>Description</label><textarea name="activities[${i}][description]"></textarea></div>
            </div>
        </div>`);
}

function addResource() {
    const i = resourceIndex++;
    document.getElementById('resources-container').insertAdjacentHTML('beforeend', `
        <div class="lp-row">
            <div class="lp-row-head"><strong>New Resource</strong><button type="button" class="lp-remove" onclick="this.closest('.lp-row').remove()">Remove</button></div>
            <div class="lp-grid">
                <div class="lp-field"><label>Title</label><input name="resources[${i}][title]"></div>
                <div class="lp-field"><label>Resource Type</label><input name="resources[${i}][resource_type]"></div>
                <div class="lp-field lp-full"><label>Upload File</label><input type="file" name="resources[${i}][file]"></div>
                <div class="lp-field lp-full"><label>External URL</label><input type="url" name="resources[${i}][external_url]"></div>
                <div class="lp-field lp-full"><label>Description</label><textarea name="resources[${i}][description]"></textarea></div>
            </div>
        </div>`);
}

const classSelect = document.getElementById('class_subject_id');
const sectionSelect = document.getElementById('section_id');

function filterSections() {
    const option = classSelect.options[classSelect.selectedIndex];
    const classId = option?.dataset.classId;

    Array.from(sectionSelect.options).forEach(section => {
        if (!section.value) {
            section.hidden = false;
            return;
        }

        section.hidden = !classId || section.dataset.classId !== classId;
        if (section.hidden && section.selected) sectionSelect.value = '';
    });
}

classSelect.addEventListener('change', filterSections);
filterSections();
</script>
@endsection
