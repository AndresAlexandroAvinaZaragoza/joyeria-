<?php

use App\Http\Controllers\PorductosController;
use App\Http\Controllers\InventarioContoller;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('usuarios', UsuarioController::class)->middleware(['auth', 'verified']);
Route::resource('inventario', InventarioContoller::class)->middleware(['auth', 'verified']);
ROute::resource('productos', PorductosController::class)->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
