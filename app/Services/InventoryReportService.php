<?php

namespace App\Services;

use App\Models\Inventario;

class InventoryReportService
{
    /**
     * @return array{
     *     total_products: int,
     *     total_units: int,
     *     total_value: float,
     *     low_stock_count: int,
     *     out_of_stock_count: int,
     *     items: array<int, array{product_id: int, name: string, sku: string, category: string, stock: int, minimum: int, unit_cost: float, value: float, status: string}>
     * }
     */
    public function generate(): array
    {
        $inventory = Inventario::query()
            ->with('producto.categoria')
            ->whereHas('producto', fn ($query) => $query->where('status', true))
            ->get();

        $items = $inventory->map(fn (Inventario $record): array => $this->formatItem($record))->all();

        return [
            'total_products' => count($items),
            'total_units' => (int) $inventory->sum('stock_actual'),
            'total_value' => round((float) $inventory->sum(
                fn (Inventario $record): float => $record->stock_actual * (float) $record->producto->precio_costo
            ), 2),
            'low_stock_count' => count(array_filter($items, fn (array $item): bool => $item['status'] === 'stock_minimo')),
            'out_of_stock_count' => count(array_filter($items, fn (array $item): bool => $item['status'] === 'agotado')),
            'items' => $items,
        ];
    }

    /**
     * @return array{product_id: int, name: string, sku: string, category: string, stock: int, minimum: int, unit_cost: float, value: float, status: string}
     */
    private function formatItem(Inventario $record): array
    {
        $product = $record->producto;
        $stock = (int) $record->stock_actual;
        $minimum = (int) $record->stock_minimo;

        return [
            'product_id' => (int) $product->getKey(),
            'name' => $product->nombre,
            'sku' => $product->sku,
            'category' => $product->categoria?->nombre ?? 'Sin categoría',
            'stock' => $stock,
            'minimum' => $minimum,
            'unit_cost' => (float) $product->precio_costo,
            'value' => round($stock * (float) $product->precio_costo, 2),
            'status' => $stock === 0 ? 'agotado' : ($stock <= $minimum ? 'stock_minimo' : 'normal'),
        ];
    }
}
