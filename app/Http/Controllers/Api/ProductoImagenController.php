<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoImagenController extends Controller
{
    /**
     * Agregar una imagen a un producto.
     */
    public function store(Request $request, int $producto): JsonResponse
    {
        $productoModel = Producto::findOrFail($producto);

        $request->validate([
            'imagen' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120'
            ],
            'es_principal' => ['nullable', 'boolean'],
        ]);

        $ruta = $request->file('imagen')->store('productos', 'public');

        $esPrincipal = $request->boolean('es_principal');

        // Si es la primera imagen, automáticamente será principal.
        if ($productoModel->imagenes()->count() === 0) {
            $esPrincipal = true;
        }

        DB::transaction(function () use (
            $productoModel,
            $ruta,
            $esPrincipal
        ) {
            if ($esPrincipal) {
                $productoModel->imagenes()->update([
                    'es_principal' => false
                ]);
            }

            $orden = ($productoModel->imagenes()->max('orden') ?? 0) + 1;

            $productoModel->imagenes()->create([
                'url_img' => $ruta,
                'es_principal' => $esPrincipal,
                'orden' => $orden,
            ]);
        });

        $productoModel->load('imagenes');

        return response()->json([
            'message' => 'Imagen agregada correctamente.',
            'producto' => $productoModel,
        ], 201);
    }
}