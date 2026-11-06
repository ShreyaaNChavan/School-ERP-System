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
Route::middleware('permission:ATTENDANCE.VIEW')->group(function () {
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::get('/attendance/reports', [\App\Http\Controllers\AttendanceController::class, 'report'])
        ->middleware('permission:ATTENDANCE.EXPORT')
        ->name('attendance.report');
});

Route::post('/attendance', [\App\Http\Controllers\AttendanceController::class, 'store'])
    ->middleware('permission:ATTENDANCE.CREATE')
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