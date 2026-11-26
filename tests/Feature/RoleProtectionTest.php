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
        $user = $this->createAdministrator();
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
        $user = $this->createAdministrator();
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
        $user = $this->createAdministrator();
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
        $user = $this->createAdministrator();
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

    public function test_administrator_cannot_edit_customer_accounts(): void
    {
        $this->assertCustomerCannotBeEditedByRole('Administrador');
    }

    public function test_employee_cannot_edit_customer_accounts(): void
    {
        $this->assertCustomerCannotBeEditedByRole('Empleado');
    }

    public function test_superuser_role_cannot_be_changed_and_account_cannot_be_deleted(): void
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Administradores del sistema',
        ]);
        $actingUser = User::factory()->create(['rol_id' => $role->id_rol]);
        $superuser = User::factory()->create([
            'name' => 'Andres Alexandro Aviña Zaragoza',
            'email' => 'aavina966@gmial.com',
            'rol_id' => $role->id_rol,
        ]);
        $superuser->is_superuser = true;
        $superuser->save();
        $employeeRole = Rol::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Empleado del sistema',
        ]);

        $this->actingAs($actingUser)
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('SUPERUSUARIO')
            ->assertSee('Rol protegido de superusuario')
            ->assertSee('editIsSuperUser = true', false);

        $this->put(route('usuarios.update', $superuser), [
            'name' => 'Andres Alexandro Aviña Zaragoza',
            'email' => 'aavina966@gmial.com',
            'rol_id' => $employeeRole->id_rol,
        ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error', 'No puedes cambiar el rol de la cuenta superusuario.');

        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'rol_id' => $role->id_rol,
        ]);

        $this->delete(route('usuarios.destroy', $superuser))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error', 'No puedes eliminar la cuenta superusuario.');

        $this->assertDatabaseHas('users', ['id' => $superuser->id]);
    }

    public function test_superuser_can_edit_other_details_without_changing_role(): void
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Administradores del sistema',
        ]);
        $actingUser = User::factory()->create(['rol_id' => $role->id_rol]);
        $superuser = User::factory()->create([
            'email' => 'aavina966@gmial.com',
            'rol_id' => $role->id_rol,
        ]);
        $superuser->is_superuser = true;
        $superuser->save();

        $this->actingAs($actingUser)
            ->put(route('usuarios.update', $superuser), [
                'name' => 'Andres Actualizado',
                'email' => 'aavina966@gmial.com',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('status', 'Usuario actualizado correctamente.');

        $this->assertDatabaseHas('users', [
            'id' => $superuser->id,
            'name' => 'Andres Actualizado',
            'rol_id' => $role->id_rol,
            'is_superuser' => true,
        ]);
    }

    public function test_regular_role_can_still_be_deleted(): void
    {
        $role = Rol::create([
            'nombre' => 'Administrador',
            'descripcion' => 'Administradores del sistema',
        ]);
        $user = User::factory()->create(['rol_id' => $role->id_rol]);
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

    private function assertCustomerCannotBeEditedByRole(string $roleName): void
    {
        $staffRole = Rol::create([
            'nombre' => $roleName,
            'descripcion' => "{$roleName} del sistema",
        ]);
        $customerRole = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Clientes del sistema',
        ]);
        $staffUser = User::factory()->create(['rol_id' => $staffRole->id_rol]);
        $customer = User::factory()->create([
            'name' => 'Cliente Original',
            'email' => 'cliente@example.com',
            'rol_id' => $customerRole->id_rol,
        ]);

        $this->actingAs($staffUser)
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertDontSee(
                "editAction = '".route('usuarios.update', $customer)."'",
                false
            );

        $this->put(route('usuarios.update', $customer), [
            'name' => 'Cliente Modificado',
            'email' => 'modificado@example.com',
            'rol_id' => $staffRole->id_rol,
        ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('error', 'No puedes editar cuentas con rol de Cliente.');

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Cliente Original',
            'email' => 'cliente@example.com',
            'rol_id' => $customerRole->id_rol,
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
