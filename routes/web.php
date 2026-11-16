<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\EmployeeProfileController;
use App\Http\Controllers\ClassSectionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\MarksEntryController;
use App\Http\Controllers\AdmissionApplicationController;
use App\Http\Controllers\CurriculumController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\Transport\TransportController;
use App\Http\Controllers\Hostel\HostelController;
use App\Http\Controllers\Library\LibraryController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\AlumniController;
use App\Http\Controllers\CareerOpportunityController;
use App\Http\Controllers\CommunicationController;
use App\Http\Controllers\PtmController;
use App\Http\Controllers\StudentSupportController;
use App\Http\Controllers\CaspaIntelligenceController;
use App\Http\Controllers\ReportsAnalyticsController;
use App\Http\Controllers\ApprovalCenterController;
use App\Http\Controllers\Parent\AttendanceController as ParentAttendanceController;
use App\Http\Controllers\Parent\HomeworkController as ParentHomeworkController;
use App\Http\Controllers\Parent\AssignmentController as ParentAssignmentController;
use App\Http\Controllers\Parent\TransportController as ParentTransportController;
use App\Http\Controllers\Parent\LibraryController as ParentLibraryController;
use App\Http\Controllers\Parent\HostelController as ParentHostelController;
use App\Http\Controllers\Parent\FeeController as ParentFeeController;
use App\Http\Controllers\Parent\ChildrenController as ParentChildrenController;
use App\Http\Controllers\Parent\AnnouncementController as ParentAnnouncementController;
use App\Http\Controllers\Parent\EventController as ParentEventController;
use App\Http\Controllers\Parent\PtmController as ParentPtmController;
use App\Http\Controllers\Parent\MessageController as ParentMessageController;

// Home
Route::get('/', function () {
    return view('welcome');
});

// Dashboard
Route::middleware(['auth', 'permission:DASHBOARD.VIEW'])
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Authentication Routes
require __DIR__.'/auth.php';
// Class & Section Management - Screen 14

Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/class-sections', [ClassSectionController::class, 'index'])
        ->name('class-sections.index');
});

Route::middleware('permission:ACADEMICS.CREATE')->group(function () {
    Route::post('/class-sections/classes', [ClassSectionController::class, 'storeClass'])
        ->name('class-sections.classes.store');

    Route::post('/class-sections/sections', [ClassSectionController::class, 'storeSection'])
        ->name('class-sections.sections.store');
});

Route::middleware('permission:ACADEMICS.UPDATE')->group(function () {
    Route::put('/class-sections/classes/{schoolClass}', [ClassSectionController::class, 'updateClass'])
        ->name('class-sections.classes.update');

    Route::put('/class-sections/sections/{section}', [ClassSectionController::class, 'updateSection'])
        ->name('class-sections.sections.update');

    Route::patch('/class-sections/classes/{schoolClass}/status', [ClassSectionController::class, 'toggleClassStatus'])
        ->name('class-sections.classes.status');

    Route::patch('/class-sections/sections/{section}/status', [ClassSectionController::class, 'toggleSectionStatus'])
        ->name('class-sections.sections.status');
});

// ==========================================
// ORGANIZATION MANAGEMENT
// ==========================================

Route::middleware(['auth', 'permission:ORGANIZATIONS.VIEW'])
    ->prefix('organizations')
    ->name('organizations.')
    ->group(function () {

        Route::get('/', [OrganizationController::class, 'index'])
            ->name('index');

        Route::middleware('permission:ORGANIZATIONS.CREATE')->group(function () {
            Route::get('/create', [OrganizationController::class, 'create'])
                ->name('create');

            Route::post('/', [OrganizationController::class, 'store'])
                ->name('store');
        });

        Route::middleware('permission:ORGANIZATIONS.UPDATE')->group(function () {
            Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])
                ->name('edit');

            Route::put('/{organization}', [OrganizationController::class, 'update'])
                ->name('update');

            Route::patch('/{organization}/status', [OrganizationController::class, 'toggleStatus'])
                ->name('status');
        });
    });


// ==========================================
// SCHOOL MANAGEMENT
// ==========================================

Route::middleware(['auth'])
    ->prefix('schools')
    ->name('schools.')
    ->group(function () {

        Route::get('/', [SchoolController::class, 'index'])
            ->middleware('permission:SCHOOLS.VIEW')
            ->name('index');

        Route::middleware('permission:SCHOOLS.CREATE')->group(function () {
            Route::get('/create', [SchoolController::class, 'create'])
                ->name('create');

            Route::post('/', [SchoolController::class, 'store'])
                ->name('store');
        });

        Route::middleware('permission:SCHOOLS.UPDATE')->group(function () {
            Route::get('/{school}/edit', [SchoolController::class, 'edit'])
                ->name('edit');

            Route::put('/{school}', [SchoolController::class, 'update'])
                ->name('update');

            Route::patch('/{school}/status', [SchoolController::class, 'toggleStatus'])
                ->name('status');
        });
    });


// Employee Profiles - Screen 13

Route::middleware('permission:STAFF.VIEW')->group(function () {
    Route::get('/employee-profiles', [EmployeeProfileController::class, 'index'])
        ->name('employee-profiles.index');

    Route::get('/employee-profiles/{staff}', [EmployeeProfileController::class, 'show'])
        ->name('employee-profiles.show');
});

// Guardian / Parent routes
Route::middleware(['auth'])->group(function () {

    Route::get('/guardians', [GuardianController::class, 'index'])
        ->middleware('permission:GUARDIANS.VIEW')
        ->name('guardians.index');

    Route::get('/guardians/create', [GuardianController::class, 'create'])
        ->middleware('permission:GUARDIANS.CREATE')
        ->name('guardians.create');

    Route::post('/guardians', [GuardianController::class, 'store'])
        ->middleware('permission:GUARDIANS.CREATE')
        ->name('guardians.store');

    Route::get('/guardians/{guardian}', [GuardianController::class, 'show'])
        ->middleware('permission:GUARDIANS.VIEW')
        ->name('guardians.show');

    Route::get('/guardians/{guardian}/edit', [GuardianController::class, 'edit'])
        ->middleware('permission:GUARDIANS.UPDATE')
        ->name('guardians.edit');

    Route::put('/guardians/{guardian}', [GuardianController::class, 'update'])
        ->middleware('permission:GUARDIANS.UPDATE')
        ->name('guardians.update');

    Route::delete('/guardians/{guardian}', [GuardianController::class, 'destroy'])
        ->middleware('permission:GUARDIANS.DELETE')
        ->name('guardians.destroy');
});

// ==========================================
// CAMPUS MANAGEMENT
// ==========================================

Route::middleware(['auth'])
    ->prefix('campuses')
    ->name('campuses.')
    ->group(function () {

        Route::get('/', [CampusController::class, 'index'])
            ->middleware('permission:CAMPUSES.VIEW')
            ->name('index');

        Route::middleware('permission:CAMPUSES.CREATE')->group(function () {
            Route::get('/create', [CampusController::class, 'create'])
                ->name('create');

            Route::post('/', [CampusController::class, 'store'])
                ->name('store');
        });

        Route::middleware('permission:CAMPUSES.UPDATE')->group(function () {
            Route::get('/{campus}/edit', [CampusController::class, 'edit'])
                ->name('edit');

            Route::put('/{campus}', [CampusController::class, 'update'])
                ->name('update');

            Route::patch('/{campus}/status', [CampusController::class, 'toggleStatus'])
                ->name('status');
        });
    });



// Staff / Teacher routes
Route::middleware(['auth'])->group(function () {

    Route::get('/staff', [StaffController::class, 'index'])
        ->middleware('permission:STAFF.VIEW')
        ->name('staff.index');

    Route::get('/staff/create', [StaffController::class, 'create'])
        ->middleware('permission:STAFF.CREATE')
        ->name('staff.create');

    Route::post('/staff', [StaffController::class, 'store'])
        ->middleware('permission:STAFF.CREATE')
        ->name('staff.store');

    Route::get('/staff/{staff}', [StaffController::class, 'show'])
        ->middleware('permission:STAFF.VIEW')
        ->name('staff.show');

    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])
        ->middleware('permission:STAFF.UPDATE')
        ->name('staff.edit');

    Route::put('/staff/{staff}', [StaffController::class, 'update'])
        ->middleware('permission:STAFF.UPDATE')
        ->name('staff.update');

    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])
        ->middleware('permission:STAFF.DELETE')
        ->name('staff.destroy');
});


// ==========================================
// STUDENT MANAGEMENT
// Screen 9 - Student Directory
// ==========================================

Route::middleware(['auth'])->group(function () {

    // Student Directory
    Route::middleware('permission:STUDENTS.VIEW')
        ->get('/students', [StudentController::class, 'index'])
        ->name('students.index');

    // Create Student
    Route::middleware('permission:STUDENTS.CREATE')
        ->get('/students/create', [StudentController::class, 'create'])
        ->name('students.create');

    Route::middleware('permission:STUDENTS.CREATE')
        ->post('/students', [StudentController::class, 'store'])
        ->name('students.store');

    // Student Profile
    Route::middleware('permission:STUDENTS.VIEW')
        ->get('/students/{student}', [StudentController::class, 'show'])
        ->name('students.show');

    // Edit Student
    Route::middleware('permission:STUDENTS.UPDATE')
        ->get('/students/{student}/edit', [StudentController::class, 'edit'])
        ->name('students.edit');

    Route::middleware('permission:STUDENTS.UPDATE')
        ->put('/students/{student}', [StudentController::class, 'update'])
        ->name('students.update');

    // Activate / Deactivate Student
    Route::middleware('permission:STUDENTS.UPDATE')
        ->patch('/students/{student}/status', [StudentController::class, 'toggleStatus'])
        ->name('students.status');
});


// ==========================================
// FEES MANAGEMENT
// ==========================================

Route::middleware(['auth', 'permission:FEES.REFUND'])
    ->get('/fees/refunds', function () {
        return 'Fee refund access granted!';
    })
    ->name('fees.refunds');




// 1. Static routes first
Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/subjects', [SubjectController::class, 'index'])
        ->name('subjects.index');
});

Route::middleware('permission:ACADEMICS.CREATE')->group(function () {
    Route::get('/subjects/create', [SubjectController::class, 'create'])
        ->name('subjects.create');

    Route::post('/subjects', [SubjectController::class, 'store'])
        ->name('subjects.store');
});

// 2. Dynamic subject routes after static routes
Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/subjects/{subject}', [SubjectController::class, 'show'])
        ->name('subjects.show');
});

Route::middleware('permission:ACADEMICS.UPDATE')->group(function () {
    Route::get('/subjects/{subject}/edit', [SubjectController::class, 'edit'])
        ->name('subjects.edit');

    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])
        ->name('subjects.update');
});

Route::middleware('permission:ACADEMICS.DELETE')->group(function () {
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])
        ->name('subjects.destroy');
});

// 3. Class-subject assignments
Route::middleware('permission:ACADEMICS.CREATE')->post(
    '/subjects/{subject}/assignments',
    [SubjectController::class, 'assignClass']
)->name('subjects.assignments.store');

Route::middleware('permission:ACADEMICS.UPDATE')->put(
    '/class-subjects/{classSubject}',
    [SubjectController::class, 'updateAssignment']
)->name('subjects.assignments.update');

Route::middleware('permission:ACADEMICS.DELETE')->delete(
    '/class-subjects/{classSubject}',
    [SubjectController::class, 'removeAssignment']
)->name('subjects.assignments.destroy');



// Curriculum / Syllabus - Screen 16

// Curriculum / Syllabus - Screen 16

// Static routes first
Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/curricula', [CurriculumController::class, 'index'])
        ->name('curricula.index');

    Route::get('/curricula/{curriculum}/pdf', [CurriculumController::class, 'downloadPdf'])
        ->name('curricula.pdf');
});

Route::middleware('permission:ACADEMICS.CREATE')->group(function () {
    Route::get('/curricula/create', [CurriculumController::class, 'create'])
        ->name('curricula.create');

    Route::post('/curricula', [CurriculumController::class, 'store'])
        ->name('curricula.store');

    Route::post('/curricula/{curriculum}/chapters', [CurriculumController::class, 'storeChapter'])
        ->name('curricula.chapters.store');

    Route::post('/chapters/{chapter}/topics', [CurriculumController::class, 'storeTopic'])
        ->name('curricula.topics.store');

    Route::post('/curricula/{curriculum}/publish', [CurriculumController::class, 'publish'])
        ->name('curricula.publish');
});

// Dynamic routes after static routes
Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/curricula/{curriculum}', [CurriculumController::class, 'show'])
        ->name('curricula.show');
});

Route::middleware('permission:ACADEMICS.UPDATE')->group(function () {
    Route::get('/curricula/{curriculum}/edit', [CurriculumController::class, 'edit'])
        ->name('curricula.edit');

    Route::put('/curricula/{curriculum}', [CurriculumController::class, 'update'])
        ->name('curricula.update');

    Route::put('/chapters/{chapter}', [CurriculumController::class, 'updateChapter'])
        ->name('curricula.chapters.update');

    Route::patch('/topics/{topic}/progress', [CurriculumController::class, 'updateTopic'])
        ->name('curricula.topics.progress');

    Route::post('/curricula/{curriculum}/archive', [CurriculumController::class, 'archive'])
        ->name('curricula.archive');
});

Route::middleware('permission:ACADEMICS.DELETE')->group(function () {
    Route::delete('/curricula/{curriculum}', [CurriculumController::class, 'destroy'])
        ->name('curricula.destroy');

    Route::delete('/chapters/{chapter}', [CurriculumController::class, 'destroyChapter'])
        ->name('curricula.chapters.destroy');

    Route::delete('/topics/{topic}', [CurriculumController::class, 'destroyTopic'])
        ->name('curricula.topics.destroy');
});



use App\Http\Controllers\LessonPlanController;

// Lesson Planning
Route::middleware('auth')->prefix('lesson-plans')->name('lesson-plans.')->group(function () {
    Route::get('/', [LessonPlanController::class, 'index'])
        ->middleware('permission:ACADEMICS.VIEW')
        ->name('index');

    Route::get('/create', [LessonPlanController::class, 'create'])
        ->middleware('permission:ACADEMICS.CREATE')
        ->name('create');

    Route::post('/', [LessonPlanController::class, 'store'])
        ->middleware('permission:ACADEMICS.CREATE')
        ->name('store');

    Route::get('/{lessonPlan}/pdf', [LessonPlanController::class, 'pdf'])
        ->middleware('permission:ACADEMICS.VIEW')
        ->name('pdf');

    Route::patch('/{lessonPlan}/status', [LessonPlanController::class, 'updateStatus'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('status');

    Route::get('/{lessonPlan}/edit', [LessonPlanController::class, 'edit'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('edit');

    Route::put('/{lessonPlan}', [LessonPlanController::class, 'update'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('update');

    Route::delete('/{lessonPlan}', [LessonPlanController::class, 'destroy'])
        ->middleware('permission:ACADEMICS.DELETE')
        ->name('destroy');

    Route::get('/{lessonPlan}', [LessonPlanController::class, 'show'])
        ->middleware('permission:ACADEMICS.VIEW')
        ->name('show');
});



use App\Http\Controllers\TimetableController;

Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
    Route::get('/timetables', [TimetableController::class, 'index'])
        ->name('timetables.index');

    Route::get('/timetables/create', [TimetableController::class, 'create'])
        ->middleware('permission:ACADEMICS.CREATE')
        ->name('timetables.create');

    Route::get('/timetables/{timetable}', [TimetableController::class, 'show'])
        ->name('timetables.show');

    Route::get('/timetables/{timetable}/edit', [TimetableController::class, 'edit'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('timetables.edit');

    Route::post('/timetables', [TimetableController::class, 'store'])
        ->middleware('permission:ACADEMICS.CREATE')
        ->name('timetables.store');

    Route::put('/timetables/{timetable}', [TimetableController::class, 'update'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('timetables.update');

    Route::delete('/timetables/{timetable}', [TimetableController::class, 'destroy'])
        ->middleware('permission:ACADEMICS.DELETE')
        ->name('timetables.destroy');

    Route::post('/timetables/{timetable}/publish', [TimetableController::class, 'publish'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('timetables.publish');

    Route::post('/timetables/{timetable}/archive', [TimetableController::class, 'archive'])
        ->middleware('permission:ACADEMICS.UPDATE')
        ->name('timetables.archive');
});

/*
|--------------------------------------------------------------------------
| Attendance Management routes
|--------------------------------------------------------------------------
| Add these routes inside your existing authenticated route group.
| Do not duplicate an existing attendance route group.
*/
// Route::middleware('permission:ATTENDANCE.VIEW')->group(function () {
//     Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])
//         ->name('attendance.index');

//     Route::get('/attendance/reports', [\App\Http\Controllers\AttendanceController::class, 'report'])
//         ->middleware('permission:ATTENDANCE.EXPORT')
//         ->name('attendance.report');
// });

// Route::post('/attendance', [\App\Http\Controllers\AttendanceController::class, 'store'])
//     ->middleware('permission:ATTENDANCE.CREATE')
//     ->name('attendance.store');
// Attendance Management
Route::middleware(['auth', 'permission:ATTENDANCE.VIEW'])->group(function () {

    Route::get('/attendance', [
        \App\Http\Controllers\AttendanceController::class,
        'index'
    ])->name('attendance.index');

    // Teachers can VIEW attendance reports with ATTENDANCE.VIEW
    Route::get('/attendance/reports', [
        \App\Http\Controllers\AttendanceController::class,
        'report'
    ])->name('attendance.report');
});

Route::post('/attendance', [
    \App\Http\Controllers\AttendanceController::class,
    'store'
])->middleware(['auth', 'permission:ATTENDANCE.CREATE'])
  ->name('attendance.store');


/*
|--------------------------------------------------------------------------
| Homework Management routes
|--------------------------------------------------------------------------
| Add inside the existing authenticated route group in routes/web.php.
| If your group already applies permission:ACADEMICS.VIEW, do not duplicate
| that middleware on these routes.
*/
use App\Http\Controllers\HomeworkController;

Route::middleware(['auth', 'permission:ACADEMICS.VIEW'])->group(function () {
    Route::get('/homework', [HomeworkController::class, 'index'])->name('homework.index');
    Route::get('/homework/create', [HomeworkController::class, 'create'])
        ->middleware('permission:ACADEMICS.CREATE')->name('homework.create');
    Route::post('/homework', [HomeworkController::class, 'store'])
        ->middleware('permission:ACADEMICS.CREATE')->name('homework.store');
    Route::get('/homework/{homework}', [HomeworkController::class, 'show'])->name('homework.show');
    Route::get('/homework/{homework}/edit', [HomeworkController::class, 'edit'])
        ->middleware('permission:ACADEMICS.UPDATE')->name('homework.edit');
    Route::put('/homework/{homework}', [HomeworkController::class, 'update'])
        ->middleware('permission:ACADEMICS.UPDATE')->name('homework.update');
    Route::patch('/homework/submissions/{submission}/review', [HomeworkController::class, 'review'])
        ->middleware('permission:ACADEMICS.UPDATE')->name('homework.submissions.review');
    Route::delete('/homework/{homework}', [HomeworkController::class, 'destroy'])
        ->middleware('permission:ACADEMICS.DELETE')->name('homework.destroy');
});

use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\StudyMaterialController;
use App\Http\Controllers\QuestionBankController;

/*
| Add these routes inside your existing authenticated route group.
| Keep your already-working Homework routes unchanged.
*/

Route::middleware(['auth', 'permission:ACADEMICS.VIEW'])->group(function () {
    // Screen 21 – Assignments
    Route::resource('assignments', AssignmentController::class);
    Route::get('assignments/{assignment}/submissions', [AssignmentController::class, 'submissions'])
        ->name('assignments.submissions');
    Route::post('assignments/{assignment}/submissions/{submission}/review', [AssignmentController::class, 'review'])
        ->name('assignments.submissions.review');

    // Screen 22 – Study Material
    Route::resource('study-materials', StudyMaterialController::class);

    // Screen 24 – Question Bank
    Route::resource('question-bank', QuestionBankController::class);
    Route::post('question-bank/{question}/toggle', [QuestionBankController::class, 'toggle'])
        ->name('question-bank.toggle');
});


/*
|--------------------------------------------------------------------------
| EXAMINATION MANAGEMENT - SCREEN 23
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Examination directory
    Route::middleware('permission:EXAMINATIONS.VIEW')->group(function () {
        Route::get('/examinations', [
            \App\Http\Controllers\ExaminationController::class,
            'index'
        ])->name('examinations.index');
    });

    // Create - MUST come before {examination}
    Route::middleware('permission:EXAMINATIONS.CREATE')->group(function () {
        Route::get('/examinations/create', [
            \App\Http\Controllers\ExaminationController::class,
            'create'
        ])->name('examinations.create');

        Route::post('/examinations', [
            \App\Http\Controllers\ExaminationController::class,
            'store'
        ])->name('examinations.store');
    });

    // Edit
    Route::middleware('permission:EXAMINATIONS.UPDATE')->group(function () {
        Route::get('/examinations/{examination}/edit', [
            \App\Http\Controllers\ExaminationController::class,
            'edit'
        ])->name('examinations.edit');

        Route::put('/examinations/{examination}', [
            \App\Http\Controllers\ExaminationController::class,
            'update'
        ])->name('examinations.update');
    });

    // Publish
    Route::post('/examinations/{examination}/publish', [
        \App\Http\Controllers\ExaminationController::class,
        'publish'
    ])->middleware('permission:EXAMINATIONS.PUBLISH')
      ->name('examinations.publish');

    // Delete
    Route::delete('/examinations/{examination}', [
        \App\Http\Controllers\ExaminationController::class,
        'destroy'
    ])->middleware('permission:EXAMINATIONS.DELETE')
      ->name('examinations.destroy');

    // Show - keep dynamic route after static routes
    Route::get('/examinations/{examination}', [
        \App\Http\Controllers\ExaminationController::class,
        'show'
    ])->middleware('permission:EXAMINATIONS.VIEW')
      ->name('examinations.show');
});

/*
|--------------------------------------------------------------------------
| MARKS ENTRY - SCREEN 25
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    // Marks Entry Directory
    Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
        Route::get('/marks-entry', [
            \App\Http\Controllers\MarksEntryController::class,
            'index'
        ])->name('marks-entry.index');

        // Individual examination subject marks sheet
        Route::get('/marks-entry/{examSubject}', [
            \App\Http\Controllers\MarksEntryController::class,
            'show'
        ])->name('marks-entry.show');
    });

    // Save marks
    Route::post('/marks-entry/{examSubject}/save', [
        \App\Http\Controllers\MarksEntryController::class,
        'save'
    ])->middleware('permission:ACADEMICS.CREATE')
      ->name('marks-entry.save');

    // Publish marks sheet
    Route::post('/marks-entry/{examSubject}/publish', [
        \App\Http\Controllers\MarksEntryController::class,
        'publish'
    ])->middleware('permission:EXAMINATIONS.PUBLISH')
      ->name('marks-entry.publish');
});


use App\Http\Controllers\ReportCardController;

Route::middleware(['auth'])->group(function () {

    Route::middleware('permission:ACADEMICS.VIEW')->group(function () {
        Route::get('/report-cards', [ReportCardController::class, 'index'])
            ->name('report-cards.index');

        Route::get('/report-cards/{reportCard}', [ReportCardController::class, 'show'])
            ->name('report-cards.show');
    });

    Route::post('/report-cards/generate', [ReportCardController::class, 'generate'])
        ->middleware('permission:ACADEMICS.CREATE')
        ->name('report-cards.generate');

    Route::post('/report-cards/publish-section', [ReportCardController::class, 'publishSection'])
        ->middleware('permission:EXAMINATIONS.PUBLISH')
        ->name('report-cards.publish-section');

    Route::post('/report-cards/{reportCard}/publish', [ReportCardController::class, 'publish'])
        ->middleware('permission:EXAMINATIONS.PUBLISH')
        ->name('report-cards.publish');
});


use App\Http\Controllers\AdmissionCrmController;

Route::prefix('admission-crm')
    ->name('admission-crm.')
    ->group(function () {

        Route::get('/', [AdmissionCrmController::class, 'index'])
            ->middleware('permission:ADMISSIONS.VIEW')
            ->name('index');

        Route::get('/create', [AdmissionCrmController::class, 'create'])
            ->middleware('permission:ADMISSIONS.CREATE')
            ->name('create');

        Route::post('/', [AdmissionCrmController::class, 'store'])
            ->middleware('permission:ADMISSIONS.CREATE')
            ->name('store');

        Route::get('/{admissionLead}', [AdmissionCrmController::class, 'show'])
            ->middleware('permission:ADMISSIONS.VIEW')
            ->name('show');

        Route::get('/{admissionLead}/edit', [AdmissionCrmController::class, 'edit'])
            ->middleware('permission:ADMISSIONS.UPDATE')
            ->name('edit');

        Route::put('/{admissionLead}', [AdmissionCrmController::class, 'update'])
            ->middleware('permission:ADMISSIONS.UPDATE')
            ->name('update');

        Route::patch('/{admissionLead}/stage', [AdmissionCrmController::class, 'updateStage'])
            ->middleware('permission:ADMISSIONS.UPDATE')
            ->name('stage');

        Route::post('/{admissionLead}/followups', [AdmissionCrmController::class, 'storeFollowup'])
            ->middleware('permission:ADMISSIONS.CREATE')
            ->name('followups.store');

        Route::patch('/followups/{followup}/complete', [AdmissionCrmController::class, 'completeFollowup'])
            ->middleware('permission:ADMISSIONS.UPDATE')
            ->name('followups.complete');

        Route::delete('/{admissionLead}', [AdmissionCrmController::class, 'destroy'])
            ->middleware('permission:ADMISSIONS.DELETE')
            ->name('destroy');
    });


Route::prefix('admission-applications')
    ->name('admission-applications.')
    ->group(function () {

        Route::get('/', [AdmissionApplicationController::class, 'index'])
            ->middleware('permission:ADMISSIONS.VIEW')
            ->name('index');

        Route::get('/create', [AdmissionApplicationController::class, 'create'])
            ->middleware('permission:ADMISSIONS.CREATE')
            ->name('create');

        Route::post('/', [AdmissionApplicationController::class, 'store'])
            ->middleware('permission:ADMISSIONS.CREATE')
            ->name('store');

        Route::get('/{admissionApplication}', [AdmissionApplicationController::class, 'show'])
            ->middleware('permission:ADMISSIONS.VIEW')
            ->name('show');

        Route::patch('/{admissionApplication}/status', [AdmissionApplicationController::class, 'updateStatus'])
            ->middleware('permission:ADMISSIONS.UPDATE')
            ->name('status');

        // Document upload
        Route::post('/{admissionApplication}/documents', [
            AdmissionApplicationController::class,
            'uploadDocument'
        ])->middleware('permission:ADMISSIONS.UPDATE')
          ->name('documents.upload');

        // Secure document download
        Route::get('/{admissionApplication}/documents/{documentIndex}', [
            AdmissionApplicationController::class,
            'downloadDocument'
        ])->middleware('permission:ADMISSIONS.VIEW')
                  ->whereNumber('documentIndex')
        ->name('documents.download');

        // Assessment recording
        Route::post('/{admissionApplication}/assessments', [
            AdmissionApplicationController::class,
            'storeAssessment'
        ])->middleware('permission:ADMISSIONS.UPDATE')
          ->name('assessments.store');

                  // Admission decision
        Route::post('/{admissionApplication}/decision', [
            AdmissionApplicationController::class,
            'storeDecision'
        ])->middleware('permission:ADMISSIONS.UPDATE')
          ->name('decision.store');
    });

use App\Http\Controllers\CounsellingController;

Route::prefix('counselling')
    ->name('counselling.')
    ->controller(CounsellingController::class)
    ->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{counselling}', 'show')->name('show');
        Route::get('/{counselling}/edit', 'edit')->name('edit');
        Route::put('/{counselling}', 'update')->name('update');
        Route::patch('/{counselling}/status', 'updateStatus')->name('status');
        Route::delete('/{counselling}', 'destroy')->name('destroy');
    });


// Add these routes INSIDE the existing authenticated route group in routes/web.php.
Route::prefix('fees')->name('fees.')->controller(FeeController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');

    Route::get('/fee-types', 'feeTypesIndex')->name('types.index');
    Route::post('/fee-types', 'feeTypesStore')->name('types.store');
    Route::patch('/fee-types/{feeType}/toggle', 'feeTypesToggle')->name('types.toggle');

    Route::get('/invoices', 'invoiceIndex')->name('invoices.index');
    Route::get('/invoices/create', 'invoiceCreate')->name('invoices.create');
    Route::post('/invoices', 'invoiceStore')->name('invoices.store');
    Route::get('/invoices/{invoice}', 'invoiceShow')->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', 'invoiceEdit')->name('invoices.edit');
    Route::put('/invoices/{invoice}', 'invoiceUpdate')->name('invoices.update');
    Route::delete('/invoices/{invoice}', 'invoiceDelete')->name('invoices.destroy');

    Route::get('/payments', 'paymentIndex')->name('payments.index');
    Route::get('/payments/create', 'paymentCreate')->name('payments.create');
    Route::post('/payments', 'paymentStore')->name('payments.store');
    Route::get('/payments/{payment}/receipt', 'receipt')->name('payments.receipt');
});
use App\Http\Controllers\HR\HRController;

Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::get('/', [HRController::class,'dashboard'])->name('dashboard');
    Route::get('/recruitment',[HRController::class,'recruitment'])->name('recruitment');
    Route::post('/recruitment/jobs',[HRController::class,'storeJob'])->name('jobs.store');
    Route::get('/recruitment/jobs/{job}',[HRController::class,'job'])->name('jobs.show');
    Route::post('/recruitment/jobs/{job}/applications',[HRController::class,'storeApplication'])->name('applications.store');
    Route::patch('/applications/{application}',[HRController::class,'updateApplication'])->name('applications.update');
    Route::get('/attendance',[HRController::class,'attendance'])->name('attendance');
    Route::post('/attendance',[HRController::class,'storeAttendance'])->name('attendance.store');
    Route::get('/attendance-rules',[HRController::class,'attendanceRules'])->name('attendance-rules');
    Route::post('/attendance-rules',[HRController::class,'storeAttendanceRule'])->name('attendance-rules.store');
    Route::get('/leave',[HRController::class,'leave'])->name('leave');
    Route::post('/leave/types',[HRController::class,'storeLeaveType'])->name('leave.types.store');
    Route::post('/leave/requests',[HRController::class,'storeLeaveRequest'])->name('leave.requests.store');
    Route::patch('/leave/requests/{leaveRequest}',[HRController::class,'reviewLeaveRequest'])->name('leave.requests.review');
    Route::get('/payroll',[HRController::class,'payroll'])->name('payroll');
    Route::post('/payroll/periods',[HRController::class,'storePayrollPeriod'])->name('payroll.periods.store');
    Route::get('/payroll/periods/{period}',[HRController::class,'payrollPeriod'])->name('payroll.periods.show');
    Route::post('/payroll/periods/{period}/generate',[HRController::class,'generatePayroll'])->name('payroll.generate');
    Route::patch('/payroll/records/{record}',[HRController::class,'updatePayrollRecord'])->name('payroll.records.update');
    Route::get('/payroll/records/{record}/payslip',[HRController::class,'payslip'])->name('payroll.payslip');
});

Route::middleware(['auth'])->prefix('transport')->name('transport.')->group(function(){
Route::get('/',[TransportController::class,'dashboard'])->name('dashboard');
Route::get('/vehicles',[TransportController::class,'vehicles'])->name('vehicles'); Route::post('/vehicles',[TransportController::class,'storeVehicle'])->name('vehicles.store');
Route::get('/vehicles/{vehicle}',[TransportController::class,'vehicle'])->name('vehicles.show'); Route::put('/vehicles/{vehicle}',[TransportController::class,'updateVehicle'])->name('vehicles.update');
Route::get('/routes',[TransportController::class,'routes'])->name('routes'); Route::post('/routes',[TransportController::class,'storeRoute'])->name('routes.store');
Route::get('/routes/{route}',[TransportController::class,'routeShow'])->name('routes.show'); Route::post('/routes/{route}/stops',[TransportController::class,'storeStop'])->name('stops.store'); Route::delete('/stops/{stop}',[TransportController::class,'deleteStop'])->name('stops.delete');
Route::post('/vehicles/{vehicle}/driver',[TransportController::class,'assignDriver'])->name('drivers.assign'); Route::get('/tracking',[TransportController::class,'tracking'])->name('tracking');
Route::patch('/driver-assignments/{assignment}/unassign',
    [TransportController::class, 'unassignDriver']
)->name('drivers.unassign');
});

// Merge inside routes/web.php; do not replace existing routes.
Route::middleware(['auth'])->prefix('hostel')->name('hostel.')->group(function () {
 Route::get('/',[HostelController::class,'dashboard'])->name('dashboard');
 Route::get('/list',[HostelController::class,'index'])->name('index'); Route::post('/list',[HostelController::class,'store'])->name('store');
 Route::get('/rooms',[HostelController::class,'rooms'])->name('rooms'); Route::post('/rooms',[HostelController::class,'storeRoom'])->name('rooms.store'); Route::post('/rooms/{room}/beds',[HostelController::class,'storeBed'])->name('beds.store');
 Route::get('/room-types',[HostelController::class,'roomTypes'])->name('room-types'); Route::post('/room-types',[HostelController::class,'storeRoomType'])->name('room-types.store');
 Route::get('/allocations',[HostelController::class,'allocations'])->name('allocations'); Route::post('/allocations',[HostelController::class,'storeAllocation'])->name('allocations.store'); Route::patch('/allocations/{allocation}/close',[HostelController::class,'endAllocation'])->name('allocations.close');
 Route::get('/fees',[HostelController::class,'fees'])->name('fees'); Route::post('/fees/invoices',[HostelController::class,'storeInvoice'])->name('fees.invoices.store'); Route::post('/fees/invoices/{invoice}/payments',[HostelController::class,'payInvoice'])->name('fees.payments.store');
 Route::get('/wardens',[HostelController::class,'wardens'])->name('wardens'); Route::post('/wardens',[HostelController::class,'storeWarden'])->name('wardens.store');
 Route::get('/visitors',[HostelController::class,'visitors'])->name('visitors'); Route::post('/visitors',[HostelController::class,'storeVisitor'])->name('visitors.store'); Route::patch('/visitors/{visitor}/action',[HostelController::class,'visitorAction'])->name('visitors.action');
 Route::get('/attendance',[HostelController::class,'attendance'])->name('attendance'); Route::post('/attendance',[HostelController::class,'storeAttendance'])->name('attendance.store');
});




Route::middleware(['auth'])->group(function () {
    // LIBRARY
    Route::prefix('library')->name('library.')->group(function () {
        Route::get('/', [LibraryController::class, 'dashboard'])->name('dashboard');
        Route::resource('books', LibraryController::class)->except(['show']);
        Route::get('/books/{book}', [LibraryController::class, 'showBook'])->name('books.show');
        Route::post('/books/{book}/copies', [LibraryController::class, 'storeCopy'])->name('copies.store');
        Route::patch('/copies/{copy}', [LibraryController::class, 'updateCopy'])->name('copies.update');
        Route::get('/loans', [LibraryController::class, 'loans'])->name('loans.index');
        Route::get('/issue', [LibraryController::class, 'issueForm'])->name('loans.issue');
        Route::post('/issue', [LibraryController::class, 'issue'])->name('loans.store');
        Route::patch('/loans/{loan}/return', [LibraryController::class, 'returnBook'])->name('loans.return');
    });

    // INVENTORY & ASSETS
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'dashboard'])->name('dashboard');

        Route::get('/categories', [InventoryController::class, 'categories'])->name('categories.index');
        Route::post('/categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('/categories/{category}', [InventoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('/categories/{category}', [InventoryController::class, 'destroyCategory'])->name('categories.destroy');

        Route::resource('items', InventoryController::class)->except(['show']);
        Route::get('/items/{item}', [InventoryController::class, 'showItem'])->name('items.show');

        Route::get('/vendors', [InventoryController::class, 'vendors'])->name('vendors.index');
        Route::post('/vendors', [InventoryController::class, 'storeVendor'])->name('vendors.store');
        Route::put('/vendors/{vendor}', [InventoryController::class, 'updateVendor'])->name('vendors.update');
        Route::delete('/vendors/{vendor}', [InventoryController::class, 'destroyVendor'])->name('vendors.destroy');

        Route::get('/assets', [InventoryController::class, 'assets'])->name('assets.index');
        Route::get('/assets/create', [InventoryController::class, 'createAsset'])->name('assets.create');
        Route::post('/assets', [InventoryController::class, 'storeAsset'])->name('assets.store');
        Route::get('/assets/{asset}/edit', [InventoryController::class, 'editAsset'])->name('assets.edit');
        Route::put('/assets/{asset}', [InventoryController::class, 'updateAsset'])->name('assets.update');
        Route::delete('/assets/{asset}', [InventoryController::class, 'destroyAsset'])->name('assets.destroy');

        Route::get('/purchase-orders', [InventoryController::class, 'purchaseOrders'])->name('purchase-orders.index');
        Route::get('/purchase-orders/create', [InventoryController::class, 'createPurchaseOrder'])->name('purchase-orders.create');
        Route::post('/purchase-orders', [InventoryController::class, 'storePurchaseOrder'])->name('purchase-orders.store');
        Route::get('/purchase-orders/{order}', [InventoryController::class, 'showPurchaseOrder'])->name('purchase-orders.show');
        Route::patch('/purchase-orders/{order}/status', [InventoryController::class, 'updatePurchaseOrderStatus'])->name('purchase-orders.status');
    });
});

// CERTIFICATE MANAGEMENT — SCREENS 50 & 51

Route::prefix('certificates')->name('certificates.')->group(function () {

    // Public certificate verification
    Route::get('/verify', [CertificateController::class, 'verifyForm'])
        ->name('verify');

    Route::get('/verify/{certificateNumber}', [CertificateController::class, 'verify'])
        ->name('verify.result');

    // Authenticated certificate management
    Route::middleware(['auth'])->group(function () {

        Route::get('/', [CertificateController::class, 'dashboard'])
            ->name('dashboard');

        Route::get('/history', [CertificateController::class, 'index'])
            ->name('index');

        Route::get('/create', [CertificateController::class, 'create'])
            ->name('create');

        Route::post('/', [CertificateController::class, 'store'])
            ->name('store');

        Route::get('/{certificate}/pdf', [CertificateController::class, 'download'])
            ->name('download');

        Route::get('/{certificate}', [CertificateController::class, 'show'])
            ->name('show');
    });
});


// EVENTS MANAGEMENT
Route::prefix('events')->name('events.')->controller(EventController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/list', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');

    Route::get('/{event}/edit', 'edit')->name('edit');
    Route::put('/{event}', 'update')->name('update');
    Route::delete('/{event}', 'destroy')->name('destroy');
    Route::get('/{event}', 'show')->name('show');
});



Route::middleware(['auth'])->group(function () {
    foreach ([
        'sports' => SportController::class,
        'clubs' => ClubController::class,
        'alumni' => AlumniController::class,
        'career' => CareerOpportunityController::class,
    ] as $prefix => $controller) {
        Route::prefix($prefix)->name($prefix.'.')->controller($controller)->group(function () {
            Route::get('/', 'dashboard')->name('dashboard');
            Route::get('/list', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
            Route::put('/{id}', 'update')->whereNumber('id')->name('update');
            Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
            Route::get('/{id}', 'show')->whereNumber('id')->name('show');
        });
    }
});




Route::prefix('communication')->name('communication.')->controller(CommunicationController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/announcements', 'index')->name('index');
    Route::get('/announcements/create', 'create')->name('create');
    Route::post('/announcements', 'store')->name('store');
    Route::get('/announcements/{id}', 'show')->whereNumber('id')->name('show');
    Route::get('/announcements/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/announcements/{id}', 'update')->whereNumber('id')->name('update');
    Route::post('/announcements/{id}/publish', 'publish')->whereNumber('id')->name('publish');
    Route::post('/announcements/{id}/archive', 'archive')->whereNumber('id')->name('archive');
    Route::delete('/announcements/{id}', 'destroy')->whereNumber('id')->name('destroy');

    Route::get('/messages', 'messages')->name('messages');
    Route::get('/messages/compose', 'compose')->name('compose');
    Route::post('/messages', 'sendMessage')->name('messages.send');
    Route::get('/messages/{id}', 'messageShow')->whereNumber('id')->name('message.show');
    Route::post('/messages/{id}/reply', 'reply')->whereNumber('id')->name('message.reply');

    Route::get('/templates', 'templates')->name('templates');
    Route::post('/templates', 'storeTemplate')->name('templates.store');
    Route::get('/deliveries', 'deliveries')->name('deliveries');
});


Route::prefix('ptm')->name('ptm.')->controller(PtmController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/history', 'index')->name('index');
    Route::get('/create', 'create')->name('create');
    Route::post('/', 'store')->name('store');
    Route::get('/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/{id}', 'update')->whereNumber('id')->name('update');
    Route::patch('/{id}/status', 'status')->whereNumber('id')->name('status');
    Route::delete('/{id}', 'destroy')->whereNumber('id')->name('destroy');
    Route::get('/{id}', 'show')->whereNumber('id')->name('show');
});


// Add this group inside your existing authenticated route group in routes/web.php.
Route::prefix('student-support')->name('student-support.')->controller(StudentSupportController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/{module}', 'index')->name('index');
    Route::get('/{module}/create', 'create')->name('create');
    Route::post('/{module}', 'store')->name('store');
    Route::get('/{module}/{id}', 'show')->whereNumber('id')->name('show');
    Route::patch('/{module}/{id}/status', 'updateStatus')->whereNumber('id')->name('status');
    Route::post('/{module}/{id}/notes', 'addNote')->whereNumber('id')->name('notes');
});




// //Add inside routes/web.php. All screens are authenticated; add your permission middleware per route before production.
//  Route::middleware(['auth'])->prefix('caspa')->name('caspa.')->group(function () {
//      Route::get('/{screen?}', [CaspaIntelligenceController::class,'index'])->defaults('screen','chat')->where('screen','[a-z-]+')->name('workspace');
//      Route::post('/ai/respond', [CaspaIntelligenceController::class,'chat'])->name('ai.respond');
//      Route::post('/{screen}/records', [CaspaIntelligenceController::class,'store'])->where('screen','[a-z-]+')->name('records.store');
//      Route::post('/{screen}/settings', [CaspaIntelligenceController::class,'saveRecord'])->where('screen','[a-z-]+')->name('settings.save');
//      Route::post('/approvals/{id}/decision', [CaspaIntelligenceController::class,'decide'])->whereNumber('id')->name('approvals.decide');
//  });


/*
|--------------------------------------------------------------------------
| CASPA - Screen 69: Reports & Analytics
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])
    ->prefix('caspa/reports')
    ->name('caspa.reports.')
    ->controller(ReportsAnalyticsController::class)
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('caspa.reports.index', [
                'report' => 'dashboard'
            ]);
        })->name('home');

        Route::get('/export/{report}/{format}', 'export')
            ->where([
                'report' => 'dashboard|attendance|academic|performance|classes|grades|fees|teachers|custom',
                'format' => 'csv|pdf'
            ])
            ->name('export');

        Route::get('/{report?}', 'index')
            ->where('report', 'dashboard|attendance|academic|performance|classes|grades|fees|teachers|custom')
            ->defaults('report', 'dashboard')
            ->name('index');
    });

Route::middleware(['auth'])->prefix('approval-center')->name('approvals.')->controller(ApprovalCenterController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/requests', 'requests')->name('requests');
    Route::get('/requests/create', 'create')->name('create');
    Route::post('/requests', 'store')->name('store');
    Route::get('/requests/{id}', 'show')->whereNumber('id')->name('show');
    Route::post('/requests/{id}/decision', 'decide')->whereNumber('id')->name('decide');
    Route::get('/my-approvals', 'myApprovals')->name('mine');
    Route::get('/history', 'history')->name('history');
    Route::get('/workflow', 'workflow')->name('workflow');
    Route::post('/workflow', 'saveWorkflow')->name('workflow.save');
    Route::get('/delegations', 'delegations')->name('delegations');
    Route::post('/delegations', 'storeDelegation')->name('delegations.store');
});


// Add this inside routes/web.php. Do not add a second <?php if pasting into an existing file.

use App\Http\Controllers\RoleBuilderController;

Route::middleware(['auth'])->prefix('role-builder')->name('role-builder.')->controller(RoleBuilderController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/roles', 'index')->name('index');
    Route::get('/roles/create', 'create')->name('create');
    Route::post('/roles', 'store')->name('store');
    Route::get('/roles/{id}', 'show')->whereNumber('id')->name('show');
    Route::get('/roles/{id}/edit', 'edit')->whereNumber('id')->name('edit');
    Route::put('/roles/{id}', 'update')->whereNumber('id')->name('update');
    Route::patch('/roles/{id}/toggle', 'toggle')->whereNumber('id')->name('toggle');
    Route::post('/roles/{id}/assign', 'assign')->whereNumber('id')->name('assign');
    Route::delete('/roles/{id}/assignments/{assignment}', 'removeAssignment')->whereNumber('id')->whereNumber('assignment')->name('assignment.remove');
    Route::get('/compare', 'compare')->name('compare');
});



use App\Http\Controllers\PermissionMatrixController;

Route::middleware(['auth'])->prefix('permission-matrix')->name('permission-matrix.')->controller(PermissionMatrixController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::put('/', 'update')->name('update');
});



use App\Http\Controllers\WorkflowBuilderController;
use App\Http\Controllers\AuditLogsController;
use App\Http\Controllers\SystemSettingsController;
use App\Http\Controllers\IntegrationHubController;

Route::middleware(['auth'])->prefix('workflow-builder')->name('workflow-builder.')->controller(WorkflowBuilderController::class)->group(function(){
 Route::get('/','dashboard')->name('dashboard'); Route::get('/workflows','index')->name('index'); Route::get('/create','create')->name('create'); Route::post('/','store')->name('store');
 Route::get('/{id}','show')->whereNumber('id')->name('show'); Route::get('/{id}/edit','edit')->whereNumber('id')->name('edit'); Route::put('/{id}','update')->whereNumber('id')->name('update');
 Route::patch('/{id}/toggle','toggle')->whereNumber('id')->name('toggle'); Route::post('/{id}/run','run')->whereNumber('id')->name('run');
});
Route::middleware(['auth'])->prefix('audit-logs')->name('audit-logs.')->controller(AuditLogsController::class)->group(function(){
 Route::get('/','dashboard')->name('dashboard'); Route::get('/events','index')->name('index'); Route::get('/export','export')->name('export'); Route::get('/events/{id}','show')->whereNumber('id')->name('show');
});
Route::middleware(['auth'])->prefix('system-settings')->name('system-settings.')->controller(SystemSettingsController::class)->group(function(){
 Route::get('/','dashboard')->name('dashboard'); Route::get('/section/{section}','index')->name('section'); Route::put('/section/{section}','update')->name('update');
});
Route::middleware(['auth'])->prefix('integration-hub')->name('integration-hub.')->controller(IntegrationHubController::class)->group(function(){
 Route::get('/','dashboard')->name('dashboard'); Route::get('/integrations','index')->name('index'); Route::get('/create','create')->name('create'); Route::post('/','store')->name('store');
 Route::get('/{id}','show')->whereNumber('id')->name('show'); Route::get('/{id}/edit','edit')->whereNumber('id')->name('edit'); Route::put('/{id}','update')->whereNumber('id')->name('update');
 Route::post('/{id}/test','test')->whereNumber('id')->name('test'); Route::patch('/{id}/toggle','toggle')->whereNumber('id')->name('toggle');
});

use App\Http\Controllers\CaspaChatController;
use App\Http\Controllers\CaspaStudentLearningController;
use App\Http\Controllers\CaspaTeacherAssistantController;
use App\Http\Controllers\CaspaPrincipalIntelligenceController;

Route::middleware(['auth'])->group(function () {
    Route::get('/caspa-chat',[CaspaChatController::class,'index'])->name('caspa-chat.index');
    Route::post('/caspa-chat/send',[CaspaChatController::class,'send'])->name('caspa-chat.send');
    Route::get('/caspa-chat/conversations/{id}',[CaspaChatController::class,'conversation'])->whereNumber('id')->name('caspa-chat.conversation');

    Route::get('/student-learning',[CaspaStudentLearningController::class,'index'])->name('student-learning.index');
    Route::post('/student-learning/ask',[CaspaStudentLearningController::class,'ask'])->name('student-learning.ask');
    Route::get('/student-learning/conversations/{id}',[CaspaStudentLearningController::class,'conversation'])->whereNumber('id')->name('student-learning.conversation');

    Route::get('/teacher-assistant',[CaspaTeacherAssistantController::class,'index'])->name('teacher-assistant.index');
    Route::post('/teacher-assistant/ask',[CaspaTeacherAssistantController::class,'ask'])->name('teacher-assistant.ask');
    Route::get('/teacher-assistant/conversations/{id}',[CaspaTeacherAssistantController::class,'conversation'])->whereNumber('id')->name('teacher-assistant.conversation');

    Route::get('/principal-intelligence',[CaspaPrincipalIntelligenceController::class,'index'])->name('principal-intelligence.index');
    Route::post('/principal-intelligence/ask',[CaspaPrincipalIntelligenceController::class,'ask'])->name('principal-intelligence.ask');
    Route::get('/principal-intelligence/conversations/{id}',[CaspaPrincipalIntelligenceController::class,'conversation'])->whereNumber('id')->name('principal-intelligence.conversation');
});


/*
|--------------------------------------------------------------------------
| Role dashboards
|--------------------------------------------------------------------------
| Keep the existing /dashboard route and replace only its current route
| declaration with the block below. These URLs require authentication and
| the matching active role assignment.
*/
Route::middleware(['auth', 'permission:DASHBOARD.VIEW'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/principal', [\App\Http\Controllers\DashboardController::class, 'principal'])
        ->name('dashboard.principal');

    Route::get('/dashboard/admin', [\App\Http\Controllers\DashboardController::class, 'admin'])
        ->name('dashboard.admin');

    Route::get('/dashboard/student', [\App\Http\Controllers\DashboardController::class, 'student'])
        ->name('dashboard.student');

    Route::get('/dashboard/teacher', [\App\Http\Controllers\DashboardController::class, 'teacher'])
        ->name('dashboard.teacher');

    Route::get('/dashboard/parent', [\App\Http\Controllers\DashboardController::class, 'parent'])
        ->name('dashboard.parent');
});


use App\Http\Controllers\Student\StudentTransportController;
use App\Http\Controllers\Student\StudentHostelController;
use App\Http\Controllers\Student\StudentLibraryController;

Route::middleware(['auth'])->group(function () {

    Route::get('/my-transport', [StudentTransportController::class, 'index'])
        ->name('student.transport');

    Route::get('/my-hostel', [StudentHostelController::class, 'index'])
        ->name('student.hostel');

    Route::get('/my-library', [StudentLibraryController::class, 'index'])
        ->name('student.library');

});



// ======================================================
// PARENT PORTAL - CHILD SERVICES
// Dedicated read-only routes for linked children
// ======================================================

Route::middleware(['auth'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {

        Route::get('/attendance', [ParentAttendanceController::class, 'index'])
            ->name('attendance.index');

        Route::get('/homework', [ParentHomeworkController::class, 'index'])
            ->name('homework.index');

        Route::get('/assignments', [ParentAssignmentController::class, 'index'])
            ->name('assignments.index');

        Route::get('/transport', [ParentTransportController::class, 'index'])
            ->name('transport.index');

        Route::get('/library', [ParentLibraryController::class, 'index'])
            ->name('library.index');

        Route::get('/hostel', [ParentHostelController::class, 'index'])
            ->name('hostel.index');
    });

Route::get('/fees', [ParentFeeController::class, 'index'])
    ->name('fees.index');

Route::get('/fees/{invoice}', [ParentFeeController::class, 'show'])
    ->whereNumber('invoice')
    ->name('fees.show');
Route::middleware(['auth'])
    ->prefix('parent')
    ->name('parent.')
    ->group(function () {

        // Children
        Route::get('/children', [ParentChildrenController::class, 'index'])
            ->name('children.index');

        // Fees
        Route::get('/fees', [ParentFeeController::class, 'index'])
            ->name('fees.index');

        Route::get('/fees/{invoice}', [ParentFeeController::class, 'show'])
            ->whereNumber('invoice')
            ->name('fees.show');

        // School announcements
        Route::get('/announcements', [ParentAnnouncementController::class, 'index'])
            ->name('announcements.index');

        Route::get('/announcements/{id}', [ParentAnnouncementController::class, 'show'])
            ->whereNumber('id')
            ->name('announcements.show');

        // Events
        Route::get('/events', [ParentEventController::class, 'index'])
            ->name('events.index');

        // Parent–Teacher Meetings
        Route::get('/ptm', [ParentPtmController::class, 'index'])
            ->name('ptm.index');

        Route::get('/ptm/create', [ParentPtmController::class, 'create'])
            ->name('ptm.create');

        Route::post('/ptm', [ParentPtmController::class, 'store'])
            ->name('ptm.store');

        // CASPA Chat
        Route::get('/messages', [ParentMessageController::class, 'index'])
            ->name('messages.index');

        Route::get('/messages/compose', [ParentMessageController::class, 'create'])
            ->name('messages.create');

        Route::post('/messages', [ParentMessageController::class, 'store'])
            ->name('messages.store');

        Route::get('/messages/{id}', [ParentMessageController::class, 'show'])
            ->whereNumber('id')
            ->name('messages.show');

        Route::post('/messages/{id}/reply', [ParentMessageController::class, 'reply'])
            ->whereNumber('id')
            ->name('messages.reply');
    });



use App\Http\Controllers\Teacher\TeacherSelfServiceController;
use App\Http\Controllers\Teacher\TeacherCommunicationController;
use App\Http\Controllers\Teacher\TeacherMarksEntryController;

Route::middleware(['auth'])
    ->prefix('teacher')
    ->name('teacher.')
    ->group(function () {
        // My profile
        Route::get('/profile', [TeacherSelfServiceController::class, 'profile'])
            ->name('profile.show');
        Route::get('/profile/edit', [TeacherSelfServiceController::class, 'editProfile'])
            ->name('profile.edit');
        Route::put('/profile', [TeacherSelfServiceController::class, 'updateProfile'])
            ->name('profile.update');

        // My own staff attendance (not student attendance)
        Route::get('/attendance', [TeacherSelfServiceController::class, 'attendance'])
            ->name('attendance.index');

        // My own payroll and payslips
        Route::get('/payroll', [TeacherSelfServiceController::class, 'payroll'])
            ->name('payroll.index');
        Route::get('/payroll/{record}/payslip', [TeacherSelfServiceController::class, 'payslip'])
            ->whereNumber('record')
            ->name('payroll.payslip');

        // Teacher-to-parent / principal conversations
        Route::get('/messages', [TeacherCommunicationController::class, 'index'])
            ->name('messages.index');
        Route::get('/messages/compose', [TeacherCommunicationController::class, 'compose'])
            ->name('messages.compose');
        Route::post('/messages', [TeacherCommunicationController::class, 'send'])
            ->name('messages.send');
        Route::get('/messages/{id}', [TeacherCommunicationController::class, 'show'])
            ->whereNumber('id')
            ->name('messages.show');
        Route::post('/messages/{id}/reply', [TeacherCommunicationController::class, 'reply'])
            ->whereNumber('id')
            ->name('messages.reply');

        // Teacher marks entry - only the teacher's assigned exam subjects
        Route::get('/marks-entry', [TeacherMarksEntryController::class, 'index'])
            ->name('marks.index');
        Route::get('/marks-entry/{examSubject}', [TeacherMarksEntryController::class, 'show'])
            ->whereNumber('examSubject')
            ->name('marks.show');
        Route::post('/marks-entry/{examSubject}/save', [TeacherMarksEntryController::class, 'save'])
            ->whereNumber('examSubject')
            ->name('marks.save');
        Route::post('/marks-entry/{examSubject}/publish', [TeacherMarksEntryController::class, 'publish'])
            ->whereNumber('examSubject')
            ->name('marks.publish');
    });
