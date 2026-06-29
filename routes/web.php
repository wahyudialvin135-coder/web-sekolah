<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MonitoringController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Monitoring
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
    Route::get('/monitoring/{school}', [MonitoringController::class, 'show'])->name('monitoring.show');
    Route::post('/monitoring/{school}/priority', [MonitoringController::class, 'togglePriority'])->name('monitoring.priority');
    Route::post('/monitoring/{school}/notes', [MonitoringController::class, 'updateNotes'])->name('monitoring.notes');

    // Map
    Route::get('/map', [MonitoringController::class, 'mapView'])->name('map.index');

    // Reports & Exports
    Route::get('/reports', [MonitoringController::class, 'reportView'])->name('reports.index');
    Route::get('/reports/print', [MonitoringController::class, 'printReport'])->name('reports.print');
    Route::get('/reports/export', [MonitoringController::class, 'exportCsv'])->name('reports.export');
});

require __DIR__.'/auth.php';
