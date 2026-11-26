<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk()
            ->assertSee('Mi perfil')
            ->assertSee('Información del perfil')
            ->assertSee('Actualizar contraseña')
            ->assertSee('Eliminar cuenta')
            ->assertDontSee('Profile Information')
            ->assertDontSee('Update Password');
    }

    public function test_profile_validation_errors_are_displayed_in_spanish(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from('/profile')
            ->followingRedirects()
            ->patch('/profile', [
                'name' => '',
                'email' => 'invalid-email',
            ])
            ->assertOk()
            ->assertSeeText('El campo nombre es obligatorio.')
            ->assertSeeText('El campo correo electrónico debe ser una dirección de correo electrónico válida.');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->followingRedirects()
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertOk()
            ->assertSeeText('La contraseña ingresada es incorrecta.');

        $this->assertNotNull($user->fresh());
    }

    public function test_user_with_orders_cannot_delete_their_account(): void
    {
        $user = User::factory()->create();

        DB::table('pedidos')->insert([
            'numero_pedido' => 'PED-TEST-1',
            'subtotal' => 100,
            'total' => 100,
            'user_id' => $user->getKey(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->from('/profile')
            ->followingRedirects()
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertOk()
            ->assertSeeText('No puedes eliminar tu cuenta porque tienes pedidos asociados.');

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh());
    }
}
