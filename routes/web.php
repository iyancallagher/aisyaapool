<?php

use App\Http\Controllers\Admin\KategoriController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MenuController as PublicMenuController;
use App\Http\Controllers\PesananController;
use Illuminate\Support\Facades\Route;

// ---------- Sisi Pelanggan (Public, tanpa login) ----------
Route::inertia('/', 'Welcome')->name('home');
Route::get('/katalog', [PublicMenuController::class, 'index'])->name('katalog');
Route::post('/pesanan', [PesananController::class, 'store'])->name('pesanan.store');

// ---------- Halaman yang butuh login ----------
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::patch('/pesanan/{pesanan}/status', [DashboardController::class, 'updateStatus'])->name('pesanan.update-status');

    Route::get('/menu-kelola', [MenuController::class, 'index'])->name('menu.index');
    Route::post('/menu-kelola', [MenuController::class, 'store'])->name('menu.store');
    Route::patch('/menu-kelola/{menu}', [MenuController::class, 'update'])->name('menu.update');
    Route::delete('/menu-kelola/{menu}', [MenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('/kategori-kelola', [KategoriController::class, 'index'])->name('kategori.index');
    Route::post('/kategori-kelola', [KategoriController::class, 'store'])->name('kategori.store');
    Route::patch('/kategori-kelola/{kategori}', [KategoriController::class, 'update'])->name('kategori.update');
    Route::delete('/kategori-kelola/{kategori}', [KategoriController::class, 'destroy'])->name('kategori.destroy');
});

require __DIR__.'/settings.php';