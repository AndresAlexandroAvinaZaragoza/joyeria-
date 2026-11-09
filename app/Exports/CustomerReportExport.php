<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustomerReportExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly array $report) {}

    public function collection(): Collection
    {
        return collect($this->report['history'])->map(fn (array $customer): array => [
            $customer['name'], $customer['email'], $customer['orders'], $customer['total'],
            $customer['registered_at']?->format('d/m/Y'),
        ]);
    }

    public function headings(): array
    {
        return ['Cliente', 'Email', 'Pedidos', 'Total comprado', 'Registro'];
    }
}
