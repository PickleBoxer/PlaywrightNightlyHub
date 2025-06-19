<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Import\ImportPlaywrightController;
use App\Http\Controllers\Import\UploadReportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Reports routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{idReport}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');

    // Upload routes for authenticated users
    Route::get('/upload', [UploadController::class, 'index'])->name('upload.index');
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

// Import routes
// TODO: move to API routes in the future
Route::get('/import/playwright', ImportPlaywrightController::class)->name('import.playwright');
Route::post('/import/upload', UploadReportController::class)->name('upload.report');
