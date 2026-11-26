<?php

namespace Tests\Feature;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ImageFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_can_be_filtered_by_photo_count(): void
    {
        $user = User::factory()->create();
        $categoryId = $this->createCategory();

        for ($photoCount = 0; $photoCount <= 6; $photoCount++) {
            $this->createProductWithPhotos($categoryId, $photoCount, $photoCount);
        }

        $this->actingAs($user)
            ->get(route('imagenes.index', ['photo_count' => '0']))
            ->assertOk()
            ->assertViewHas('productosConImagenes', fn ($products) => $products->total() === 1
                && $products->first()->nombre === 'Producto 0');

        foreach ([1, 2, 3, 4] as $photoCount) {
            $this->actingAs($user)
                ->get(route('imagenes.index', ['photo_count' => (string) $photoCount]))
                ->assertOk()
                ->assertViewHas('productosConImagenes', fn ($products) => $products->total() === 1
                    && $products->first()->nombre === "Producto {$photoCount}");
        }

        $this->actingAs($user)
            ->get(route('imagenes.index', ['photo_count' => 'more_than_5']))
            ->assertOk()
            ->assertViewHas('productosConImagenes', fn ($products) => $products->total() === 2
                && $products->pluck('nombre')->all() === ['Producto 5', 'Producto 6']);
    }

    public function test_products_can_be_sorted_by_recently_added(): void
    {
        $user = User::factory()->create();
        $categoryId = $this->createCategory();
        $this->createProductWithPhotos($categoryId, 1, 0, now()->subDay());
        $this->createProductWithPhotos($categoryId, 2, 0, now());

        $this->actingAs($user)
            ->get(route('imagenes.index', ['sort' => 'recent']))
            ->assertOk()
            ->assertViewHas('productosConImagenes', fn ($products) => $products->first()->nombre === 'Producto 2'
                && $products->last()->nombre === 'Producto 1');
    }

    private function createCategory(): int
    {
        return DB::table('categorias')->insertGetId([
            'nombre' => 'Anillos',
            'slug' => 'anillos',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'id_categorias');
    }

    private function createProductWithPhotos(int $categoryId, int $productNumber, int $photoCount, ?DateTimeInterface $createdAt = null): void
    {
        $createdAt ??= now();
        $productId = DB::table('productos')->insertGetId([
            'sku' => 'SKU-'.$productNumber,
            'nombre' => 'Producto '.$productNumber,
            'precio_costo' => 10,
            'precio_venta' => 20,
            'status' => true,
            'id_categoria' => $categoryId,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ], 'id_productos');

        for ($photo = 0; $photo < $photoCount; $photo++) {
            DB::table('producto_img')->insert([
                'url_img' => "productos/{$productNumber}-{$photo}.jpg",
                'producto_id' => $productId,
                'created_at' => now(),
            ]);
        }
    }
}
