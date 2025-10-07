<?php

use App\Http\Controllers\Admin\ClassroomController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Web\AttendanceController as WebAttendanceController;
use App\Http\Controllers\Web\StudentController as WebStudentController;
use App\Http\Controllers\Web\TeachingJournalController as WebTeachingJournalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Form login berbasis web.
Route::get('/login', [WebLoginController::class, 'create'])->name('login');
Route::post('/login', [WebLoginController::class, 'store'])->name('login.store');

// Tampilan dashboard sederhana setelah login.
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/logout', [WebLoginController::class, 'destroy'])->name('logout');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('classrooms', ClassroomController::class)->except('show');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::post('students/import', [WebStudentController::class, 'import'])->name('students.import');
        Route::resource('students', WebStudentController::class)->except('show')->names('students');
        Route::resource('teachers', TeacherController::class)
            ->except('show')
            ->parameters(['teachers' => 'teacher'])
            ->names('teachers');

        Route::get('teacher-reports', [\App\Http\Controllers\Web\TeacherReportController::class, 'index'])->name('teacher-reports.index');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('attendances', [WebAttendanceController::class, 'index'])->name('web.attendances.index');
        Route::post('attendances/bulk', [WebAttendanceController::class, 'bulkStore'])->name('web.attendances.bulk');
        Route::get('attendances/history', [WebAttendanceController::class, 'history'])->name('web.attendances.history');
        Route::delete('attendances/{attendance}', [WebAttendanceController::class, 'destroy'])->name('web.attendances.destroy');

        Route::resource('teaching-journals', WebTeachingJournalController::class)
            ->except('show')
            ->parameters(['teaching-journals' => 'teachingJournal'])
            ->names('web.teaching-journals');
    });
});
