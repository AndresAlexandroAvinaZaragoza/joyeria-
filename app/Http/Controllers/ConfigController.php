<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Rol;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
            ->when($request->input('roles_users') === 'with_users', function ($query): void {
                $query->has('usuarios');
            })
            ->when($request->input('roles_users') === 'without_users', function ($query): void {
                $query->doesntHave('usuarios');
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
            ->when($request->filled('categorias_status'), function ($query) use ($request): void {
                $query->where('status', $request->input('categorias_status') === 'active');
            })
            ->latest('id_categorias')
            ->paginate(8, ['*'], 'categorias_page')
            ->withQueryString();

        $homepageImages = DB::table('homepage_images')
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get()
            ->map(function ($image) {
                $image->url = $this->homepageImageUrl($image->path);

                return $image;
            });

        return view('admin.user.config', compact('roles', 'categorias', 'homepageImages'));
    }

    public function storeHomepageImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1843'],
        ], [
            'image.uploaded' => 'El servidor rechazó la imagen. Reduce su tamaño a menos de 1.8 MB e inténtalo de nuevo.',
            'image.max' => 'La imagen no debe superar los 1.8 MB.',
        ]);

        $path = $validated['image']->store('fondos-inicio', 'public');

        if ($path === false) {
            return to_route('config.index')->with('error', 'No se pudo guardar la imagen. Inténtalo de nuevo.');
        }

        DB::transaction(function () use ($path): void {
            DB::table('homepage_images')->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('homepage_images')->insert([
                'path' => $path,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return to_route('config.index')->with('status', 'Imagen subida y aplicada al inicio correctamente.');
    }

    public function activateHomepageImage(int $homepageImage): RedirectResponse
    {
        DB::table('homepage_images')->where('id', $homepageImage)->firstOrFail();

        DB::transaction(function () use ($homepageImage): void {
            DB::table('homepage_images')->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('homepage_images')->where('id', $homepageImage)->update([
                'is_active' => true,
                'updated_at' => now(),
            ]);
        });

        return to_route('config.index')->with('status', 'Imagen de inicio actualizada correctamente.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:rol,nombre'],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

        Rol::create($validated);

        return to_route('config.index')->with('status', 'Rol creado correctamente.');
    }

    public function updateRole(Request $request, Rol $rol): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', Rule::unique('rol', 'nombre')->ignore($rol->id_rol, 'id_rol')],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

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
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'slug' => ['required', 'string', 'max:255', 'unique:categorias,slug'],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

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
            'nombre' => ['required', 'string', 'max:100', 'regex:/^[\pL\s]+$/u'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categorias', 'slug')->ignore($categoria->id_categorias, 'id_categorias')],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

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

    private function homepageImageUrl(string $path): string
    {
        return str_starts_with($path, 'images/')
            ? asset($path)
            : Storage::disk('public')->url($path);
    }
}
