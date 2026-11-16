@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    $activeAssignments = $user->roleAssignments()
        ->where('is_active', true)
        ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
        ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
        ->whereHas('role', fn($q) => $q->where('is_active', true))
        ->with('role.permissions')
        ->get();

    $assignedRoles = $activeAssignments->pluck('role.name')->filter()->unique()->values();
    $roleCodes = $activeAssignments->pluck('role.code')->filter()->unique()->values();

    $permissionCodes = $activeAssignments
        ->flatMap(fn($assignment) => $assignment->role?->permissions?->pluck('code') ?? collect())
        ->filter()
        ->unique()
        ->values()
        ->all();

    $can = fn(string $permission) => in_array($permission, $permissionCodes, true);

    // Match role codes as well as normalized role names.
    $normalizedRoleNames = $assignedRoles->map(
        fn($name) => strtoupper(str_replace([' ', '-'], '_', trim($name)))
    );

    $hasRole = function (string $role) use ($roleCodes, $normalizedRoleNames) {
        $aliases = [
            'ACCOUNTANT' => ['ACCOUNTANT', 'ACCOUNTS'],
            'HR_MANAGER' => ['HR_MANAGER', 'HR'],
            'TRANSPORT_MANAGER' => ['TRANSPORT_MANAGER', 'TRANSPORT'],
            'HOSTEL_WARDEN' => ['HOSTEL_WARDEN', 'WARDEN'],
            'LIBRARIAN' => ['LIBRARIAN', 'LIBRARY'],
        ];

        foreach ($aliases[$role] ?? [$role] as $candidate) {
            if ($roleCodes->contains($candidate) || $normalizedRoleNames->contains($candidate)) {
                return true;
            }
        }
        return false;
    };

    /*
    |--------------------------------------------------------------------------
    | Student menu
    |--------------------------------------------------------------------------
    | Student navigation is explicitly defined.
    | Route existence is checked before displaying each item.
    | Backend authorization and student-record filtering must still be
    | enforced by controllers and middleware.
    */

    $studentMenu = [
        'Platform' => [
            ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
        ],

        'Academics' => [
            // ['My Class & Section', 'school', 'class-sections.index', ['class-sections.*']],
            //['My Subjects', 'book', 'subjects.index', ['subjects.*']],
            ['Curriculum & Syllabus', 'book', 'curricula.index', ['curricula.*']],
            ['Lesson Planning', 'file', 'lesson-plans.index', ['lesson-plans.*']],
            ['Timetable Management', 'calendar', 'timetables.index', ['timetables.*']],
            ['My Attendance', 'calendar', 'attendance.index', ['attendance.*']],
            ['Homework', 'file', 'homework.index', ['homework.*']],
            ['Assignments', 'clipboard', 'assignments.index', ['assignments.*']],
            ['Study Materials', 'book', 'study-materials.index', ['study-materials.*']],
            ['Examinations & Results', 'file', 'examinations.index', ['examinations.*']],
            ['Report Cards', 'file', 'report-cards.index', ['report-cards.*']],
        ],

        'Business & Operations' => [
            //  ['My Fees', 'wallet', 'fees.dashboard', ['fees.*']],
            // ['My Transport', 'bus', 'transport.dashboard', ['transport.*']],
            // ['My Library', 'book', 'library.dashboard', ['library.*']],
            ['My Transport', 'bus', 'student.transport', ['student.transport']],
            ['My Library', 'book', 'student.library', ['student.library']],
            ['My Hostel', 'building', 'student.hostel', ['student.hostel']],
        ],

        'Communication & Support' => [
            ['Student Support', 'message-circle', '/student-support', ['student-support*']],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Parent menu
    |--------------------------------------------------------------------------
    | Parents use their linked children's existing records. Parent-specific
    | routes must enforce guardian-child ownership in the backend.
    */


    $parentMenu = [
        'Platform' => [
            ['Dashboard', 'grid', 'dashboard.parent', ['dashboard.parent']],
            ['My Children', 'users', 'parent.children.index', ['parent.children.*']],
        ],

        'Quick Access' => [
            ['Attendance', 'calendar', 'parent.attendance.index', ['parent.attendance.*']],
            ['Homework', 'file', 'parent.homework.index', ['parent.homework.*']],
            ['Assignments', 'clipboard', 'parent.assignments.index', ['parent.assignments.*']],
            //['Fee Details', 'wallet', '/fees', ['fees', 'fees/*']],
            ['Fee Details', 'wallet', 'parent.fees.index', ['parent.fees.*']],
            ['Examinations', 'file', '/examinations', ['examinations', 'examinations/*']],
            //['School Notices', 'megaphone', '/communication/announcements', ['communication/announcements', 'communication/announcements/*']],
            // ['Events Calendar', 'calendar-days', '/events', ['events', 'events/*']],
            // ['Parent–Teacher Meetings', 'users', '/ptm', ['ptm', 'ptm/*']],
            ['CASPA Chat', 'message-circle', '/caspa-chat', ['caspa-chat', 'caspa-chat/*']],
        ],

        'My Child Services' => [
            //['Study Materials', 'book', '/study-materials', ['study-materials', 'study-materials/*']],
            ['Report Cards', 'file-text', '/report-cards', ['report-cards', 'report-cards/*']],
            ['Children’s Transport', 'bus', 'parent.transport.index', ['parent.transport.*']],
            ['Hostel', 'building', 'parent.hostel.index', ['parent.hostel.*']],
            ['Library', 'book-open', 'parent.library.index', ['parent.library.*']],
        ],

        'AI & Support' => [
            //  ['Communication', 'message-circle', 'communication.dashboard', ['communication.*']],
            ['School Notices', 'megaphone', 'parent.announcements.index', ['parent.announcements.*']],
            ['Events Calendar', 'calendar-days', 'parent.events.index', ['parent.events.*']],
            ['Parent–Teacher Meetings', 'users', 'parent.ptm.index', ['parent.ptm.*']],
            ['Messages', 'message-circle', 'parent.messages.index', ['parent.messages.*']],
          //  ['CASPA AI', 'brain-circuit', '/caspa-ai', ['caspa-ai', 'caspa-ai/*']],

        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Teacher menu
    |--------------------------------------------------------------------------
    | Teachers use dedicated self-service routes. These links expose only the
    | authenticated teacher's own profile, attendance, payroll and messages.
    | Marks-entry access must still be restricted to assigned exam subjects
    | inside the controller.
    */

    $teacherMenu = [
        'Platform' => [
            ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
        ],

        'Academics' => [
            // Existing teacher academic tools remain available.
            ['Timetable Management', 'calendar', 'timetables.index', ['timetables.*']],
            // Student/class attendance is separate from the teacher's own attendance.
            ['Student Attendance', 'calendar-check', 'attendance.index', ['attendance.*']],
            ['Homework', 'file', 'homework.index', ['homework.*']],
            ['Assignments', 'clipboard', 'assignments.index', ['assignments.*']],
            ['Study Materials', 'book', 'study-materials.index', ['study-materials.*']],
            // Dedicated teacher marks-entry route limits access to assigned exam subjects.
            ['Marks Entry', 'clipboard', 'teacher.marks.index', ['teacher.marks.*']],
            ['Report Cards', 'file-text', 'report-cards.index', ['report-cards.*']],
        ],

        'My Workspace' => [
            ['My Profile', 'user', 'teacher.profile.show', ['teacher.profile.*']],
            ['My Attendance', 'calendar', 'teacher.attendance.index', ['teacher.attendance.*']],
            ['My Payroll', 'wallet', 'teacher.payroll.index', ['teacher.payroll.*']],
        ],

        'Communication & Support' => [
            ['Messages', 'message-circle', 'teacher.messages.index', ['teacher.messages.*']],
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Other role menus
    |--------------------------------------------------------------------------
    */

    $menuGroups = [
        'Platform' => [
            ['Dashboard', 'grid', 'dashboard', 'DASHBOARD.VIEW', ['dashboard', 'dashboard.*']],
        ],

        'Administration' => [
            ['Organizations', 'building', 'organizations.index', 'ORGANIZATIONS.VIEW', ['organizations.*']],
            ['Schools', 'school', 'schools.index', 'SCHOOLS.VIEW', ['schools.*']],
            ['Campuses', 'map', 'campuses.index', 'CAMPUSES.VIEW', ['campuses.*']],
            ['Users & Roles', 'users', 'role-builder.dashboard', 'ROLES.VIEW', ['role-builder.*']],
            ['Permission Matrix', 'shield', 'permission-matrix.index', 'ROLES.VIEW', ['permission-matrix.*']],
        ],

        'Academics' => [
            ['Students', 'graduation', 'students.index', 'STUDENTS.VIEW', ['students.*']],
            ['Parents & Guardians', 'users', 'guardians.index', 'GUARDIANS.VIEW', ['guardians.*']],
            ['Teachers & Staff', 'users', 'staff.index', 'STAFF.VIEW', ['staff.*', 'employee-profiles.*']],
            ['Classes & Sections', 'school', 'class-sections.index', 'ACADEMICS.VIEW', ['class-sections.*']],
            ['Subjects', 'book', 'subjects.index', 'ACADEMICS.VIEW', ['subjects.*']],
            ['Curriculum & Syllabus', 'book', 'curricula.index', 'ACADEMICS.VIEW', ['curricula.*']],
            ['Lesson Planning', 'file', 'lesson-plans.index', 'ACADEMICS.VIEW', ['lesson-plans.*']],
            ['Timetable Management', 'calendar', 'timetables.index', 'ACADEMICS.VIEW', ['timetables.*']],
            ['Attendance', 'calendar', 'attendance.index', 'ATTENDANCE.VIEW', ['attendance.*']],
            ['Homework', 'file', 'homework.index', 'HOMEWORK.VIEW', ['homework.*']],
            ['Assignments', 'clipboard', 'assignments.index', 'HOMEWORK.VIEW', ['assignments.*']],
            ['Study Materials', 'book', 'study-materials.index', 'ACADEMICS.VIEW', ['study-materials.*']],
            ['Examinations', 'file', 'examinations.index', 'EXAMINATIONS.VIEW', ['examinations.*']],
            ['Marks Entry', 'clipboard', 'marks-entry.index', 'EXAMINATIONS.VIEW', ['marks-entry.*']],
            ['Report Cards', 'file', 'report-cards.index', 'EXAMINATIONS.VIEW', ['report-cards.*']],
            ['Question Bank', 'book', 'question-bank.index', 'EXAMINATIONS.VIEW', ['question-bank.*']],
        ],

        'Business & Operations' => [
            ['Admissions CRM', 'users', 'admission-crm.index', 'ADMISSIONS.VIEW', ['admission-crm.*']],
            ['Admission Applications', 'file', 'admission-applications.index', 'ADMISSIONS.VIEW', ['admission-applications.*']],
            ['Counselling', 'message-circle', 'counselling.index', 'ADMISSIONS.VIEW', ['counselling.*']],
            ['Finance & Fees', 'wallet', 'fees.dashboard', 'FEES.VIEW', ['fees.*']],
            ['HR & Payroll', 'briefcase', 'hr.dashboard', 'HR.VIEW', ['hr.*']],
            ['Transport', 'bus', 'transport.dashboard', 'TRANSPORT.VIEW', ['transport.*']],
            ['Hostel', 'home', 'hostel.dashboard', 'HOSTEL.VIEW', ['hostel.*']],
            ['Library', 'book', 'library.dashboard', 'LIBRARY.VIEW', ['library.*']],
            ['Inventory & Assets', 'package', 'inventory.dashboard', 'INVENTORY.VIEW', ['inventory.*']],
        ],

        'Communication & Support' => [
            ['Communication', 'message-circle', 'communication.dashboard', 'COMMUNICATIONS.VIEW', ['communication.*']],
        ],

        'Reports & Governance' => [
            ['Reports & Analytics', 'chart', 'caspa.reports.home', 'REPORTS.VIEW', ['caspa.reports.*']],
            ['Audit Logs', 'file', 'audit-logs.dashboard', 'AUDIT.VIEW', ['audit-logs.*']],
            ['System Settings', 'settings', 'system-settings.dashboard', 'SETTINGS.VIEW', ['system-settings.*']],
        ],
    ];

    // Focused workspaces for the five operational staff accounts.
    $staffRoleMenus = [
        'ACCOUNTANT' => [
            'Platform' => [
                ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
            ],
            'Finance & Accounts' => [
                ['Finance & Fees', 'wallet', 'fees.dashboard', ['fees.*']],
                ['Reports & Analytics', 'chart', 'caspa.reports.home', ['caspa.reports.*']],
            ],
        ],
        'HR_MANAGER' => [
            'Platform' => [
                ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
            ],
            'HR & Payroll' => [
                ['HR Dashboard', 'briefcase', 'hr.dashboard', ['hr.dashboard']],
            ],
            'Reports & Governance' => [
                ['Reports & Analytics', 'chart', 'caspa.reports.home', ['caspa.reports.*']],
            ],
        ],
        'TRANSPORT_MANAGER' => [
            'Platform' => [
                ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
            ],
            'Transport Operations' => [
                ['Transport Dashboard', 'bus', 'transport.dashboard', ['transport.dashboard']],
            ],
        ],
        'HOSTEL_WARDEN' => [
            'Platform' => [
                ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
            ],
            'Hostel Operations' => [
                ['Hostel Dashboard', 'home', 'hostel.dashboard', ['hostel.dashboard']],
            ],
        ],
        'LIBRARIAN' => [
            'Platform' => [
                ['Dashboard', 'grid', 'dashboard', ['dashboard', 'dashboard.*']],
            ],
            'Library Operations' => [
                ['Library Dashboard', 'book', 'library.dashboard', ['library.dashboard']],
            ],
        ],
    ];

    $visibleGroups = [];

    if ($hasRole('TEACHER')) {
        // Teacher navigation is explicit and uses dedicated self-service routes.
        foreach ($teacherMenu as $groupName => $items) {
            $visibleItems = [];

            foreach ($items as [$label, $icon, $routeName, $patterns]) {
                if (!Route::has($routeName)) {
                    continue;
                }

                $visibleItems[] = [
                    'label' => $label,
                    'icon' => $icon,
                    'route' => $routeName,
                    'active' => request()->routeIs(...$patterns),
                ];
            }

            if (!empty($visibleItems)) {
                $visibleGroups[$groupName] = $visibleItems;
            }
        }
    } elseif ($hasRole('STUDENT')) {
        // Student sees only the explicitly approved student menu.
        foreach ($studentMenu as $groupName => $items) {
            $visibleItems = [];

            foreach ($items as [$label, $icon, $routeName, $patterns]) {

                // Student Support uses its direct URL.
                if ($routeName === '/student-support') {
                    $visibleItems[] = [
                        'label' => $label,
                        'icon' => $icon,
                        'url' => url('/student-support'),
                        'active' => request()->is('student-support') || request()->is('student-support/*'),
                    ];

                    continue;
                }

                if (!Route::has($routeName)) {
                    continue;
                }

                $visibleItems[] = [
                    'label' => $label,
                    'icon' => $icon,
                    'route' => $routeName,
                    'active' => request()->routeIs(...$patterns),
                ];
            }

            if (!empty($visibleItems)) {
                $visibleGroups[$groupName] = $visibleItems;
            }
        }
    } elseif ($hasRole('PARENT')) {
        // Parent navigation is explicit; it does not depend on admin permissions.
        // Each parent route must filter records through the authenticated guardian.
        foreach ($parentMenu as $groupName => $items) {
            $visibleItems = [];

            foreach ($items as [$label, $icon, $routeName, $patterns]) {
                // Parent links may use an existing named route or a direct URL.
                // Direct URLs remain visible even if a named route has not been registered.
                if (str_starts_with($routeName, '/')) {
                    $visibleItems[] = [
                        'label' => $label,
                        'icon' => $icon,
                        'url' => url($routeName),
                        'active' => request()->is(...$patterns),
                    ];

                    continue;
                }

                if (!Route::has($routeName)) {
                    continue;
                }

                $visibleItems[] = [
                    'label' => $label,
                    'icon' => $icon,
                    'route' => $routeName,
                    'active' => request()->routeIs(...$patterns),
                ];
            }

            if (!empty($visibleItems)) {
                $visibleGroups[$groupName] = $visibleItems;
            }
        }
    } else {
        $selectedStaffMenu = null;

        foreach (['ACCOUNTANT', 'HR_MANAGER', 'TRANSPORT_MANAGER', 'HOSTEL_WARDEN', 'LIBRARIAN'] as $staffRole) {
            if ($hasRole($staffRole)) {
                $selectedStaffMenu = $staffRoleMenus[$staffRole];
                break;
            }
        }

        if ($selectedStaffMenu !== null) {
            foreach ($selectedStaffMenu as $groupName => $items) {
                $visibleItems = [];

                foreach ($items as [$label, $icon, $routeName, $patterns]) {
                    if (!Route::has($routeName)) {
                        continue;
                    }

                    $visibleItems[] = [
                        'label' => $label,
                        'icon' => $icon,
                        'route' => $routeName,
                        'active' => request()->routeIs(...$patterns),
                    ];
                }

                if (!empty($visibleItems)) {
                    $visibleGroups[$groupName] = $visibleItems;
                }
            }
        } else {
            // All remaining roles retain the full permission-driven navigation.
            foreach ($menuGroups as $groupName => $items) {
                $visibleItems = [];

                foreach ($items as [$label, $icon, $routeName, $permission, $patterns]) {
                    if (!$can($permission) || !Route::has($routeName)) {
                        continue;
                    }

                    $visibleItems[] = [
                        'label' => $label,
                        'icon' => $icon,
                        'route' => $routeName,
                        'active' => request()->routeIs(...$patterns),
                    ];
                }

                if (!empty($visibleItems)) {
                    $visibleGroups[$groupName] = $visibleItems;
                }
            }
        }
    }

    /* One distinct line icon per menu option (falls back to <x-icon> for any new item) */
    $svgIcons = [
        'Dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'My Profile' => '<circle cx="12" cy="8" r="3.5"/><path d="M4.5 21v-1.5A5.5 5.5 0 0 1 10 14h4a5.5 5.5 0 0 1 5.5 5.5V21"/>',
        'My Payroll' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="M3 10h18M7 15h3M16 15h1"/>',
        'My Children' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20v-1.2A4.8 4.8 0 0 1 7.8 14h2.4A4.8 4.8 0 0 1 15 18.8V20"/><circle cx="17.5" cy="9" r="2.4"/><path d="M17 14.2a4 4 0 0 1 4 3.8V20"/>',
        'Curriculum & Syllabus' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M4 21.5V5.5M9 8h7M9 12h7"/>',
        'Lesson Planning' => '<path d="M5 3h11a2 2 0 0 1 2 2v4"/><path d="M5 3v16a2 2 0 0 0 2 2h4"/><path d="M9 8h5M9 12h3"/><path d="m20.5 13-6 6-2 .5.5-2 6-6Z"/>',
        'Timetable Management' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 10h18"/><circle cx="15.5" cy="16" r="3"/><path d="M15.5 14.5V16l1 .8"/>',
        'My Attendance' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 10h18M9 15.5l2 2 4-4"/>',
        'Student Attendance' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 10h18M8 15h2M14 15h2M8 18h2"/>',
        'Attendance' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 10h18M9 15.5l2 2 4-4"/>',
        'Homework' => '<path d="m4 20 1-4L16.5 4.5a2.1 2.1 0 0 1 3 3L8 19Z"/><path d="m14 7 3 3M4 20h6"/>',
        'Assignments' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 10h6M9 14h6M9 18h3"/>',
        'Study Materials' => '<rect x="3" y="3" width="4" height="18" rx="1"/><rect x="9" y="3" width="4" height="18" rx="1"/><path d="m15.5 5 3.4-.9 2.6 14-3.4.9Z"/>',
        'Examinations & Results' => '<circle cx="12" cy="9" r="5.5"/><path d="m8.8 13.8-1.3 7 4.5-2.5 4.5 2.5-1.3-7"/>',
        'Report Cards' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 17v-3M12 17v-5M15 17v-2"/>',
        'My Transport' => '<rect x="4" y="3.5" width="16" height="13" rx="3"/><path d="M4 11h16M8 16.5V20M16 16.5V20"/><circle cx="8" cy="14" r=".7"/><circle cx="16" cy="14" r=".7"/>',
        'Children’s Transport' => '<rect x="4" y="3.5" width="16" height="13" rx="3"/><path d="M4 11h16M8 16.5V20M16 16.5V20"/><circle cx="8" cy="14" r=".7"/><circle cx="16" cy="14" r=".7"/>',
        'Transport' => '<rect x="4" y="3.5" width="16" height="13" rx="3"/><path d="M4 11h16M8 16.5V20M16 16.5V20"/><circle cx="8" cy="14" r=".7"/><circle cx="16" cy="14" r=".7"/>',
        'My Library' => '<path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5Z"/><path d="M13 3v6l2-1.5L17 9V3"/>',
        'Library' => '<path d="M2 5.5C4.5 4 8 4 12 6.5c4-2.5 7.5-2.5 10-1v13c-2.5-1.5-6-1.5-10 1-4-2.5-7.5-2.5-10-1Z"/><path d="M12 6.5v13"/>',
        'My Hostel' => '<path d="M3 18V8M3 14h18v4M21 14v-2.5A2.5 2.5 0 0 0 18.5 9H11v5"/><circle cx="7" cy="11.5" r="1.8"/>',
        'Hostel' => '<path d="M3 18V8M3 14h18v4M21 14v-2.5A2.5 2.5 0 0 0 18.5 9H11v5"/><circle cx="7" cy="11.5" r="1.8"/>',
        'Student Support' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5"/><path d="m5.6 5.6 3.9 3.9M14.5 14.5l3.9 3.9M18.4 5.6l-3.9 3.9M9.5 14.5l-3.9 3.9"/>',
        'Fee Details' => '<path d="M4 7a2 2 0 0 1 2-2h12v3"/><rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M16 13.5h2"/>',
        'Finance & Fees' => '<path d="M4 7a2 2 0 0 1 2-2h12v3"/><rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M16 13.5h2"/>',
        'Examinations' => '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M9 14h6M9 17h3"/>',
        'CASPA Chat' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12Z"/>',
        'School Notices' => '<path d="M3 11v3a1 1 0 0 0 1 1h3l8 4V6L7 10H4a1 1 0 0 0-1 1Z"/><path d="M18.5 9a4 4 0 0 1 0 6"/>',
        'Events Calendar' => '<rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M8 2v4M16 2v4M3 10h18M8 14h.01M12 14h.01M16 14h.01M8 17.5h.01M12 17.5h.01"/>',
        'Parent–Teacher Meetings' => '<path d="M3 4h18M5 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4"/><path d="M12 15v4M8 21l4-2 4 2"/>',
        'Messages' => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><path d="m3.5 7.5 8.5 6 8.5-6"/>',
        'CASPA AI' => '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9Z"/><path d="M19 15v4M17 17h4"/>',
        'Organizations' => '<rect x="5" y="3" width="10" height="18" rx="1.5"/><path d="M15 9h4a1 1 0 0 1 1 1v11h-5M9 7h2M9 11h2M9 15h2"/>',
        'Schools' => '<path d="m3 9 9-5 9 5"/><path d="M5 9v11h14V9M10 20v-5h4v5"/>',
        'Campuses' => '<path d="M12 21s7-6.2 7-11.2A7 7 0 0 0 5 9.8C5 14.8 12 21 12 21Z"/><circle cx="12" cy="9.8" r="2.6"/>',
        'Users & Roles' => '<circle cx="9" cy="8" r="3.5"/><path d="M3 20v-1a5 5 0 0 1 5-5h2"/><circle cx="17.5" cy="16.5" r="2.2"/><path d="M17.5 12.5v1.3M17.5 19.2v1.3M14 16.5h1.3M19.7 16.5H21"/>',
        'Permission Matrix' => '<path d="M12 3 4.5 6v5.5c0 4.5 3 8 7.5 9.5 4.5-1.5 7.5-5 7.5-9.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>',
        'Students' => '<path d="m22 9-10-5L2 9l10 5Z"/><path d="M6 11.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-4.5M22 9v6"/>',
        'Parents & Guardians' => '<circle cx="8" cy="8" r="3"/><circle cx="17" cy="9" r="2.4"/><path d="M2.5 20v-1a4.5 4.5 0 0 1 4.5-4.5h2A4.5 4.5 0 0 1 13.5 19v1M15 14.5a4 4 0 0 1 6 3.5V20"/>',
        'Teachers & Staff' => '<rect x="3" y="7" width="18" height="13" rx="2.5"/><path d="M9 7V5a1.5 1.5 0 0 1 1.5-1.5h3A1.5 1.5 0 0 1 15 5v2M3 13h18"/>',
        'Classes & Sections' => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/><path d="M17 3v6M14 6h6M3 15h6M6 12v6"/>',
        'Subjects' => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V3H6.5A2.5 2.5 0 0 0 4 5.5Z"/><path d="M4 19.5V21h16v-1.5M9 13l2.5-6 2.5 6M9.8 11.5h3.4"/>',
        'Marks Entry' => '<path d="m3.5 6 1.5 1.5L8 4.5M3.5 12.5 5 14l3-3M3.5 19 5 20.5l3-3"/><path d="M12 6h9M12 13h9M12 19h9"/>',
        'Question Bank' => '<circle cx="12" cy="12" r="9"/><path d="M9.5 9.3a2.6 2.6 0 0 1 5 .9c0 1.7-2.5 2.2-2.5 3.8M12 17.2v.01"/>',
        'Admissions CRM' => '<circle cx="9.5" cy="8" r="3.5"/><path d="M3 20v-1a5 5 0 0 1 5-5h3M18 10v6M15 13h6"/>',
        'Admission Applications' => '<path d="M14 3H7a2 2 0 0 0-2 2v6M5 18v1a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8Z"/><path d="M14 3v5h5M3 15h9m-3-3 3 3-3 3"/>',
        'Counselling' => '<path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.6A8 8 0 1 1 21 12Z"/><path d="M12 15.3s-3-1.8-3-3.9a1.7 1.7 0 0 1 3-1 1.7 1.7 0 0 1 3 1c0 2.1-3 3.9-3 3.9Z"/>',
        'HR & Payroll' => '<rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="12" cy="10" r="3"/><path d="M6.5 19v-.5A4.5 4.5 0 0 1 11 14h2a4.5 4.5 0 0 1 4.5 4.5v.5"/>',
        'Inventory & Assets' => '<path d="m12 3 8.5 4.5v9L12 21l-8.5-4.5v-9Z"/><path d="m3.7 7.6 8.3 4.4 8.3-4.4M12 12v9"/>',
        'Communication' => '<path d="m21 3-9.5 18-2-8L1.5 11Z"/><path d="m21 3-11.5 10"/>',
        'Reports & Analytics' => '<path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6" rx=".6"/><rect x="12" y="8" width="3" height="10" rx=".6"/><rect x="17" y="5" width="3" height="13" rx=".6"/>',
        'Audit Logs' => '<path d="M8 4h10a2 2 0 0 1 2 2v11a3 3 0 0 1-3 3H7"/><path d="M8 4a2 2 0 0 0-2 2v11a3 3 0 0 1-3 3h4a3 3 0 0 0 3-3V4Z"/><path d="M12 9h5M12 13h5"/>',
        'System Settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"/>',
    ];

    $currentRoleLabel = $assignedRoles->isNotEmpty()
        ? $assignedRoles->implode(' / ')
        : 'No Active Role';

    $initials = collect(preg_split('/\s+/', trim($user->name ?? 'User')))
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<style>
    /* Profile area only: name initials avatar + popup with My Profile / Logout. Sidebar colours stay as in your layout. */
    .side-svg {
        flex: 0 0 auto;
        fill: none;
        stroke: currentColor;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .sbx-bottom {
        position: relative;
    }

    .sbx-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 10px 12px;
        border: 1px solid color-mix(in srgb, currentColor 14%, transparent);
        border-radius: 14px;
        background: color-mix(in srgb, currentColor 5%, transparent);
        color: inherit;
        font: inherit;
        text-align: left;
        cursor: pointer;
        transition: background .15s, border-color .15s;
    }

    .sbx-trigger:hover,
    .sbx-trigger[aria-expanded="true"] {
        background: color-mix(in srgb, currentColor 10%, transparent);
    }

    .sbx-trigger:focus-visible {
        outline: 2px solid #6366F1;
        outline-offset: 2px;
    }

    .sbx-av {
        flex: 0 0 auto;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 14px;
        color: inherit;
        background: color-mix(in srgb, currentColor 14%, transparent);
        border: 0;
        box-shadow: none;
    }

    .sbx-trigger .profile-info {
        flex: 1;
        min-width: 0;
    }

    .sbx-trigger .profile-name {
        font-weight: 700;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sbx-trigger .profile-role {
        opacity: .7;
        font-size: 12px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sbx-chev {
        flex: 0 0 auto;
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        opacity: .55;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .sbx-menu {
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: calc(100% - 4px);
        background: #fff;
        color: #1E293B;
        border: 1px solid #E2E8F0;
        border-radius: 14px;
        padding: 6px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, .18);
        z-index: 60;
        transform-origin: bottom center;
        animation: sbxPop .16s ease-out;
    }

    .sbx-menu[hidden] {
        display: none;
    }

    .sbx-item {
        display: flex;
        align-items: center;
        gap: 11px;
        width: 100%;
        padding: 11px 12px;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #1E293B;
        font: 600 14px inherit;
        font-family: inherit;
        text-decoration: none;
        text-align: left;
        cursor: pointer;
    }

    .sbx-item:hover {
        background: #F1F5F9;
    }

    .sbx-danger {
        color: #E11D48;
    }

    .sbx-danger:hover {
        background: #FFF1F2;
    }

    .sbx-sep {
        height: 1px;
        margin: 6px 8px;
        background: #E2E8F0;
    }

    .sbx-menu .sidebar-logout {
        margin: 0;
    }

    @@keyframes sbxPop {
        from {
            opacity: 0;
            transform: translateY(6px) scale(.98);
        }

        to {
            opacity: 1;
            transform: none;
        }
    }

    @@media (prefers-reduced-motion:reduce) {
        .sbx-menu {
            animation: none;
        }

        .sbx-trigger {
            transition: none;
        }
    }
</style>

<aside class="sidebar">

    <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="brand">
        <img src="{{ asset('images/schoolnexa-logo.png') }}" alt="SchoolNexa Logo" class="brand-logo">

        <span class="brand-name">School<span>Nexa</span></span>
    </a>

    <nav class="sidebar-nav" aria-label="Main navigation">

        @forelse($visibleGroups as $groupName => $items)

            <div class="sidebar-nav-group">

                <div class="side-caption">{{ $groupName }}</div>

                @foreach($items as $item)

                    <a href="{{ $item['url'] ?? route($item['route']) }}"
                        class="side-link {{ $item['active'] ? 'active' : '' }}" @if($item['active']) aria-current="page" @endif
                        title="{{ $item['label'] }}">

                        @if(isset($svgIcons[$item['label']]))
                            <svg class="side-svg" width="19" height="19" viewBox="0 0 24 24" aria-hidden="true">{!! $svgIcons[$item['label']] !!}</svg>
                        @else
                            <x-icon :name="$item['icon']" size="19" />
                        @endif

                        <span class="side-text">{{ $item['label'] }}</span>
                    </a>

                @endforeach

            </div>

        @empty

            <div class="sidebar-empty">
                No modules are assigned to this account.
            </div>

        @endforelse

    </nav>

    <div class="side-bottom sbx-bottom" id="sbxUser">

        {{-- Popup menu: My Profile / Logout --}}
        <div class="sbx-menu" id="sbxMenu" role="menu" hidden>

            @if($hasRole('TEACHER') && Route::has('teacher.profile.edit'))
                <a href="{{ route('teacher.profile.edit') }}" class="sbx-item" role="menuitem">
                    <x-icon name="user" size="17" />
                    <span>My Profile</span>
                </a>
            @elseif($hasRole('PARENT') && Route::has('parent.profile.edit'))
                <a href="{{ route('parent.profile.edit') }}" class="sbx-item" role="menuitem">
                    <x-icon name="user" size="17" />
                    <span>My Profile</span>
                </a>
            @elseif(Route::has('profile.edit'))
                <a href="{{ route('profile.edit') }}" class="sbx-item" role="menuitem">
                    <x-icon name="user" size="17" />
                    <span>My Profile</span>
                </a>
            @endif

            @if(Route::has('logout'))
                <div class="sbx-sep"></div>

                <form method="POST" action="{{ route('logout') }}" class="sidebar-logout">
                    @csrf

                    <button type="submit" class="sbx-item sbx-danger" role="menuitem">
                        <x-icon name="log-out" size="17" />
                        <span>Logout</span>
                    </button>
                </form>
            @endif

        </div>

        {{-- Profile trigger --}}
        <button type="button" class="profile-mini sbx-trigger" id="sbxTrigger" aria-haspopup="menu"
            aria-expanded="false" aria-controls="sbxMenu">

            <div class="avatar sbx-av">{{ mb_strtoupper($initials ?: 'U') }}</div>

            <div class="profile-info">
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-role" title="{{ $currentRoleLabel }}">
                    {{ $currentRoleLabel }}
                </div>
            </div>

            <svg class="sbx-chev" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m8 9 4-4 4 4M8 15l4 4 4-4" />
            </svg>

        </button>

    </div>

</aside>

<script>
    /* Profile popup: click to open, click outside or Esc to close */
    (function () {
        var t = document.getElementById('sbxTrigger'), m = document.getElementById('sbxMenu'), box = document.getElementById('sbxUser');
        if (!t || !m) return;
        function set(open) { m.hidden = !open; t.setAttribute('aria-expanded', open ? 'true' : 'false'); }
        t.addEventListener('click', function (e) { e.stopPropagation(); set(m.hidden); });
        document.addEventListener('click', function (e) { if (!m.hidden && !box.contains(e.target)) set(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.hidden) { set(false); t.focus(); } });
    })();
</script>