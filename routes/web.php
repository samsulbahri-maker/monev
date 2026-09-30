<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('proposals', ProposalController::class);
    Route::post('proposals/{proposal}/progress', [ProgressController::class, 'store'])->name('proposals.progress.store');
    Route::get('proposals/{proposal}/evidence', [ProposalController::class, 'evidence'])->name('proposals.evidence');
    Route::get('proposals/{proposal}/progress/{progressUpdate}/evidence', [ProgressController::class, 'evidence'])
        ->scopeBindings()
        ->name('proposals.progress.evidence');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::middleware('can:manage-master-data')->group(function () {
        Route::resource('opds', OpdController::class)->except('show');
        Route::resource('programs', ProgramController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
    });
});
