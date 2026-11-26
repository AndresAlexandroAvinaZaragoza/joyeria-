<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_is_redirected_to_store_from_staff_pages(): void
    {
        $role = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Cliente registrado',
        ]);
        $user = User::factory()->create(['rol_id' => $role->id_rol]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk();

        foreach ([
            'dashboard',
            'inventario.index',
            'imagenes.index',
            'admin.kardex.index',
            'productos.index',
            'usuarios.index',
            'admin.orders.index',
            'config.index',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertRedirect(route('home'));
        }
    }

    public function test_employee_can_access_staff_dashboard_and_inventory(): void
    {
        $role = Rol::create([
            'nombre' => 'Empleado',
            'descripcion' => 'Empleado del sistema',
        ]);
        $user = User::factory()->create(['rol_id' => $role->id_rol]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();

        $this->get(route('inventario.index'))
            ->assertOk();
    }

    public function test_cliente_cannot_access_inventory_or_change_products_through_api(): void
    {
        $role = Rol::create([
            'nombre' => 'Cliente',
            'descripcion' => 'Cliente registrado',
        ]);
        $user = User::factory()->create(['rol_id' => $role->id_rol]);

        Sanctum::actingAs($user);

        $this->getJson('/api/inventario')->assertForbidden();
        $this->postJson('/api/productos')->assertForbidden();
        $this->postJson('/api/productos/1/imagenes')->assertForbidden();
    }
}
