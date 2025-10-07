<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MonthlyReportController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\TeachingJournalController;
use Illuminate\Support\Facades\Route;

// Endpoint login publik.
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Endpoint RESTful utama.
    Route::apiResource('students', StudentController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('teaching-journals', TeachingJournalController::class);

    // Endpoint laporan bulanan serta export.
    Route::get('reports/monthly', [MonthlyReportController::class, 'show']);
    Route::get('reports/monthly/pdf', [MonthlyReportController::class, 'exportPdf']);
    Route::get('reports/monthly/excel', [MonthlyReportController::class, 'exportExcel']);

    // Endpoint logout.
    Route::post('logout', [AuthController::class, 'logout']);
});
