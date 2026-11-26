<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect('/' . auth()->user()->role->slug . '/dashboard'));

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin']);
        Route::resource('departments', DepartmentController::class)->names('admin.departments');
        Route::resource('teachers', TeacherController::class)->names('admin.teachers');
        Route::resource('subjects', SubjectController::class)->names('admin.subjects');
        Route::resource('academic_years', AcademicYearController::class)->names('admin.academic_years');
        Route::resource('semesters', SemesterController::class)->names('admin.semesters');
        Route::resource('curricula', CurriculumController::class)->names('admin.curricula');
    });
    Route::middleware('role:guru')->prefix('guru')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'guru']);
        Route::get('/assessments', [AssessmentController::class, 'index']);
        Route::post('/assessments', [AssessmentController::class, 'store']);
        Route::post('/assessments/bulk', [AssessmentController::class, 'storeBulk']);
        Route::post('/assessments/submit', [AssessmentController::class, 'submit']);
        Route::post('/assessments/lock', [AssessmentController::class, 'lock']);
        Route::post('/assessments/revision', [AssessmentController::class, 'requestRevision']);
        Route::post('/assessments/revision/approve', [AssessmentController::class, 'approveRevision']);
        Route::get('/assessments/recap', [AssessmentController::class, 'recap']);
        Route::get('/assessments/calculate', [AssessmentController::class, 'calculate']);
        Route::get('/assessments/history', [AssessmentController::class, 'history']);
        Route::get('/descriptions', [AssessmentController::class, 'descriptions']);
        Route::post('/descriptions', [AssessmentController::class, 'storeDescription']);
        Route::get('/components', [AssessmentController::class, 'components']);
        Route::post('/components', [AssessmentController::class, 'storeComponent']);
    });
    Route::middleware('role:wali_kelas')->prefix('wali')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'wali']);
        Route::get('/assessments/recap', [AssessmentController::class, 'waliRecap']);
    });
});
