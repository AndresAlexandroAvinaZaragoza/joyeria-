<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use App\Services\CustomerReportService;
use App\Services\InventoryReportService;
use App\Services\OrderReportService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_report_paginates_items_and_keeps_filters_in_links(): void
    {
        $this->createStaffUser();
        $items = array_map(fn (int $number): array => [
            'name' => "Inventario {$number}",
            'sku' => "SKU-{$number}",
            'category' => 'Anillos',
            'stock' => 3,
            'minimum' => 2,
            'unit_cost' => 10,
            'value' => 30,
            'status' => 'normal',
        ], range(1, 11));

        $this->mock(InventoryReportService::class)
            ->shouldReceive('generate')
            ->once()
            ->andReturn([
                'total_products' => 11,
                'total_units' => 33,
                'total_value' => 330,
                'low_stock_count' => 0,
                'out_of_stock_count' => 0,
                'items' => $items,
            ]);

        $response = $this->get(route('admin.reports.inventory', [
            'inventory_page' => 2,
            'status' => 'minimos',
        ]));

        $response->assertOk()
            ->assertSeeText('Inventario 11')
            ->assertViewHas('report', function (array $report): bool {
                return $report['items']->currentPage() === 2
                    && $report['items']->total() === 11
                    && $report['items']->getCollection()->first()['name'] === 'Inventario 11'
                    && str_contains($report['items']->url(1), 'status=minimos');
            });
    }

    public function test_orders_report_paginates_customer_rows(): void
    {
        $this->createStaffUser();
        $customers = array_map(fn (int $number): array => [
            'name' => "Cliente {$number}",
            'email' => "cliente{$number}@example.com",
            'orders' => 1,
            'total' => 20,
            'last_order' => CarbonImmutable::now(),
        ], range(1, 11));

        $this->mock(OrderReportService::class)
            ->shouldReceive('generate')
            ->once()
            ->andReturn([
                'total' => 11,
                'pending' => 0,
                'completed' => 11,
                'cancelled' => 0,
                'customers' => $customers,
            ]);

        $this->get(route('admin.reports.orders', ['orders_page' => 2]))
            ->assertOk()
            ->assertSeeText('Cliente 11')
            ->assertViewHas('report', fn (array $report): bool => $report['customers']->currentPage() === 2
                && $report['customers']->total() === 11
                && $report['customers']->getCollection()->first()['name'] === 'Cliente 11');
    }

    public function test_customer_report_paginates_purchase_history(): void
    {
        $this->createStaffUser();
        $customers = array_map(fn (int $number): array => [
            'name' => "Cliente {$number}",
            'email' => "cliente{$number}@example.com",
            'orders' => 1,
            'total' => 20,
            'registered_at' => CarbonImmutable::now(),
            'last_order' => CarbonImmutable::now(),
        ], range(1, 11));

        $this->mock(CustomerReportService::class)
            ->shouldReceive('generate')
            ->once()
            ->andReturn([
                'registered' => 11,
                'frequent' => 0,
                'top_customers' => $customers,
                'history' => $customers,
            ]);

        $this->get(route('admin.reports.customers', ['customer_history_page' => 2]))
            ->assertOk()
            ->assertSeeText('Cliente 11')
            ->assertViewHas('report', fn (array $report): bool => is_array($report['top_customers'])
                && $report['history']->currentPage() === 2
                && $report['history']->total() === 11
                && $report['history']->getCollection()->first()['name'] === 'Cliente 11');
    }

    private function createStaffUser(): User
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Administradores del sistema',
        ]);
        $user = User::factory()->create(['rol_id' => $role->id_rol]);

        $this->actingAs($user);

        return $user;
    }
}
