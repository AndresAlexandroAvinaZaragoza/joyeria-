<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    /**
     * Obtener todos los productos.
     */
    public function index(): JsonResponse
    {
        $productos = Producto::with([
            'categoria',
            'inventario',
            'imagenes'
        ])
        ->orderByDesc('id_productos')
        ->get();

        return response()->json([
            'message' => 'Productos obtenidos correctamente.',
            'productos' => $productos,
        ]);
    }

    /**
     * Guardar un nuevo producto.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'id_categoria' => ['required', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100'],
            'peso_gr' => ['nullable', 'numeric', 'min:0'],
            'talla_medida' => ['nullable', 'string', 'max:30'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],

            'stock_actual' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
        ]);

        $producto = Producto::create([
            'sku' => $datos['sku'],
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'id_categoria' => $datos['id_categoria'],
            'material' => $datos['material'],
            'peso_gr' => $datos['peso_gr'] ?? null,
            'talla_medida' => $datos['talla_medida'] ?? null,
            'precio_costo' => $datos['precio_costo'],
            'precio_venta' => $datos['precio_venta'],
            'status' => true,
        ]);

        $producto->inventario()->create([
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
        ]);

        $producto->load([
            'categoria',
            'inventario',
            'imagenes'
        ]);

        return response()->json([
            'message' => 'Producto creado correctamente.',
            'producto' => $producto,
        ], 201);
    }

    /**
     * Obtener un producto específico.
     */
    public function show(int $producto): JsonResponse
    {
        $producto = Producto::with([
            'categoria',
            'inventario',
            'imagenes'
        ])->findOrFail($producto);

        return response()->json([
            'producto' => $producto,
        ]);
    }
}