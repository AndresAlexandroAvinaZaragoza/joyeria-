<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventarioContoller extends Controller
{
    public function index(Request $request): View
    {
        $inventarioQuery = Inventario::with('producto.categoria');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $inventarioQuery->whereHas('producto', function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $inventarioQuery->whereHas('producto', function ($query) use ($request) {
                $query->where('id_categoria', $request->input('categoria'));
            });
        }

        $inventarios = $inventarioQuery->paginate(10)->withQueryString();
        $productos = Producto::with('categoria')
            ->where('status', true)
            ->whereDoesntHave('inventario')
            ->orderBy('nombre')
            ->get();
        $categorias = Categoria::where('status', true)->orderBy('nombre')->get();

        return view('admin.productos.inventario', compact('inventarios', 'productos', 'categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'producto_id' => ['required', 'exists:productos,id_productos'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
        ]);

        $inventario = Inventario::firstOrNew(['producto_id' => $request->producto_id]);
        $inventario->stock_actual += (int) $request->cantidad;
        if ($request->filled('stock_minimo')) {
            $inventario->stock_minimo = $request->stock_minimo;
        }
        $inventario->save();

        return redirect()->route('inventario.index')->with('status', 'Stock agregado correctamente.');
    }

    public function update(Request $request, int $inventario): RedirectResponse
    {
        $request->validate([
            'stock_actual' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['nullable', 'integer', 'min:0'],
        ]);

        $registro = Inventario::findOrFail($inventario);
        $registro->update([
            'stock_actual' => $request->stock_actual,
            'stock_minimo' => $request->stock_minimo ?? 0,
        ]);

        return redirect()->route('inventario.index')->with('status', 'Stock actualizado correctamente.');
    }
}
