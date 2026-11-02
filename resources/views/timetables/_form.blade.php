@php
    $isEdit = isset($timetable) && $timetable;
    $initialPeriods = old('periods');
    if (!$initialPeriods && $isEdit) {
        $initialPeriods = $timetable->periods->map(function ($period) {
            $entries = [];
            foreach ($period->entries as $entry) {
                $entries[$entry->day_of_week] = [
                    'class_subject_id' => $entry->class_subject_id,
                    'staff_profile_id' => $entry->staff_profile_id,
                    'room_number' => $entry->room_number,
                ];
            }
            return [
                'period_number' => $period->period_number,
                'name' => $period->name,
                'period_type' => $period->period_type,
                'start_time' => substr((string) $period->start_time, 0, 5),
                'end_time' => substr((string) $period->end_time, 0, 5),
                'entries' => $entries,
            ];
        })->values()->all();
    }
    if (!$initialPeriods) {
        $initialPeriods = [
            ['period_number'=>1,'name'=>'Period 1','period_type'=>'teaching','start_time'=>'08:30','end_time'=>'09:10','entries'=>[]],
            ['period_number'=>2,'name'=>'Period 2','period_type'=>'teaching','start_time'=>'09:10','end_time'=>'09:50','entries'=>[]],
            ['period_number'=>3,'name'=>'Short Break','period_type'=>'break','start_time'=>'09:50','end_time'=>'10:10','entries'=>[]],
        ];
    }
@endphp

<div style="font-family:Inter,sans-serif;color:#25313C;padding:22px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:20px;">
        <div>
            <h1 style="font-size:22px;font-weight:700;color:#123B5D;margin:0;">{{ $pageTitle }}</h1>
            <p style="font-size:13px;color:#64748B;margin:5px 0 0;">Configure the weekly schedule, periods and teacher allocations.</p>
        </div>
        <a href="{{ route('timetables.index') }}" class="tt-secondary">Back to Timetables</a>
    </div>

    @if($errors->any())
        <div class="tt-error"><strong>Please correct the following:</strong>
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" id="timetableBuilderForm">
        @csrf
        @if($formMethod !== 'POST') @method($formMethod) @endif

        <section class="tt-panel">
            <h2>Basic Information</h2>
            <div class="tt-form-grid">
                <div class="tt-field"><label>Timetable Name *</label><input name="name" value="{{ old('name',$timetable->name ?? '') }}" placeholder="e.g. Class 8 A - Weekly Timetable" required></div>
                <div class="tt-field"><label>School *</label><select name="school_id" id="ttSchool" required><option value="">Select School</option>
                    @foreach($schools as $school)<option value="{{ $school->id }}" @selected(old('school_id',$timetable->school_id ?? '') == $school->id)>{{ $school->name }}</option>@endforeach
                </select></div>
                <div class="tt-field"><label>Campus *</label><select name="campus_id" id="ttCampus" required><option value="">Select Campus</option>
                    @foreach($campuses as $campus)<option value="{{ $campus->id }}" data-school="{{ $campus->school_id }}" @selected(old('campus_id',$timetable->campus_id ?? '') == $campus->id)>{{ $campus->name }}</option>@endforeach
                </select></div>
                <div class="tt-field"><label>Academic Year *</label><select name="academic_year_id" required><option value="">Select Academic Year</option>
                    @foreach($academicYears as $year)<option value="{{ $year->id }}" @selected(old('academic_year_id',$timetable->academic_year_id ?? '') == $year->id)>{{ $year->name }}</option>@endforeach
                </select></div>
                <div class="tt-field"><label>Class *</label><select name="school_class_id" id="ttClass" required><option value="">Select Class</option>
                    @foreach($classes as $class)<option value="{{ $class->id }}" data-school="{{ $class->school_id }}" @selected(old('school_class_id',$timetable->school_class_id ?? '') == $class->id)>{{ $class->name }}</option>@endforeach
                </select></div>
                <div class="tt-field"><label>Section *</label><select name="section_id" id="ttSection" required><option value="">Select Section</option>
                    @foreach($sections as $section)<option value="{{ $section->id }}" data-class="{{ $section->school_class_id }}" data-campus="{{ $section->campus_id }}" @selected(old('section_id',$timetable->section_id ?? '') == $section->id)>{{ $section->schoolClass->name ?? 'Class' }} - {{ $section->name }}</option>@endforeach
                </select></div>
                <div class="tt-field"><label>Effective From</label><input type="date" name="effective_from" value="{{ old('effective_from',isset($timetable->effective_from)?$timetable->effective_from->format('Y-m-d'):'') }}"></div>
                <div class="tt-field"><label>Effective Until</label><input type="date" name="effective_until" value="{{ old('effective_until',isset($timetable->effective_until)?$timetable->effective_until->format('Y-m-d'):'') }}"></div>
            </div>
        </section>

        <section class="tt-panel">
            <div class="tt-section-head"><div><h2>Periods & Weekly Schedule</h2><p>Add teaching periods and breaks, then assign a subject and teacher for each day.</p></div>
                <button type="button" class="tt-primary" id="addPeriodButton">+ Add Period</button>
            </div>
            <div id="periodContainer"></div>
            <div class="tt-note"><strong>Note:</strong> Break periods are excluded from subject and teacher allocation. Empty teaching slots can be assigned later.</div>
        </section>

        <div class="tt-footer"><a href="{{ route('timetables.index') }}" class="tt-secondary">Cancel</a><button class="tt-primary" type="submit">{{ $submitLabel }}</button></div>
    </form>
</div>

<style>
.tt-panel{background:#fff;border:1px solid #CBD5E1;border-radius:14px;padding:20px;margin-bottom:18px}
.tt-panel h2{font:650 16px Inter,sans-serif;color:#123B5D;margin:0 0 16px}
.tt-section-head{display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:15px}
.tt-section-head h2{margin:0}.tt-section-head p{font:400 12px Inter,sans-serif;color:#64748B;margin:5px 0 0}
.tt-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.tt-field label{display:block;font:600 12px Inter,sans-serif;margin-bottom:7px}
.tt-field input,.tt-field select,.tt-period-field input,.tt-period-field select,.tt-day-card select,.tt-day-card input{width:100%;height:40px;box-sizing:border-box;border:1px solid #CBD5E1;border-radius:8px;padding:0 10px;font:400 13px Inter,sans-serif;color:#25313C;background:#fff}
.tt-period{border:1px solid #CBD5E1;border-radius:11px;padding:15px;margin-bottom:12px}
.tt-period-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:13px}
.tt-period-title{font:650 13px Inter,sans-serif;color:#123B5D}
.tt-period-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:11px}
.tt-period-field label{display:block;font:600 12px Inter,sans-serif;color:#64748B;margin-bottom:6px}
.tt-day-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-top:14px}
.tt-day-card{border:1px solid #E2E8F0;border-radius:9px;padding:11px;background:#F8FAFC}
.tt-day-card strong{display:block;font:650 12px Inter,sans-serif;color:#123B5D;margin-bottom:7px}
.tt-day-card input,.tt-day-card select{height:35px;margin-top:6px;font-size:12px}
.tt-primary,.tt-secondary,.tt-remove{display:inline-flex;align-items:center;justify-content:center;min-height:40px;padding:0 14px;border-radius:9px;font:600 13px Inter,sans-serif;text-decoration:none;cursor:pointer;white-space:nowrap}
.tt-primary{background:#123B5D;color:#fff;border:1px solid #123B5D}.tt-secondary{background:#fff;color:#123B5D;border:1px solid #CBD5E1}
.tt-remove{min-height:32px;padding:0 10px;background:#FEF2F2;color:#DC2626;border:1px solid #FECACA;font-size:12px}
.tt-note{padding:12px 14px;border:1px solid #E2E8F0;background:#F8FAFC;border-radius:9px;color:#64748B;font:400 12px/1.6 Inter,sans-serif}
.tt-footer{display:flex;justify-content:flex-end;gap:9px;margin:18px 0 5px}
.tt-error{padding:13px 16px;margin-bottom:16px;border:1px solid #FECACA;background:#FEF2F2;color:#B91C1C;border-radius:10px;font-size:13px}
@media(max-width:850px){.tt-period-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.tt-day-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.tt-form-grid,.tt-period-grid,.tt-day-grid{grid-template-columns:1fr}.tt-panel{padding:14px}}
</style>

<script>
const ttDays=['monday','tuesday','wednesday','thursday','friday','saturday'];
const ttSubjects={{ Illuminate\Support\Js::from($classSubjects->map(fn($x)=>['id'=>$x->id,'class_id'=>$x->school_class_id,'name'=>$x->subject->name ?? 'Subject'])->values()) }};
const ttTeachers={{ Illuminate\Support\Js::from($teachers->map(fn($x)=>['id'=>$x->id,'name'=>trim($x->first_name.' '.$x->last_name)])->values()) }};
const ttInitial={{ Illuminate\Support\Js::from($initialPeriods) }};
const ttContainer=document.getElementById('periodContainer');let ttIndex=0;
function ttEsc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
function ttTime(m){return String(Math.floor(m/60)).padStart(2,'0')+':'+String(m%60).padStart(2,'0');}
function ttSubjectsHtml(selected=''){let h='<option value="">Select Subject</option>';ttSubjects.filter(s=>String(s.class_id)===String(document.getElementById('ttClass').value)).forEach(s=>h+=`<option value="${s.id}" ${String(selected)===String(s.id)?'selected':''}>${ttEsc(s.name)}</option>`);return h;}
function ttTeachersHtml(selected=''){let h='<option value="">Select Teacher</option>';ttTeachers.forEach(t=>h+=`<option value="${t.id}" ${String(selected)===String(t.id)?'selected':''}>${ttEsc(t.name)}</option>`);return h;}
function ttAddPeriod(data={}){
 const i=ttIndex++,n=i+1,type=data.period_type||'teaching';let cards='';
 ttDays.forEach(day=>{const e=(data.entries||{})[day]||{};cards+=`<div class="tt-day-card"><strong>${day[0].toUpperCase()+day.slice(1)}</strong>
 <select name="periods[${i}][entries][${day}][class_subject_id]" class="tt-subject">${ttSubjectsHtml(e.class_subject_id)}</select>
 <select name="periods[${i}][entries][${day}][staff_profile_id]">${ttTeachersHtml(e.staff_profile_id)}</select>
 <input name="periods[${i}][entries][${day}][room_number]" value="${ttEsc(e.room_number||'')}" placeholder="Room (optional)"></div>`;});
 ttContainer.insertAdjacentHTML('beforeend',`<div class="tt-period"><div class="tt-period-header"><strong class="tt-period-title">Period ${n}</strong><button type="button" class="tt-remove" onclick="this.closest('.tt-period').remove();ttRenumber()">Remove</button></div>
 <input type="hidden" name="periods[${i}][period_number]" value="${data.period_number||n}">
 <div class="tt-period-grid">
 <div class="tt-period-field"><label>Period Name *</label><input name="periods[${i}][name]" value="${ttEsc(data.name||'Period '+n)}" required></div>
 <div class="tt-period-field"><label>Period Type *</label><select name="periods[${i}][period_type]" onchange="ttToggle(this)"><option value="teaching" ${type==='teaching'?'selected':''}>Teaching</option><option value="break" ${type==='break'?'selected':''}>Break</option></select></div>
 <div class="tt-period-field"><label>Start Time *</label><input type="time" name="periods[${i}][start_time]" value="${ttEsc(data.start_time||ttTime(510+(n-1)*50))}" required></div>
 <div class="tt-period-field"><label>End Time *</label><input type="time" name="periods[${i}][end_time]" value="${ttEsc(data.end_time||ttTime(550+(n-1)*50))}" required></div></div>
 <div class="tt-day-grid">${cards}</div></div>`);
 if(type==='break')ttToggle(ttContainer.lastElementChild.querySelector('select[name$="[period_type]"]'));
}
function ttToggle(el){const g=el.closest('.tt-period').querySelector('.tt-day-grid'),off=el.value==='break';g.style.display=off?'none':'grid';g.querySelectorAll('input,select').forEach(x=>x.disabled=off);}
function ttRenumber(){ttContainer.querySelectorAll('.tt-period').forEach((p,i)=>{p.querySelector('.tt-period-title').textContent='Period '+(i+1);p.querySelector('input[type=hidden]').value=i+1;});}
function ttFilter(){const c=document.getElementById('ttCampus').value,k=document.getElementById('ttClass').value;document.querySelectorAll('#ttSection option').forEach(o=>{if(o.value)o.hidden=o.dataset.class!==k||o.dataset.campus!==c;});}
function ttRefreshSubjects(){ttContainer.querySelectorAll('.tt-subject').forEach(s=>{const v=s.value;s.innerHTML=ttSubjectsHtml(v);});}
document.getElementById('addPeriodButton').addEventListener('click',()=>ttAddPeriod());
document.getElementById('ttSchool').addEventListener('change',function(){document.querySelectorAll('#ttCampus option').forEach(o=>{if(o.value)o.hidden=o.dataset.school!==this.value;});document.getElementById('ttCampus').value='';document.getElementById('ttClass').value='';document.getElementById('ttSection').value='';ttFilter();ttRefreshSubjects();});
document.getElementById('ttCampus').addEventListener('change',()=>{document.getElementById('ttSection').value='';ttFilter();});
document.getElementById('ttClass').addEventListener('change',()=>{document.getElementById('ttSection').value='';ttFilter();ttRefreshSubjects();});
ttInitial.forEach(ttAddPeriod);ttFilter();
document.getElementById('timetableBuilderForm').addEventListener('submit',e=>{if(!ttContainer.querySelector('.tt-period')){e.preventDefault();alert('Please add at least one period.');}});
</script>
