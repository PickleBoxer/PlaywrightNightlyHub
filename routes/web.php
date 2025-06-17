<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

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
Route::get('/import/report/playwright', [ImportController::class, 'importPlaywright']);
Route::post('/import/upload', [ImportController::class, 'uploadReport'])->name('upload.report');
