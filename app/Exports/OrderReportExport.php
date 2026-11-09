<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        return collect($this->report['customers'])->map(fn (array $customer): array => [
            $customer['name'], $customer['email'], $customer['orders'], $customer['total'],
        ]);
    }

    public function headings(): array
    {
        return ['Cliente', 'Email', 'Pedidos', 'Total comprado'];
    }
}
