<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KinerjaPicController;
use App\Http\Controllers\MahasiswaImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\SuperAdmin\FakultasController;
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

    // Edit profil (nama, email, foto, password) - semua user login
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Dashboard: pilih Fakultas -> Prodi -> tabel Data & Follow Up (PIC & SuperAdmin)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/prodi/{prodi}', [DashboardController::class, 'prodi'])->name('dashboard.prodi');
    Route::get('/dashboard/legacy', [DashboardController::class, 'legacy'])->name('dashboard.legacy');
    Route::put('/mahasiswa/{mahasiswa}/follow-up', [DashboardController::class, 'updateFollowUp'])
        ->name('follow-up.update');
    Route::delete('/mahasiswa/{mahasiswa}', [DashboardController::class, 'destroy'])->name('mahasiswa.destroy');
    Route::delete('/mahasiswa-bulk', [DashboardController::class, 'destroyBulk'])->name('mahasiswa.destroy-bulk');

    // Import Excel (PIC & SuperAdmin boleh upload)
    Route::get('/mahasiswa/import', [MahasiswaImportController::class, 'create'])->name('mahasiswa.import');
    Route::post('/mahasiswa/import', [MahasiswaImportController::class, 'store'])->name('mahasiswa.import.store');

    // Statistik - bisa dilihat semua user
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik');

    // Kinerja PIC: SuperAdmin bisa lihat semua PIC, PIC hanya bisa lihat datanya sendiri
    // (pembatasan aksesnya ditangani di dalam KinerjaPicController, bukan di sini)
    Route::get('/kinerja', [KinerjaPicController::class, 'index'])->name('kinerja.index');

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

        Route::get('/fakultas', [FakultasController::class, 'index'])->name('fakultas.index');
        Route::post('/fakultas', [FakultasController::class, 'store'])->name('fakultas.store');
        Route::patch('/fakultas/{fakultas}', [FakultasController::class, 'update'])->name('fakultas.update');
        Route::delete('/fakultas/{fakultas}', [FakultasController::class, 'destroy'])->name('fakultas.destroy');
        Route::post('/fakultas/{fakultas}/prodi', [FakultasController::class, 'storeProdi'])->name('fakultas.prodi.store');
        Route::patch('/prodi/{prodi}', [FakultasController::class, 'updateProdi'])->name('prodi.update');
        Route::delete('/prodi/{prodi}', [FakultasController::class, 'destroyProdi'])->name('prodi.destroy');
    });
});
