<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaImportController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\SuperAdmin\RencanaWisudaController;
use App\Http\Controllers\SuperAdmin\StatusFollowUpController;
use App\Http\Controllers\SuperAdmin\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

// ==== Guest (login saja, register publik DIMATIKAN) ====
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard tabel utama (PIC & SuperAdmin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('/mahasiswa/{mahasiswa}/follow-up', [DashboardController::class, 'updateFollowUp'])
        ->name('follow-up.update');

    // Import Excel (PIC & SuperAdmin boleh upload)
    Route::get('/mahasiswa/import', [MahasiswaImportController::class, 'create'])->name('mahasiswa.import');
    Route::post('/mahasiswa/import', [MahasiswaImportController::class, 'store'])->name('mahasiswa.import.store');

    // Statistik - bisa dilihat semua user
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik');

    // ==== Khusus SuperAdmin ====
    Route::middleware('superadmin')->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::get('/status-follow-up', [StatusFollowUpController::class, 'index'])->name('status-follow-up.index');
        Route::post('/status-follow-up', [StatusFollowUpController::class, 'store'])->name('status-follow-up.store');
        Route::patch('/status-follow-up/{statusFollowUp}', [StatusFollowUpController::class, 'update'])->name('status-follow-up.update');
        Route::delete('/status-follow-up/{statusFollowUp}', [StatusFollowUpController::class, 'destroy'])->name('status-follow-up.destroy');

        Route::get('/rencana-wisuda', [RencanaWisudaController::class, 'index'])->name('rencana-wisuda.index');
        Route::post('/rencana-wisuda', [RencanaWisudaController::class, 'store'])->name('rencana-wisuda.store');
        Route::patch('/rencana-wisuda/{rencanaWisuda}', [RencanaWisudaController::class, 'update'])->name('rencana-wisuda.update');
        Route::delete('/rencana-wisuda/{rencanaWisuda}', [RencanaWisudaController::class, 'destroy'])->name('rencana-wisuda.destroy');
    });
});
