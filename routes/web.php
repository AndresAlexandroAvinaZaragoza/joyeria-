<?php

use App\Http\Controllers\ConfigController;
use App\Http\Controllers\InventarioContoller;
use App\Http\Controllers\PorductosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
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
Route::resource('inventario', InventarioContoller::class)->only(['index', 'store', 'update'])->middleware(['auth', 'verified']);
Route::patch('productos/{producto}/status', [PorductosController::class, 'toggleStatus'])
    ->middleware(['auth', 'verified'])
    ->name('productos.toggle-status');
Route::resource('productos', PorductosController::class)->middleware(['auth', 'verified']);
Route::get('config', [ConfigController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('config.index');
Route::post('config/roles', [ConfigController::class, 'storeRole'])
    ->middleware(['auth', 'verified'])
    ->name('config.roles.store');
Route::put('config/roles/{rol}', [ConfigController::class, 'updateRole'])
    ->middleware(['auth', 'verified'])
    ->name('config.roles.update');
Route::delete('config/roles/{rol}', [ConfigController::class, 'destroyRole'])
    ->middleware(['auth', 'verified'])
    ->name('config.roles.destroy');
Route::post('config/categorias', [ConfigController::class, 'storeCategory'])
    ->middleware(['auth', 'verified'])
    ->name('config.categories.store');
Route::put('config/categorias/{categoria}', [ConfigController::class, 'updateCategory'])
    ->middleware(['auth', 'verified'])
    ->name('config.categories.update');
Route::delete('config/categorias/{categoria}', [ConfigController::class, 'destroyCategory'])
    ->middleware(['auth', 'verified'])
    ->name('config.categories.destroy');
require __DIR__.'/auth.php';
