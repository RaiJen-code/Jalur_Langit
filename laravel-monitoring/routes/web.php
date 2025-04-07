<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Hapus yang duplikat dan gunakan satu deklarasi saja
Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/api/latest-data', [DashboardController::class, 'getLatestData'])->name('dashboard.latest');
//Route::get('/export/{type}', [DashboardController::class, 'exportData'])->name('dashboard.export');
Route::get('/dashboard/latest', [DashboardController::class, 'getLatestData'])->name('dashboard.latest');

