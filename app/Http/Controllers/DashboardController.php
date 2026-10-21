<?php

namespace App\Http\Controllers;

use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Producto;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = Carbon::now()->startOfMonth();

        return view('dashboard', [
            'activeProducts' => Producto::where('status', true)->count(),
            'lowStockProducts' => Inventario::whereColumn('stock_actual', '<=', 'stock_minimo')->count(),
            'pendingOrders' => Pedido::whereIn('estado', ['pendiente', 'preparando'])->count(),
            'monthlyRevenue' => Pedido::where('created_at', '>=', $monthStart)
                ->whereNotIn('estado', ['cancelado'])
                ->sum('total'),
            'recentOrders' => Pedido::with('usuario')
                ->latest()
                ->limit(5)
                ->get(),
            'lowStockItems' => Inventario::with('producto')
                ->whereColumn('stock_actual', '<=', 'stock_minimo')
                ->orderBy('stock_actual')
                ->limit(4)
                ->get(),
        ]);
    }
}
