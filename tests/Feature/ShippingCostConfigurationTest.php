<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\PayPalService;
use App\Services\PedidoEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Mockery\MockInterface;
use Tests\TestCase;

class ShippingCostConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_the_shipping_cost(): void
    {
        $user = $this->createAdministrator();

        $response = $this->actingAs($user)->put(route('config.shipping-cost.update'), [
            'shipping_cost' => '45.50',
        ]);

        $response->assertRedirect(route('config.index'))
            ->assertSessionHas('status', 'Costo de envío actualizado correctamente.');
        $this->assertSame('45.50', StoreSetting::query()->findOrFail(1)->shipping_cost);

        $this->actingAs($user)->get(route('config.index'))
            ->assertOk()
            ->assertSee('id="shipping-cost"', false)
            ->assertSee('value="45.50"', false);
    }

    public function test_shipping_cost_must_be_nonnegative_and_have_at_most_two_decimal_places(): void
    {
        $user = $this->createAdministrator();

        $this->actingAs($user)
            ->from(route('config.index'))
            ->put(route('config.shipping-cost.update'), ['shipping_cost' => '-1.25'])
            ->assertSessionHasErrors('shipping_cost');

        $this->assertSame('0.00', StoreSetting::query()->findOrFail(1)->shipping_cost);
    }

    public function test_checkout_displays_the_configured_shipping_cost_and_total(): void
    {
        $user = User::factory()->create();
        $this->createCart($user);
        DB::table('store_settings')->where('id', 1)->update(['shipping_cost' => 10]);

        $response = $this->actingAs($user)->get(route('payments.create'));

        $response->assertOk()
            ->assertSee('$50.00')
            ->assertSee('$10.00')
            ->assertSee('$60.00');
    }

    public function test_paypal_order_includes_configured_shipping_cost(): void
    {
        $user = User::factory()->create();
        $addressId = $this->createCart($user);
        DB::table('store_settings')->where('id', 1)->update(['shipping_cost' => 10]);

        $this->mock(PayPalService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createOrder')
                ->once()
                ->with('60.00', config('services.paypal.currency'))
                ->andReturn(['id' => 'paypal-order']);
        });

        $response = $this->actingAs($user)->postJson(route('payments.paypal.create'), [
            'direccion_id' => $addressId,
        ]);

        $response->assertOk()->assertExactJson(['id' => 'paypal-order']);
        $this->assertSame('60.00', session('paypal.pending_order.amount'));
        $this->assertSame('10.00', session('paypal.pending_order.shipping_cost'));
    }

    public function test_confirmed_payment_saves_shipping_cost_in_the_order_and_payment(): void
    {
        $user = User::factory()->create();
        $addressId = $this->createCart($user);
        $productId = DB::table('productos')->where('sku', 'SKU-'.$user->getKey())->value('id_productos');
        DB::table('inventario')->where('producto_id', $productId)->update(['stock_actual' => 10]);
        $orderId = 'paypal-order';
        $this->withSession([
            'paypal.pending_order' => [
                'order_id' => $orderId,
                'direccion_id' => $addressId,
                'amount' => '60.00',
                'shipping_cost' => '10.00',
            ],
        ]);
        $this->mock(PayPalService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('captureOrder')
                ->once()
                ->with('paypal-order')
                ->andReturn([
                    'purchase_units' => [[
                        'payments' => [
                            'captures' => [[
                                'status' => 'COMPLETED',
                                'id' => 'capture-id',
                                'amount' => [
                                    'value' => '60.00',
                                    'currency_code' => config('services.paypal.currency'),
                                ],
                            ]],
                        ],
                    ]],
                ]);
        });
        $this->mock(PedidoEmailService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('sendStatusUpdate')->once();
        });

        $response = $this->actingAs($user)->postJson(route('payments.paypal.capture'), [
            'order_id' => $orderId,
        ]);

        $response->assertOk()->assertJsonPath('redirect', route('orders.index'));
        $this->assertDatabaseHas('pedidos', [
            'subtotal' => '50.00',
            'costo_envio' => '10.00',
            'total' => '60.00',
        ]);
        $this->assertDatabaseHas('pagos', [
            'monto' => '60.00',
            'estado' => 'completado',
        ]);
    }

    private function createCart(User $user): int
    {
        $now = now();
        $categoryId = DB::table('categorias')->insertGetId([
            'nombre' => 'Anillos',
            'slug' => 'anillos-test-'.$user->getKey(),
            'status' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $productId = DB::table('productos')->insertGetId([
            'sku' => 'SKU-'.$user->getKey(),
            'nombre' => 'Anillo de prueba',
            'precio_costo' => 10,
            'precio_venta' => 25,
            'status' => true,
            'id_categoria' => $categoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $cartId = DB::table('carrito')->insertGetId([
            'user_id' => $user->getKey(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('carrito_items')->insert([
            'cantidad' => 2,
            'producto_id' => $productId,
            'carrito_id' => $cartId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return DB::table('direccion_clientes')->insertGetId([
            'calle_numero' => 'Calle 1',
            'colonia' => 'Centro',
            'ciudad' => 'Ciudad',
            'estado' => 'Estado',
            'cp' => '12345',
            'user_id' => $user->getKey(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createAdministrator(): User
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Administradores del sistema',
        ]);

        return User::factory()->create(['rol_id' => $role->id_rol]);
    }
}
