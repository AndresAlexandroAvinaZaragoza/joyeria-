<?php

namespace Tests\Feature;

use App\Models\Envio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ShipmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipments_can_be_filtered_by_status_and_missing_shipping_details(): void
    {
        $staff = $this->createStaffUser();
        $pendingId = $this->createShipment('pendiente', 'DHL', 'PEND-1');
        $toShipId = $this->createShipment('preparando', null, null);
        $inTransitId = $this->createShipment('en_transito', 'FedEx', 'TRANSIT-1');
        $deliveredId = $this->createShipment('entregado', 'Estafeta', 'DELIVERED-1', 'entregado');
        $shippedId = $this->createShipment('enviado', 'DHL', 'SENT-1');

        $this->actingAs($staff)
            ->get(route('admin.shipments.index', ['status' => 'pendiente']))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 1
                && $envios->first()->getKey() === $pendingId);

        $this->actingAs($staff)
            ->get(route('admin.shipments.index', ['status' => 'por_enviar']))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 1
                && $envios->first()->getKey() === $toShipId);

        $this->actingAs($staff)
            ->get(route('admin.shipments.index', ['status' => 'en_transito']))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 1
                && $envios->first()->getKey() === $inTransitId);

        $this->actingAs($staff)
            ->get(route('admin.shipments.index', ['status' => 'entregado']))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 1
                && $envios->first()->getKey() === $deliveredId);

        $this->actingAs($staff)
            ->get(route('admin.shipments.index', ['status' => 'enviado']))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 1
                && $envios->first()->getKey() === $shippedId);

        $this->actingAs($staff)
            ->get(route('admin.shipments.index'))
            ->assertOk()
            ->assertViewHas('envios', fn ($envios) => $envios->total() === 4);
    }

    public function test_delivered_shipments_cannot_be_updated_from_web(): void
    {
        $staff = $this->createStaffUser();
        $shipmentId = $this->createShipment('entregado', 'Estafeta', 'DELIVERED-2', 'entregado');

        $this->actingAs($staff)
            ->patch(route('admin.shipments.update', $shipmentId), [
                'estado' => 'en_transito',
                'paqueteria' => 'DHL',
                'numero_guia' => 'NEW-GUIDE',
            ])
            ->assertStatus(422);

        $this->assertDatabaseHas('envios', [
            'id_envios' => $shipmentId,
            'estado' => 'entregado',
            'paqueteria' => 'Estafeta',
            'numero_guia' => 'DELIVERED-2',
        ]);
    }

    private function createStaffUser(): User
    {
        $roleId = DB::table('rol')->insertGetId([
            'nombre' => 'empleado',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_rol');

        return User::factory()->create(['rol_id' => $roleId]);
    }

    private function createShipment(string $status, ?string $carrier, ?string $trackingNumber, string $orderStatus = 'pendiente'): int
    {
        $customer = User::factory()->create();
        $orderId = DB::table('pedidos')->insertGetId([
            'numero_pedido' => 'PED-'.uniqid(),
            'subtotal' => 100,
            'total' => 100,
            'estado' => $orderStatus,
            'user_id' => $customer->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_pedidos');

        return Envio::query()->create([
            'direccion_destino' => 'Dirección de prueba',
            'estado' => $status,
            'paqueteria' => $carrier,
            'numero_guia' => $trackingNumber,
            'pedido_id' => $orderId,
        ])->getKey();
    }
}
