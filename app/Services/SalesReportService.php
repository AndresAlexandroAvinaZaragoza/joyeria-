<?php

namespace App\Services;

use App\Models\Pedido;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;

class SalesReportService
{
    /**
     * @return array{
     *     from: CarbonImmutable,
     *     to: CarbonImmutable,
     *     total_revenue: float,
     *     sales_count: int,
     *     daily: array<int, array{label: string, sales: int, revenue: float}>,
     *     weekly: array<int, array{label: string, sales: int, revenue: float}>,
     *     monthly: array<int, array{label: string, sales: int, revenue: float}>,
     *     top_products: array<int, array{product_id: int, name: string, sku: string, quantity: int, revenue: float}>
     * }
     */
    public function generate(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $orders = Pedido::query()
            ->where('estado', '<>', 'cancelado')
            ->whereBetween('created_at', [$from, $to])
            ->with('detalles.producto')
            ->orderBy('created_at')
            ->get();

        return [
            'from' => $from,
            'to' => $to,
            'total_revenue' => round((float) $orders->sum('total'), 2),
            'sales_count' => $orders->count(),
            'daily' => $this->groupOrders($orders, fn (Pedido $order): string => CarbonImmutable::instance($order->created_at)->format('Y-m-d')),
            'weekly' => $this->groupOrders($orders, fn (Pedido $order): string => CarbonImmutable::instance($order->created_at)->startOfWeek(Carbon::MONDAY)->format('Y-m-d')),
            'monthly' => $this->groupOrders($orders, fn (Pedido $order): string => CarbonImmutable::instance($order->created_at)->startOfMonth()->format('Y-m')),
            'top_products' => $this->topProducts($orders),
        ];
    }

    /**
     * @param  Collection<int, Pedido>  $orders
     * @return array<int, array{label: string, sales: int, revenue: float}>
     */
    private function groupOrders(Collection $orders, callable $groupBy): array
    {
        return $orders
            ->groupBy($groupBy)
            ->map(fn ($group, string $label): array => [
                'label' => $label,
                'sales' => $group->count(),
                'revenue' => round((float) $group->sum('total'), 2),
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Pedido>  $orders
     * @return array<int, array{product_id: int, name: string, sku: string, quantity: int, revenue: float}>
     */
    private function topProducts(Collection $orders): array
    {
        return $orders
            ->flatMap(fn (Pedido $order) => $order->detalles)
            ->groupBy('producto_id')
            ->map(function ($details, string $productId): array {
                $product = $details->first()->producto;

                return [
                    'product_id' => (int) $productId,
                    'name' => $product?->nombre ?? 'Producto no disponible',
                    'sku' => $product?->sku ?? '—',
                    'quantity' => (int) $details->sum('cantidad'),
                    'revenue' => round((float) $details->sum('importe'), 2),
                ];
            })
            ->sortByDesc('quantity')
            ->take(10)
            ->values()
            ->all();
    }
}
