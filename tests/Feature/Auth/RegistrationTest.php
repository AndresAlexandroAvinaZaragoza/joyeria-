<?php

namespace Tests\Feature\Auth;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::forceCreate([
            'id_rol' => 4,
            'nombre' => 'Cliente',
            'descripcion' => 'Cliente registrado',
        ]);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Notification::fake();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'apellidos' => 'Test Apellidos',
            'telefono' => '5555555555',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertAuthenticated();
        $this->assertFalse($user->hasVerifiedEmail());
        Notification::assertSentTo($user, VerifyEmail::class);
        $response->assertRedirect(route('verification.notice', absolute: false));
    }

    public function test_name_must_contain_only_letters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test123',
            'apellidos' => 'Apellidos',
            'telefono' => '5555555555',
            'email' => 'letters@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_surnames_must_contain_only_letters(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test',
            'apellidos' => 'Apellidos2',
            'telefono' => '5555555555',
            'email' => 'surnames@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('apellidos');
    }

    public function test_phone_must_contain_only_digits(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test',
            'apellidos' => 'Apellidos',
            'telefono' => '55 5555 ABC',
            'email' => 'phone@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors('telefono');
    }

    public function test_registration_fields_respect_database_lengths(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('A', 256),
            'apellidos' => str_repeat('A', 101),
            'telefono' => str_repeat('1', 21),
            'email' => str_repeat('a', 256).'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['name', 'apellidos', 'telefono', 'email']);
    }
}
