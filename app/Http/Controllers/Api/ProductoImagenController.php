<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Producto_img;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoImagenController extends Controller
{
    // Agregar fotografía
    public function store(Request $request, int $producto): JsonResponse
    {
        $productoModel = Producto::findOrFail($producto);

        $request->validate([
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'es_principal' => ['nullable', 'boolean'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $ruta = $request->file('imagen')->store('productos', 'public');

        try {
            DB::transaction(function () use ($request, $productoModel, $ruta) {
                $esPrincipal = $request->boolean('es_principal');

                if (!$productoModel->imagenes()->exists()) {
                    $esPrincipal = true;
                }

                if ($esPrincipal) {
                    $productoModel->imagenes()->update([
                        'es_principal' => false,
                    ]);
                }

                $orden = $request->filled('orden')
                    ? (int) $request->input('orden')
                    : (($productoModel->imagenes()->max('orden') ?? 0) + 1);

                $productoModel->imagenes()->create([
                    'url_img' => $ruta,
                    'es_principal' => $esPrincipal,
                    'orden' => $orden,
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($ruta);
            throw $e;
        }

        return response()->json([
            'message' => 'Imagen agregada correctamente.',
            'producto' => $productoModel->load('imagenes'),
        ], 201);
    }

    // Editar fotografía, orden y estado principal
    public function update(
        Request $request,
        int $producto,
        int $imagen
    ): JsonResponse {

        $productoModel = Producto::findOrFail($producto);

        $imagenModel = Producto_img::where('producto_id', $producto)
            ->where('id_img', $imagen)
            ->firstOrFail();

        $validated = $request->validate([
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'es_principal' => ['required', 'boolean'],
            'orden' => ['required', 'integer', 'min:0'],
        ]);

        $rutaAnterior = $imagenModel->url_img;

        $rutaNueva = $request->hasFile('imagen')
            ? $request->file('imagen')->store('productos', 'public')
            : null;

        try {
            DB::transaction(function () use (
                $imagenModel,
                $validated,
                $rutaNueva,
                $producto
            ) {
                $esPrincipal = (bool) $validated['es_principal'];

                if ($esPrincipal) {
                    Producto_img::where('producto_id', $producto)
                        ->where('id_img', '<>', $imagenModel->id_img)
                        ->update(['es_principal' => false]);
                }

                $imagenModel->update([
                    'url_img' => $rutaNueva ?? $imagenModel->url_img,
                    'es_principal' => $esPrincipal,
                    'orden' => $validated['orden'],
                ]);
            });
        } catch (\Throwable $e) {
            if ($rutaNueva) {
                Storage::disk('public')->delete($rutaNueva);
            }
            throw $e;
        }

        if ($rutaNueva && $rutaAnterior) {
            Storage::disk('public')->delete($rutaAnterior);
        }

        return response()->json([
            'message' => 'Imagen actualizada correctamente.',
            'producto' => $productoModel->load('imagenes'),
        ]);
    }

    // Eliminar una fotografía
    public function destroy(
        int $producto,
        int $imagen
    ): JsonResponse {

        $productoModel = Producto::findOrFail($producto);

        $imagenModel = Producto_img::where('producto_id', $producto)
            ->where('id_img', $imagen)
            ->firstOrFail();

        $ruta = $imagenModel->url_img;
        $eraPrincipal = (bool) $imagenModel->es_principal;

        DB::transaction(function () use (
            $imagenModel,
            $productoModel,
            $eraPrincipal
        ) {
            $imagenModel->delete();

            if ($eraPrincipal) {
                $siguiente = $productoModel->imagenes()
                    ->orderBy('orden')
                    ->orderBy('id_img')
                    ->first();

                if ($siguiente) {
                    $siguiente->update([
                        'es_principal' => true,
                    ]);
                }
            }
        });

        if ($ruta) {
            Storage::disk('public')->delete($ruta);
        }

        return response()->json([
            'message' => 'Imagen eliminada correctamente.',
            'producto' => $productoModel->load('imagenes'),
        ]);
    }
}
