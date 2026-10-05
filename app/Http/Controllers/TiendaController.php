<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TiendaController extends Controller
{
    public function index(Request $request): View
    {
        $productosQuery = Producto::query()
            ->with(['categoria', 'imagenes' => fn ($query) => $query->orderByDesc('es_principal')->orderBy('orden')])
            ->where('status', true);

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();

            $productosQuery->where(function ($query) use ($search) {
                $query->where('nombre', 'like', "%{$search}%")
                    ->orWhere('descripcion', 'like', "%{$search}%")
                    ->orWhere('material', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('categoria')) {
            $productosQuery->whereHas('categoria', function ($query) use ($request) {
                $query->where('slug', $request->string('categoria')->toString());
            });
        }

        $productos = $productosQuery->latest()->paginate(8)->withQueryString();
        $categorias = Categoria::query()
            ->where('status', true)
            ->orderBy('nombre')
            ->get();

        return view('welcome', [
            'productos' => $productos,
            'categorias' => $categorias,
            'cartCount' => $this->cartCount($request),
            'cartItems' => $this->cartItems($request),
        ]);
    }

    public function cart(Request $request): View
    {
        $items = $this->cartItems($request);

        return view('store.cart', [
            'items' => $items,
            'cartItems' => $items,
            'total' => $items->sum('subtotal'),
            'cartCount' => $this->cartCount($request),
            'categorias' => Categoria::where('status', true)->orderBy('nombre')->get(),
        ]);
    }

    public function addToCart(Request $request, int $producto): RedirectResponse
    {
        $productoModel = Producto::where('status', true)->findOrFail($producto);
        $cart = $request->session()->get('cart', []);
        $cart[$productoModel->getKey()] = min(($cart[$productoModel->getKey()] ?? 0) + 1, 99);
        $request->session()->put('cart', $cart);

        return back()->with('cart_status', 'La pieza se agregó al carrito.');
    }

    public function updateCart(Request $request): RedirectResponse
    {
        $quantities = $request->validate([
            'quantities' => ['array'],
            'quantities.*' => ['integer', 'min:1', 'max:99'],
        ])['quantities'] ?? [];

        $validProductIds = Producto::whereIn('id_productos', array_keys($quantities))
            ->where('status', true)
            ->pluck('id_productos')
            ->all();

        $cart = [];
        foreach ($validProductIds as $productId) {
            $cart[$productId] = $quantities[$productId];
        }

        $request->session()->put('cart', $cart);

        return back()->with('cart_status', 'El carrito se actualizó.');
    }

    public function removeFromCart(Request $request, int $producto): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$producto]);
        $request->session()->put('cart', $cart);

        return back()->with('cart_status', 'La pieza se retiró del carrito.');
    }

    private function cartCount(Request $request): int
    {
        return collect($request->session()->get('cart', []))->sum();
    }

    private function cartItems(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $productos = Producto::with(['categoria', 'imagenes'])
            ->whereIn('id_productos', array_keys($cart))
            ->get()
            ->keyBy('id_productos');

        return collect($cart)->map(function (int $quantity, string $productId) use ($productos) {
            $producto = $productos->get((int) $productId);

            if (! $producto) {
                return null;
            }

            return [
                'producto' => $producto,
                'quantity' => $quantity,
                'subtotal' => $producto->precio_venta * $quantity,
            ];
        })->filter();
    }
}
