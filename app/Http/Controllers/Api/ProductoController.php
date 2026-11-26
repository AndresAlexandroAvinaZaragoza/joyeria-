<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
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
        ], $this->productValidationMessages());

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
        ], $this->productValidationMessages());

        $producto->update($datos);

        return response()->json(
            $producto->fresh()->load([
                'categoria',
                'inventario',
                'imagenes',
            ])
        );
    }

    public function destroy(Producto $producto): JsonResponse
    {
        if ($producto->hasBlockingReferences()) {
            return response()->json([
                'message' => 'No se puede eliminar el producto porque está asociado a un carrito, pedido, compra o movimiento de inventario. Puedes desactivarlo en su lugar.',
            ], 409);
        }

        $producto->delete();

        return response()->json([
            'message' => 'Producto eliminado correctamente.',
        ]);
    }

    public function categorias(): JsonResponse
    {
        $categorias = Categoria::where('status', true)
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'message' => 'Categorías activas obtenidas correctamente.',
            'categorias' => $categorias,
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

    /**
     * @return array<string, string>
     */
    private function productValidationMessages(): array
    {
        return [
            'sku.required' => 'El código SKU es obligatorio.',
            'sku.max' => 'El SKU no puede superar los 50 caracteres.',
            'sku.regex' => 'El SKU solo puede contener letras, números y guiones (ej. JOY-001).',
            'sku.unique' => 'Este SKU ya está registrado en otro producto. Debe ser único.',
            'nombre.required' => 'El nombre del producto es obligatorio.',
            'nombre.max' => 'El nombre no puede superar los 100 caracteres.',
            'nombre.regex' => 'El nombre del producto solo debe contener letras y espacios (sin números ni símbolos).',
            'descripcion.max' => 'La descripción no puede superar los 1000 caracteres.',
            'id_categoria.required' => 'Debe seleccionar una categoría.',
            'id_categoria.integer' => 'La categoría seleccionada no es válida.',
            'id_categoria.exists' => 'La categoría seleccionada no existe o está inactiva.',
            'material.required' => 'El material es obligatorio.',
            'material.max' => 'El material no puede superar los 100 caracteres.',
            'material.regex' => 'El material solo puede contener letras, números y espacios.',
            'peso_gr.numeric' => 'El peso debe ser un valor numérico.',
            'peso_gr.min' => 'El peso no puede ser negativo.',
            'talla_medida.max' => 'La talla o medida no puede superar los 30 caracteres.',
            'talla_medida.regex' => 'La talla o medida contiene caracteres no válidos.',
            'precio_costo.required' => 'El precio de costo es obligatorio.',
            'precio_costo.numeric' => 'El precio de costo debe ser numérico.',
            'precio_costo.min' => 'El precio de costo no puede ser negativo.',
            'precio_venta.required' => 'El precio de venta es obligatorio.',
            'precio_venta.numeric' => 'El precio de venta debe ser numérico.',
            'precio_venta.min' => 'El precio de venta no puede ser negativo.',
            'stock_actual.required' => 'El stock actual es obligatorio.',
            'stock_actual.integer' => 'El stock actual debe ser un número entero.',
            'stock_minimo.required' => 'El stock mínimo es obligatorio.',
            'stock_minimo.integer' => 'El stock mínimo debe ser un número entero.',
            'status.required' => 'El estado del producto es obligatorio.',
            'status.boolean' => 'El estado debe ser verdadero o falso.',
        ];
    }
}
