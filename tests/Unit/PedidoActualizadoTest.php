<?php

namespace Tests\Unit;

use App\Mail\PedidoActualizado;
use App\Models\Pedido;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class PedidoActualizadoTest extends TestCase
{
    public function test_payment_update_email_contains_order_status_and_products(): void
    {
        $user = new User(['name' => 'Andrea']);
        $product = new Producto(['nombre' => 'Brazalete Aurora']);
        $detail = new PedidoDetalle(['cantidad' => 1]);
        $detail->setRelation('producto', $product);

        $order = new Pedido([
            'numero_pedido' => 'LS-TEST-1234',
            'total' => 1250,
        ]);
        $order->setRelation('usuario', $user);
        $order->setRelation('detalles', new Collection([$detail]));

        $mail = new PedidoActualizado($order, 'confirmado', 'pago');

        $this->assertSame('Compra confirmada: LS-TEST-1234', $mail->envelope()->subject);
        $this->assertStringContainsString('Pago confirmado', $mail->render());
        $this->assertStringContainsString('Brazalete Aurora', $mail->render());
    }
}
