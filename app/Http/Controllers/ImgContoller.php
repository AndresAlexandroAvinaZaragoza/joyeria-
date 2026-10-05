<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Producto_img;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ImgContoller extends Controller
{
    public function index(Request $request): View
    {
        $productosQuery = Producto::with(['imagenes' => function ($query) {
            $query->orderByDesc('es_principal')->orderBy('orden');
        }])->where('status', true);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $productosQuery->where(function ($query) use ($search) {
                $query->where('sku', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        $productosConImagenes = $productosQuery
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();
        $productos = Producto::where('status', true)->orderBy('nombre')->get(['id_productos', 'sku', 'nombre']);

        return view('admin.productos.imagenes', compact('productosConImagenes', 'productos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'exists:productos,id_productos'],
            'imagen' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'es_principal' => ['nullable', 'boolean'],
            'orden' => ['required', 'integer', 'min:0'],
        ]);

        $path = $request->file('imagen')->store('productos', 'public');

        DB::transaction(function () use ($validated, $path): void {
            $esPrincipal = (bool) ($validated['es_principal'] ?? false);

            if ($esPrincipal) {
                Producto_img::where('producto_id', $validated['producto_id'])->update(['es_principal' => false]);
            }

            Producto_img::create([
                'producto_id' => $validated['producto_id'],
                'url_img' => $path,
                'es_principal' => $esPrincipal,
                'orden' => $validated['orden'],
            ]);
        });

        return redirect()->route('imagenes.index')->with('status', 'Imagen agregada correctamente.');
    }

    public function update(Request $request, int $imagen): RedirectResponse
    {
        $imagenModel = Producto_img::findOrFail($imagen);
        $validated = $request->validate([
            'imagen' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'es_principal' => ['nullable', 'boolean'],
            'orden' => ['required', 'integer', 'min:0'],
        ]);

        $oldPath = $imagenModel->url_img;
        $newPath = $request->file('imagen')?->store('productos', 'public');

        DB::transaction(function () use ($imagenModel, $validated, $newPath): void {
            $esPrincipal = (bool) ($validated['es_principal'] ?? false);

            if ($esPrincipal) {
                Producto_img::where('producto_id', $imagenModel->producto_id)
                    ->where('id_img', '<>', $imagenModel->getKey())
                    ->update(['es_principal' => false]);
            }

            $imagenModel->update([
                'url_img' => $newPath ?? $imagenModel->url_img,
                'es_principal' => $esPrincipal,
                'orden' => $validated['orden'],
            ]);
        });

        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('imagenes.index')->with('status', 'Imagen actualizada correctamente.');
    }

    public function destroy(int $imagen): RedirectResponse
    {
        $imagenModel = Producto_img::findOrFail($imagen);
        $path = $imagenModel->url_img;
        $imagenModel->delete();

        if ($path) {
            Storage::disk('public')->delete($path);
        }

        return redirect()->route('imagenes.index')->with('status', 'Imagen eliminada correctamente.');
    }
}
