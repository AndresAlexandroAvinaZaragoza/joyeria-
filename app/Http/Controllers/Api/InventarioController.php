<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Producto;
use App\Services\JoyeriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    /**
     * Obtener todos los registros de inventario.
     */
    public function index(): JsonResponse
    {
        $inventarios = Inventario::with('producto.categoria')
            ->orderByDesc('id_inventario')
            ->get();

        return response()->json([
            'message' => 'Inventario obtenido correctamente.',
            'inventarios' => $inventarios,
        ]);
    }

    /**
     * Obtener un registro de inventario específico.
     */
    public function show(int $inventario): JsonResponse
    {
        $inventarioModel = Inventario::with('producto.categoria')
            ->findOrFail($inventario);

        return response()->json([
            'message' => 'Registro de inventario obtenido correctamente.',
            'inventario' => $inventarioModel,
        ]);
    }

    /**
     * Crear el inventario de un producto y registrar su entrada inicial.
     */
    public function store(Request $request, JoyeriaService $service): JsonResponse
    {
        $datos = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id_productos'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:2147483647'],
            'stock_minimo' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $producto = Producto::findOrFail($datos['producto_id']);

        $inventario = DB::transaction(function () use ($datos, $producto, $request, $service): Inventario {
            $inventario = $producto->inventario()->firstOrCreate(
                [],
                [
                    'stock_actual' => 0,
                    'stock_minimo' => $datos['stock_minimo'] ?? 0,
                ]
            );

            if (array_key_exists('stock_minimo', $datos)) {
                $inventario->update(['stock_minimo' => $datos['stock_minimo']]);
            }

            $service->adjustInventory(
                $producto,
                $request->user()->getKey(),
                'ENTRADA',
                (int) $datos['cantidad']
            );

            return $inventario->fresh('producto.categoria');
        });

        return response()->json([
            'message' => 'Stock agregado correctamente.',
            'inventario' => $inventario,
        ], 201);
    }

    /**
     * Ajustar las existencias y el stock mínimo de un producto.
     */
    public function update(
        Request $request,
        int $inventario,
        JoyeriaService $service
    ): JsonResponse {
        $datos = $request->validate([
            'stock_actual' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'stock_minimo' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
        ]);

        $inventarioModel = Inventario::with('producto')->findOrFail($inventario);
        $stockAnterior = $inventarioModel->stock_actual;
        $stockNuevo = (int) $datos['stock_actual'];

        DB::transaction(function () use (
            $datos,
            $inventarioModel,
            $request,
            $service,
            $stockAnterior,
            $stockNuevo
        ): void {
            if ($stockNuevo !== $stockAnterior) {
                $service->adjustInventory(
                    $inventarioModel->producto,
                    $request->user()->getKey(),
                    $stockNuevo > $stockAnterior ? 'ENTRADA' : 'SALIDA',
                    abs($stockNuevo - $stockAnterior)
                );
            }

            $inventarioModel->update([
                'stock_minimo' => $datos['stock_minimo'] ?? 0,
            ]);
        });

        return response()->json([
            'message' => 'Inventario actualizado correctamente.',
            'inventario' => $inventarioModel->fresh('producto.categoria'),
        ]);
    }
}
