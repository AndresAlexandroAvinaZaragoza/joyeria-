<?php

namespace Tests\Feature\Api;

use App\Mail\PedidoActualizado;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EnvioStatusEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_sends_an_email_when_shipment_status_changes(): void
    {
        Mail::fake();

        $staff = $this->createStaffUser();
        $pedidoId = DB::table('pedidos')->insertGetId([
            'numero_pedido' => 'PED-EMAIL-1',
            'subtotal' => 100,
            'total' => 100,
            'user_id' => User::factory()->create()->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_pedidos');
        $envioId = DB::table('envios')->insertGetId([
            'direccion_destino' => 'Dirección de prueba',
            'estado' => 'preparando',
            'paqueteria' => 'Estafeta',
            'numero_guia' => 'GUIA12345',
            'pedido_id' => $pedidoId,
            'created_at' => now(),
        ], 'id_envios');

        Sanctum::actingAs($staff);

        $this->patchJson('/api/envios/'.$envioId, ['estado' => 'enviado'])
            ->assertOk()
            ->assertJsonPath('envio.estado', 'enviado')
            ->assertJsonPath('envio.paqueteria', 'Estafeta')
            ->assertJsonPath('envio.numero_guia', 'GUIA12345');

        Mail::assertSent(PedidoActualizado::class, fn (PedidoActualizado $mail) => $mail->estado === 'enviado'
            && $mail->tipo === 'envio');
        $emailContent = Mail::sent(PedidoActualizado::class)->first()->render();
        $this->assertStringContainsString('Estafeta', $emailContent);
        $this->assertStringContainsString('GUIA12345', $emailContent);

        $this->patchJson('/api/envios/'.$envioId, ['estado' => 'enviado'])
            ->assertOk();

        Mail::assertSent(PedidoActualizado::class, 1);
    }

    public function test_api_rejects_updates_to_delivered_shipments(): void
    {
        $staff = $this->createStaffUser();
        $customer = User::factory()->create();
        $pedidoId = DB::table('pedidos')->insertGetId([
            'numero_pedido' => 'PED-EMAIL-2',
            'subtotal' => 100,
            'total' => 100,
            'estado' => 'entregado',
            'user_id' => $customer->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_pedidos');
        $envioId = DB::table('envios')->insertGetId([
            'direccion_destino' => 'Dirección de prueba',
            'estado' => 'entregado',
            'pedido_id' => $pedidoId,
            'created_at' => now(),
        ], 'id_envios');

        Sanctum::actingAs($staff);

        $this->patchJson('/api/envios/'.$envioId, ['estado' => 'en_transito'])
            ->assertUnprocessable();
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
}
