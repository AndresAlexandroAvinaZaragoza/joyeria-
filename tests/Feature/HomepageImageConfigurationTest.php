<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageImageConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_the_default_background_image(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('/images/img-fondo-inicio.jpeg');
    }

    public function test_configuration_page_shows_background_image_management(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('config.index'));

        $response->assertOk()
            ->assertSee('Imágenes de inicio')
            ->assertSee('SUBIR Y APLICAR')
            ->assertSee('/images/img-fondo-inicio.jpeg');
    }

    public function test_admin_can_upload_a_background_image_under_1_8_mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('config.homepage-images.store'), [
            'image' => UploadedFile::fake()->image('inicio.jpg')->size(1800),
        ]);

        $response->assertSessionHasNoErrors()
            ->assertRedirect(route('config.index'));

        $image = DB::table('homepage_images')->where('is_active', true)->first();

        $this->assertNotNull($image);
        $this->assertSame(1, DB::table('homepage_images')->where('is_active', true)->count());
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_admin_can_select_an_existing_background_image(): void
    {
        $user = User::factory()->create();
        $existingImage = DB::table('homepage_images')->insertGetId([
            'path' => 'fondos-inicio/otra.jpg',
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->patch(route('config.homepage-images.activate', $existingImage));

        $response->assertRedirect(route('config.index'));
        $this->assertDatabaseHas('homepage_images', ['id' => $existingImage, 'is_active' => true]);
        $this->assertDatabaseHas('homepage_images', ['path' => 'images/img-fondo-inicio.jpeg', 'is_active' => false]);
        $this->assertSame(1, DB::table('homepage_images')->where('is_active', true)->count());
    }

    public function test_admin_cannot_upload_a_background_image_over_1_8_mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('config.index'))->post(route('config.homepage-images.store'), [
            'image' => UploadedFile::fake()->image('inicio.jpg')->size(1900),
        ]);

        $response->assertSessionHasErrors('image');
        $this->assertSame(1, DB::table('homepage_images')->where('is_active', true)->count());
    }

    public function test_admin_cannot_upload_more_than_four_homepage_images(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        foreach (range(1, 3) as $number) {
            DB::table('homepage_images')->insert([
                'path' => "fondos-inicio/imagen-{$number}.jpg",
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($user)->from(route('config.index'))->post(route('config.homepage-images.store'), [
            'image' => UploadedFile::fake()->image('inicio.jpg')->size(1000),
        ]);

        $response->assertRedirect(route('config.index'))
            ->assertSessionHas('error', 'Solo puedes guardar hasta 4 imágenes de inicio. Elimina una para subir otra.');
        $this->assertSame(4, DB::table('homepage_images')->count());

        $this->actingAs($user)->get(route('config.index'))
            ->assertOk()
            ->assertSee('GALERÍA COMPLETA')
            ->assertSee('4 de 4 imágenes');
    }

    public function test_admin_can_delete_an_inactive_uploaded_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $path = UploadedFile::fake()->image('opcion.jpg')->store('fondos-inicio', 'public');
        $imageId = DB::table('homepage_images')->insertGetId([
            'path' => $path,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('config.homepage-images.destroy', $imageId));

        $response->assertRedirect(route('config.index'));
        $this->assertDatabaseMissing('homepage_images', ['id' => $imageId]);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseHas('homepage_images', ['path' => 'images/img-fondo-inicio.jpeg', 'is_active' => true]);
    }

    public function test_deleting_the_active_image_activates_another_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        DB::table('homepage_images')->update(['is_active' => false]);
        $path = UploadedFile::fake()->image('actual.jpg')->store('fondos-inicio', 'public');
        $activeImageId = DB::table('homepage_images')->insertGetId([
            'path' => $path,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->delete(route('config.homepage-images.destroy', $activeImageId));

        $response->assertRedirect(route('config.index'));
        $this->assertDatabaseHas('homepage_images', ['path' => 'images/img-fondo-inicio.jpeg', 'is_active' => true]);
        $this->assertSame(1, DB::table('homepage_images')->where('is_active', true)->count());
        Storage::disk('public')->assertMissing($path);
    }

    public function test_admin_cannot_delete_the_only_homepage_image(): void
    {
        $user = User::factory()->create();
        $imageId = DB::table('homepage_images')->where('is_active', true)->value('id');

        $response = $this->actingAs($user)->from(route('config.index'))->delete(route('config.homepage-images.destroy', $imageId));

        $response->assertRedirect(route('config.index'))
            ->assertSessionHas('error', 'Debes conservar al menos una imagen de inicio.');
        $this->assertDatabaseHas('homepage_images', ['id' => $imageId, 'is_active' => true]);
    }
}
