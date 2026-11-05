<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        return collect([
            ['Periodo', $this->report['from']->format('d/m/Y').' - '.$this->report['to']->format('d/m/Y'), null, null],
            ['Ingresos totales', $this->report['total_revenue'], null, null],
            ['Ventas realizadas', $this->report['sales_count'], null, null],
            [null, null, null, null],
            ...collect($this->report['top_products'])->map(fn (array $product): array => [
                $product['name'],
                $product['sku'],
                $product['quantity'],
                $product['revenue'],
            ])->all(),
        ]);
    }

    public function headings(): array
    {
        return ['Producto / resumen', 'SKU / periodo', 'Unidades / ventas', 'Ingresos'];
    }
}
