<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class InventarioFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_can_be_filtered_by_stock_minimum_no_stock_or_all(): void
    {
        $user = User::factory()->create();
        $categoryId = DB::table('categorias')->insertGetId([
            'nombre' => 'Anillos',
            'slug' => 'anillos',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_categorias');

        $this->createInventoryProduct($categoryId, 'SKU-LOW', 'Stock mínimo', 2, 2);
        $this->createInventoryProduct($categoryId, 'SKU-EMPTY', 'Sin stock', 0, 2);
        $this->createInventoryProduct($categoryId, 'SKU-AVAILABLE', 'Stock disponible', 5, 2);

        $this->actingAs($user)
            ->get(route('inventario.index', ['stock' => 'stock_minimo']))
            ->assertOk()
            ->assertSeeText('SKU-LOW')
            ->assertDontSeeText('SKU-EMPTY')
            ->assertDontSeeText('SKU-AVAILABLE');

        $this->actingAs($user)
            ->get(route('inventario.index', ['stock' => 'sin_stock']))
            ->assertOk()
            ->assertSeeText('SKU-EMPTY')
            ->assertDontSeeText('SKU-LOW')
            ->assertDontSeeText('SKU-AVAILABLE');

        $this->actingAs($user)
            ->get(route('inventario.index'))
            ->assertOk()
            ->assertSeeText('SKU-LOW')
            ->assertSeeText('SKU-EMPTY')
            ->assertSeeText('SKU-AVAILABLE');
    }

    private function createInventoryProduct(int $categoryId, string $sku, string $name, int $stock, int $minimum): void
    {
        $productId = DB::table('productos')->insertGetId([
            'sku' => $sku,
            'nombre' => $name,
            'precio_costo' => 10,
            'precio_venta' => 20,
            'status' => true,
            'id_categoria' => $categoryId,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_productos');

        DB::table('inventario')->where('producto_id', $productId)->update([
            'stock_actual' => $stock,
            'stock_minimo' => $minimum,
        ]);
    }
}
