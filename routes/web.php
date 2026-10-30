
<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\CampusController;
use App\Http\Controllers\StudentController;

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
