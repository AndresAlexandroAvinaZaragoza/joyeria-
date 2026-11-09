<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
            'imagenes',
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
            'sku' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'descripcion' => ['nullable', 'string', 'max:1000', $this->descriptionWordLimitRule()],
            'id_categoria' => ['required', 'integer', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'peso_gr' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'talla_medida' => ['nullable', 'string', 'max:30', 'regex:/^[\pL0-9.\s-]+$/u'],
            'precio_costo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:99999999.99'],

            'stock_actual' => ['required', 'integer', 'min:0', 'max:2147483647'],
            'stock_minimo' => ['required', 'integer', 'min:0', 'max:2147483647'],
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
            'imagenes',
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
            'imagenes',
        ])->findOrFail($producto);

        return response()->json([
            'producto' => $producto,
        ]);
    }

    public function update(Request $request, Producto $producto)
    {
        $datos = $request->validate([
            'sku' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('productos', 'sku')->ignore($producto->getKey(), 'id_productos')],
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'descripcion' => ['nullable', 'string', 'max:1000', $this->descriptionWordLimitRule()],
            'material' => ['required', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'peso_gr' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
            'talla_medida' => ['nullable', 'string', 'max:30', 'regex:/^[\pL0-9.\s-]+$/u'],
            'precio_costo' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'status' => ['required', 'boolean'],
            'id_categoria' => ['required', 'integer', 'exists:categorias,id_categorias'],
        ]);

        $producto->update($datos);

        return response()->json(
            $producto->fresh()->load([
                'categoria',
                'inventario',
                'imagenes',
            ])
        );
    }

    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    private function descriptionWordLimitRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            preg_match_all('/[\p{L}\p{N}]+/u', $value, $matches);

            if (count($matches[0]) > 150) {
                $fail('La descripción no puede superar 150 palabras.');
            }
        };
    }
}
