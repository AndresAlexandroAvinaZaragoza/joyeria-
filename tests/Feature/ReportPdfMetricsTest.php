<?php

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Tests\TestCase;

class ReportPdfMetricsTest extends TestCase
{
    public function test_report_pdf_templates_render_all_summary_metrics(): void
    {
        $sales = view('admin.reportes.exports.ventas', [
            'report' => [
                'from' => CarbonImmutable::parse('2026-11-01'),
                'to' => CarbonImmutable::parse('2026-11-26'),
                'total_revenue' => 180,
                'sales_count' => 2,
                'top_products' => [],
            ],
        ])->render();

        $inventory = view('admin.reportes.exports.inventario', [
            'report' => [
                'total_products' => 5,
                'total_units' => 20,
                'total_value' => 1200,
                'low_stock_count' => 3,
                'out_of_stock_count' => 1,
                'items' => [],
            ],
        ])->render();

        $orders = view('admin.reportes.exports.pedidos', [
            'report' => [
                'pending' => 2,
                'completed' => 7,
                'cancelled' => 1,
                'customers' => [],
            ],
        ])->render();

        $customers = view('admin.reportes.exports.clientes', [
            'report' => [
                'registered' => 12,
                'frequent' => 4,
                'top_customers' => array_fill(0, 10, []),
                'history' => [],
            ],
        ])->render();

        foreach (['Ingresos totales', 'Ventas realizadas', 'Ticket promedio', '$90.00 MXN'] as $metric) {
            $this->assertStringContainsString($metric, $sales);
        }

        foreach (['Productos', 'Existencias', 'Valor total', 'Stock mínimo', 'Agotados'] as $metric) {
            $this->assertStringContainsString($metric, $inventory);
        }

        foreach (['Pedidos pendientes', 'Pedidos completados', 'Pedidos cancelados'] as $metric) {
            $this->assertStringContainsString($metric, $orders);
        }

        foreach (['Clientes registrados', 'Clientes frecuentes', 'Con mayores compras'] as $metric) {
            $this->assertStringContainsString($metric, $customers);
        }
    }
}
