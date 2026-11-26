<?php

use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\CompetencyExamController as AdminCompetencyExamController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\HomeroomAssignmentController;
use App\Http\Controllers\Admin\TeachingAssignmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\InternshipController as AdminInternshipController;
use App\Http\Controllers\Admin\ReportCardController as AdminReportCardController;
use App\Http\Controllers\Admin\SchoolProfileController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\SemesterController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Teacher\AssessmentController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\InternshipController as TeacherInternshipController;
use App\Http\Controllers\Wali\HomeroomController;
use App\Http\Controllers\Wali\InternshipController as WaliInternshipController;
use App\Http\Controllers\Wali\ReportCardController as WaliReportCardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect('/' . auth()->user()->role->slug . '/dashboard'));

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin']);
        Route::resource('departments', DepartmentController::class)->names('admin.departments');
        Route::resource('teachers', TeacherController::class)->names('admin.teachers');
        Route::resource('students', StudentController::class)->names('admin.students');
        Route::resource('classes', SchoolClassController::class)->names('admin.classes');
        Route::resource('subjects', SubjectController::class)->names('admin.subjects');
        Route::resource('academic_years', AcademicYearController::class)->names('admin.academic_years');
        Route::resource('semesters', SemesterController::class)->names('admin.semesters');
        Route::resource('curricula', CurriculumController::class)->names('admin.curricula');
        Route::resource('teaching-assignments', TeachingAssignmentController::class)->names('admin.teaching-assignments');
        Route::resource('homeroom-assignments', HomeroomAssignmentController::class)->names('admin.homeroom-assignments');
        Route::resource('users', UserController::class)->names('admin.users');
        Route::get('/attendance/monitoring', [AdminAttendanceController::class, 'monitoring']);
        Route::get('/report-cards', [AdminReportCardController::class, 'index'])->name('admin.report-cards.index');
        Route::get('/report-cards/{reportCard}', [AdminReportCardController::class, 'show'])->name('admin.report-cards.show');
        Route::get('/report-cards/{reportCard}/history', [AdminReportCardController::class, 'history'])->name('admin.report-cards.history');
        Route::post('/report-cards/{reportCard}/approve', [AdminReportCardController::class, 'approve'])->name('admin.report-cards.approve');
        Route::post('/report-cards/{reportCard}/reject', [AdminReportCardController::class, 'reject'])->name('admin.report-cards.reject');
        Route::post('/report-cards/{reportCard}/publish', [AdminReportCardController::class, 'publish'])->name('admin.report-cards.publish');
        Route::post('/report-cards/{reportCard}/lock', [AdminReportCardController::class, 'lock'])->name('admin.report-cards.lock');
        Route::get('/report-cards/{reportCard}/pdf', [AdminReportCardController::class, 'pdf'])->name('admin.report-cards.pdf');
        Route::get('/school-profile', [SchoolProfileController::class, 'edit'])->name('admin.school-profile.edit');
        Route::put('/school-profile', [SchoolProfileController::class, 'update'])->name('admin.school-profile.update');
        Route::get('/print-settings', [SchoolProfileController::class, 'editPrint'])->name('admin.print-settings.edit');
        Route::put('/print-settings', [SchoolProfileController::class, 'updatePrint'])->name('admin.print-settings.update');
        // Stage 11: PKL (admin kelola penuh).
        Route::get('/internships/monitoring', [AdminInternshipController::class, 'monitoring'])->name('admin.internships.monitoring');
        Route::get('/internships/recap', [AdminInternshipController::class, 'recap'])->name('admin.internships.recap');
        Route::get('/internships', [AdminInternshipController::class, 'index'])->name('admin.internships.index');
        Route::post('/internships', [AdminInternshipController::class, 'store'])->name('admin.internships.store');
        Route::get('/internships/{internship}/edit', [AdminInternshipController::class, 'edit'])->name('admin.internships.edit');
        Route::put('/internships/{internship}', [AdminInternshipController::class, 'update'])->name('admin.internships.update');
        Route::delete('/internships/{internship}', [AdminInternshipController::class, 'destroy'])->name('admin.internships.destroy');
        Route::get('/internships/{internship}/assessments', [AdminInternshipController::class, 'assessments'])->name('admin.internships.assessments');
        Route::post('/internships/{internship}/assessments', [AdminInternshipController::class, 'storeAssessment'])->name('admin.internships.assessments.store');
        Route::put('/internships/assessments/{assessment}', [AdminInternshipController::class, 'updateAssessment'])->name('admin.internships.assessments.update');
        Route::delete('/internships/assessments/{assessment}', [AdminInternshipController::class, 'destroyAssessment'])->name('admin.internships.assessments.destroy');
        // Stage 11: UKK (admin kelola penuh).
        Route::get('/competency-exams/recap', [AdminCompetencyExamController::class, 'recap'])->name('admin.competency-exams.recap');
        Route::get('/competency-exams/schedule', [AdminCompetencyExamController::class, 'schedule'])->name('admin.competency-exams.schedule');
        Route::get('/competency-exams', [AdminCompetencyExamController::class, 'index'])->name('admin.competency-exams.index');
        Route::post('/competency-exams', [AdminCompetencyExamController::class, 'store'])->name('admin.competency-exams.store');
        Route::put('/competency-exams/{exam}', [AdminCompetencyExamController::class, 'update'])->name('admin.competency-exams.update');
        Route::delete('/competency-exams/{exam}', [AdminCompetencyExamController::class, 'destroy'])->name('admin.competency-exams.destroy');
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
        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'storeBulk']);
        Route::post('/attendance/{attendance}/correct', [AttendanceController::class, 'correct']);
        Route::get('/attendance/history', [AttendanceController::class, 'history']);
        Route::get('/attendance/recap', [AttendanceController::class, 'recap']);
        Route::get('/attendance/academic-recap', [AttendanceController::class, 'academicRecap']);
        // Stage 11: PKL bimbingan guru + UKK mapel yang diampu (hanya-baca).
        Route::get('/internships', [TeacherInternshipController::class, 'index'])->name('guru.internships.index');
        Route::get('/internships/{internship}', [TeacherInternshipController::class, 'show'])->name('guru.internships.show');
        Route::post('/internships/{internship}/assessments', [TeacherInternshipController::class, 'storeAssessment'])->name('guru.internships.assessments.store');
        Route::put('/internships/assessments/{assessment}', [TeacherInternshipController::class, 'updateAssessment'])->name('guru.internships.assessments.update');
        Route::delete('/internships/assessments/{assessment}', [TeacherInternshipController::class, 'destroyAssessment'])->name('guru.internships.assessments.destroy');
        Route::get('/competency-exams', [TeacherInternshipController::class, 'competencyExams'])->name('guru.competency-exams.index');
    });
    Route::middleware('role:wali_kelas')->prefix('wali')->group(function () {
        Route::get('/dashboard', [HomeroomController::class, 'dashboard'])->name('wali.dashboard');
        Route::get('/assessments/recap', [AssessmentController::class, 'waliRecap']);
        Route::get('/attendance/recap', [AttendanceController::class, 'waliRecap']);
        Route::get('/attendance/academic-recap', [AttendanceController::class, 'waliAcademicRecap']);
        Route::get('/students', [HomeroomController::class, 'students'])->name('wali.students');
        Route::get('/notes', [HomeroomController::class, 'notes'])->name('wali.notes');
        Route::post('/notes', [HomeroomController::class, 'storeNote'])->name('wali.notes.store');
        Route::put('/notes/{note}', [HomeroomController::class, 'updateNote'])->name('wali.notes.update');
        Route::delete('/notes/{note}', [HomeroomController::class, 'destroyNote'])->name('wali.notes.destroy');
        Route::get('/achievements', [HomeroomController::class, 'achievements'])->name('wali.achievements');
        Route::post('/achievements', [HomeroomController::class, 'storeAchievement'])->name('wali.achievements.store');
        Route::put('/achievements/{achievement}', [HomeroomController::class, 'updateAchievement'])->name('wali.achievements.update');
        Route::delete('/achievements/{achievement}', [HomeroomController::class, 'destroyAchievement'])->name('wali.achievements.destroy');
        Route::get('/extracurriculars', [HomeroomController::class, 'extracurriculars'])->name('wali.extracurriculars');
        Route::post('/extracurriculars', [HomeroomController::class, 'storeExtracurricular'])->name('wali.extracurriculars.store');
        Route::put('/extracurriculars/{record}', [HomeroomController::class, 'updateExtracurricular'])->name('wali.extracurriculars.update');
        Route::delete('/extracurriculars/{record}', [HomeroomController::class, 'destroyExtracurricular'])->name('wali.extracurriculars.destroy');
        Route::post('/extracurriculars/master', [HomeroomController::class, 'storeExtracurricularMaster'])->name('wali.extracurriculars.master.store');
        Route::put('/extracurriculars/master/{extracurricular}', [HomeroomController::class, 'updateExtracurricularMaster'])->name('wali.extracurriculars.master.update');
        Route::delete('/extracurriculars/master/{extracurricular}', [HomeroomController::class, 'destroyExtracurricularMaster'])->name('wali.extracurriculars.master.destroy');
        Route::get('/completeness', [HomeroomController::class, 'completeness'])->name('wali.completeness');
        Route::get('/preview/{student}', [HomeroomController::class, 'preview'])->name('wali.preview');
        Route::post('/submit', [HomeroomController::class, 'submit'])->name('wali.submit');
        Route::get('/history/{student}', [HomeroomController::class, 'history'])->name('wali.history');
        Route::get('/report-cards', [WaliReportCardController::class, 'index'])->name('wali.report-cards.index');
        Route::get('/report-cards/{reportCard}/pdf', [WaliReportCardController::class, 'pdf'])->name('wali.report-cards.pdf');
        // Stage 11: PKL/UKK siswa kelas wali (hanya-baca).
        Route::get('/internships/recap', [WaliInternshipController::class, 'recap'])->name('wali.internships.recap');
        Route::get('/internships', [WaliInternshipController::class, 'index'])->name('wali.internships.index');
        Route::get('/competency-exams', [WaliInternshipController::class, 'competencyExams'])->name('wali.competency-exams.index');
    });
    // Fase A: prefix memakai slug peran (lihat redirect '/' di atas),
    // berbeda dengan wali yang slug-nya wali_kelas tapi prefix-nya wali.
    Route::middleware('role:kepala_sekolah')->prefix('kepala_sekolah')->group(function () {
        Route::get('/dashboard', [MonitoringController::class, 'kepala'])->name('kepala.dashboard');
        Route::post('/report-cards/{reportCard}/lock', [MonitoringController::class, 'lockReportCard'])->name('kepala.report-cards.lock');
    });
    Route::middleware('role:waka_kurikulum')->prefix('waka_kurikulum')->group(function () {
        Route::get('/dashboard', [MonitoringController::class, 'waka'])->name('waka.dashboard');
    });
});
