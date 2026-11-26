<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_role_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $cliente = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Clientes del sistema',
        ]);

        $this->actingAs($user)
            ->delete(route('config.roles.destroy', $cliente))
            ->assertRedirect(route('config.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('rol', ['id_rol' => $cliente->id_rol]);
    }

    public function test_administrador_role_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $administrador = Rol::create([
            'nombre' => 'ADMINISTRADOR',
            'descripcion' => 'Administradores del sistema',
        ]);

        $this->actingAs($user)
            ->delete(route('config.roles.destroy', $administrador))
            ->assertRedirect(route('config.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('rol', ['id_rol' => $administrador->id_rol]);
    }

    public function test_protected_role_can_be_modified(): void
    {
        $user = User::factory()->create();
        $cliente = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Descripción anterior',
        ]);

        $this->actingAs($user)
            ->put(route('config.roles.update', $cliente), [
                'nombre' => 'Cliente',
                'descripcion' => 'Descripción modificada',
            ])
            ->assertRedirect(route('config.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('rol', [
            'id_rol' => $cliente->id_rol,
            'descripcion' => 'Descripción modificada',
            'nombre' => 'Cliente',
        ]);
    }

    public function test_protected_role_name_cannot_be_changed_via_server(): void
    {
        $user = User::factory()->create();
        $cliente = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Descripción anterior',
        ]);

        $this->actingAs($user)
            ->put(route('config.roles.update', $cliente), [
                'nombre' => 'Comprador',
                'descripcion' => 'Descripción modificada',
            ])
            ->assertRedirect(route('config.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('rol', [
            'id_rol' => $cliente->id_rol,
            'nombre' => 'Cliente',
            'descripcion' => 'Descripción modificada',
        ]);
    }

    public function test_administrator_user_cannot_be_deleted(): void
    {
        $adminRole = Rol::create([
            'nombre' => 'ADMINISTRADOR',
            'descripcion' => 'Administradores del sistema',
        ]);
        $actingUser = User::factory()->create(['rol_id' => $adminRole->id_rol]);
        $administrator = User::factory()->create(['rol_id' => $adminRole->id_rol]);

        $this->actingAs($actingUser)
            ->delete(route('usuarios.destroy', $administrator))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
    }

    public function test_regular_user_can_still_be_deleted(): void
    {
        $employeeRole = Rol::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Empleado del sistema',
        ]);
        $actingUser = User::factory()->create(['rol_id' => $employeeRole->id_rol]);
        $employee = User::factory()->create(['rol_id' => $employeeRole->id_rol]);

        $this->actingAs($actingUser)
            ->delete(route('usuarios.destroy', $employee))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_regular_role_can_still_be_deleted(): void
    {
        $user = User::factory()->create();
        $empleado = Rol::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Empleado del sistema',
        ]);

        $this->actingAs($user)
            ->delete(route('config.roles.destroy', $empleado))
            ->assertRedirect(route('config.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('rol', ['id_rol' => $empleado->id_rol]);
    }
}
