<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        return collect($this->report['items'])->map(fn (array $item): array => [
            $item['name'],
            $item['sku'],
            $item['category'],
            $item['stock'],
            $item['minimum'],
            $item['unit_cost'],
            $item['value'],
            match ($item['status']) {
                'agotado' => 'Agotado',
                'stock_minimo' => 'Stock mínimo',
                default => 'Normal',
            },
        ]);
    }

    public function headings(): array
    {
        return [
            'Producto',
            'SKU',
            'Categoría',
            'Existencias',
            'Stock mínimo',
            'Costo unitario',
            'Valor inventario',
            'Estado',
        ];
    }
}
