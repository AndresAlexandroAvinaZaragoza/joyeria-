<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Rol;
use App\Models\StoreSetting;
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
    private const MAX_HOMEPAGE_IMAGES = 4;

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

        $shippingCost = StoreSetting::query()->findOrFail(1)->shipping_cost;

        return view('admin.user.config', compact('roles', 'categorias', 'homepageImages', 'shippingCost'));
    }

    public function updateShippingCost(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_cost' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:99999999.99'],
        ]);

        StoreSetting::query()->findOrFail(1)->update([
            'shipping_cost' => $validated['shipping_cost'],
        ]);

        return to_route('config.index')->with('status', 'Costo de envío actualizado correctamente.');
    }

    public function storeHomepageImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1843'],
        ], [
            'image.uploaded' => 'El servidor rechazó la imagen. Reduce su tamaño a menos de 1.8 MB e inténtalo de nuevo.',
            'image.max' => 'La imagen no debe superar los 1.8 MB.',
        ]);

        $result = DB::transaction(function () use ($validated): array {
            $images = DB::table('homepage_images')->lockForUpdate();

            if ($images->count() >= self::MAX_HOMEPAGE_IMAGES) {
                return ['error' => 'Solo puedes guardar hasta 4 imágenes de inicio. Elimina una para subir otra.'];
            }

            $path = $validated['image']->store('fondos-inicio', 'public');

            if ($path === false) {
                return ['error' => 'No se pudo guardar la imagen. Inténtalo de nuevo.'];
            }

            $images->update(['is_active' => false, 'updated_at' => now()]);
            DB::table('homepage_images')->insert([
                'path' => $path,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return ['path' => $path];
        });

        if (isset($result['error'])) {
            return to_route('config.index')->with('error', $result['error']);
        }

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

    public function destroyHomepageImage(int $homepageImage): RedirectResponse
    {
        $result = DB::transaction(function () use ($homepageImage): array {
            $image = DB::table('homepage_images')->where('id', $homepageImage)->lockForUpdate()->first();

            if ($image === null) {
                abort(404);
            }

            if (DB::table('homepage_images')->count() <= 1) {
                return ['error' => 'Debes conservar al menos una imagen de inicio.'];
            }

            DB::table('homepage_images')->where('id', $homepageImage)->delete();

            if ($image->is_active) {
                $nextImage = DB::table('homepage_images')->orderByDesc('id')->first();

                DB::table('homepage_images')->where('id', $nextImage->id)->update([
                    'is_active' => true,
                    'updated_at' => now(),
                ]);
            }

            return ['path' => $image->path];
        });

        if (isset($result['error'])) {
            return to_route('config.index')->with('error', $result['error']);
        }

        if (! str_starts_with($result['path'], 'images/') && ! Storage::disk('public')->delete($result['path'])) {
            return to_route('config.index')->with('error', 'La imagen se quitó de la galería, pero no se pudo borrar el archivo almacenado.');
        }

        return to_route('config.index')->with('status', 'Imagen eliminada correctamente.');
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', 'unique:rol,nombre'],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

        if ($this->isProtectedRoleName($validated['nombre'])) {
            return to_route('config.index')->with('error', 'No puedes crear roles llamados Cliente o ADMINISTRADOR.');
        }

        Rol::create($validated);

        return to_route('config.index')->with('status', 'Rol creado correctamente.');
    }

    public function updateRole(Request $request, Rol $rol): RedirectResponse
    {
        if ($rol->isProtected()) {
            $validated = $request->validate([
                'descripcion' => ['nullable', 'string', 'max:65535'],
            ]);

            $rol->update($validated);

            return to_route('config.index')->with('status', 'Rol actualizado correctamente.');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:30', 'regex:/^[\pL\s]+$/u', Rule::unique('rol', 'nombre')->ignore($rol->id_rol, 'id_rol')],
            'descripcion' => ['nullable', 'string', 'max:65535'],
        ], ['nombre.regex' => 'El nombre solo debe contener letras y espacios.']);

        if ($this->isProtectedRoleName($validated['nombre'])) {
            return to_route('config.index')->with('error', 'No puedes crear ni renombrar roles como Cliente o ADMINISTRADOR.');
        }

        $rol->update($validated);

        return to_route('config.index')->with('status', 'Rol actualizado correctamente.');
    }

    public function destroyRole(Rol $rol): RedirectResponse
    {
        if ($rol->isProtected()) {
            return to_route('config.index')->with('error', 'El rol Cliente y el rol ADMINISTRADOR no se pueden eliminar.');
        }

        try {
            $rol->delete();
        } catch (QueryException) {
            return to_route('config.index')->with('error', 'No puedes eliminar un rol que tiene usuarios asignados.');
        }

        return to_route('config.index')->with('status', 'Rol eliminado correctamente.');
    }

    private function isProtectedRoleName(string $name): bool
    {
        $name = mb_strtoupper(trim($name));

        return in_array($name, ['CLIENTE', 'ADMINISTRADOR'], true);
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
