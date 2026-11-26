<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductDeletionTest extends TestCase
{
    use RefreshDatabase;

    private const DELETE_BLOCKED_MESSAGE = 'No se puede eliminar el producto porque está asociado a un carrito, pedido, compra o movimiento de inventario. Puedes desactivarlo en su lugar.';

    public function test_product_with_cart_items_cannot_be_deleted_from_admin(): void
    {
        $user = $this->createEmployee();
        $productId = $this->createProduct();
        $this->createCartItem($productId);

        $this->actingAs($user)
            ->delete(route('productos.destroy', $productId))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('error', self::DELETE_BLOCKED_MESSAGE);

        $this->get(route('productos.index'))
            ->assertOk()
            ->assertSeeText(self::DELETE_BLOCKED_MESSAGE);

        $this->assertDatabaseHas('productos', ['id_productos' => $productId]);
    }

    public function test_product_with_order_details_cannot_be_deleted_from_admin(): void
    {
        $user = $this->createEmployee();
        $productId = $this->createProduct();
        DB::table('inventario')->where('producto_id', $productId)->update(['stock_actual' => 1]);
        $orderId = DB::table('pedidos')->insertGetId([
            'numero_pedido' => 'PED-'.uniqid(),
            'subtotal' => 20,
            'total' => 20,
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_pedidos');

        DB::table('pedido_detalles')->insert([
            'precio_unitario' => 20,
            'cantidad' => 1,
            'importe' => 20,
            'pedido_id' => $orderId,
            'producto_id' => $productId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->delete(route('productos.destroy', $productId))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('error', self::DELETE_BLOCKED_MESSAGE);

        $this->assertDatabaseHas('productos', ['id_productos' => $productId]);
    }

    public function test_product_without_transactional_references_can_be_deleted(): void
    {
        $user = $this->createEmployee();
        $productId = $this->createProduct();

        $this->actingAs($user)
            ->delete(route('productos.destroy', $productId))
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('status', 'Producto eliminado correctamente.');

        $this->assertDatabaseMissing('productos', ['id_productos' => $productId]);
    }

    public function test_api_returns_conflict_when_product_has_cart_items(): void
    {
        $user = $this->createEmployee();
        $productId = $this->createProduct();
        $this->createCartItem($productId);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/productos/'.$productId)
            ->assertConflict()
            ->assertExactJson(['message' => self::DELETE_BLOCKED_MESSAGE]);

        $this->assertDatabaseHas('productos', ['id_productos' => $productId]);
    }

    private function createProduct(): int
    {
        $categoryId = DB::table('categorias')->insertGetId([
            'nombre' => 'Anillos',
            'slug' => 'anillos',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_categorias');

        return DB::table('productos')->insertGetId([
            'sku' => 'SKU-'.uniqid(),
            'nombre' => 'Anillo',
            'precio_costo' => 10,
            'precio_venta' => 20,
            'status' => true,
            'id_categoria' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_productos');
    }

    private function createCartItem(int $productId): void
    {
        $cartId = DB::table('carrito')->insertGetId([
            'session_token' => 'test-session',
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_carrito');

        DB::table('carrito_items')->insert([
            'cantidad' => 1,
            'producto_id' => $productId,
            'carrito_id' => $cartId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createEmployee(): User
    {
        $role = Rol::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Empleado del sistema',
        ]);

        return User::factory()->create(['rol_id' => $role->id_rol]);
    }
}
