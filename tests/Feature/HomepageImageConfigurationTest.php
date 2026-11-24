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
            ->assertSee('Imagen de inicio')
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
}
