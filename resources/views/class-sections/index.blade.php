@extends('layouts.schoolnexa')

@section('content')
<div class="cs-page">

    <div class="cs-header">
        <div>
            <h1>Class & Section Management</h1>
            <p>Manage academic classes, sections, classrooms and student capacity.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="cs-alert success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="cs-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cs-stats">
        <div class="cs-stat">
            <span>Total Classes</span>
            <strong>{{ $stats['total_classes'] }}</strong>
        </div>
        <div class="cs-stat">
            <span>Active Classes</span>
            <strong class="cs-green">{{ $stats['active_classes'] }}</strong>
        </div>
        <div class="cs-stat">
            <span>Total Sections</span>
            <strong>{{ $stats['total_sections'] }}</strong>
        </div>
        <div class="cs-stat">
            <span>Active Sections</span>
            <strong class="cs-green">{{ $stats['active_sections'] }}</strong>
        </div>
    </div>

    {{-- Class Management --}}
    <section class="cs-panel">
        <div class="cs-panel-heading">
            <div>
                <h2>Class Management</h2>
                <p>Create and manage school classes.</p>
            </div>
            <button type="button" class="cs-primary-btn" onclick="csToggle('addClassForm')">
                + Add Class
            </button>
        </div>

        <div id="addClassForm" class="cs-form-box" hidden>
            <h3>Create New Class</h3>
            @include('class-sections.class-form', ['schoolClass' => null])
        </div>

        <form method="GET" action="{{ route('class-sections.index') }}" class="cs-filters">
            <input type="text" name="search" placeholder="Search class name or code..."
                   value="{{ request('search') }}">

            <select name="school_id">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" @selected(request('school_id') == $school->id)>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="cs-filter-btn">Filter</button>
            <a href="{{ route('class-sections.index') }}" class="cs-reset">Reset</a>
        </form>

        <div class="cs-table-wrap">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Class</th>
                        <th>Code</th>
                        <th>School</th>
                        <th>Grade</th>
                        <th>Sections</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        <tr>
                            <td><strong>{{ $class->name }}</strong></td>
                            <td>{{ $class->code ?: '—' }}</td>
                            <td>{{ $class->school?->name ?? '—' }}</td>
                            <td>{{ $class->grade_level ?? '—' }}</td>
                            <td>{{ $class->sections->count() }}</td>
                            <td>
                                <span class="cs-status {{ $class->is_active ? 'active' : 'inactive' }}">
                                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="cs-outline-btn"
                                        onclick="csToggle('editClass{{ $class->id }}')">
                                    Edit
                                </button>

                                <form method="POST"
                                      action="{{ route('class-sections.classes.status', $class) }}"
                                      class="cs-inline-form">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="cs-text-btn">
                                        {{ $class->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr id="editClass{{ $class->id }}" hidden>
                            <td colspan="7">
                                <div class="cs-form-box">
                                    <h3>Edit {{ $class->name }}</h3>
                                    @include('class-sections.class-form', ['schoolClass' => $class])
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="cs-empty">No classes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="cs-pagination">{{ $classes->links() }}</div>
    </section>

    {{-- Section Management --}}
    <section class="cs-panel">
        <div class="cs-panel-heading">
            <div>
                <h2>Section Management</h2>
                <p>Manage class sections, campus allocation and classroom capacity.</p>
            </div>
            <button type="button" class="cs-primary-btn" onclick="csToggle('addSectionForm')">
                + Add Section
            </button>
        </div>

        <div id="addSectionForm" class="cs-form-box" hidden>
            <h3>Create New Section</h3>
            @include('class-sections.section-form', ['section' => null])
        </div>

        <div class="cs-table-wrap">
            <table class="cs-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Class</th>
                        <th>Campus</th>
                        <th>Room Number</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classes as $class)
                        @foreach($class->sections as $section)
                            <tr>
                                <td><strong>{{ $section->name }}</strong></td>
                                <td>{{ $class->name }}</td>
                                <td>{{ $section->campus?->name ?? '—' }}</td>
                                <td>{{ $section->room_number ?: '—' }}</td>
                                <td>{{ $section->capacity ?? '—' }}</td>
                                <td>
                                    <span class="cs-status {{ $section->is_active ? 'active' : 'inactive' }}">
                                        {{ $section->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="cs-outline-btn"
                                            onclick="csToggle('editSection{{ $section->id }}')">
                                        Edit
                                    </button>

                                    <form method="POST"
                                          action="{{ route('class-sections.sections.status', $section) }}"
                                          class="cs-inline-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="cs-text-btn">
                                            {{ $section->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <tr id="editSection{{ $section->id }}" hidden>
                                <td colspan="7">
                                    <div class="cs-form-box">
                                        <h3>Edit Section {{ $section->name }}</h3>
                                        @include('class-sections.section-form', ['section' => $section])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
function csToggle(id) {
    const element = document.getElementById(id);
    if (element) {
        element.hidden = !element.hidden;
    }
}
</script>

<style>
.cs-page{font-family:Inter,sans-serif;color:#25313C}
.cs-header{margin-bottom:22px}
.cs-header h1{font-size:23px;font-weight:700;color:#123B5D;margin:0}
.cs-header p{font-size:13px;color:#64748B;margin:6px 0 0}
.cs-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
.cs-stat,.cs-panel{background:#fff;border:1px solid #CBD5E1;border-radius:16px;padding:19px}
.cs-stat span{display:block;color:#64748B;font-size:12px;margin-bottom:8px}
.cs-stat strong{font-size:25px;color:#123B5D}
.cs-green{color:#16A34A!important}
.cs-panel{margin-bottom:20px}
.cs-panel-heading{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px}
.cs-panel-heading h2{font-size:16px;color:#123B5D;margin:0}
.cs-panel-heading p{font-size:12px;color:#64748B;margin:5px 0 0}
.cs-primary-btn{background:#123B5D;color:#fff;border:1px solid #123B5D;border-radius:9px;padding:10px 16px;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap}
.cs-outline-btn{background:#fff;color:#123B5D;border:1px solid #CBD5E1;border-radius:8px;padding:7px 11px;font-size:12px;font-weight:600;cursor:pointer}
.cs-text-btn{background:none;border:0;color:#176B87;font-size:12px;font-weight:600;cursor:pointer;padding:7px}
.cs-inline-form{display:inline-block}
.cs-filters{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:17px}
.cs-filters input,.cs-filters select,.cs-form-grid input,.cs-form-grid select{height:40px;padding:0 11px;border:1px solid #CBD5E1;border-radius:8px;background:#fff;color:#25313C;font-size:12px;width:100%;box-sizing:border-box}
.cs-filters input{flex:1;min-width:200px;width:auto}
.cs-filters select{width:auto;min-width:150px}
.cs-filter-btn{height:40px;padding:0 17px;background:#176B87;color:#fff;border:0;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer}
.cs-reset{display:flex;align-items:center;color:#64748B;font-size:12px;text-decoration:none;padding:0 8px}
.cs-table-wrap{overflow-x:auto}
.cs-table{width:100%;border-collapse:collapse;white-space:nowrap}
.cs-table th{text-align:left;background:#F8FAFC;padding:12px;color:#64748B;font-size:10px;text-transform:uppercase;letter-spacing:.04em}
.cs-table td{padding:13px 12px;border-bottom:1px solid #E2E8F0;font-size:12px}
.cs-status{display:inline-block;border-radius:20px;padding:5px 9px;font-size:10px;font-weight:600}
.cs-status.active{background:#DCFCE7;color:#15803D}
.cs-status.inactive{background:#FEE2E2;color:#B91C1C}
.cs-empty{text-align:center;color:#64748B;padding:30px!important}
.cs-pagination{margin-top:16px}
.cs-form-box{padding:17px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:12px;margin:15px 0}
.cs-form-box h3{font-size:14px;color:#123B5D;margin:0 0 15px}
.cs-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.cs-form-grid label{display:block;font-size:12px;font-weight:600;margin-bottom:7px;color:#334155}
.cs-form-grid label span{color:#DC2626}
.cs-form-actions{margin-top:17px}
.cs-error{display:block;color:#DC2626;font-size:11px;margin-top:5px}
.cs-alert{padding:12px 15px;border-radius:10px;margin-bottom:16px;font-size:12px}
.cs-alert.success{background:#DCFCE7;color:#166534}
.cs-alert.error{background:#FEE2E2;color:#991B1B}
.cs-alert ul{margin:7px 0 0;padding-left:20px}
@media(max-width:850px){.cs-stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:600px){.cs-stats,.cs-form-grid{grid-template-columns:1fr}.cs-panel-heading{align-items:flex-start;flex-direction:column}}
</style>
@endsection
