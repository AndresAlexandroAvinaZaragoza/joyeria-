<?php
use App\Http\Controllers\Api\EnvioController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductoImagenController;
use App\Http\Controllers\Api\ProductoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/productos', [ProductoController::class, 'index']);
    Route::get('/productos/{producto}', [ProductoController::class, 'show']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::put('/productos/{producto}', [ProductoController::class, 'update']);
    Route::delete('/productos/{producto}', [ProductoController::class, 'destroy']);

    Route::post('/productos/{producto}/imagenes',[ProductoImagenController::class, 'store']);
    Route::post(
    '/productos/{producto}/imagenes/{imagen}',
    [ProductoImagenController::class, 'update']
    );

    Route::delete(
        '/productos/{producto}/imagenes/{imagen}',
        [ProductoImagenController::class, 'destroy']
    );

    // Consultar todos los pedidos
    Route::get('/pedidos', [PedidoController::class, 'index']);

    // Consultar un pedido específico
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show']);

    // Consultar todos los envíos
    Route::get('/envios', [EnvioController::class, 'index']);

    // Consultar un envío específico
    Route::get('/envios/{envio}', [EnvioController::class, 'show']);

    // Actualizar un envío
    Route::patch('/envios/{envio}', [EnvioController::class, 'update']);
}); 