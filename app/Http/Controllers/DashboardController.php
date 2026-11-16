<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Main dashboard. Keeps the existing Super Admin dashboard view intact.
     * Other roles are sent to their own role dashboard.
     */
    public function index()
    {
        $role = $this->activeRoleCode();

        $roleViews = [
            'PRINCIPAL' => 'dashboards.principal',
            'SCHOOL_ADMIN' => 'dashboards.admin',
            'STUDENT' => 'dashboards.student',
            'TEACHER' => 'dashboards.teacher',
            'PARENT' => 'dashboards.parent',
        ];

        if (isset($roleViews[$role])) {
            return $this->renderRoleDashboard($role);
        }

        // Existing Super Admin dashboard data contract is preserved.
        $stats = [
            'organizations' => $this->count('organizations'),
            'schools' => $this->count('schools'),
            'campuses' => $this->count('campuses'),
            'users' => $this->count('users'),
            'students' => $this->count('student_profiles'),
            'staff' => $this->count('staff_profiles'),
            'admissions' => $this->count('admission_leads'),
            'attendance' => $this->count('student_attendance'),
            'payments' => $this->count('fee_payments'),
            'vehicles' => $this->count('transport_vehicles'),
            'hostel_rooms' => $this->count('hostel_rooms'),
            'books' => $this->count('library_books'),
            'safety_cases' => $this->count('safeguarding_cases'),
            'events' => $this->count('events'),
        ];

        $organizationSchoolData = collect();
        if (Schema::hasTable('organizations') && Schema::hasTable('schools')) {
            $organizationSchoolData = DB::table('organizations')
                ->leftJoin('schools', 'schools.organization_id', '=', 'organizations.id')
                ->select('organizations.name', DB::raw('COUNT(schools.id) as total'))
                ->groupBy('organizations.id', 'organizations.name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();
        }

        $userGrowth = [];
        if (Schema::hasTable('users')) {
            for ($i = 5; $i >= 0; $i--) {
                $month = now()->copy()->subMonths($i);
                $userGrowth[] = [
                    'label' => $month->format('M'),
                    'total' => DB::table('users')
                        ->whereYear('created_at', $month->year)
                        ->whereMonth('created_at', $month->month)
                        ->count(),
                ];
            }
        }

        $user = auth()->user();
        $modules = [
            ['name' => 'Organizations', 'permission' => 'ORGANIZATIONS.VIEW', 'icon' => 'building'],
            ['name' => 'Schools', 'permission' => 'SCHOOLS.VIEW', 'icon' => 'school'],
            ['name' => 'Campuses', 'permission' => 'CAMPUSES.VIEW', 'icon' => 'map'],
            ['name' => 'Users & Roles', 'permission' => 'USERS.VIEW', 'icon' => 'user'],
            ['name' => 'Students', 'permission' => 'STUDENTS.VIEW', 'icon' => 'students'],
            ['name' => 'Admissions', 'permission' => 'ADMISSIONS.VIEW', 'icon' => 'admissions'],
            ['name' => 'Finance', 'permission' => 'FINANCE.VIEW', 'icon' => 'finance'],
            ['name' => 'HR & Payroll', 'permission' => 'HR.VIEW', 'icon' => 'staff'],
            ['name' => 'Transport', 'permission' => 'TRANSPORT.VIEW', 'icon' => 'transport'],
            ['name' => 'Hostel', 'permission' => 'HOSTEL.VIEW', 'icon' => 'hostel'],
            ['name' => 'Library', 'permission' => 'LIBRARY.VIEW', 'icon' => 'library'],
            ['name' => 'Reports', 'permission' => 'REPORTS.VIEW', 'icon' => 'reports'],
            ['name' => 'Settings', 'permission' => 'SETTINGS.VIEW', 'icon' => 'settings'],
        ];

        $modules = array_values(array_filter($modules, function ($module) use ($user) {
            return $user && method_exists($user, 'hasPermission')
                ? $user->hasPermission($module['permission'])
                : false;
        }));

        // Operational dashboard widgets: every metric is read from existing records.
        $attendanceStats = [
            'students' => ['present' => 0, 'absent' => 0, 'late' => 0, 'emergency' => 0],
            'teachers' => ['present' => 0, 'absent' => 0, 'late' => 0, 'emergency' => 0],
            'staff' => ['present' => 0, 'absent' => 0, 'late' => 0, 'emergency' => 0],
        ];
        if (Schema::hasTable('student_attendance') && Schema::hasTable('attendance_sessions')) {
            $todayAttendance = DB::table('student_attendance')
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'student_attendance.attendance_session_id')
                ->whereDate('attendance_sessions.attendance_date', today());
            foreach (['present', 'absent', 'late'] as $status) {
                $attendanceStats['students'][$status] = (clone $todayAttendance)
                    ->whereRaw('LOWER(student_attendance.status) = ?', [$status])->count();
            }
        }
        if (Schema::hasTable('employee_attendance') && Schema::hasTable('staff_profiles')) {
            $staffToday = DB::table('employee_attendance')
                ->join('staff_profiles', 'staff_profiles.id', '=', 'employee_attendance.staff_profile_id')
                ->whereDate('employee_attendance.attendance_date', today());
            foreach (['present', 'absent', 'late'] as $status) {
                $count = (clone $staffToday)->whereRaw('LOWER(employee_attendance.status) = ?', [$status])->count();
                $attendanceStats['staff'][$status] = $count;
                $attendanceStats['teachers'][$status] = $count; // refined when teacher-only attendance is available
            }
        }

        $libraryStats = ['total_titles' => $this->count('library_books'), 'available' => 0, 'issued' => 0, 'reserved' => 0, 'low_stock' => 0];
        $topBooks = collect();
        if (Schema::hasTable('library_copies')) {
            $copyStatuses = DB::table('library_copies')->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->get();
            foreach ($copyStatuses as $statusRow) {
                $status = strtolower((string) $statusRow->status);
                if (in_array($status, ['available', 'issued', 'reserved'], true)) $libraryStats[$status] = (int) $statusRow->total;
            }
            if (Schema::hasTable('library_loans')) {
                $topBooks = DB::table('library_loans')->join('library_copies', 'library_copies.id', '=', 'library_loans.library_copy_id')
                    ->join('library_books', 'library_books.id', '=', 'library_copies.library_book_id')
                    ->select('library_books.title', 'library_books.author', DB::raw('COUNT(library_loans.id) AS borrows'))
                    ->groupBy('library_books.id', 'library_books.title', 'library_books.author')
                    ->orderByDesc('borrows')->limit(5)->get();
            }
        }
        if (Schema::hasTable('inventory_items')) {
            $libraryStats['low_stock'] = DB::table('inventory_items')->whereColumn('quantity_on_hand', '<=', 'reorder_level')->count();
        }

        $fleetStats = ['total' => 0, 'active' => 0, 'idle' => 0, 'service' => 0, 'offline' => 0, 'trips' => 0, 'distance' => 0, 'occupancy' => 0, 'on_time' => 0];
        $routeStatus = collect();
        if (Schema::hasTable('transport_vehicles')) {
            $fleetStats['total'] = DB::table('transport_vehicles')->count();
            $vehicleStatuses = DB::table('transport_vehicles')->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->get();
            foreach ($vehicleStatuses as $vehicleStatus) {
                $status = strtolower((string) $vehicleStatus->status);
                if (in_array($status, ['active', 'running'], true)) $fleetStats['active'] += (int) $vehicleStatus->total;
                elseif (in_array($status, ['maintenance', 'service', 'under_maintenance'], true)) $fleetStats['service'] += (int) $vehicleStatus->total;
                elseif (in_array($status, ['offline', 'inactive'], true)) $fleetStats['offline'] += (int) $vehicleStatus->total;
                else $fleetStats['idle'] += (int) $vehicleStatus->total;
            }
            $routeStatus = DB::table('transport_vehicles')->orderBy('registration_number')->limit(6)
                ->get(['registration_number', 'vehicle_type', 'status'])
                ->map(fn ($v) => ['name' => $v->registration_number, 'status' => strtolower((string) $v->status), 'onboard' => '—', 'eta' => '—', 'speed' => '—']);
        }

        $hostelStats = ['occupied' => 0, 'available' => 0, 'reserved' => 0, 'maintenance' => 0];
        $hostelBlocks = collect();
        if (Schema::hasTable('hostels') && Schema::hasTable('hostel_rooms') && Schema::hasTable('hostel_beds')) {
            $hostelBlocks = DB::table('hostels')->leftJoin('hostel_rooms', 'hostel_rooms.hostel_id', '=', 'hostels.id')
                ->leftJoin('hostel_beds', 'hostel_beds.hostel_room_id', '=', 'hostel_rooms.id')
                ->select('hostels.id', 'hostels.name', DB::raw("SUM(CASE WHEN LOWER(hostel_beds.status) = 'occupied' THEN 1 ELSE 0 END) AS occupied"),
                    DB::raw('COUNT(hostel_beds.id) AS total_beds'))
                ->groupBy('hostels.id', 'hostels.name')->orderBy('hostels.name')->get()
                ->map(fn ($h) => ['name' => $h->name, 'pct' => (int) $h->total_beds > 0 ? round((int) $h->occupied * 100 / (int) $h->total_beds, 1) : 0]);
            $bedRows = DB::table('hostel_beds')->select('status', DB::raw('COUNT(*) AS total'))->groupBy('status')->get();
            foreach ($bedRows as $bedRow) {
                $status = strtolower((string) $bedRow->status);
                if (array_key_exists($status, $hostelStats)) $hostelStats[$status] = (int) $bedRow->total;
            }
            // Active hostel allocations are the authoritative occupied-bed count.
            if (Schema::hasTable('hostel_allocations')) {
                $hostelStats['occupied'] = DB::table('hostel_allocations')->where('status', 'active')->count();
            }
        }

        $mapPoints = collect();
        if (Schema::hasTable('campuses')) {
            $mapPoints = DB::table('campuses')->whereNotNull('latitude')->whereNotNull('longitude')
                ->get(['name', 'latitude', 'longitude'])->map(fn ($c) => [
                    'name' => $c->name, 'lat' => (float) $c->latitude, 'lng' => (float) $c->longitude,
                    'type' => 'campus', 'status' => 'live', 'route' => 'Campus location',
                ]);
        }

        $resolveDashboardUrl = fn (array $names, string $fallback) => collect($names)
            ->first(fn ($name) => \Illuminate\Support\Facades\Route::has($name))
            ? route(collect($names)->first(fn ($name) => \Illuminate\Support\Facades\Route::has($name)))
            : url($fallback);
        $calendarUrl = $resolveDashboardUrl(['events.calendar', 'events.index', 'events.dashboard', 'caspa.events.index'], '/events');
        $aiChatUrl = $resolveDashboardUrl(['caspa.chat.index', 'caspa-chat.index', 'chat.index', 'caspa.chat', 'ai.chat'], '/caspa-chat');
        $hostelUrl = $resolveDashboardUrl(['hostels.index', 'hostel.dashboard', 'hostels.dashboard', 'hostel.index'], '/hostels');
        $hostelAllocationUrl = $resolveDashboardUrl(['hostel.allocations.index', 'hostel-allocations.index', 'hostels.allocations', 'hostel.allocations'], '/hostel-allocations');

        return view('dashboard', compact(
            'stats', 'modules', 'organizationSchoolData', 'userGrowth', 'attendanceStats',
            'libraryStats', 'topBooks', 'fleetStats', 'routeStatus', 'hostelStats',
            'hostelBlocks', 'mapPoints', 'calendarUrl', 'aiChatUrl', 'hostelUrl', 'hostelAllocationUrl'
        ));
    }

    public function principal()
    {
        return $this->renderRoleDashboard('PRINCIPAL');
    }

    public function admin()
    {
        return $this->renderRoleDashboard('SCHOOL_ADMIN');
    }

    public function student()
    {
        return $this->renderRoleDashboard('STUDENT');
    }

    public function teacher()
    {
        return $this->renderRoleDashboard('TEACHER');
    }

    public function parent()
    {
        return $this->renderRoleDashboard('PARENT');
    }

    private function renderRoleDashboard(string $requiredRole)
    {
        abort_unless(auth()->check(), 401);

        $roles = $this->activeRoleCodes();
        abort_unless(in_array($requiredRole, $roles, true), 403, 'You are not authorized to view this dashboard.');

        $user = auth()->user();
        $schoolId = $this->activeSchoolId();
        $campusId = $this->activeCampusId();

        $stats = [
            'students' => 0,
            'teachers' => 0,
            'staff' => 0,
            'attendance' => 0,
            'pending_approvals' => 0,
            'admissions' => 0,
            'assignments_due' => 0,
            'completed_assignments' => 0,
            'upcoming_exams' => 0,
            'assigned_classes' => 0,
            'today_periods' => 0,
            'pending_reviews' => 0,
            'children' => 0,
            'pending_fees' => 0,
            'unread_notices' => 0,
        ];

        $recentItems = collect();
        $upcomingItems = collect();

        if ($requiredRole === 'PRINCIPAL' || $requiredRole === 'SCHOOL_ADMIN') {
            $filters = request()->validate([
                'academic_year_id' => ['nullable', 'integer'],
                'campus_id' => ['nullable', 'integer'],
            ]);

            $selectedCampus = !empty($filters['campus_id']) ? (int) $filters['campus_id'] : $campusId;
            if ($selectedCampus && Schema::hasTable('campuses')) {
                $validCampus = DB::table('campuses')->where('id', $selectedCampus)
                    ->where('school_id', $schoolId)->exists();
                if (!$validCampus) $selectedCampus = $campusId;
            }

            $academicYearId = !empty($filters['academic_year_id']) ? (int) $filters['academic_year_id'] : null;
            if ($academicYearId && Schema::hasTable('academic_years')) {
                $validYear = DB::table('academic_years')->where('id', $academicYearId)
                    ->where('school_id', $schoolId)->exists();
                if (!$validYear) $academicYearId = null;
            }

            $stats = array_merge($stats, $this->principalStats($schoolId, $selectedCampus, $academicYearId));
            $analytics = $this->principalAnalytics($schoolId, $selectedCampus, $academicYearId);
            $campuses = $this->availableCampuses($schoolId);
            $academicYears = $this->availableAcademicYears($schoolId);
            $selectedCampusId = $selectedCampus;
            $selectedAcademicYearId = $academicYearId;
            $recentItems = $this->recentAnnouncements($schoolId, $selectedCampus);
            $upcomingItems = $this->upcomingEvents($schoolId, $selectedCampus);

            if ($requiredRole === 'PRINCIPAL') {
                return view('dashboards.principal', array_merge(
                    compact('stats', 'recentItems', 'upcomingItems', 'campuses', 'academicYears',
                        'selectedCampusId', 'selectedAcademicYearId'),
                    $analytics
                ));
            }
        }

        if ($requiredRole === 'TEACHER') {
            $teacherProfile = Schema::hasTable('staff_profiles')
                ? DB::table('staff_profiles')->where('user_id', $user->id)->whereNull('deleted_at')->first()
                : null;

            $teacherAssignments = collect();
            $teacherClasses = collect();
            $todayTimetable = collect();
            $teacherAttendance = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0];
            $attendanceTrend = collect();
            $teacherAssignmentList = collect();
            $teacherExams = collect();
            $classPerformance = collect();
            $gradeDistribution = collect();
            $teacherLessonPlans = collect();
            $teacherAnnouncements = collect();
            $teacherEvents = collect();
            $teacherTasks = collect();
            $teacherProfileName = $teacherProfile
                ? trim(($teacherProfile->first_name ?? '') . ' ' . ($teacherProfile->last_name ?? ''))
                : ($user->name ?? 'Teacher');

            if ($teacherProfile) {
                // The teaching allocation table is the source of truth for this teacher's classes.
                if (Schema::hasTable('teaching_assignments')) {
                    $allocationQuery = DB::table('teaching_assignments as ta')
                        ->where('ta.staff_profile_id', $teacherProfile->id);
                    if (Schema::hasColumn('teaching_assignments', 'academic_year_id') && Schema::hasTable('academic_years')) {
                        $activeYearId = DB::table('academic_years')->where('school_id', $schoolId)
                            ->where('status', 'active')->value('id');
                        if ($activeYearId) $allocationQuery->where('ta.academic_year_id', $activeYearId);
                    }
                    $teacherAssignments = (clone $allocationQuery)->get();

                    if (Schema::hasTable('class_subjects') && Schema::hasTable('school_classes') && Schema::hasTable('sections')) {
                        $classQuery = DB::table('teaching_assignments as ta')
                            ->join('class_subjects as cs', 'cs.id', '=', 'ta.class_subject_id')
                            ->join('school_classes as sc', 'sc.id', '=', 'cs.school_class_id')
                            ->join('sections as sec', 'sec.id', '=', 'ta.section_id')
                            ->leftJoin('subjects as sub', 'sub.id', '=', 'cs.subject_id')
                            ->where('ta.staff_profile_id', $teacherProfile->id);
                        if ($schoolId && Schema::hasColumn('school_classes', 'school_id')) $classQuery->where('sc.school_id', $schoolId);
                        if ($teacherAssignments->isNotEmpty() && Schema::hasColumn('teaching_assignments', 'academic_year_id')) {
                            $yearIds = $teacherAssignments->pluck('academic_year_id')->filter()->unique()->values();
                            if ($yearIds->isNotEmpty()) $classQuery->whereIn('ta.academic_year_id', $yearIds);
                        }
                        $teacherClasses = $classQuery->select(
                            'ta.id as allocation_id', 'ta.academic_year_id', 'ta.section_id',
                            'cs.id as class_subject_id', 'cs.subject_id', 'sc.id as class_id',
                            'sc.name as class_name', 'sec.name as section_name',
                            'sub.name as subject_name'
                        )->distinct()->orderBy('sc.name')->orderBy('sec.name')->get();
                    }
                }

                $sectionIds = $teacherClasses->pluck('section_id')->filter()->unique()->values();
                $classIds = $teacherClasses->pluck('class_id')->filter()->unique()->values();
                $subjectIds = $teacherClasses->pluck('subject_id')->filter()->unique()->values();
                $classSubjectIds = $teacherClasses->pluck('class_subject_id')->filter()->unique()->values();

                $stats['assigned_classes'] = $teacherClasses->count();
                if (Schema::hasTable('student_enrollments') && $sectionIds->isNotEmpty()) {
                    $studentQuery = DB::table('student_enrollments')
                        ->whereIn('student_enrollments.section_id', $sectionIds)
                        ->where('student_enrollments.status', 'active');
                    if ($schoolId && Schema::hasTable('student_profiles')) {
                        $studentQuery->join('student_profiles as sp', 'sp.id', '=', 'student_enrollments.student_id')
                            ->where('sp.school_id', $schoolId)->whereNull('sp.deleted_at');
                    }
                    $stats['students'] = (clone $studentQuery)->distinct('student_enrollments.student_id')->count('student_enrollments.student_id');
                }

                // Timetable rows are linked to the teacher either directly or through teaching allocation.
                if (Schema::hasTable('timetable_entries')) {
                    $tt = DB::table('timetable_entries as te');
                    $hasTeacherColumn = Schema::hasColumn('timetable_entries', 'staff_profile_id');
                    $hasAllocationColumn = Schema::hasColumn('timetable_entries', 'teaching_assignment_id');
                    if ($hasTeacherColumn) {
                        $tt->where('te.staff_profile_id', $teacherProfile->id);
                    } elseif ($hasAllocationColumn && $teacherAssignments->isNotEmpty()) {
                        $tt->whereIn('te.teaching_assignment_id', $teacherAssignments->pluck('id'));
                    } else {
                        $tt = null;
                    }

                    if ($tt) {
                        if (Schema::hasColumn('timetable_entries', 'timetable_id') && Schema::hasTable('timetables')) {
                            $tt->leftJoin('timetables as tm', 'tm.id', '=', 'te.timetable_id');
                            if ($schoolId && Schema::hasColumn('timetables', 'school_id')) $tt->where('tm.school_id', $schoolId);
                            if ($campusId && Schema::hasColumn('timetables', 'campus_id')) $tt->where('tm.campus_id', $campusId);
                            if (Schema::hasColumn('timetables', 'status')) $tt->whereIn('tm.status', ['published', 'active']);
                        }
                        $ttRows = $tt->limit(250)->get(['te.*']);
                        $todayName = strtolower(now()->format('l'));
                        $todayNumber = (string) now()->dayOfWeekIso;
                        $todayTimetable = $ttRows->filter(function ($row) use ($todayName, $todayNumber) {
                            foreach (['day_of_week', 'weekday', 'day'] as $column) {
                                if (isset($row->{$column})) {
                                    $value = strtolower(trim((string) $row->{$column}));
                                    return $value === $todayName || $value === strtolower(substr($todayName, 0, 3)) || $value === $todayNumber;
                                }
                            }
                            return true;
                        })->values()->map(function ($row) use ($teacherClasses) {
                            $allocation = null;
                            if (isset($row->teaching_assignment_id)) $allocation = $teacherClasses->firstWhere('allocation_id', $row->teaching_assignment_id);
                            $class = $allocation->class_name ?? ($row->class_name ?? 'Assigned class');
                            $section = $allocation->section_name ?? ($row->section_name ?? '');
                            $subject = $allocation->subject_name ?? ($row->subject_name ?? 'Teaching period');
                            $start = $row->start_time ?? $row->starts_at ?? $row->from_time ?? null;
                            $finish = $row->end_time ?? $row->ends_at ?? $row->to_time ?? null;
                            return (object) [
                                'class_name' => trim($class . ' ' . $section),
                                'subject_name' => $subject,
                                'start_time' => $start,
                                'end_time' => $finish,
                                'room' => $row->room_number ?? $row->room ?? '—',
                                'status' => 'Scheduled',
                            ];
                        })->sortBy('start_time')->values();
                        $stats['today_periods'] = $todayTimetable->count();
                    }
                }

                // Assignment visibility is restricted to columns that identify the assigned teacher.
                if (Schema::hasTable('assignments')) {
                    $assignmentTeacherColumn = collect(['staff_profile_id', 'teacher_id', 'created_by'])
                        ->first(fn ($column) => Schema::hasColumn('assignments', $column));
                    if ($assignmentTeacherColumn) {
                        $assignmentQuery = DB::table('assignments')->where($assignmentTeacherColumn, $assignmentTeacherColumn === 'created_by' ? $user->id : $teacherProfile->id);
                        if ($schoolId && Schema::hasColumn('assignments', 'school_id')) $assignmentQuery->where('school_id', $schoolId);
                        $assignmentRows = (clone $assignmentQuery)->latest('id')->limit(30)->get();
                        $teacherAssignmentList = $assignmentRows->map(function ($row) {
                            return (object) [
                                'title' => $row->title ?? $row->name ?? 'Assignment',
                                'class_name' => $row->class_name ?? '—',
                                'due_date' => $row->due_date ?? $row->submission_deadline ?? $row->end_date ?? null,
                                'status' => $row->status ?? 'Published',
                                'id' => $row->id,
                            ];
                        });
                        $stats['assignments_created'] = $assignmentRows->count();
                        if (Schema::hasTable('assignment_submissions')) {
                            $submissionQuery = DB::table('assignment_submissions')->whereIn('assignment_id', $assignmentRows->pluck('id'));
                            $stats['pending_reviews'] = Schema::hasColumn('assignment_submissions', 'reviewed_at')
                                ? (clone $submissionQuery)->whereNull('reviewed_at')->count()
                                : (Schema::hasColumn('assignment_submissions', 'status') ? (clone $submissionQuery)->whereIn('status', ['submitted', 'pending', 'under_review'])->count() : 0);
                        }
                        $upcomingItems = $teacherAssignmentList->filter(fn ($item) => $item->due_date && \Carbon\Carbon::parse($item->due_date)->greaterThanOrEqualTo(today()))->take(6)->values();
                        $recentItems = $teacherAssignmentList->take(8)->values();
                    }
                }

                // Attendance is limited to the teacher's assigned sections.
                if ($sectionIds->isNotEmpty() && Schema::hasTable('attendance_sessions') && Schema::hasTable('student_attendance')) {
                    $attendanceQuery = DB::table('student_attendance as sa')
                        ->join('attendance_sessions as ats', 'ats.id', '=', 'sa.attendance_session_id')
                        ->whereIn('ats.section_id', $sectionIds);
                    if ($schoolId) $attendanceQuery->where('ats.school_id', $schoolId);
                    $todayAttendance = (clone $attendanceQuery)->whereDate('ats.attendance_date', today());
                    foreach (['present', 'absent', 'late'] as $status) {
                        $teacherAttendance[$status] = (clone $todayAttendance)->whereRaw('LOWER(sa.status) = ?', [$status])->count();
                    }
                    $teacherAttendance['total'] = (clone $todayAttendance)->count();
                    $teacherAttendance['rate'] = $teacherAttendance['total'] > 0
                        ? round(($teacherAttendance['present'] / $teacherAttendance['total']) * 100, 1) : 0;
                    $stats['attendance'] = $teacherAttendance['rate'];

                    for ($i = 6; $i >= 0; $i--) {
                        $date = today()->subDays($i);
                        $dayQuery = (clone $attendanceQuery)->whereDate('ats.attendance_date', $date);
                        $total = (clone $dayQuery)->count();
                        $present = (clone $dayQuery)->whereRaw('LOWER(sa.status) = ?', ['present'])->count();
                        $attendanceTrend->push((object) [
                            'label' => $date->format('D'),
                            'date' => $date->format('Y-m-d'),
                            'present' => $present,
                            'total' => $total,
                            'rate' => $total ? round($present / $total * 100, 1) : 0,
                        ]);
                    }
                }

                // Examination results are scoped to the sections this teacher teaches.
                if ($sectionIds->isNotEmpty() && Schema::hasTable('exam_subjects') && Schema::hasTable('examinations')) {
                    $examQuery = DB::table('exam_subjects as es')
                        ->join('examinations as ex', 'ex.id', '=', 'es.examination_id')
                        ->whereIn('es.section_id', $sectionIds);
                    if ($schoolId) $examQuery->where('ex.school_id', $schoolId);
                    $teacherExams = (clone $examQuery)->orderBy('es.exam_date')->limit(8)
                        ->get(['es.id', 'es.exam_date', 'es.start_time', 'es.maximum_marks', 'ex.name as examination_name', 'ex.status']);
                    if (Schema::hasTable('exam_results')) {
                        $results = DB::table('exam_results as er')
                            ->join('exam_subjects as es', 'es.id', '=', 'er.exam_subject_id')
                            ->whereIn('es.section_id', $sectionIds)
                            ->whereNotNull('er.marks_obtained')->get(['er.marks_obtained', 'er.grade', 'es.maximum_marks']);
                        if ($results->isNotEmpty()) {
                            $classPerformance = collect([ (object) [
                                'label' => 'My assigned sections',
                                'average' => round($results->avg(fn ($r) => $r->maximum_marks > 0 ? ($r->marks_obtained / $r->maximum_marks) * 100 : 0), 1),
                                'results' => $results->count(),
                            ]]);
                            $gradeDistribution = $results->groupBy(fn ($r) => $r->grade ?: 'Ungraded')
                                ->map(fn ($items, $grade) => (object) ['grade' => $grade, 'count' => $items->count()])->values();
                        }
                    }
                }

                // Lesson plans can be keyed by staff profile or by the authenticated creator.
                if (Schema::hasTable('lesson_plans')) {
                    $lessonTeacherColumn = collect(['staff_profile_id', 'teacher_id', 'created_by', 'user_id'])
                        ->first(fn ($column) => Schema::hasColumn('lesson_plans', $column));
                    if ($lessonTeacherColumn) {
                        $lessonQuery = DB::table('lesson_plans')->where($lessonTeacherColumn, in_array($lessonTeacherColumn, ['created_by', 'user_id'], true) ? $user->id : $teacherProfile->id);
                        if ($schoolId && Schema::hasColumn('lesson_plans', 'school_id')) $lessonQuery->where('school_id', $schoolId);
                        $teacherLessonPlans = $lessonQuery->latest('id')->limit(6)->get();
                    }
                }

                if (Schema::hasTable('announcements')) {
                    $annQuery = DB::table('announcements')->where('school_id', $schoolId)
                        ->whereIn('status', ['published', 'active'])
                        ->where(function ($q) { $q->whereNull('published_at')->orWhere('published_at', '<=', now()); });
                    if (Schema::hasColumn('announcements', 'expires_at')) $annQuery->where(function ($q) { $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()); });
                    if ($campusId && Schema::hasColumn('announcements', 'campus_id')) $annQuery->where(function ($q) use ($campusId) { $q->whereNull('campus_id')->orWhere('campus_id', $campusId); });
                    $teacherAnnouncements = $annQuery->latest('published_at')->limit(5)->get();
                }
                if (Schema::hasTable('events')) {
                    $eventQuery = DB::table('events')->where('school_id', $schoolId)
                        ->whereDate('starts_at', '>=', today());
                    if ($campusId && Schema::hasColumn('events', 'campus_id')) $eventQuery->where(function ($q) use ($campusId) { $q->whereNull('campus_id')->orWhere('campus_id', $campusId); });
                    $teacherEvents = $eventQuery->orderBy('starts_at')->limit(5)->get();
                }
            }

            $stats['pending_reviews'] = $stats['pending_reviews'] ?? 0;
            $stats['assignments_created'] = $stats['assignments_created'] ?? 0;
            $stats['upcoming_exams'] = $teacherExams->count();
            $teacherTasks = $upcomingItems;

            return view('dashboards.teacher', compact(
                'stats', 'recentItems', 'upcomingItems', 'teacherProfile', 'teacherProfileName',
                'teacherAssignments', 'teacherClasses', 'todayTimetable', 'teacherAttendance',
                'attendanceTrend', 'teacherAssignmentList', 'teacherExams', 'classPerformance',
                'gradeDistribution', 'teacherLessonPlans', 'teacherAnnouncements', 'teacherEvents', 'teacherTasks'
            ));
        }

        if ($requiredRole === 'STUDENT') {
            $studentDashboardData = $this->studentDashboardData($user, $schoolId, $campusId);

            return view('dashboards.student', array_merge(
                compact('stats', 'recentItems', 'upcomingItems'),
                $studentDashboardData
            ));
        }

        if ($requiredRole === 'PARENT') {
            $guardian = Schema::hasTable('guardians')
                ? DB::table('guardians')->where('user_id', $user->id)->first()
                : null;

            $children = collect();
            $selectedChild = null;
            $childAttendance = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0];
            $childFees = ['total' => 0, 'paid' => 0, 'balance' => 0, 'overdue' => 0];
            $childAcademic = ['average' => null, 'upcoming_exams' => 0, 'pending_assignments' => 0];
            $childEnrollment = null;
            $childRecentResults = collect();
            $childAssignments = collect();
            $childAnnouncements = collect();
            $childEvents = collect();
            $childPayments = collect();
            $childAttendanceDays = collect();

            // Additional data used by the extended Parent Dashboard.
            $childUpcomingExams = collect();
            $childDocuments = collect();
            $childTransport = null;
            $childBorrowedBooks = collect();
            $childLibraryStats = ['available' => null, 'total' => null, 'availability_rate' => null];
            $childAchievements = collect();
            $childAttendanceTrend = collect();
            $childPerformance = [
                'attendance' => null,
                'marks' => null,
                'homework' => null,
                'engagement' => null,
            ];

            if ($guardian && Schema::hasTable('student_guardians') && Schema::hasTable('student_profiles')) {
                $childIds = DB::table('student_guardians')
                    ->where('guardian_id', $guardian->id)
                    ->pluck('student_id')->unique()->values();

                $stats['children'] = $childIds->count();
                    $children = DB::table('student_profiles')
                        ->whereIn('id', $childIds)
                        ->whereNull('deleted_at')
                        ->orderBy('first_name')->orderBy('last_name')
                        ->get(['id', 'school_id', 'campus_id', 'admission_number', 'first_name', 'middle_name', 'last_name', 'photo_path', 'status']);
                    $stats['children'] = $children->count();

                    // Every child comes exclusively from this guardian's pivot links.
                    // Build an independent dashboard payload for each linked student.
                    $baseSchoolId = $schoolId;
                    $baseCampusId = $campusId;
                    $childData = collect();

                    foreach ($children as $selectedChild) {
                        $childAttendance = ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'rate' => 0];
                        $childFees = ['total' => 0, 'paid' => 0, 'balance' => 0, 'overdue' => 0];
                        $childAcademic = ['average' => null, 'upcoming_exams' => 0, 'pending_assignments' => 0];
                        $childEnrollment = null;
                        $childRecentResults = collect();
                        $childAssignments = collect();
                        $childAnnouncements = collect();
                        $childEvents = collect();
                        $childPayments = collect();
                        $childAttendanceDays = collect();
                        $childUpcomingExams = collect();
                        $childDocuments = collect();
                        $childTransport = null;
                        $childBorrowedBooks = collect();
                        $childLibraryStats = ['available' => null, 'total' => null, 'availability_rate' => null];
                        $childAchievements = collect();
                        $childAttendanceTrend = collect();
                        $childPerformance = ['attendance' => null, 'marks' => null, 'homework' => null, 'engagement' => null];

                        $studentId = (int) $selectedChild->id;
                        $schoolId = $selectedChild->school_id ? (int) $selectedChild->school_id : $baseSchoolId;
                        $campusId = $selectedChild->campus_id ? (int) $selectedChild->campus_id : $baseCampusId;

                        if (Schema::hasTable('student_enrollments')) {
                            $enrollmentQuery = DB::table('student_enrollments')
                                ->where('student_id', $studentId)->where('status', 'active')
                                ->orderByDesc('academic_year_id');
                            $childEnrollment = $enrollmentQuery->first();
                            if ($childEnrollment && Schema::hasTable('school_classes')) {
                                $class = DB::table('school_classes')->where('id', $childEnrollment->school_class_id)->value('name');
                                $childEnrollment->class_name = $class;
                                $childEnrollment->section_name = Schema::hasTable('sections')
                                    ? DB::table('sections')->where('id', $childEnrollment->section_id)->value('name') : null;
                            }
                        }

                        // Attendance for the selected child only.
                        if (Schema::hasTable('student_attendance')) {
                            $attendanceQuery = DB::table('student_attendance')->where('student_id', $studentId);
                            $childAttendance['total'] = (clone $attendanceQuery)->count();
                            $childAttendance['present'] = (clone $attendanceQuery)->whereRaw('LOWER(status) = ?', ['present'])->count();
                            $childAttendance['absent'] = (clone $attendanceQuery)->whereRaw('LOWER(status) = ?', ['absent'])->count();
                            $childAttendance['late'] = (clone $attendanceQuery)->whereRaw('LOWER(status) = ?', ['late'])->count();
                            $childAttendance['rate'] = $childAttendance['total'] > 0
                                ? round(($childAttendance['present'] / $childAttendance['total']) * 100, 1) : 0;
                            $stats['attendance'] = $childAttendance['total'];
                            if (Schema::hasTable('attendance_sessions')) {
                                $childAttendanceDays = DB::table('student_attendance')
                                    ->join('attendance_sessions', 'attendance_sessions.id', '=', 'student_attendance.attendance_session_id')
                                    ->where('student_attendance.student_id', $studentId)
                                    ->orderByDesc('attendance_sessions.attendance_date')->limit(7)
                                    ->get(['attendance_sessions.attendance_date', 'student_attendance.status']);
                            }
                        }

                        // Fee balances and payments for this selected child.
                        if (Schema::hasTable('fee_invoices')) {
                            $invoiceQuery = DB::table('fee_invoices')->where('student_id', $studentId);
                            $childFees['total'] = (float) (clone $invoiceQuery)->sum('total_amount');
                            $childFees['paid'] = (float) (clone $invoiceQuery)->sum('paid_amount');
                            $childFees['balance'] = (float) (clone $invoiceQuery)->sum('balance_amount');
                            $childFees['overdue'] = (clone $invoiceQuery)->where('balance_amount', '>', 0)
                                ->whereDate('due_date', '<', today())->count();
                            $stats['pending_fees'] = $childFees['balance'];
                            if (Schema::hasTable('fee_payments')) {
                                $childPayments = DB::table('fee_payments')
                                    ->join('fee_invoices', 'fee_invoices.id', '=', 'fee_payments.fee_invoice_id')
                                    ->where('fee_invoices.student_id', $studentId)
                                    ->orderByDesc('fee_payments.paid_at')->limit(5)
                                    ->get(['fee_payments.receipt_number', 'fee_payments.amount', 'fee_payments.payment_method', 'fee_payments.paid_at', 'fee_payments.status']);
                            }
                        }

                        // Upcoming exams and the selected child's published results.
                        if (Schema::hasTable('student_enrollments') && Schema::hasTable('exam_subjects') && Schema::hasTable('examinations')) {
                            $sectionIds = DB::table('student_enrollments')->where('student_id', $studentId)
                                ->where('status', 'active')->pluck('section_id')->filter()->unique();
                            $examQuery = DB::table('exam_subjects')
                                ->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id')
                                ->whereIn('exam_subjects.section_id', $sectionIds);
                            $childAcademic['upcoming_exams'] = (clone $examQuery)
                                ->whereDate('exam_subjects.exam_date', '>=', today())->count();
                            $childRecentResults = DB::table('exam_results')
                                ->join('exam_subjects', 'exam_subjects.id', '=', 'exam_results.exam_subject_id')
                                ->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id')
                                ->where('exam_results.student_id', $studentId)
                                ->whereNotNull('exam_results.published_at')
                                ->orderByDesc('exam_results.published_at')->limit(6)
                                ->get(['examinations.name as exam_name', 'exam_results.marks_obtained', 'exam_subjects.maximum_marks', 'exam_results.grade', 'exam_results.published_at']);
                            $validResults = $childRecentResults->filter(fn ($r) => is_numeric($r->marks_obtained) && (float) $r->maximum_marks > 0);
                            $childAcademic['average'] = $validResults->isNotEmpty()
                                ? round($validResults->avg(fn ($r) => ((float) $r->marks_obtained / (float) $r->maximum_marks) * 100), 1) : null;
                        }

                        // Homework is visible only when assigned to this child's active section.
                        // Keep overdue work visible too; parents need to see outstanding work.
                        if (Schema::hasTable('assignments') && Schema::hasTable('student_enrollments')) {
                            $sectionIds = DB::table('student_enrollments')
                                ->where('student_id', $studentId)
                                ->where('status', 'active')
                                ->pluck('section_id')->filter()->unique()->values();

                            if ($sectionIds->isNotEmpty() && Schema::hasColumn('assignments', 'section_id')) {
                                $assignmentQuery = DB::table('assignments as a')
                                    ->whereIn('a.section_id', $sectionIds);

                                // Do not show unpublished drafts to parents.
                                if (Schema::hasColumn('assignments', 'status')) {
                                    $assignmentQuery->whereNotIn('a.status', ['draft', 'deleted', 'archived']);
                                }

                                // Attach this child's own submission state, never another student's.
                                if (Schema::hasTable('assignment_submissions')
                                    && Schema::hasColumn('assignment_submissions', 'assignment_id')
                                    && Schema::hasColumn('assignment_submissions', 'student_id')) {
                                    $assignmentQuery->leftJoin('assignment_submissions as asub', function ($join) use ($studentId) {
                                        $join->on('asub.assignment_id', '=', 'a.id')
                                            ->where('asub.student_id', '=', $studentId);
                                    });
                                }

                                $assignmentColumns = Schema::getColumnListing('assignments');
                                $assignmentSelect = ['a.id'];
                                foreach (['title', 'description', 'due_date', 'status', 'created_at'] as $column) {
                                    if (in_array($column, $assignmentColumns, true)) {
                                        $assignmentSelect[] = 'a.' . $column;
                                    }
                                }
                                if (Schema::hasTable('assignment_submissions')
                                    && Schema::hasColumn('assignment_submissions', 'assignment_id')
                                    && Schema::hasColumn('assignment_submissions', 'student_id')) {
                                    foreach (['status', 'submitted_at', 'marks_obtained'] as $column) {
                                        if (Schema::hasColumn('assignment_submissions', $column)) {
                                            $assignmentSelect[] = 'asub.' . $column . ' as submission_' . $column;
                                        }
                                    }
                                }

                                $orderColumn = Schema::hasColumn('assignments', 'due_date') ? 'a.due_date' : 'a.id';
                                $childAssignments = $assignmentQuery
                                    ->select($assignmentSelect)
                                    ->distinct()
                                    ->orderBy($orderColumn, Schema::hasColumn('assignments', 'due_date') ? 'asc' : 'desc')
                                    ->limit(8)
                                    ->get()
                                    ->map(function ($assignment) {
                                        $assignment->display_status = $assignment->submission_status
                                            ?? ($assignment->submission_submitted_at ? 'submitted' : ($assignment->status ?? 'assigned'));
                                        return $assignment;
                                    });
                            }

                            if (Schema::hasTable('assignment_submissions')
                                && Schema::hasColumn('assignment_submissions', 'student_id')) {
                                $pendingQuery = DB::table('assignment_submissions')
                                    ->where('student_id', $studentId);
                                $childAcademic['pending_assignments'] = Schema::hasColumn('assignment_submissions', 'submitted_at')
                                    ? (clone $pendingQuery)->whereNull('submitted_at')->count()
                                    : (Schema::hasColumn('assignment_submissions', 'status')
                                        ? (clone $pendingQuery)->whereIn('status', ['pending', 'assigned', 'not_submitted'])->count()
                                        : 0);
                            }
                        }

                        // Upcoming exam timetable for this child's active section(s).
                        if (Schema::hasTable('exam_subjects') && Schema::hasTable('examinations')
                            && Schema::hasTable('student_enrollments')) {
                            $sectionIdsForExams = DB::table('student_enrollments')
                                ->where('student_id', $studentId)
                                ->where('status', 'active')
                                ->pluck('section_id')->filter()->unique();

                            if ($sectionIdsForExams->isNotEmpty()) {
                                $examListQuery = DB::table('exam_subjects')
                                    ->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id')
                                    ->whereIn('exam_subjects.section_id', $sectionIdsForExams)
                                    ->whereDate('exam_subjects.exam_date', '>=', today())
                                    ->orderBy('exam_subjects.exam_date')
                                    ->limit(8);

                                if ($schoolId && Schema::hasColumn('examinations', 'school_id')) {
                                    $examListQuery->where('examinations.school_id', $schoolId);
                                }

                                $examSelect = [
                                    'examinations.name',
                                    'exam_subjects.exam_date',
                                    'exam_subjects.start_time',
                                    'exam_subjects.room_number',
                                ];

                                if (Schema::hasTable('subjects') && Schema::hasColumn('exam_subjects', 'subject_id')
                                    && Schema::hasColumn('subjects', 'id') && Schema::hasColumn('subjects', 'name')) {
                                    $examListQuery->leftJoin('subjects', 'subjects.id', '=', 'exam_subjects.subject_id');
                                    $examSelect[] = 'subjects.name as subject_name';
                                }

                                $childUpcomingExams = $examListQuery->get($examSelect)->map(function ($exam) {
                                    $exam->subject_name = $exam->subject_name ?? $exam->name;
                                    return $exam;
                                });
                            }
                        }

                        // Attendance trend: aggregate the selected child's records by week.
                        if (Schema::hasTable('student_attendance') && Schema::hasTable('attendance_sessions')) {
                            $trendRows = DB::table('student_attendance')
                                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'student_attendance.attendance_session_id')
                                ->where('student_attendance.student_id', $studentId)
                                ->whereDate('attendance_sessions.attendance_date', '>=', today()->copy()->subWeeks(6)->startOfWeek())
                                ->whereDate('attendance_sessions.attendance_date', '<=', today())
                                ->get(['attendance_sessions.attendance_date', 'student_attendance.status']);

                            $weekly = $trendRows->groupBy(function ($row) {
                                return Carbon::parse($row->attendance_date)->startOfWeek()->format('Y-m-d');
                            });

                            for ($week = 5; $week >= 0; $week--) {
                                $weekStart = today()->copy()->startOfWeek()->subWeeks($week);
                                $key = $weekStart->format('Y-m-d');
                                $records = $weekly->get($key, collect());
                                $presentCount = $records->filter(fn ($r) => strtolower((string) $r->status) === 'present')->count();
                                $totalCount = $records->count();

                                $childAttendanceTrend->push([
                                    'label' => $weekStart->format('d M'),
                                    'percentage' => $totalCount > 0 ? round(($presentCount / $totalCount) * 100, 1) : 0,
                                ]);
                            }
                        }

                        // Borrowed books and library availability for this selected child.
                        if (Schema::hasTable('library_loans') && Schema::hasTable('library_copies')
                            && Schema::hasTable('library_books')) {
                            $loanQuery = DB::table('library_loans')
                                ->join('library_copies', 'library_copies.id', '=', 'library_loans.library_copy_id')
                                ->join('library_books', 'library_books.id', '=', 'library_copies.library_book_id')
                                ->where('library_loans.student_id', $studentId);

                            if (Schema::hasColumn('library_loans', 'returned_at')) {
                                $loanQuery->whereNull('library_loans.returned_at');
                            } elseif (Schema::hasColumn('library_loans', 'status')) {
                                $loanQuery->whereIn('library_loans.status', ['issued', 'borrowed', 'active']);
                            }

                            $childBorrowedBooks = $loanQuery->orderByDesc('library_loans.issued_at')
                                ->limit(8)
                                ->get([
                                    'library_books.title',
                                    'library_loans.due_at',
                                    'library_loans.status',
                                ]);

                            $libraryQuery = DB::table('library_copies')
                                ->join('library_books', 'library_books.id', '=', 'library_copies.library_book_id');

                            if ($schoolId && Schema::hasColumn('library_books', 'school_id')) {
                                $libraryQuery->where('library_books.school_id', $schoolId);
                            }

                            $childLibraryStats['total'] = (clone $libraryQuery)->count();
                            $childLibraryStats['available'] = (clone $libraryQuery)
                                ->whereRaw('LOWER(library_copies.status) = ?', ['available'])->count();
                            $childLibraryStats['availability_rate'] = $childLibraryStats['total'] > 0
                                ? round(($childLibraryStats['available'] / $childLibraryStats['total']) * 100, 1)
                                : 0;
                        }

                        // Calculate homework completion only when submission records exist.
                        if (Schema::hasTable('assignment_submissions')
                            && Schema::hasColumn('assignment_submissions', 'student_id')
                            && Schema::hasColumn('assignment_submissions', 'submitted_at')) {
                            $submissionQuery = DB::table('assignment_submissions')
                                ->where('student_id', $studentId);
                            $submissionTotal = (clone $submissionQuery)->count();
                            $submissionDone = (clone $submissionQuery)->whereNotNull('submitted_at')->count();
                            $childPerformance['homework'] = $submissionTotal > 0
                                ? round(($submissionDone / $submissionTotal) * 100, 1)
                                : null;
                        }

                        $childPerformance['attendance'] = $childAttendance['total'] > 0
                            ? $childAttendance['rate'] : null;
                        $childPerformance['marks'] = $childAcademic['average'];

                        // Documents are read only from certificate records linked to this student.
                        // Column checks keep this compatible with different certificate schemas.
                        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'student_id')) {
                            $certificateColumns = Schema::getColumnListing('certificates');
                            $certificateQuery = DB::table('certificates')->where('student_id', $studentId);

                            if (in_array('school_id', $certificateColumns, true) && $schoolId) {
                                $certificateQuery->where('school_id', $schoolId);
                            }

                            $certificateSelect = ['id'];
                            foreach (['title', 'name', 'certificate_type', 'created_at', 'file_path', 'pdf_path', 'status'] as $column) {
                                if (in_array($column, $certificateColumns, true)) {
                                    $certificateSelect[] = $column;
                                }
                            }

                            $childDocuments = $certificateQuery->latest('id')->limit(8)->get($certificateSelect)
                                ->map(function ($document) {
                                    $document->title = $document->title
                                        ?? $document->name
                                        ?? $document->certificate_type
                                        ?? 'Student Certificate';
                                    $document->path = $document->file_path ?? $document->pdf_path ?? null;
                                    return $document;
                                });
                        }

                        // Achievement records are optional; only query a known student-linked table.
                        foreach (['student_achievements', 'student_awards'] as $achievementTable) {
                            if (!Schema::hasTable($achievementTable) || !Schema::hasColumn($achievementTable, 'student_id')) {
                                continue;
                            }

                            $achievementColumns = Schema::getColumnListing($achievementTable);
                            $achievementSelect = ['id'];
                            foreach (['title', 'name', 'category', 'description', 'detail', 'academic_year', 'year', 'created_at'] as $column) {
                                if (in_array($column, $achievementColumns, true)) {
                                    $achievementSelect[] = $column;
                                }
                            }

                            $achievementQuery = DB::table($achievementTable)->where('student_id', $studentId);
                            if (in_array('school_id', $achievementColumns, true) && $schoolId) {
                                $achievementQuery->where('school_id', $schoolId);
                            }
                            $childAchievements = $achievementQuery->latest('id')->limit(8)->get($achievementSelect);
                            break;
                        }

                        // Transport assignments are optional. Do not infer a child's route
                        // from the school's fleet; only show a record explicitly linked to them.
                        $transportAssignmentTables = ['student_transport_assignments', 'transport_assignments', 'transport_route_students'];
                        foreach ($transportAssignmentTables as $transportTable) {
                            if (!Schema::hasTable($transportTable) || !Schema::hasColumn($transportTable, 'student_id')) {
                                continue;
                            }

                            $transportColumns = Schema::getColumnListing($transportTable);
                            $transportAssignment = DB::table($transportTable)
                                ->where('student_id', $studentId)
                                ->when(in_array('status', $transportColumns, true), fn ($q) => $q->whereIn('status', ['active', 'assigned']))
                                ->latest('id')->first();

                            if (!$transportAssignment) {
                                continue;
                            }

                            $routeForeignKey = collect(['transport_route_id', 'route_id'])
                                ->first(fn ($column) => in_array($column, $transportColumns, true));
                            $vehicleForeignKey = collect(['transport_vehicle_id', 'vehicle_id'])
                                ->first(fn ($column) => in_array($column, $transportColumns, true));

                            $transportData = [];
                            if ($routeForeignKey && Schema::hasTable('transport_routes')) {
                                $route = DB::table('transport_routes')->where('id', $transportAssignment->{$routeForeignKey})->first();
                                if ($route) $transportData['route_name'] = $route->name ?? 'Assigned route';
                            }
                            if ($vehicleForeignKey && Schema::hasTable('transport_vehicles')) {
                                $vehicle = DB::table('transport_vehicles')->where('id', $transportAssignment->{$vehicleForeignKey})->first();
                                if ($vehicle) {
                                    $transportData['registration_number'] = $vehicle->registration_number ?? null;
                                    $transportData['vehicle_number'] = $vehicle->registration_number ?? null;
                                }
                            }
                            if (in_array('pickup_time', $transportColumns, true)) {
                                $transportData['pickup_time'] = $transportAssignment->pickup_time;
                            }
                            if (in_array('driver_name', $transportColumns, true)) {
                                $transportData['driver_name'] = $transportAssignment->driver_name;
                            }
                            if (in_array('driver_phone', $transportColumns, true)) {
                                $transportData['driver_phone'] = $transportAssignment->driver_phone;
                            }
                            $childTransport = $transportData ?: null;
                            break;
                        }

                        // Notices are limited to the selected child's school/campus.
                        $childAnnouncements = $this->recentAnnouncements($schoolId, $campusId)->take(5)->values();
                        $childEvents = $this->upcomingEvents($schoolId, $campusId)->take(5)->values();
                        if (Schema::hasTable('announcements') && Schema::hasTable('announcement_reads')) {
                            $noticeQuery = DB::table('announcements')->where('status', 'published');
                            if ($schoolId) $noticeQuery->where('school_id', $schoolId);
                            if ($campusId) $noticeQuery->where(fn ($q) => $q->whereNull('campus_id')->orWhere('campus_id', $campusId));
                            $stats['unread_notices'] = (clone $noticeQuery)
                                ->leftJoin('announcement_reads', function ($join) use ($user) {
                                    $join->on('announcement_reads.announcement_id', '=', 'announcements.id')
                                        ->where('announcement_reads.user_id', '=', $user->id);
                                })->whereNull('announcement_reads.id')->count();
                        }

                        $recentItems = $childRecentResults->map(fn ($r) => (object) [
                            'title' => $r->exam_name,
                            'date' => $r->published_at,
                            'status' => $r->grade ?: (is_numeric($r->marks_obtained) ? $r->marks_obtained . '/' . $r->maximum_marks : 'Published'),
                        ]);
                        $upcomingItems = $childEvents;

                        $childData->push([
                            'student' => $selectedChild,
                            'enrollment' => $childEnrollment,
                            'attendance' => $childAttendance,
                            'fees' => $childFees,
                            'academic' => $childAcademic,
                            'recentResults' => $childRecentResults,
                            'assignments' => $childAssignments,
                            'announcements' => $childAnnouncements,
                            'events' => $childEvents,
                            'payments' => $childPayments,
                            'attendanceDays' => $childAttendanceDays,
                            'upcomingExams' => $childUpcomingExams,
                            'documents' => $childDocuments,
                            'transport' => $childTransport,
                            'borrowedBooks' => $childBorrowedBooks,
                            'libraryStats' => $childLibraryStats,
                            'achievements' => $childAchievements,
                            'attendanceTrend' => $childAttendanceTrend,
                            'performance' => $childPerformance,
                        ]);
                    }
            }

            $parentDashboardData = [
                'children' => $children,
                'childData' => $childData ?? collect(),
            ];

            return view('dashboards.parent', array_merge(
                compact('stats', 'recentItems', 'upcomingItems'),
                $parentDashboardData
            ));
        }

        return view($this->viewForRole($requiredRole), compact('stats', 'recentItems', 'upcomingItems'));
    }


    /**
     * Build the complete student dashboard from records linked to the authenticated
     * student's profile. No arbitrary student ID fallback is used.
     */
    private function studentDashboardData($user, ?int $schoolId, ?int $campusId): array
    {
        $empty = [
            'studentProfile' => null, 'student' => null,
            'attendanceSummary' => ['present' => 0, 'absent' => 0, 'late' => 0, 'total' => 0, 'percentage' => null],
            'feeSummary' => ['total' => 0, 'paid' => 0, 'balance' => 0, 'overdue' => 0],
            'academicSummary' => ['average' => null, 'grade' => null, 'rank' => null],
            'todayTimetable' => collect(), 'studentAssignments' => collect(),
            'studentExams' => collect(), 'learningResources' => collect(),
            'studentAnnouncements' => collect(), 'studentEvents' => collect(),
            'feePayments' => collect(), 'mySubjects' => collect(),
            'attendanceTrend' => collect(), 'subjectPerformance' => collect(),
            'studentDocuments' => collect(), 'studentPerformance' => ['homework' => null, 'engagement' => null],
            'studentTransport' => null, 'borrowedBooks' => collect(),
            'libraryStats' => ['available' => null, 'total' => null, 'availability_rate' => null],
            'studentHostel' => null, 'hostelFeeInvoices' => collect(),
            'hostelFeePayments' => collect(), 'hostelAttendance' => collect(),
            'hostelWarden' => null, 'className' => null, 'sectionName' => null,
            'rollNumber' => null, 'academicYearName' => null, 'campusName' => null,
        ];

        if (!Schema::hasTable('student_profiles')) {
            return $empty;
        }

        $student = DB::table('student_profiles')
            ->where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->first();

        // A student dashboard must never expose another student's data if the login
        // is not linked to a student profile.
        if (!$student) {
            return $empty;
        }

        $studentId = (int) $student->id;
        $schoolId = isset($student->school_id) ? (int) $student->school_id : $schoolId;
        $campusId = isset($student->campus_id) ? (int) $student->campus_id : $campusId;

        $data = $empty;
        $data['studentProfile'] = $student;
        $data['student'] = $student;

        $enrollment = null;
        if (Schema::hasTable('student_enrollments')) {
            $enrollmentQuery = DB::table('student_enrollments')
                ->where('student_id', $studentId);
            if (Schema::hasColumn('student_enrollments', 'status')) {
                $enrollmentQuery->where('status', 'active');
            }
            if (Schema::hasColumn('student_enrollments', 'academic_year_id')) {
                $enrollmentQuery->orderByDesc('academic_year_id');
            }
            $enrollment = $enrollmentQuery->latest('id')->first();
        }

        if ($enrollment) {
            if (!empty($enrollment->school_class_id) && Schema::hasTable('school_classes')) {
                $data['className'] = DB::table('school_classes')
                    ->where('id', $enrollment->school_class_id)->value('name');
            }
            if (!empty($enrollment->section_id) && Schema::hasTable('sections')) {
                $data['sectionName'] = DB::table('sections')
                    ->where('id', $enrollment->section_id)->value('name');
            }
            $data['rollNumber'] = $enrollment->roll_number ?? null;
            if (!empty($enrollment->academic_year_id) && Schema::hasTable('academic_years')) {
                $data['academicYearName'] = DB::table('academic_years')
                    ->where('id', $enrollment->academic_year_id)->value('name');
            }
        }

        if ($campusId && Schema::hasTable('campuses')) {
            $data['campusName'] = DB::table('campuses')->where('id', $campusId)->value('name');
        }

        // Attendance: retain an unknown state when no attendance has been recorded.
        if (Schema::hasTable('student_attendance') && Schema::hasColumn('student_attendance', 'student_id')) {
            $attendanceQuery = DB::table('student_attendance')->where('student_id', $studentId);
            $attendance = $data['attendanceSummary'];
            $attendance['total'] = (clone $attendanceQuery)->count();
            foreach (['present', 'absent', 'late'] as $status) {
                $attendance[$status] = (clone $attendanceQuery)
                    ->whereRaw('LOWER(status) = ?', [$status])->count();
            }
            $attendance['percentage'] = $attendance['total'] > 0
                ? round(($attendance['present'] / $attendance['total']) * 100, 1)
                : null;
            $data['attendanceSummary'] = $attendance;
        }

        // Assignments are selected by the student's active section, then joined to
        // that student's own submission row (if one exists).
        $studentAssignments = collect();
        if ($enrollment && !empty($enrollment->section_id)
            && Schema::hasTable('assignments')
            && Schema::hasColumn('assignments', 'section_id')) {
            $assignmentQuery = DB::table('assignments as a')
                ->where('a.section_id', $enrollment->section_id);

            if (Schema::hasColumn('assignments', 'school_id') && $schoolId) {
                $assignmentQuery->where('a.school_id', $schoolId);
            }
            if (Schema::hasColumn('assignments', 'status')) {
                $assignmentQuery->whereIn('a.status', ['published', 'active']);
            }

            $submissionJoinAvailable = Schema::hasTable('assignment_submissions')
                && Schema::hasColumn('assignment_submissions', 'assignment_id')
                && Schema::hasColumn('assignment_submissions', 'student_id');

            if ($submissionJoinAvailable) {
                $assignmentQuery->leftJoin('assignment_submissions as sub', function ($join) use ($studentId) {
                    $join->on('sub.assignment_id', '=', 'a.id')
                        ->where('sub.student_id', '=', $studentId);
                });
            }

            $assignmentSelect = ['a.id', 'a.title'];
            foreach (['description', 'due_date', 'max_marks', 'maximum_marks', 'status'] as $column) {
                if (Schema::hasColumn('assignments', $column)) {
                    $assignmentSelect[] = 'a.' . $column;
                }
            }
            if (Schema::hasTable('subjects') && Schema::hasColumn('assignments', 'subject_id')) {
                $assignmentQuery->leftJoin('subjects as subj', 'subj.id', '=', 'a.subject_id');
                $assignmentSelect[] = 'subj.name as subject_name';
            }
            if ($submissionJoinAvailable) {
                foreach (['submission_status', 'status', 'submitted_at', 'marks', 'marks_obtained', 'feedback'] as $column) {
                    if (Schema::hasColumn('assignment_submissions', $column)) {
                        $alias = $column === 'status' ? 'submission_status' : $column;
                        $assignmentSelect[] = 'sub.' . $column . ' as ' . $alias;
                    }
                }
            }

            if (Schema::hasColumn('assignments', 'due_date')) {
                $assignmentQuery->orderBy('a.due_date');
            } else {
                $assignmentQuery->orderByDesc('a.id');
            }

            $studentAssignments = $assignmentQuery->limit(30)->get(array_values(array_unique($assignmentSelect)))
                ->map(function ($row) {
                    $row->submission_status = $row->submission_status
                        ?? ($row->submitted_at ?? null ? 'submitted' : 'pending');
                    return $row;
                });
        }
        $data['studentAssignments'] = $studentAssignments;

        $pendingAssignments = $studentAssignments->filter(function ($row) {
            return in_array(strtolower((string) ($row->submission_status ?? 'pending')),
                ['pending', 'draft', 'not_submitted', ''], true);
        })->count();
        $completedAssignments = $studentAssignments->count() - $pendingAssignments;

        // Exam timetable and published marks for the active section.
        $studentExams = collect();
        if ($enrollment && !empty($enrollment->section_id)
            && Schema::hasTable('exam_subjects') && Schema::hasTable('examinations')) {
            $examQuery = DB::table('exam_subjects as es')
                ->join('examinations as ex', 'ex.id', '=', 'es.examination_id')
                ->where('es.section_id', $enrollment->section_id)
                ->whereDate('es.exam_date', '>=', today());

            if ($schoolId && Schema::hasColumn('examinations', 'school_id')) {
                $examQuery->where('ex.school_id', $schoolId);
            }
            if (Schema::hasColumn('examinations', 'status')) {
                $examQuery->whereIn('ex.status', ['published', 'scheduled', 'active']);
            }
            if (Schema::hasTable('subjects') && Schema::hasColumn('exam_subjects', 'subject_id')) {
                $examQuery->leftJoin('subjects as subj', 'subj.id', '=', 'es.subject_id');
            }

            $examSelect = ['es.exam_date'];
            foreach (['start_time', 'end_time', 'maximum_marks', 'passing_marks', 'room_number'] as $column) {
                if (Schema::hasColumn('exam_subjects', $column)) $examSelect[] = 'es.' . $column;
            }
            $examSelect[] = 'ex.name as exam_name';
            if (Schema::hasTable('subjects') && Schema::hasColumn('exam_subjects', 'subject_id')) {
                $examSelect[] = 'subj.name as subject_name';
            }
            $studentExams = $examQuery->orderBy('es.exam_date')->get($examSelect);
        }
        $data['studentExams'] = $studentExams;

        $results = collect();
        if (Schema::hasTable('exam_results') && Schema::hasTable('exam_subjects')
            && Schema::hasColumn('exam_results', 'student_id')) {
            $resultQuery = DB::table('exam_results as er')
                ->join('exam_subjects as es', 'es.id', '=', 'er.exam_subject_id')
                ->where('er.student_id', $studentId)
                ->whereNotNull('er.marks_obtained');
            if (Schema::hasColumn('exam_results', 'published_at')) {
                $resultQuery->whereNotNull('er.published_at');
            }
            if (Schema::hasTable('subjects') && Schema::hasColumn('exam_subjects', 'subject_id')) {
                $resultQuery->leftJoin('subjects as subj', 'subj.id', '=', 'es.subject_id');
            }
            $resultSelect = ['er.marks_obtained'];
            foreach (['grade', 'published_at'] as $column) {
                if (Schema::hasColumn('exam_results', $column)) $resultSelect[] = 'er.' . $column;
            }
            if (Schema::hasColumn('exam_subjects', 'maximum_marks')) $resultSelect[] = 'es.maximum_marks';
            if (Schema::hasTable('subjects') && Schema::hasColumn('exam_subjects', 'subject_id')) {
                $resultSelect[] = 'subj.name as subject_name';
            }
            $results = $resultQuery->orderByDesc('er.id')->get($resultSelect);
        }

        $validResults = $results->filter(fn ($r) =>
            is_numeric($r->marks_obtained ?? null) && (float) ($r->maximum_marks ?? 0) > 0
        );
        $average = $validResults->isNotEmpty()
            ? round($validResults->avg(fn ($r) =>
                ((float) $r->marks_obtained / (float) $r->maximum_marks) * 100
            ), 1)
            : null;

        $subjectPerformance = $validResults->groupBy(fn ($r) => $r->subject_name ?? 'Subject')
            ->map(function ($rows, $subjectName) {
                return (object) [
                    'subject_name' => $subjectName,
                    'percentage' => round($rows->avg(fn ($r) =>
                        ((float) $r->marks_obtained / (float) $r->maximum_marks) * 100
                    ), 1),
                ];
            })->values();

        $data['academicSummary'] = [
            'average' => $average,
            'grade' => $validResults->first()->grade ?? null,
            'rank' => null,
        ];
        $data['subjectPerformance'] = $subjectPerformance;

        // Weekly attendance trend is only populated when attendance records exist.
        if (Schema::hasTable('student_attendance') && Schema::hasColumn('student_attendance', 'student_id')) {
            $attendanceRows = DB::table('student_attendance')
                ->where('student_id', $studentId)
                ->orderByDesc(Schema::hasColumn('student_attendance', 'attendance_date') ? 'attendance_date' : 'id')
                ->limit(90)->get();
            if ($attendanceRows->isNotEmpty()) {
                $byDate = $attendanceRows->groupBy(function ($row) {
                    $date = $row->attendance_date ?? $row->created_at ?? null;
                    return $date ? Carbon::parse($date)->startOfWeek()->format('Y-m-d') : 'unknown';
                });
                foreach ($byDate as $week => $rows) {
                    if ($week === 'unknown') continue;
                    $present = $rows->filter(fn ($r) => strtolower((string) $r->status) === 'present')->count();
                    $data['attendanceTrend']->push([
                        'label' => Carbon::parse($week)->format('d M'),
                        'percentage' => $rows->count() ? round($present / $rows->count() * 100, 1) : 0,
                    ]);
                }
            }
        }

        // Timetable entries for the active section and today's weekday.
        if ($enrollment && !empty($enrollment->section_id)
            && Schema::hasTable('timetable_entries') && Schema::hasTable('timetables')) {
            $ttColumns = Schema::getColumnListing('timetable_entries');
            $ttQuery = DB::table('timetable_entries as te');
            $ttHasTimetableId = in_array('timetable_id', $ttColumns, true);
            if ($ttHasTimetableId) {
                $ttQuery->join('timetables as tm', 'tm.id', '=', 'te.timetable_id');
                if (Schema::hasColumn('timetables', 'section_id')) {
                    $ttQuery->where('tm.section_id', $enrollment->section_id);
                } elseif (in_array('section_id', $ttColumns, true)) {
                    $ttQuery->where('te.section_id', $enrollment->section_id);
                }
                if ($schoolId && Schema::hasColumn('timetables', 'school_id')) {
                    $ttQuery->where('tm.school_id', $schoolId);
                }
                if (Schema::hasColumn('timetables', 'status')) {
                    $ttQuery->whereIn('tm.status', ['published', 'active']);
                }
            } elseif (in_array('section_id', $ttColumns, true)) {
                $ttQuery->where('te.section_id', $enrollment->section_id);
            }

            $dayColumn = collect(['day_of_week', 'weekday', 'day'])
                ->first(fn ($column) => in_array($column, $ttColumns, true));
            if ($dayColumn) {
                $dayName = strtolower(now()->format('l'));
                $dayShort = strtolower(now()->format('D'));
                $dayNumber = (string) now()->dayOfWeekIso;
                $ttQuery->where(function ($q) use ($dayColumn, $dayName, $dayShort, $dayNumber) {
                    $q->whereRaw('LOWER(te.' . $dayColumn . ') = ?', [$dayName])
                        ->orWhereRaw('LOWER(te.' . $dayColumn . ') = ?', [$dayShort])
                        ->orWhere('te.' . $dayColumn, $dayNumber);
                });
            }

            $ttSelect = ['te.*'];
            if (in_array('period_id', $ttColumns, true) && Schema::hasTable('periods')) {
                $ttQuery->leftJoin('periods as p', 'p.id', '=', 'te.period_id');
                foreach (['name', 'period_name', 'start_time', 'end_time'] as $column) {
                    if (Schema::hasColumn('periods', $column)) {
                        $alias = $column === 'name' || $column === 'period_name' ? 'period_name' : $column;
                        $ttSelect[] = 'p.' . $column . ' as ' . $alias;
                    }
                }
            }
            if (Schema::hasTable('subjects') && in_array('subject_id', $ttColumns, true)) {
                $ttQuery->leftJoin('subjects as ts', 'ts.id', '=', 'te.subject_id');
                $ttSelect[] = 'ts.name as subject_name';
            }
            if (Schema::hasTable('staff_profiles') && in_array('staff_profile_id', $ttColumns, true)) {
                $ttQuery->leftJoin('staff_profiles as tsp', 'tsp.id', '=', 'te.staff_profile_id');
                $nameParts = [];
                if (Schema::hasColumn('staff_profiles', 'first_name')) $nameParts[] = "tsp.first_name";
                if (Schema::hasColumn('staff_profiles', 'last_name')) $nameParts[] = "tsp.last_name";
                if ($nameParts) $ttSelect[] = DB::raw("TRIM(CONCAT(" . implode(", ' ', ", $nameParts) . ")) as teacher_name");
            }

            $data['todayTimetable'] = $ttQuery->orderByRaw(
                in_array('period_id', $ttColumns, true) ? 'te.period_id' : 'te.id'
            )->limit(30)->get($ttSelect)->map(function ($row) {
                $row->start_time = $row->start_time ?? null;
                $row->end_time = $row->end_time ?? null;
                $row->subject_name = $row->subject_name ?? 'Class period';
                $row->teacher_name = $row->teacher_name ?? '—';
                $row->room_number = $row->room_number ?? $row->room ?? '—';
                $row->period_name = $row->period_name ?? 'Period';
                return $row;
            });
        }

        // Subject list from the student's enrolled class.
        if ($enrollment && !empty($enrollment->school_class_id)
            && Schema::hasTable('class_subjects') && Schema::hasTable('subjects')) {
            $subjectQuery = DB::table('class_subjects as cs')
                ->join('subjects as s', 's.id', '=', 'cs.subject_id')
                ->where('cs.school_class_id', $enrollment->school_class_id);
            // Select the subject name before adding the optional teacher expression.
            // Calling addSelect() on an empty query initializes the select list, so
            // passing columns later to get() can otherwise leave s.name out of SELECT.
            $subjectQuery->select([
                's.name as subject_name',
                's.name',
            ]);

            if (Schema::hasTable('teaching_assignments')
                && Schema::hasColumn('teaching_assignments', 'class_subject_id')
                && Schema::hasColumn('teaching_assignments', 'section_id')
                && Schema::hasColumn('teaching_assignments', 'staff_profile_id')
                && Schema::hasTable('staff_profiles')) {
                $subjectQuery->leftJoin('teaching_assignments as ta', function ($join) use ($enrollment) {
                    $join->on('ta.class_subject_id', '=', 'cs.id')
                        ->where('ta.section_id', '=', $enrollment->section_id);
                })->leftJoin('staff_profiles as sp', 'sp.id', '=', 'ta.staff_profile_id');

                $subjectQuery->addSelect(DB::raw(
                    "TRIM(CONCAT(COALESCE(sp.first_name,''),' ',COALESCE(sp.last_name,''))) as teacher_name"
                ));
            }

            $data['mySubjects'] = $subjectQuery
                ->distinct()
                ->orderBy('s.name')
                ->get();
        }

        // General school fee summary, if regular fee invoices exist for this student.
        if (Schema::hasTable('fee_invoices') && Schema::hasColumn('fee_invoices', 'student_id')) {
            $feeQuery = DB::table('fee_invoices')->where('student_id', $studentId);
            $feeColumns = Schema::getColumnListing('fee_invoices');
            $totalColumn = in_array('total_amount', $feeColumns, true) ? 'total_amount' : (in_array('amount', $feeColumns, true) ? 'amount' : null);
            if ($totalColumn) $data['feeSummary']['total'] = (float) (clone $feeQuery)->sum($totalColumn);
            if (in_array('paid_amount', $feeColumns, true)) $data['feeSummary']['paid'] = (float) (clone $feeQuery)->sum('paid_amount');
            if (in_array('balance_amount', $feeColumns, true)) {
                $data['feeSummary']['balance'] = (float) (clone $feeQuery)->sum('balance_amount');
            } else {
                $data['feeSummary']['balance'] = max(0, $data['feeSummary']['total'] - $data['feeSummary']['paid']);
            }
            if (in_array('due_date', $feeColumns, true) && in_array('balance_amount', $feeColumns, true)) {
                $data['feeSummary']['overdue'] = (clone $feeQuery)->where('balance_amount', '>', 0)
                    ->whereDate('due_date', '<', today())->count();
            }
            if (Schema::hasTable('fee_payments') && Schema::hasColumn('fee_payments', 'fee_invoice_id')) {
                $data['feePayments'] = DB::table('fee_payments as fp')
                    ->join('fee_invoices as fi', 'fi.id', '=', 'fp.fee_invoice_id')
                    ->where('fi.student_id', $studentId)
                    ->orderByDesc(Schema::hasColumn('fee_payments', 'paid_at') ? 'fp.paid_at' : 'fp.id')
                    ->limit(6)->get();
            }
        }

        // Certificates: expose an open URL only when the stored public file exists.
        if (Schema::hasTable('certificates') && Schema::hasColumn('certificates', 'student_id')) {
            $certificateColumns = Schema::getColumnListing('certificates');
            $select = ['id'];
            foreach (['title', 'name', 'certificate_type', 'issued_date', 'created_at', 'file_path', 'pdf_path', 'status'] as $column) {
                if (in_array($column, $certificateColumns, true)) $select[] = $column;
            }
            $data['studentDocuments'] = DB::table('certificates')->where('student_id', $studentId)
                ->latest('id')->limit(8)->get($select)->map(function ($doc) {
                    $doc->title = $doc->title ?? $doc->name ?? $doc->certificate_type ?? 'Student Certificate';
                    $doc->created_at = $doc->created_at ?? $doc->issued_date ?? null;
                    $path = $doc->file_path ?? $doc->pdf_path ?? null;
                    $doc->path = $path;
                    $doc->url = null;
                    if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                        $doc->url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
                    }
                    return $doc;
                });
        }

        // Transport: show only the student's own active assignment, route and stop.
        if (Schema::hasTable('student_transport_assignments')) {
            $transportQuery = DB::table('student_transport_assignments as sta')
                ->where('sta.student_id', $studentId);
            if (Schema::hasColumn('student_transport_assignments', 'status')) {
                $transportQuery->whereIn('sta.status', ['active', 'assigned']);
            }
            $transportAssignment = $transportQuery->latest('sta.id')->first();
            if ($transportAssignment) {
                $transport = [
                    'route_name' => null, 'stop_name' => null, 'pickup_time' => null,
                    'drop_time' => null, 'trip_type' => $transportAssignment->trip_type ?? null,
                    'fee_amount' => $transportAssignment->fee_amount ?? null,
                    'registration_number' => null, 'driver_name' => null, 'driver_phone' => null,
                ];
                if (!empty($transportAssignment->transport_route_id) && Schema::hasTable('transport_routes')) {
                    $route = DB::table('transport_routes')->where('id', $transportAssignment->transport_route_id)->first();
                    if ($route) $transport['route_name'] = $route->name ?? $route->route_name ?? 'Assigned route';
                }
                if (!empty($transportAssignment->transport_stop_id) && Schema::hasTable('transport_stops')) {
                    $stop = DB::table('transport_stops')->where('id', $transportAssignment->transport_stop_id)->first();
                    if ($stop) {
                        $transport['stop_name'] = $stop->name ?? $stop->stop_name ?? null;
                        $transport['pickup_time'] = $stop->pickup_time ?? null;
                        $transport['drop_time'] = $stop->drop_time ?? $stop->dropoff_time ?? null;
                    }
                }
                $data['studentTransport'] = $transport;
            }
        }

        // Library: only current unreturned loans belonging to this student.
        if (Schema::hasTable('library_loans') && Schema::hasTable('library_copies')
            && Schema::hasTable('library_books')) {
            $loanCopyColumn = Schema::hasColumn('library_loans', 'library_copy_id')
                ? 'library_copy_id'
                : (Schema::hasColumn('library_loans', 'copy_id') ? 'copy_id' : null);
            if ($loanCopyColumn) {
                $loanQuery = DB::table('library_loans as ll')
                    ->join('library_copies as lc', 'lc.id', '=', 'll.' . $loanCopyColumn)
                    ->join('library_books as lb', 'lb.id', '=', 'lc.library_book_id')
                    ->where('ll.student_id', $studentId);
                if (Schema::hasColumn('library_loans', 'returned_at')) {
                    $loanQuery->whereNull('ll.returned_at');
                } elseif (Schema::hasColumn('library_loans', 'status')) {
                    $loanQuery->whereIn('ll.status', ['issued', 'borrowed', 'active', 'overdue']);
                }
                $loanSelect = ['lb.title'];
                foreach (['author'] as $column) if (Schema::hasColumn('library_books', $column)) $loanSelect[] = 'lb.' . $column;
                foreach (['issued_at', 'due_at', 'status', 'fine_amount'] as $column) {
                    if (Schema::hasColumn('library_loans', $column)) $loanSelect[] = 'll.' . $column;
                }
                $data['borrowedBooks'] = $loanQuery->orderByDesc(
                    Schema::hasColumn('library_loans', 'issued_at') ? 'll.issued_at' : 'll.id'
                )->limit(8)->get($loanSelect);
            }

            $libraryQuery = DB::table('library_copies');
            if (Schema::hasColumn('library_copies', 'status')) {
                $data['libraryStats']['total'] = (clone $libraryQuery)->count();
                $data['libraryStats']['available'] = (clone $libraryQuery)
                    ->whereRaw('LOWER(status) = ?', ['available'])->count();
                $data['libraryStats']['availability_rate'] = $data['libraryStats']['total'] > 0
                    ? round($data['libraryStats']['available'] / $data['libraryStats']['total'] * 100, 1)
                    : null;
            }
        }

        // Hostel: allocation, room/bed, warden, allocation-linked invoices and payments.
        if (Schema::hasTable('hostel_allocations') && Schema::hasTable('hostel_beds')
            && Schema::hasTable('hostel_rooms') && Schema::hasTable('hostels')) {
            $hostelQuery = DB::table('hostel_allocations as ha')
                ->join('hostel_beds as hb', 'hb.id', '=', 'ha.hostel_bed_id')
                ->join('hostel_rooms as hr', 'hr.id', '=', 'hb.hostel_room_id')
                ->join('hostels as h', 'h.id', '=', 'hr.hostel_id')
                ->where('ha.student_id', $studentId);
            if (Schema::hasColumn('hostel_allocations', 'status')) {
                $hostelQuery->where('ha.status', 'active');
            }
            $hostelAllocation = $hostelQuery->orderByDesc('ha.id')->first([
                'ha.id as allocation_id', 'ha.start_date', 'ha.end_date', 'ha.status as allocation_status',
                'hb.bed_number', 'hr.room_number', 'hr.fee_per_term', 'h.id as hostel_id',
                'h.name as hostel_name', 'h.hostel_type', 'h.warden_name', 'h.warden_phone',
            ]);

            if ($hostelAllocation) {
                $data['studentHostel'] = $hostelAllocation;

                if (Schema::hasTable('hostel_wardens')) {
                    $warden = DB::table('hostel_wardens')->where('hostel_id', $hostelAllocation->hostel_id)
                        ->where('status', 'active')->orderByDesc('id')->first();
                    if ($warden) {
                        $data['hostelWarden'] = $warden;
                    }
                }
                if (!$data['hostelWarden'] && ($hostelAllocation->warden_name || $hostelAllocation->warden_phone)) {
                    $data['hostelWarden'] = (object) [
                        'name' => $hostelAllocation->warden_name,
                        'phone' => $hostelAllocation->warden_phone,
                        'email' => null,
                        'shift' => null,
                    ];
                }

                if (Schema::hasTable('hostel_fee_invoices')) {
                    $invoiceQuery = DB::table('hostel_fee_invoices')
                        ->where('hostel_allocation_id', $hostelAllocation->allocation_id);
                    $data['hostelFeeInvoices'] = (clone $invoiceQuery)->orderByDesc('issued_on')->get();
                    if (Schema::hasTable('hostel_fee_payments')) {
                        $data['hostelFeePayments'] = DB::table('hostel_fee_payments as hp')
                            ->join('hostel_fee_invoices as hi', 'hi.id', '=', 'hp.hostel_fee_invoice_id')
                            ->where('hi.hostel_allocation_id', $hostelAllocation->allocation_id)
                            ->orderByDesc('hp.paid_on')
                            ->get(['hp.receipt_number', 'hp.amount', 'hp.payment_method', 'hp.paid_on', 'hi.invoice_number']);
                    }
                }
                if (Schema::hasTable('hostel_attendance')) {
                    $data['hostelAttendance'] = DB::table('hostel_attendance')
                        ->where('student_id', $studentId)
                        ->orderByDesc('attendance_date')->limit(7)->get();
                }
            }
        }

        // Published announcements and upcoming school events.
        if (Schema::hasTable('announcements')) {
            $annQuery = DB::table('announcements')->whereIn('status', ['published', 'active']);
            if ($schoolId && Schema::hasColumn('announcements', 'school_id')) $annQuery->where('school_id', $schoolId);
            if ($campusId && Schema::hasColumn('announcements', 'campus_id')) {
                $annQuery->where(fn ($q) => $q->whereNull('campus_id')->orWhere('campus_id', $campusId));
            }
            if (Schema::hasColumn('announcements', 'published_at')) {
                $annQuery->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
                $annQuery->orderByDesc('published_at');
            } else {
                $annQuery->orderByDesc('id');
            }
            $annSelect = ['title'];
            foreach (['content', 'description', 'published_at'] as $column) {
                if (Schema::hasColumn('announcements', $column)) $annSelect[] = $column;
            }
            $data['studentAnnouncements'] = $annQuery->limit(6)->get($annSelect);
        }

        if (Schema::hasTable('events') && Schema::hasColumn('events', 'starts_at')) {
            $eventQuery = DB::table('events')->whereDate('starts_at', '>=', today());
            if ($schoolId && Schema::hasColumn('events', 'school_id')) $eventQuery->where('school_id', $schoolId);
            if ($campusId && Schema::hasColumn('events', 'campus_id')) {
                $eventQuery->where(fn ($q) => $q->whereNull('campus_id')->orWhere('campus_id', $campusId));
            }
            $eventSelect = ['title', 'starts_at'];
            if (Schema::hasColumn('events', 'venue')) $eventSelect[] = 'venue';
            $data['studentEvents'] = $eventQuery->orderBy('starts_at')->limit(6)->get($eventSelect);
        }

        // Optional resources table: only read it when its student/class linkage is known.
        if (Schema::hasTable('learning_resources') && $enrollment
            && Schema::hasColumn('learning_resources', 'section_id')
            && Schema::hasColumn('learning_resources', 'title')) {
            $resourceQuery = DB::table('learning_resources')->where('section_id', $enrollment->section_id);
            $resourceSelect = ['title'];
            foreach (['url', 'file_path', 'subject_id'] as $column) {
                if (Schema::hasColumn('learning_resources', $column)) $resourceSelect[] = $column;
            }
            if (Schema::hasTable('subjects') && Schema::hasColumn('learning_resources', 'subject_id')) {
                $resourceQuery->leftJoin('subjects', 'subjects.id', '=', 'learning_resources.subject_id');
                $resourceSelect[] = 'subjects.name as subject_name';
            }
            $data['learningResources'] = $resourceQuery->latest('id')->limit(12)->get($resourceSelect)
                ->map(function ($resource) {
                    $resource->url = $resource->url ?? (
                        !empty($resource->file_path)
                            ? \Illuminate\Support\Facades\Storage::disk('public')->url($resource->file_path)
                            : null
                    );
                    return $resource;
                });
        }

        $submissionTotal = $studentAssignments->count();
        $submittedTotal = $studentAssignments->filter(fn ($row) =>
            !empty($row->submitted_at) || in_array(strtolower((string) ($row->submission_status ?? '')), ['submitted', 'evaluated', 'completed'], true)
        )->count();

        $data['studentPerformance'] = [
            'homework' => $submissionTotal > 0 ? round($submittedTotal / $submissionTotal * 100, 1) : null,
            'engagement' => null,
        ];

        $data['stats'] = [
            'attendance_percentage' => $data['attendanceSummary']['percentage'],
            'pending_assignments' => $pendingAssignments,
            'completed_assignments' => $completedAssignments,
            'upcoming_exams' => $studentExams->count(),
            'academic_average' => $average,
            'fee_balance' => $data['feeSummary']['balance'],
        ];

        $data['recentItems'] = $studentAssignments->take(6)->values();
        $data['upcomingItems'] = $data['studentEvents']->take(6)->values();

        return $data;
    }

    private function principalStats(?int $schoolId, ?int $campusId, ?int $academicYearId): array
    {
        $students = $this->schoolScopedQuery('student_profiles', $schoolId, $campusId);
        if ($academicYearId && Schema::hasTable('student_enrollments')) {
            $ids = DB::table('student_enrollments')->where('academic_year_id', $academicYearId)
                ->where('status', 'active')->pluck('student_id');
            $students->whereIn('id', $ids);
        }

        $staff = $this->schoolScopedQuery('staff_profiles', $schoolId, $campusId);
        $teachers = (clone $staff)->where('designation', 'LIKE', '%teacher%')->count();
        $invoices = $this->schoolScopedQuery('fee_invoices', $schoolId, $campusId);
        if ($academicYearId && Schema::hasColumn('fee_invoices', 'academic_year_id')) {
            $invoices->where('academic_year_id', $academicYearId);
        }

        $payments = DB::table('fee_payments');
        if (Schema::hasTable('fee_invoices')) {
            $payments->join('fee_invoices', 'fee_invoices.id', '=', 'fee_payments.fee_invoice_id');
            if ($schoolId) $payments->where('fee_invoices.school_id', $schoolId);
            if ($campusId && Schema::hasColumn('fee_invoices', 'campus_id')) $payments->where('fee_invoices.campus_id', $campusId);
            if ($academicYearId && Schema::hasColumn('fee_invoices', 'academic_year_id')) $payments->where('fee_invoices.academic_year_id', $academicYearId);
        }

        return [
            'students' => (clone $students)->count(),
            'staff' => (clone $staff)->count(),
            'teachers' => $teachers,
            'non_teaching_staff' => max(0, (clone $staff)->count() - $teachers),
            'attendance' => $this->todayAttendanceCount($schoolId, $campusId),
            'pending_approvals' => $this->approvalCount($schoolId),
            'admissions' => $this->schoolCount('admission_leads', $schoolId, $campusId),
            'fees_invoiced' => (float) (clone $invoices)->sum('total_amount'),
            'fees_collected' => (float) (clone $payments)->where('fee_payments.status', 'paid')->sum('fee_payments.amount'),
            'fees_outstanding' => (float) (clone $invoices)->sum('balance_amount'),
            'overdue_invoices' => (clone $invoices)->where('balance_amount', '>', 0)->whereDate('due_date', '<', today())->count(),
            'today_staff_present' => $this->staffAttendanceToday($schoolId, $campusId, 'present'),
            'today_staff_absent' => $this->staffAttendanceToday($schoolId, $campusId, 'absent'),
            'upcoming_exams' => $this->upcomingExamCount($schoolId, $campusId),
        ];
    }

    private function principalAnalytics(?int $schoolId, ?int $campusId, ?int $academicYearId): array
    {
        $gender = ['Male' => 0, 'Female' => 0, 'Other / Unspecified' => 0];
        if (Schema::hasTable('student_profiles')) {
            $q = $this->schoolScopedQuery('student_profiles', $schoolId, $campusId);
            foreach ($q->select('gender', DB::raw('COUNT(*) total'))->groupBy('gender')->get() as $r) {
                $key = match (strtolower((string) $r->gender)) {
                    'male', 'm' => 'Male', 'female', 'f' => 'Female', default => 'Other / Unspecified'
                };
                $gender[$key] += (int) $r->total;
            }
        }

        $teacherCount = $this->schoolCount('staff_profiles', $schoolId, $campusId,
            fn($q) => $q->where('designation', 'LIKE', '%teacher%'));
        $staffCount = $this->schoolCount('staff_profiles', $schoolId, $campusId);

        $feeStatus = ['Paid' => 0, 'Partial' => 0, 'Pending' => 0, 'Overdue' => 0];
        if (Schema::hasTable('fee_invoices')) {
            $q = $this->schoolScopedQuery('fee_invoices', $schoolId, $campusId);
            if ($academicYearId && Schema::hasColumn('fee_invoices', 'academic_year_id')) $q->where('academic_year_id', $academicYearId);
            $feeStatus['Paid'] = (clone $q)->where('balance_amount', '<=', 0)->count();
            $feeStatus['Overdue'] = (clone $q)->where('balance_amount', '>', 0)->whereDate('due_date', '<', today())->count();
            $feeStatus['Partial'] = (clone $q)->where('paid_amount', '>', 0)->where('balance_amount', '>', 0)->whereDate('due_date', '>=', today())->count();
            $feeStatus['Pending'] = (clone $q)->where('paid_amount', '<=', 0)->where('balance_amount', '>', 0)->whereDate('due_date', '>=', today())->count();
        }

        $monthlyFees = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = now()->copy()->startOfMonth()->subMonths($i);
            $total = 0;
            if (Schema::hasTable('fee_payments') && Schema::hasTable('fee_invoices')) {
                $q = DB::table('fee_payments')->join('fee_invoices', 'fee_invoices.id', '=', 'fee_payments.fee_invoice_id')
                    ->where('fee_payments.status', 'paid')->whereYear('fee_payments.paid_at', $m->year)
                    ->whereMonth('fee_payments.paid_at', $m->month);
                if ($schoolId) $q->where('fee_invoices.school_id', $schoolId);
                if ($campusId && Schema::hasColumn('fee_invoices', 'campus_id')) $q->where('fee_invoices.campus_id', $campusId);
                if ($academicYearId && Schema::hasColumn('fee_invoices', 'academic_year_id')) $q->where('fee_invoices.academic_year_id', $academicYearId);
                $total = (float) $q->sum('fee_payments.amount');
            }
            $monthlyFees[] = ['label' => $m->format('M'), 'total' => round($total, 2)];
        }

        $attendanceTrend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = today()->subDays($i); $total = 0; $present = 0;
            if (Schema::hasTable('student_attendance') && Schema::hasTable('attendance_sessions')) {
                $q = DB::table('student_attendance')->join('attendance_sessions', 'attendance_sessions.id', '=', 'student_attendance.attendance_session_id')
                    ->whereDate('attendance_sessions.attendance_date', $d);
                if ($schoolId) $q->where('attendance_sessions.school_id', $schoolId);
                if ($campusId) $q->where('attendance_sessions.campus_id', $campusId);
                $total = (clone $q)->count();
                $present = (clone $q)->whereIn('student_attendance.status', ['present', 'Present'])->count();
            }
            $attendanceTrend[] = ['label' => $d->format('D'), 'rate' => $total ? round($present * 100 / $total, 1) : 0];
        }

        $classStrength = [];
        if (Schema::hasTable('student_enrollments') && Schema::hasTable('school_classes')) {
            $q = DB::table('student_enrollments')->join('school_classes', 'school_classes.id', '=', 'student_enrollments.school_class_id')
                ->where('student_enrollments.status', 'active');
            if ($schoolId) $q->where('school_classes.school_id', $schoolId);
            if ($academicYearId) $q->where('student_enrollments.academic_year_id', $academicYearId);
            if ($campusId && Schema::hasTable('sections')) {
                $q->join('sections', 'sections.id', '=', 'student_enrollments.section_id')->where('sections.campus_id', $campusId);
            }
            $classStrength = $q->select('school_classes.name', DB::raw('COUNT(*) total'))
                ->groupBy('school_classes.id', 'school_classes.name')->orderByDesc('total')->limit(8)->get()
                ->map(fn($r) => ['label' => $r->name, 'total' => (int) $r->total])->values()->all();
        }

        $examPerformance = ['Passed' => 0, 'Needs Improvement' => 0];
        if (Schema::hasTable('exam_results') && Schema::hasTable('exam_subjects') && Schema::hasTable('examinations')) {
            $q = DB::table('exam_results')->join('exam_subjects', 'exam_subjects.id', '=', 'exam_results.exam_subject_id')
                ->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id');
            if ($schoolId) $q->where('examinations.school_id', $schoolId);
            if ($academicYearId && Schema::hasColumn('examinations', 'academic_year_id')) $q->where('examinations.academic_year_id', $academicYearId);
            $examPerformance['Passed'] = (clone $q)->whereNotNull('exam_results.marks_obtained')
                ->whereRaw('exam_results.marks_obtained >= exam_subjects.passing_marks')->count();
            $examPerformance['Needs Improvement'] = (clone $q)->whereNotNull('exam_results.marks_obtained')
                ->whereRaw('exam_results.marks_obtained < exam_subjects.passing_marks')->count();
        }

        return [
            'studentGender' => $gender,
            'staffDistribution' => ['Teaching' => $teacherCount, 'Non-teaching' => max(0, $staffCount - $teacherCount)],
            'feeStatus' => $feeStatus, 'monthlyFees' => $monthlyFees,
            'attendanceTrend' => $attendanceTrend, 'classStrength' => $classStrength,
            'examPerformance' => $examPerformance,
        ];
    }

    private function schoolScopedQuery(string $table, ?int $schoolId, ?int $campusId)
    {
        $q = DB::table($table);
        if ($schoolId && Schema::hasColumn($table, 'school_id')) $q->where("$table.school_id", $schoolId);
        if ($campusId && Schema::hasColumn($table, 'campus_id')) $q->where("$table.campus_id", $campusId);
        if (Schema::hasColumn($table, 'deleted_at')) $q->whereNull("$table.deleted_at");
        return $q;
    }

    private function staffAttendanceToday(?int $schoolId, ?int $campusId, string $status): int
    {
        if (!Schema::hasTable('employee_attendance') || !Schema::hasTable('staff_profiles')) return 0;
        $q = DB::table('employee_attendance')->join('staff_profiles', 'staff_profiles.id', '=', 'employee_attendance.staff_profile_id')
            ->whereDate('employee_attendance.attendance_date', today())->where('employee_attendance.status', $status);
        if ($schoolId) $q->where('staff_profiles.school_id', $schoolId);
        if ($campusId) $q->where('staff_profiles.campus_id', $campusId);
        return $q->count();
    }

    private function upcomingExamCount(?int $schoolId, ?int $campusId): int
    {
        if (!Schema::hasTable('exam_subjects') || !Schema::hasTable('examinations')) return 0;
        $q = DB::table('exam_subjects')->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id')
            ->whereDate('exam_subjects.exam_date', '>=', today());
        if ($schoolId) $q->where('examinations.school_id', $schoolId);
        if ($campusId && Schema::hasTable('sections')) {
            $q->join('sections', 'sections.id', '=', 'exam_subjects.section_id')->where('sections.campus_id', $campusId);
        }
        return $q->count();
    }

    private function availableCampuses(?int $schoolId): Collection
    {
        if (!Schema::hasTable('campuses')) return collect();
        $q = DB::table('campuses')->select('id', 'name')->orderBy('name');
        if ($schoolId) $q->where('school_id', $schoolId);
        return $q->get();
    }

    private function availableAcademicYears(?int $schoolId): Collection
    {
        if (!Schema::hasTable('academic_years')) return collect();
        $q = DB::table('academic_years')->select('id', 'name', 'status')->orderByDesc('start_date');
        if ($schoolId) $q->where('school_id', $schoolId);
        return $q->get();
    }

    private function viewForRole(string $role): string
    {
        return [
            'PRINCIPAL' => 'dashboards.principal',
            'SCHOOL_ADMIN' => 'dashboards.admin',
            'STUDENT' => 'dashboards.student',
            'TEACHER' => 'dashboards.teacher',
            'PARENT' => 'dashboards.parent',
        ][$role];
    }

    private function activeRoleCodes(): array
    {
        if (!Schema::hasTable('user_role_assignments') || !Schema::hasTable('roles')) return [];

        return DB::table('user_role_assignments')
            ->join('roles', 'roles.id', '=', 'user_role_assignments.role_id')
            ->where('user_role_assignments.user_id', auth()->id())
            ->where('user_role_assignments.is_active', 1)
            ->where(function ($q) {
                $q->whereNull('user_role_assignments.starts_at')
                  ->orWhere('user_role_assignments.starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('user_role_assignments.expires_at')
                  ->orWhere('user_role_assignments.expires_at', '>=', now());
            })
            ->where('roles.is_active', 1)
            ->pluck('roles.code')->all();
    }

    private function activeRoleCode(): ?string
    {
        $roles = $this->activeRoleCodes();
        $priority = ['SUPER_ADMIN', 'ORG_ADMIN', 'SCHOOL_ADMIN', 'PRINCIPAL', 'TEACHER', 'ACCOUNTANT', 'LIBRARIAN', 'TRANSPORT_MANAGER', 'HR_MANAGER', 'STUDENT', 'PARENT'];
        foreach ($priority as $code) if (in_array($code, $roles, true)) return $code;
        return $roles[0] ?? null;
    }

    private function activeSchoolId(): ?int
    {
        $assignment = DB::table('user_role_assignments')
            ->where('user_id', auth()->id())->where('is_active', 1)
            ->whereNotNull('school_id')->orderByDesc('id')->first();
        return $assignment ? (int) $assignment->school_id : (session('school_id') ? (int) session('school_id') : null);
    }

    private function activeCampusId(): ?int
    {
        $assignment = DB::table('user_role_assignments')
            ->where('user_id', auth()->id())->where('is_active', 1)
            ->whereNotNull('campus_id')->orderByDesc('id')->first();
        return $assignment ? (int) $assignment->campus_id : (session('campus_id') ? (int) session('campus_id') : null);
    }

    private function count(string $table): int
    {
        return Schema::hasTable($table) ? DB::table($table)->count() : 0;
    }

    private function schoolCount(string $table, ?int $schoolId, ?int $campusId, ?callable $extra = null): int
    {
        if (!Schema::hasTable($table)) return 0;
        $q = DB::table($table);
        if ($schoolId && Schema::hasColumn($table, 'school_id')) $q->where('school_id', $schoolId);
        if ($campusId && Schema::hasColumn($table, 'campus_id')) $q->where('campus_id', $campusId);
        if (Schema::hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        if ($extra) $extra($q);
        return $q->count();
    }

    private function todayAttendanceCount(?int $schoolId, ?int $campusId): int
    {
        if (!Schema::hasTable('student_attendance') || !Schema::hasTable('attendance_sessions')) return 0;
        $q = DB::table('student_attendance')
            ->join('attendance_sessions', 'attendance_sessions.id', '=', 'student_attendance.attendance_session_id')
            ->whereDate('attendance_sessions.attendance_date', today());
        if ($schoolId) $q->where('attendance_sessions.school_id', $schoolId);
        if ($campusId) $q->where('attendance_sessions.campus_id', $campusId);
        return $q->count();
    }

    private function approvalCount(?int $schoolId): int
    {
        if (!Schema::hasTable('caspa_approval_requests')) return 0;
        $q = DB::table('caspa_approval_requests')->whereIn('status', ['pending', 'submitted', 'in_review']);
        if ($schoolId) $q->where('school_id', $schoolId);
        return $q->count();
    }

    private function recentAnnouncements(?int $schoolId, ?int $campusId): Collection
    {
        if (!Schema::hasTable('announcements')) {
            return collect();
        }

        $columns = Schema::getColumnListing('announcements');
        $query = DB::table('announcements');

        if (in_array('school_id', $columns, true) && $schoolId) {
            $query->where('school_id', $schoolId);
        }

        if (in_array('campus_id', $columns, true)) {
            if ($campusId) {
                $query->where(function ($q) use ($campusId) {
                    $q->whereNull('campus_id')->orWhere('campus_id', $campusId);
                });
            } else {
                $query->whereNull('campus_id');
            }
        }

        if (in_array('status', $columns, true)) {
            $query->where('status', 'published');
        }

        // Parent dashboard must not expose staff-only or student-only notices.
        if (in_array('audience_type', $columns, true)) {
            $query->whereIn('audience_type', ['all_school', 'campus', 'parents']);
        }

        if (in_array('published_at', $columns, true)) {
            $query->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })->orderByDesc('published_at');
        } elseif (in_array('created_at', $columns, true)) {
            $query->orderByDesc('created_at');
        }

        if (in_array('expires_at', $columns, true)) {
            $query->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
        }

        $select = ['id', 'title'];
        foreach (['published_at', 'created_at', 'status', 'content'] as $column) {
            if (in_array($column, $columns, true)) {
                $select[] = $column;
            }
        }

        return $query->limit(8)->get(array_values(array_unique($select)))
            ->map(function ($notice) {
                $notice->date = $notice->published_at ?? $notice->created_at ?? null;
                $notice->status = $notice->status ?? 'Published';
                return $notice;
            });
    }

    private function upcomingEvents(?int $schoolId, ?int $campusId): Collection
    {
        if (!Schema::hasTable('events')) return collect();
        $q = DB::table('events')->whereDate('starts_at', '>=', today())->orderBy('starts_at');
        if ($schoolId) $q->where('school_id', $schoolId);
        if ($campusId) $q->where(function ($x) use ($campusId) { $x->whereNull('campus_id')->orWhere('campus_id', $campusId); });
        return $q->limit(6)->get(['title', 'starts_at', 'status'])
            ->map(fn ($x) => (object)['title' => $x->title, 'date' => $x->starts_at, 'status' => $x->status]);
    }

    private function studentAttendanceCount(int $studentId): int
    {
        return Schema::hasTable('student_attendance')
            ? DB::table('student_attendance')->where('student_id', $studentId)->count()
            : 0;
    }

    private function studentAssignmentCount(int $studentId, bool $completed): int
    {
        if (!Schema::hasTable('assignment_submissions')) return 0;
        $q = DB::table('assignment_submissions')->where('student_id', $studentId);
        return $completed ? $q->whereNotNull('submitted_at')->count() : $q->whereNull('submitted_at')->count();
    }

    private function studentExamCount(int $studentId): int
    {
        if (!Schema::hasTable('student_enrollments') || !Schema::hasTable('exam_subjects') || !Schema::hasTable('examinations')) return 0;
        $sectionIds = DB::table('student_enrollments')->where('student_id', $studentId)->where('status', 'active')->pluck('section_id');
        return DB::table('exam_subjects')->join('examinations', 'examinations.id', '=', 'exam_subjects.examination_id')
            ->whereIn('exam_subjects.section_id', $sectionIds)->whereDate('exam_subjects.exam_date', '>=', today())->count();
    }

    private function studentRecentAssignments(int $studentId): Collection
    {
        if (!Schema::hasTable('assignment_submissions') || !Schema::hasTable('assignments')) return collect();
        return DB::table('assignment_submissions')->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->where('assignment_submissions.student_id', $studentId)->orderByDesc('assignment_submissions.created_at')
            ->limit(8)->get(['assignments.title', 'assignment_submissions.created_at', 'assignment_submissions.status']);
    }

    private function studentUpcomingAssignments(int $studentId): Collection
    {
        if (!Schema::hasTable('student_enrollments') || !Schema::hasTable('assignments')) return collect();
        $sectionIds = DB::table('student_enrollments')->where('student_id', $studentId)->where('status', 'active')->pluck('section_id');
        return DB::table('assignments')->whereIn('section_id', $sectionIds)->whereDate('due_date', '>=', today())
            ->orderBy('due_date')->limit(6)->get(['title', 'due_date', 'status'])
            ->map(fn ($x) => (object)['title' => $x->title, 'date' => $x->due_date, 'status' => $x->status]);
    }
}
