<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PorductosController extends Controller
{
    public function index(Request $request): View
    {
        $productosQuery = Producto::with('categoria');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $productosQuery->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $productosQuery->where('id_categoria', $request->input('categoria'));
        }

        if ($request->filled('material')) {
            $productosQuery->where('material', $request->input('material'));
        }

        $productos = $productosQuery->paginate(10)->withQueryString();
        $categorias = Categoria::where('status', true)->get();

        return view('admin.productos.productos', compact('categorias', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'sku' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'descripcion' => ['nullable', 'string', 'max:1000', $this->descriptionWordLimitRule()],
            'categoria' => ['required', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'peso_gr' => ['nullable', 'numeric', 'min:0'],
            'talla' => ['nullable', 'string', 'max:30', 'regex:/^[\pL0-9.\s-]+$/u'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
        ], $this->productValidationMessages());

        Producto::create([
            'sku' => $request->sku,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_categoria' => $request->categoria,
            'material' => $request->material,
            'peso_gr' => $request->peso_gr,
            'talla_medida' => $request->talla,
            'precio_costo' => $request->precio_costo,
            'precio_venta' => $request->precio_venta,
            'status' => true,
        ]);

        return redirect()->route('productos.index')->with('status', 'Producto guardado correctamente.');
    }

    public function update(Request $request, int $producto): RedirectResponse
    {
        $productoModel = Producto::findOrFail($producto);

        $request->validate([
            'sku' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/', Rule::unique('productos', 'sku')->ignore($productoModel->id_productos, 'id_productos')],
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'descripcion' => ['nullable', 'string', 'max:1000', $this->descriptionWordLimitRule()],
            'categoria' => ['required', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100', 'regex:/^[\pL0-9\s]+$/u'],
            'peso_gr' => ['nullable', 'numeric', 'min:0'],
            'talla' => ['nullable', 'string', 'max:30', 'regex:/^[\pL0-9.\s-]+$/u'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
        ], $this->productValidationMessages());

        $productoModel->update([
            'sku' => $request->sku,
            'nombre' => $request->nombre,
            'descripcion' => $request->descripcion,
            'id_categoria' => $request->categoria,
            'material' => $request->material,
            'peso_gr' => $request->peso_gr,
            'talla_medida' => $request->talla,
            'precio_costo' => $request->precio_costo,
            'precio_venta' => $request->precio_venta,
        ]);

        return redirect()->route('productos.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(int $producto): RedirectResponse
    {
        Producto::findOrFail($producto)->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado correctamente.');
    }

    public function toggleStatus(int $producto): RedirectResponse
    {
        $productoModel = Producto::findOrFail($producto);
        $productoModel->update(['status' => ! $productoModel->status]);

        return redirect()->route('productos.index')->with(
            'status',
            $productoModel->status ? 'Producto activado correctamente.' : 'Producto desactivado correctamente.'
        );
    }

    /**
     * @return array<string, string>
     */
    private function productValidationMessages(): array
    {
        return [
            'sku.regex' => 'El SKU solo puede contener letras, números y guiones.',
            'nombre.regex' => 'El nombre del producto solo debe contener letras y espacios.',
            'material.regex' => 'El material solo puede contener letras, números y espacios.',
            'talla.regex' => 'La talla o medida contiene caracteres no válidos.',
        ];
    }

    private function descriptionWordLimitRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            preg_match_all('/[\p{L}\p{N}]+/u', $value, $matches);

            if (count($matches[0]) > 150) {
                $fail('La descripción no puede superar 150 palabras.')->translate();
            }
        };
    }
}
