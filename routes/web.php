
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
use App\Http\Controllers\CurriculumController;


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
