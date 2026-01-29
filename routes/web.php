<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Route Dashboard: Menampilkan pencarian cuaca dan daftar riwayat favorit
Route::get('/dashboard', [WeatherController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route CRUD Weather: Hanya bisa diakses oleh user yang sudah login
Route::middleware(['auth', 'verified'])->group(function () {
    // Menambah data ke favorit (Create)
    Route::post('/weather/store', [WeatherController::class, 'store'])->name('weather.store');
    
    // Menghapus data dari favorit 
    Route::delete('/weather/{id}', [WeatherController::class, 'destroy'])->name('weather.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';