<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
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
            'sku' => ['required', 'string', 'max:50', 'unique:productos,sku'],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria' => ['required', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100'],
            'peso_gr' => ['nullable', 'numeric', 'min:0'],
            'talla' => ['nullable', 'string', 'max:30'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
        ]);

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
            'sku' => ['required', 'string', 'max:50', Rule::unique('productos', 'sku')->ignore($productoModel->id_productos, 'id_productos')],
            'nombre' => ['required', 'string', 'max:100'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'categoria' => ['required', 'exists:categorias,id_categorias'],
            'material' => ['required', 'string', 'max:100'],
            'peso_gr' => ['nullable', 'numeric', 'min:0'],
            'talla' => ['nullable', 'string', 'max:30'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
        ]);

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
}
