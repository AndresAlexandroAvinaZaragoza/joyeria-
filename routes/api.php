<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EnvioController;
use App\Http\Controllers\Api\InventarioController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProductoImagenController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/categorias', [ProductoController::class, 'categorias']);
    Route::get('/productos/{producto}', [ProductoController::class, 'show']);

    Route::middleware('staff')->group(function () {
        Route::post('/productos', [ProductoController::class, 'store']);
        Route::put('/productos/{producto}', [ProductoController::class, 'update']);
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);

        Route::post('/productos/{producto}/imagenes', [ProductoImagenController::class, 'store']);
        Route::post(
            '/productos/{producto}/imagenes/{imagen}',
            [ProductoImagenController::class, 'update']
        );

        Route::delete(
            '/productos/{producto}/imagenes/{imagen}',
            [ProductoImagenController::class, 'destroy']
        );

        Route::get('/inventario', [InventarioController::class, 'index']);
        Route::get('/inventario/{inventario}', [InventarioController::class, 'show']);
        Route::post('/inventario', [InventarioController::class, 'store']);
        Route::put('/inventario/{inventario}', [InventarioController::class, 'update']);
    });

    Route::get('/pedidos', [PedidoController::class, 'index']);

    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show']);

    Route::get('/envios', [EnvioController::class, 'index']);
    Route::get('/envios/{envio}', [EnvioController::class, 'show']);
    Route::patch('/envios/{envio}', [EnvioController::class, 'update']);
});
