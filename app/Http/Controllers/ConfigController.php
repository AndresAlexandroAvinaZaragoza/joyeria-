<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ConfigController extends Controller
{
    public function index(Request $request): View
    {
        $roles = Rol::query()
            ->withCount('usuarios')
            ->when($request->filled('roles_search'), function ($query) use ($request): void {
                $search = $request->string('roles_search')->toString();

                $query->where(function ($roleQuery) use ($search): void {
                    $roleQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->latest('id_rol')
            ->paginate(8, ['*'], 'roles_page')
            ->withQueryString();

        $categorias = Categoria::query()
            ->when($request->filled('categorias_search'), function ($query) use ($request): void {
                $search = $request->string('categorias_search')->toString();

                $query->where(function ($categoryQuery) use ($search): void {
                    $categoryQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->latest('id_categorias')
            ->paginate(8, ['*'], 'categorias_page')
            ->withQueryString();

        return view('admin.user.config', compact('roles', 'categorias'));
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', 'unique:rol,nombre'],
            'descripcion' => ['nullable', 'string'],
        ]);

        Rol::create($validated);

        return to_route('config.index')->with('status', 'Rol creado correctamente.');
    }

    public function updateRole(Request $request, Rol $rol): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', Rule::unique('rol', 'nombre')->ignore($rol->id_rol, 'id_rol')],
            'descripcion' => ['nullable', 'string'],
        ]);

        $rol->update($validated);

        return to_route('config.index')->with('status', 'Rol actualizado correctamente.');
    }

    public function destroyRole(Rol $rol): RedirectResponse
    {
        try {
            $rol->delete();
        } catch (QueryException) {
            return to_route('config.index')->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        return to_route('config.index')->with('status', 'Rol eliminado correctamente.');
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('nombre')),
        ]);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', 'unique:categorias,slug'],
            'descripcion' => ['nullable', 'string'],
        ]);

        $validated['status'] = $request->boolean('status');
        Categoria::create($validated);

        return to_route('config.index')->with('status', 'Categoría creada correctamente.');
    }

    public function updateCategory(Request $request, Categoria $categoria): RedirectResponse
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('nombre')),
        ]);

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categorias', 'slug')->ignore($categoria->id_categorias, 'id_categorias')],
            'descripcion' => ['nullable', 'string'],
        ]);

        $validated['status'] = $request->boolean('status');
        $categoria->update($validated);

        return to_route('config.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroyCategory(Categoria $categoria): RedirectResponse
    {
        try {
            $categoria->delete();
        } catch (QueryException) {
            return to_route('config.index')->with('error', 'No puedes eliminar una categoría que tiene productos asignados.');
        }

        return to_route('config.index')->with('status', 'Categoría eliminada correctamente.');
    }
}
