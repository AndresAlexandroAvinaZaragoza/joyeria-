<?php

namespace App\Http\Controllers;

use App\Models\Carrito;
use App\Models\CarritoItem;
use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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

        DB::transaction(function () use ($request, $productoModel): void {
            $cart = $this->getOrCreateCart($request);
            $item = $cart->items()->where('producto_id', $productoModel->getKey())->first();

            if ($item) {
                $item->update(['cantidad' => min($item->cantidad + 1, 99)]);
            } else {
                $cart->items()->create([
                    'producto_id' => $productoModel->getKey(),
                    'cantidad' => 1,
                ]);
            }
        });

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

        $cart = $this->findCart($request);

        if ($cart) {
            DB::transaction(function () use ($cart, $validProductIds, $quantities): void {
                $cart->items()->whereNotIn('producto_id', $validProductIds)->delete();

                foreach ($validProductIds as $productId) {
                    $cart->items()->updateOrCreate(
                        ['producto_id' => $productId],
                        ['cantidad' => $quantities[$productId]],
                    );
                }
            });
        }

        return back()->with('cart_status', 'El carrito se actualizó.');
    }

    public function removeFromCart(Request $request, int $producto): RedirectResponse
    {
        $cart = $this->findCart($request);

        if ($cart) {
            $cart->items()->where('producto_id', $producto)->delete();
        }

        return back()->with('cart_status', 'La pieza se retiró del carrito.');
    }

    private function cartCount(Request $request): int
    {
        return (int) ($this->findCart($request)?->items()->sum('cantidad') ?? 0);
    }

    private function cartItems(Request $request): Collection
    {
        $cart = $this->findCart($request);

        if (! $cart) {
            return collect();
        }

        return $cart->items()
            ->with(['producto.categoria', 'producto.imagenes'])
            ->get()
            ->map(fn (CarritoItem $item): array => [
                'producto' => $item->producto,
                'quantity' => $item->cantidad,
                'subtotal' => $item->producto->precio_venta * $item->cantidad,
            ]);
    }

    private function findCart(Request $request): ?Carrito
    {
        $query = Carrito::query();

        if ($request->user()) {
            return $query->where('user_id', $request->user()->getKey())->first();
        }

        return $query->whereNull('user_id')
            ->where('session_token', $request->session()->getId())
            ->first();
    }

    private function getOrCreateCart(Request $request): Carrito
    {
        $attributes = $request->user()
            ? ['user_id' => $request->user()->getKey()]
            : ['session_token' => $request->session()->getId(), 'user_id' => null];

        $cart = Carrito::firstOrCreate($attributes);

        if ($cart->wasRecentlyCreated) {
            $this->importSessionCart($request, $cart);
        }

        return $cart;
    }

    private function importSessionCart(Request $request, Carrito $cart): void
    {
        $sessionCart = $request->session()->get('cart', []);

        if ($sessionCart === []) {
            return;
        }

        $productIds = Producto::query()
            ->whereIn('id_productos', array_keys($sessionCart))
            ->where('status', true)
            ->pluck('id_productos');

        foreach ($productIds as $productId) {
            $cart->items()->create([
                'producto_id' => $productId,
                'cantidad' => min((int) $sessionCart[$productId], 99),
            ]);
        }

        $request->session()->forget('cart');
    }
}
