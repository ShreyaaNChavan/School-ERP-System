<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\CampusController;


Route::get('/', function () {
    return view('welcome');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'permission:DASHBOARD.VIEW'])
    ->get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';



Route::middleware(['auth', 'permission:STUDENTS.VIEW'])
    ->get('/students', function () {
        return 'Students module access granted!';
    })
    ->name('students.index');

Route::middleware(['auth', 'permission:FEES.REFUND'])
    ->get('/fees/refunds', function () {
        return 'Fee refund access granted!';
    })
    ->name('fees.refunds');

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


use App\Http\Controllers\SchoolController;

Route::middleware(['auth'])->prefix('schools')->name('schools.')->group(function () {

    // View schools
    Route::get('/', [SchoolController::class, 'index'])
        ->middleware('permission:SCHOOLS.VIEW')
        ->name('index');

    // Create school
    Route::middleware('permission:SCHOOLS.CREATE')->group(function () {
        Route::get('/create', [SchoolController::class, 'create'])
            ->name('create');

        Route::post('/', [SchoolController::class, 'store'])
            ->name('store');
    });

    // Update school
    Route::middleware('permission:SCHOOLS.UPDATE')->group(function () {
        Route::get('/{school}/edit', [SchoolController::class, 'edit'])
            ->name('edit');

        Route::put('/{school}', [SchoolController::class, 'update'])
            ->name('update');

        Route::patch('/{school}/status', [SchoolController::class, 'toggleStatus'])
            ->name('status');
    });

});

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